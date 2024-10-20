<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use Article;
use Content;
use MediaWiki\Context\IContextSource;
use MediaWiki\Hook\EditFilterMergedContentHook;
use MediaWiki\Html\Html;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\Page\Hook\ArticleViewHeaderHook;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\SpecialPage\SpecialPage;
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
class Hooks implements ArticleViewHeaderHook, EditFilterMergedContentHook, PageSaveCompleteHook {
	public const CONSTRUCTOR_OPTIONS = [
		'TranslateEnableMessageBundleIntegration',
	];

	private static self $instance;
	private LoggerInterface $logger;
	private MessageBundleStore $messageBundleStore;
	private WANObjectCache $WANObjectCache;
	private LinkRenderer $linkRenderer;
	private bool $enableIntegration;

	public function __construct(
		LoggerInterface $logger,
		WANObjectCache $WANObjectCache,
		MessageBundleStore $messageBundleStore,
		LinkRenderer $linkRenderer,
		bool $enableIntegration
	) {
		$this->logger = $logger;
		$this->WANObjectCache = $WANObjectCache;
		$this->messageBundleStore = $messageBundleStore;
		$this->linkRenderer = $linkRenderer;
		$this->enableIntegration = $enableIntegration;
	}

	public static function getInstance(): self {
		$services = MediaWikiServices::getInstance();
		self::$instance ??= new self(
			LoggerFactory::getInstance( 'Translate.MessageBundle' ),
			$services->getMainWANObjectCache(),
			$services->get( 'Translate:MessageBundleStore' ),
			$services->getLinkRenderer(),
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

	/**
	 * Hook: ArticleViewHeader
	 *
	 * @param Article $article
	 * @param bool|ParserOutput|null &$outputDone
	 * @param bool &$pcache
	 */
	public function onArticleViewHeader( $article, &$outputDone, &$pcache ) {
		if ( !$this->enableIntegration ) {
			return;
		}

		$articleTitle = $article->getTitle();
		if ( MessageBundle::isSourcePage( $articleTitle ) ) {
			$messageBundle = new MessageBundle( $articleTitle );
			$context = $article->getContext();
			$language = $context->getLanguage();

			$translateLink = $this->linkRenderer->makeKnownLink(
				SpecialPage::getTitleFor( 'Translate' ),
				$context->msg( 'translate-tag-translate-mb-link-desc' )->text(),
				[],
				[
					'group' => $messageBundle->getMessageGroupId(),
					'action' => 'page',
					'filter' => '',
				]
			);
			$header = Html::rawElement(
				'div',
				[
					'class' => 'mw-mb-translate-header noprint nomobile',
					'dir' => $language->getDir(),
					'lang' => $language->getHtmlCode(),
				],
				$translateLink
			);

			$output = $context->getOutput();
			$output->addHTML( $header );
			$output->addModuleStyles( 'ext.translate' );
		}
	}
}
