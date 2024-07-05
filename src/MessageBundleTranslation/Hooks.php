<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use Content;
use IContextSource;
use MediaWiki\Hook\EditFilterMergedContentHook;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Status\Status;
use MediaWiki\Storage\Hook\PageSaveCompleteHook;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use Psr\Log\LoggerInterface;
use WANObjectCache;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2021.05
 */
class Hooks implements EditFilterMergedContentHook, PageSaveCompleteHook {
	public const CONSTRUCTOR_OPTIONS = [
		'TranslateEnableMessageBundleIntegration',
	];

	private static self $instance;
	private LoggerInterface $logger;
	private MessageBundleStore $messageBundleStore;
	private WANObjectCache $WANObjectCache;
	private bool $enableIntegration;

	public function __construct(
		LoggerInterface $logger,
		WANObjectCache $WANObjectCache,
		MessageBundleStore $messageBundleStore,
		bool $enableIntegration
	) {
		$this->logger = $logger;
		$this->WANObjectCache = $WANObjectCache;
		$this->messageBundleStore = $messageBundleStore;
		$this->enableIntegration = $enableIntegration;
	}

	public static function getInstance(): self {
		$services = MediaWikiServices::getInstance();
		self::$instance ??= new self(
			LoggerFactory::getInstance( 'Translate.MessageBundle' ),
			$services->getMainWANObjectCache(),
			$services->get( 'Translate:MessageBundleStore' ),
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
				// Validation is performed in the store because injecting services into the
				// Content class is not straightforward
				$this->messageBundleStore->validate( $context->getTitle(), $content );
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
		$pageTitle = $wikiPage->getTitle();

		if ( $content === null ) {
			$this->logger->debug( "Unable to access content of page {pageName} in $method", [
				'pageName' => $pageTitle->getPrefixedText()
			] );
			return;
		}

		if ( !$content instanceof MessageBundleContent ) {
			return;
		}

		try {
			$this->messageBundleStore->save( $pageTitle, $revisionRecord, $content );
		} catch ( MalformedBundle $e ) {
			// This should not happen, as it should not be possible to save a page with invalid content
			$this->logger->warning( "Page {pageName} is not a valid message bundle in $method", [
				'pageName' => $pageTitle->getPrefixedText(),
				'exception' => $e,
			] );
			return;
		}
	}

	/** Hook: CodeEditorGetPageLanguage */
	public static function onCodeEditorGetPageLanguage( Title $title, ?string &$lang, string $model ) {
		if ( $model === MessageBundleContent::CONTENT_MODEL_ID ) {
			$lang = 'json';
		}
	}
}
