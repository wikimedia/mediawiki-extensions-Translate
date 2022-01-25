<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use Content;
use IContextSource;
use JobQueueGroup;
use MediaWiki\Hook\EditFilterMergedContentHook;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Storage\Hook\PageSaveCompleteHook;
use MessageGroupWANCache;
use Psr\Log\LoggerInterface;
use Status;
use TranslateUtils;
use User;
use WANObjectCache;
use Wikimedia\Rdbms\ILoadBalancer;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2021.05
 */
class Hooks implements EditFilterMergedContentHook, PageSaveCompleteHook {
	public const CONSTRUCTOR_OPTIONS = [
		'TranslateEnableMessageBundleIntegration',
	];

	/** @var ?self */
	private static $instance;
	/** @var LoggerInterface */
	private $logger;
	/** @var ILoadBalancer */
	private $loadBalancer;
	/** @var WANObjectCache */
	private $WANObjectCache;
	/** @var JobQueueGroup */
	private $jobQueue;
	/** @var bool */
	private $enableIntegration;

	public function __construct(
		LoggerInterface $logger,
		ILoadBalancer $loadBalancer,
		WANObjectCache $WANObjectCache,
		JobQueueGroup $jobQueue,
		bool $enableIntegration
	) {
		$this->logger = $logger;
		$this->loadBalancer = $loadBalancer;
		$this->WANObjectCache = $WANObjectCache;
		$this->jobQueue = $jobQueue;
		$this->enableIntegration = $enableIntegration;
	}

	public static function getInstance(): self {
		$services = MediaWikiServices::getInstance();
		self::$instance = self::$instance ??
			new self(
				LoggerFactory::getInstance( 'Translate.MessageBundle' ),
				$services->getDBLoadBalancer(),
				$services->getMainWANObjectCache(),
				// BC <= MW 1.36 (use service when it exists)
				TranslateUtils::getJobQueueGroup(),
				$services->getMainConfig()->get( 'TranslateEnableMessageBundleIntegration' )
			);
		return self::$instance;
	}

	/** @inheritDoc */
	public function onEditFilterMergedContent(
		IContextSource $context,
		Content $content,
		Status $status,
		$summary,
		User $user,
		$minoredit
	): void {
		if ( $content instanceof MessageBundleContent ) {
			try {
				$content->getMessages();
			} catch ( MalformedBundle $e ) {
				// MalformedBundle implements MessageSpecifier, but for unknown reason it gets
				// cast to a string if we don't convert it to a proper message.
				$status->fatal( 'translate-messagebundle-validation-error', $context->msg( $e ) );
			}
		}
	}

	/** @inheritDoc */
	public function onPageSaveComplete(
		$wikiPage,
		$user,
		$summary,
		$flags,
		$revisionRecord,
		$editResult
	): void {
		if ( !$this->enableIntegration ) {
			return;
		}

		$method = __METHOD__;
		$content = $revisionRecord->getContent( SlotRecord::MAIN );

		if ( $content === null ) {
			$this->logger->debug( "Unable to access content of page {pageName} in $method", [
				'pageName' => $wikiPage->getTitle()->getPrefixedText()
			] );
			return;
		}

		if ( !$content instanceof MessageBundleContent ) {
			return;
		}

		try {
			$messages = $content->getMessages();
		} catch ( MalformedBundle $e ) {
			// This should not happen, as it should not be possible to save a page with invalid content
			$this->logger->warning( "Page {pageName} is not a valid message bundle in $method", [
				'pageName' => $wikiPage->getTitle()->getPrefixedText(),
				'exception' => $e,
			] );
			return;
		}

		// Update mb:ready in revtag as appropriate (remove or change revision)
		$dbw = $this->loadBalancer->getConnectionRef( DB_PRIMARY );

		$previousRevisionId = $dbw->selectField(
			'revtag',
			'rt_revision',
			[
				'rt_page' => $wikiPage->getId(),
				'rt_type' => 'mb:ready',
			]
		);
		// Convert to correct type
		$previousRevisionId = $previousRevisionId ? (int)$previousRevisionId : null;

		$deleteConditions = [
			'rt_page' => $wikiPage->getId(),
			'rt_type' => 'mb:ready',
		];
		if ( $previousRevisionId !== null ) {
			$dbw->delete( 'revtag', $deleteConditions, __METHOD__ );
		}

		if ( $messages ) {
			// Bundle is valid and contains translatable messages
			$insertConditions = $deleteConditions;
			$insertConditions['rt_revision'] = $revisionRecord->getId();
			$dbw->insert( 'revtag', $insertConditions, __METHOD__ );
			// Defer most of the heavy work to the job queue
			$job = UpdateMessageBundleJob::newJob(
				$wikiPage->getTitle(),
				$revisionRecord->getId(),
				$previousRevisionId
			);

			$this->jobQueue->push( $job );
		}

		// What should we do if there are no messages? Use the previous version? Remove the group?
		// Currently, the bundle is removed from translation.
	}

	/** Hook: TranslateInitGroupLoaders */
	public static function onTranslateInitGroupLoaders( array &$groupLoader ): void {
		self::getInstance()->onTranslateInitGroupLoadersImpl( $groupLoader );
	}

	public function onTranslateInitGroupLoadersImpl( array &$groupLoader ): void {
		if ( !$this->enableIntegration ) {
			return;
		}

		$groupLoader[] = new MessageBundleMessageGroupLoader(
			$this->loadBalancer->getConnectionRef( DB_REPLICA ),
			new MessageGroupWANCache( $this->WANObjectCache )
		);
	}
}
