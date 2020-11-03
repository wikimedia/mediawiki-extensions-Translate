<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use Content;
use IContextSource;
use MediaWiki\Hook\EditFilterMergedContentHook;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Storage\Hook\PageSaveCompleteHook;
use Psr\Log\LoggerInterface;
use Status;
use User;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2021.05
 */
class Hooks implements EditFilterMergedContentHook, PageSaveCompleteHook {
	/** @var ?self */
	private static $instance;
	/** @var LoggerInterface */
	private $logger;

	public function __construct( LoggerInterface $logger ) {
		$this->logger = $logger;
	}

	public static function getInstance(): self {
		self::$instance = self::$instance ?? new self( LoggerFactory::getInstance( 'Translate.MessageBundle' ) );
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
		if ( !$content instanceof MessageBundleContent ) {
			return;
		}
		/** @var MessageBundleContent $content */
		try {
			$content->validate();
		} catch ( MalformedBundle $e ) {
			// MalformedBundle implements MessageSpecifier, but for unknown reason it gets
			// casted to a string if we don't convert it to a proper message.
			$status->fatal( 'translate-messagebundle-validation-error', $context->msg( $e ) );
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

		/** @var MessageBundleContent $content */
		try {
			$content->validate();
		} catch ( MalformedBundle $e ) {
			$this->logger->warning( "Page {pageName} is not a valid message bundle", [
				'pageName' => $wikiPage->getTitle()->getPrefixedText(),
				'exception' => $e,
			] );
			return;
		}

		// We have a valid content here
		// TODO: Implement registration as a message group
	}
}
