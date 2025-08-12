<?php

namespace MediaWiki\Extension\Translate\PageTranslation;

use Article;
use Exception;
use ManualLogEntry;
use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Config\Config;
use MediaWiki\Content\Content;
use MediaWiki\Content\TextContent;
use MediaWiki\Context\IContextSource;
use MediaWiki\Context\RequestContext;
use MediaWiki\Deferred\DeferredUpdates;
use MediaWiki\Deferred\LinksUpdate\LinksUpdate;
use MediaWiki\Extension\Translate\LogNames;
use MediaWiki\Extension\Translate\MessageBundleTranslation\MessageBundleMessageGroup;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Statistics\MessageGroupStats;
use MediaWiki\Extension\Translate\Statistics\RebuildMessageGroupStatsJob;
use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Html\Html;
use MediaWiki\Language\Language;
use MediaWiki\Language\LanguageCode;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\Linker\LinkTarget;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MainConfigNames;
use MediaWiki\MediaWikiServices;
use MediaWiki\Output\OutputPage;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Page\PageReference;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Parser\PPFrame;
use MediaWiki\ResourceLoader\Context;
use MediaWiki\Revision\MutableRevisionRecord;
use MediaWiki\Revision\RenderedRevision;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Status\Status;
use MediaWiki\Storage\EditResult;
use MediaWiki\StubObject\StubUserLang;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;
use Skin;
use UserBlockedError;
use Wikimedia\Rdbms\IDBAccessObject;
use Wikimedia\ScopedCallback;
use WikiPage;
use WikiPageMessageGroup;

/**
 * Hooks for page translation.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup PageTranslation
 */
class Hooks {
	private const PAGEPROP_HAS_LANGUAGES_TAG = 'translate-has-languages-tag';
	/** @var bool Uuugly hacks */
	public static $allowTargetEdit = false;
	/** State flag used by DeleteTranslatableBundleJob for performance optimizations. */
	public static bool $isDeleteTranslatableBundleJobRunning = false;
	/** @var bool Check if we are just rendering tags or such */
	public static $renderingContext = false;
	/** @var array Used to communicate data between LanguageLinks and SkinTemplateGetLanguageLink hooks. */
	private static $languageLinkData = [];

	/**
	 * Hook: ParserBeforeInternalParse
	 * @param Parser $wikitextParser
	 * @param null|string &$text
	 * @param-taint $text escapes_htmlnoent
	 * @param mixed $state
	 */
	public static function renderTagPage( $wikitextParser, &$text, $state ): void {
		if ( $text === null ) {
			// SMW is unhelpfully sending null text if the source contains section tags. Do not explode.
			return;
		}

		self::preprocessTagPage( $wikitextParser, $text, $state );

		// Skip further interface message parsing
		if ( $wikitextParser->getOptions()->getInterfaceMessage() ) {
			return;
		}

		// For section previews, perform additional clean-up, given tags are often
		// unbalanced when we preview one section only.
		if ( $wikitextParser->getOptions()->getIsSectionPreview() ) {
			$translatablePageParser = Services::getInstance()->getTranslatablePageParser();
			$text = $translatablePageParser->cleanupTags( $text );
		}

		// Set display title
		$title = MediaWikiServices::getInstance()
			->getTitleFactory()
			->castFromPageReference( $wikitextParser->getPage() );
		if ( !$title ) {
			return;
		}

		$page = TranslatablePage::isTranslationPage( $title );
		if ( !$page ) {
			return;
		}

		$wikitextParser->getOutput()->setUnsortedPageProperty( 'translate-is-translation' );

		try {
			self::$renderingContext = true;
			[ , $code ] = Utilities::figureMessage( $title->getText() );
			$name = $page->getPageDisplayTitle( $code );
			if ( $name ) {
				$name = $wikitextParser->recursivePreprocess( $name );

				$langConv = MediaWikiServices::getInstance()->getLanguageConverterFactory()
					->getLanguageConverter( $wikitextParser->getTargetLanguage() );
				$name = $langConv->convert( $name );
				$wikitextParser->getOutput()->setDisplayTitle( $name );
			}
			self::$renderingContext = false;
		} catch ( Exception $e ) {
			LoggerFactory::getInstance( LogNames::MAIN )->error(
				'T302754 Failed to set display title for page {title}',
				[
					'title' => $title->getPrefixedDBkey(),
					'text' => $text,
					'pageid' => $title->getId(),
				]
			);

			// Re-throw to preserve behavior
			throw $e;
		}

		$extensionData = [
			'languagecode' => $code,
			'messagegroupid' => $page->getMessageGroupId(),
			'sourcepagetitle' => [
				'namespace' => $page->getTitle()->getNamespace(),
				'dbkey' => $page->getTitle()->getDBkey()
			]
		];

		$wikitextParser->getOutput()->setExtensionData( 'translate-translation-page', $extensionData );
		// Disable edit section links
		$wikitextParser->getOutput()->setExtensionData( 'Translate-noeditsection', true );
	}

	/**
	 * Hook: ParserBeforePreprocess
	 * @param Parser $wikitextParser
	 * @param string &$text
	 * @param-taint $text escapes_htmlnoent
	 * @param mixed $state
	 */
	public static function preprocessTagPage( $wikitextParser, &$text, $state ): void {
		$translatablePageParser = Services::getInstance()->getTranslatablePageParser();

		if ( $translatablePageParser->containsMarkup( $text ) ) {
			try {
				$parserOutput = $translatablePageParser->parse( $text );
				// If parsing succeeds, replace text
				$text = $parserOutput->sourcePageTextForRendering(
					$wikitextParser->getTargetLanguage()
				);
			} catch ( ParsingFailure $e ) {
				wfDebug( 'ParsingFailure caught; expected' );
			}
		} else {
			// If the text doesn't contain <translate> markup, it can still contain <tvar> in the
			// context of a Parsoid template expansion sub-pipeline. We strip these as well.
			$unit = new TranslationUnit( $text );
			$text = $unit->getTextForTrans();
		}
	}

	/**
	 * Hook: ParserOutputPostCacheTransform
	 * @param ParserOutput $out
	 * @param string &$text
	 * @param array &$options
	 */
	public static function onParserOutputPostCacheTransform(
		ParserOutput $out,
		&$text,
		array &$options
	) {
		if ( $out->getExtensionData( 'Translate-noeditsection' ) ) {
			$options['enableSectionEditLinks'] = false;
		}
	}

	/**
	 * This sets &$revRecord to the revision of transcluded page translation if it exists,
	 * or sets it to the source language if the page translation does not exist.
	 * The page translation is chosen based on language of the source page.
	 *
	 * Hook: BeforeParserFetchTemplateRevisionRecord
	 * @param LinkTarget|null $contextLink
	 * @param LinkTarget|null $templateLink
	 * @param bool &$skip
	 * @param RevisionRecord|null &$revRecord
	 */
	public static function fetchTranslatableTemplateAndTitle(
		?LinkTarget $contextLink,
		?LinkTarget $templateLink,
		bool &$skip,
		?RevisionRecord &$revRecord
	): void {
		if ( !$templateLink ) {
			return;
		}

		$templateTitle = Title::newFromLinkTarget( $templateLink );

		$templateTranslationPage = TranslatablePage::isTranslationPage( $templateTitle );
		if ( $templateTranslationPage ) {
			// Template is referring to a translation page, fetch it and incase it doesn't
			// exist, fetch the source fallback.
			$revRecord = $templateTranslationPage->getRevisionRecordWithFallback();
			if ( !$revRecord ) {
				// In rare cases fetching of the source fallback might fail. See: T323863
				LoggerFactory::getInstance( LogNames::MAIN )->warning(
					"T323863: Could not fetch any revision record for '{groupid}'",
					[ 'groupid' => $templateTranslationPage->getMessageGroupId() ]
				);
			}
			return;
		}

		if ( !TranslatablePage::isSourcePage( $templateTitle ) ) {
			return;
		}

		$translatableTemplatePage = TranslatablePage::newFromTitle( $templateTitle );

		if ( !( $translatableTemplatePage->supportsTransclusion() ?? false ) ) {
			// Page being transcluded does not support language aware transclusion
			return;
		}

		$store = MediaWikiServices::getInstance()->getRevisionStore();

		if ( $contextLink ) {
			// Fetch the context page language, and then check if template is present in that language
			$templateTranslationTitle = $templateTitle->getSubpage(
				Title::newFromLinkTarget( $contextLink )->getPageLanguage()->getCode()
			 );

			if ( $templateTranslationTitle ) {
				if ( $templateTranslationTitle->exists() ) {
					// Template is present in the context page language, fetch the revision record and return
					$revRecord = $store->getRevisionByTitle( $templateTranslationTitle );
				} else {
					// In case the template has not been translated to the context page language,
					// we assign a MutableRevisionRecord in order to add a dependency, so that when
					// it is created, the newly created page is loaded rather than the fallback
					$revRecord = new MutableRevisionRecord( $templateTranslationTitle );
				}
				return;
			}
		}

		// Context page information not available OR the template translation title could not be determined.
		// Fetch and return the RevisionRecord of the template in the source language
		$sourceTemplateTitle = $templateTitle->getSubpage(
			$translatableTemplatePage->getMessageGroup()->getSourceLanguage()
		);
		if ( $sourceTemplateTitle && $sourceTemplateTitle->exists() ) {
			$revRecord = $store->getRevisionByTitle( $sourceTemplateTitle );
		}
	}

	/**
	 * Set the right page content language for translated pages ("Page/xx").
	 * Hook: PageContentLanguage
	 * @param Title $title
	 * @param Language|StubUserLang|string &$pageLang
	 */
	public static function onPageContentLanguage( Title $title, &$pageLang ) {
		// For translation pages, parse plural, grammar etc. with correct language,
		// and set the right direction
		if ( TranslatablePage::isTranslationPage( $title ) ) {
			[ , $code ] = Utilities::figureMessage( $title->getText() );
			$pageLang = MediaWikiServices::getInstance()->getLanguageFactory()->getLanguage( $code );
		}
	}

	/**
	 * Display an edit notice for translatable source pages if it's enabled
	 * Hook: TitleGetEditNotices
	 * @param Title $title
	 * @param int $oldid
	 * @param array &$notices
	 */
	public static function onTitleGetEditNotices( Title $title, int $oldid, array &$notices ) {
		if ( TranslatablePage::isSourcePage( $title ) ) {
			$msg = wfMessage( 'translate-edit-tag-warning' )->inContentLanguage();
			if ( !$msg->isDisabled() ) {
				$notices['translate-tag'] = $msg->parseAsBlock();
			}

			$notices[] = Html::warningBox(
				wfMessage( 'tps-edit-sourcepage-text' )->parse(),
				'translate-edit-documentation'
			);

			// The check is "we're using visual editor for WYSIWYG" (as opposed to "for wikitext
			// edition") - the message will not be displayed in that case.
			$request = RequestContext::getMain()->getRequest();
			if ( $request->getVal( 'action' ) === 'visualeditor' &&
				$request->getVal( 'paction' ) !== 'wikitext'
			) {
				$notices[] = Html::warningBox(
					wfMessage( 'tps-edit-sourcepage-ve-warning-limited-text' )->parse(),
					'translate-edit-documentation'
				);
			}
		}
	}

	/**
	 * Hook: BeforePageDisplay
	 * @param OutputPage $out
	 * @param Skin $skin
	 */
	public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
		global $wgTranslatePageTranslationULS;

		$title = $out->getTitle();
		$isSource = TranslatablePage::isSourcePage( $title );
		$isTranslation = TranslatablePage::isTranslationPage( $title );

		if ( $isSource || $isTranslation ) {
			if ( $wgTranslatePageTranslationULS ) {
				$out->addModules( 'ext.translate.pagetranslation.uls' );
			}

			if ( $isSource ) {
				// Adding a help notice
				$out->addModuleStyles( 'ext.translate.edit.documentation.styles' );
			}

			$out->addModuleStyles( 'ext.translate' );

			$out->addJsConfigVars( 'wgTranslatePageTranslation', $isTranslation ? 'translation' : 'source' );
		}
	}

	/**
	 * Hook: onVisualEditorBeforeEditor
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return bool
	 */
	public static function onVisualEditorBeforeEditor( OutputPage $out, Skin $skin ) {
		return !TranslatablePage::isTranslationPage( $out->getTitle() );
	}

	/**
	 * This is triggered after an edit to translation unit page
	 * @param WikiPage $wikiPage
	 * @param User $user
	 * @param TextContent $content
	 * @param string $summary
	 * @param bool $minor
	 * @param int $flags
	 * @param MessageHandle $handle
	 */
	public static function onSectionSave(
		WikiPage $wikiPage,
		User $user,
		TextContent $content,
		$summary,
		$minor,
		$flags,
		MessageHandle $handle
	) {
		// FuzzyBot may do some duplicate work already worked on by other jobs
		if ( $user->equals( FuzzyBot::getUser() ) ) {
			return;
		}

		$group = $handle->getGroup();
		if ( !$group instanceof WikiPageMessageGroup ) {
			return;
		}

		// Finally we know the title and can construct a Translatable page
		$page = TranslatablePage::newFromTitle( $group->getTitle() );

		// Update the target translation page
		if ( !$handle->isDoc() ) {
			$code = $handle->getCode();
			DeferredUpdates::addCallableUpdate(
				function () use ( $page, $code, $user, $flags, $summary, $handle ) {
					$unitTitle = $handle->getTitle();
					self::updateTranslationPage( $page, $code, $user, $flags, $summary, null, $unitTitle );
				}
			);
		}
	}

	private static function updateTranslationPage(
		TranslatablePage $page,
		string $code,
		User $user,
		int $flags,
		string $summary,
		?string $triggerAction = null,
		?Title $unitTitle = null
	): void {
		$source = $page->getTitle();
		$target = $source->getSubpage( $code );
		$mwInstance = MediaWikiServices::getInstance();

		// We don't know and don't care
		$flags &= ~EDIT_NEW & ~EDIT_UPDATE;

		// Update the target page
		$unitTitleText = $unitTitle ? $unitTitle->getPrefixedText() : null;
		$job = RenderTranslationPageJob::newJob( $target, $triggerAction, $unitTitleText );
		$session = null;
		if ( !$user->equals( FuzzyBot::getUser() ) ) {
			$session = RequestContext::getMain()->exportSession();
		}
		$job->setUser( $user, $session );
		$job->setSummary( $summary );
		$job->setFlags( $flags );
		$mwInstance->getJobQueueGroup()->push( $job );

		// Invalidate caches so that language bar is up-to-date
		$pages = $page->getTranslationPages();
		$wikiPageFactory = $mwInstance->getWikiPageFactory();
		foreach ( $pages as $title ) {
			if ( $title->equals( $target ) ) {
				// Handled by the RenderTranslationPageJob
				continue;
			}

			$wikiPage = $wikiPageFactory->newFromTitle( $title );
			$wikiPage->doPurge();
		}
		$sourceWikiPage = $wikiPageFactory->newFromTitle( $source );
		$sourceWikiPage->doPurge();
	}

	/**
	 * Hook: GetMagicVariableIDs
	 * @param string[] &$variableIDs
	 */
	public static function onGetMagicVariableIDs( &$variableIDs ): void {
		$variableIDs[] = 'translatablepage';
	}

	/**
	 * Hook: ParserGetVariableValueSwitch
	 */
	public static function onParserGetVariableValueSwitch(
		Parser $parser,
		array &$variableCache,
		string $magicWordId,
		?string &$ret,
		PPFrame $frame
	): void {
		switch ( $magicWordId ) {
			case 'translatablepage':
				$pageStatus = self::getTranslatablePageStatus( $parser->getPage() );
				$ret = $pageStatus !== null ? $pageStatus['page']->getTitle()->getPrefixedText() : '';
				$variableCache[$magicWordId] = $ret;
				break;
		}
	}

	/**
	 * @param string $data
	 * @param array $params
	 * @param Parser $parser
	 * @return string
	 */
	public static function languages( $data, $params, $parser ) {
		global $wgPageTranslationLanguageList;

		if ( $wgPageTranslationLanguageList === 'sidebar-only' ) {
			return '';
		}

		self::$renderingContext = true;
		$context = new ScopedCallback( static function () {
			self::$renderingContext = false;
		} );

		// Store a property that we can avoid adding language links when
		// $wgPageTranslationLanguageList === 'sidebar-fallback'
		$parser->getOutput()->setUnsortedPageProperty( self::PAGEPROP_HAS_LANGUAGES_TAG );

		$currentPage = $parser->getPage();
		$pageStatus = self::getTranslatablePageStatus( $currentPage );
		if ( !$pageStatus ) {
			return '';
		}

		$page = $pageStatus[ 'page' ];
		$status = $pageStatus[ 'languages' ];
		$pageTitle = $page->getTitle();

		// Sort by language code, which seems to be the only sane method
		ksort( $status );

		// This way the parser knows to fragment the parser cache by language code
		$userLang = $parser->getOptions()->getUserLangObj();
		$userLangCode = $userLang->getCode();
		// Should call $page->getMessageGroup()->getSourceLanguage(), but
		// group is sometimes null on WMF during page moves, reason unknown.
		// This should do the same thing for now.
		$sourceLanguage = $pageTitle->getPageLanguage()->getCode();

		$languages = [];
		$langFactory = MediaWikiServices::getInstance()->getLanguageFactory();
		foreach ( $status as $code => $percent ) {
			// Get autonyms (null)
			$name = Utilities::getLanguageName( $code, LanguageNameUtils::AUTONYMS );

			// Add links to other languages
			$suffix = ( $code === $sourceLanguage ) ? '' : "/$code";
			$targetTitleString = $pageTitle->getDBkey() . $suffix;
			$subpage = Title::makeTitle( $pageTitle->getNamespace(), $targetTitleString );

			$classes = [];
			if ( $code === $userLangCode ) {
				$classes[] = 'mw-pt-languages-ui';
			}

			$linker = $parser->getLinkRenderer();
			$lang = $langFactory->getLanguage( $code );
			if ( $currentPage->isSamePageAs( $subpage ) ) {
				$classes[] = 'mw-pt-languages-selected';
				$classes = array_merge( $classes, self::tpProgressIcon( (float)$percent ) );
				$attribs = [
					'class' => $classes,
					'lang' => $lang->getHtmlCode(),
					'dir' => $lang->getDir(),
				];

				$contents = Html::element( 'span', $attribs, $name );
			} elseif ( $subpage->isKnown() ) {
				$pagename = $page->getPageDisplayTitle( $code );
				if ( !is_string( $pagename ) ) {
					$pagename = $subpage->getPrefixedText();
				}

				$classes = array_merge( $classes, self::tpProgressIcon( (float)$percent ) );

				$title = wfMessage( 'tpt-languages-nonzero' )
					->page( $parser->getPage() )
					->inLanguage( $userLang )
					->params( $pagename )
					->numParams( 100 * $percent )
					->text();
				$attribs = [
					'title' => $title,
					'class' => $classes,
					'lang' => $lang->getHtmlCode(),
					'dir' => $lang->getDir(),
				];

				$contents = $linker->makeKnownLink( $subpage, $name, $attribs );
			} else {
				/* When language is included because it is a priority language,
				 * but translations don't exist link directly to the
				 * translation view. */
				$specialTranslateTitle = SpecialPage::getTitleFor( 'Translate' );
				$params = [
					'group' => $page->getMessageGroupId(),
					'language' => $code,
					'task' => 'view'
				];

				$classes[] = 'new'; // For red link color

				$attribs = [
					'title' => wfMessage( 'tpt-languages-zero' )
						->page( $parser->getPage() )
						->inLanguage( $userLang )
						->text(),
					'class' => $classes,
					'lang' => $lang->getHtmlCode(),
					'dir' => $lang->getDir(),
				];
				$contents = $linker->makeKnownLink( $specialTranslateTitle, $name, $attribs, $params );
			}
			$languages[ $name ] = Html::rawElement( 'li', [], $contents );
		}

		// Sort languages by autonym
		ksort( $languages );
		$languages = array_values( $languages );
		$languages = implode( "\n", $languages );

		$out = Html::openElement( 'div', [
			'class' => 'mw-pt-languages noprint navigation-not-searchable',
			'lang' => $userLang->getHtmlCode(),
			'dir' => $userLang->getDir()
		] );
		$out .= Html::rawElement( 'div', [ 'class' => 'mw-pt-languages-label' ],
			wfMessage( 'tpt-languages-legend' )
				->page( $parser->getPage() )
				->inLanguage( $userLang )
				->escaped()
		);
		$out .= Html::rawElement(
			'ul',
			[ 'class' => 'mw-pt-languages-list' ],
			$languages
		);
		$out .= Html::closeElement( 'div' );

		$parser->getOutput()->addModuleStyles( [
			'ext.translate.tag.languages',
		] );

		return $out;
	}

	/**
	 * Return icon CSS class for given progress status: percentages
	 * are too accurate and take more space than simple images.
	 * @param float $percent
	 * @return string[]
	 */
	private static function tpProgressIcon( float $percent ) {
		$classes = [ 'mw-pt-progress' ];
		$percent *= 100;
		if ( $percent < 15 ) {
			$classes[] = 'mw-pt-progress--low';
		} elseif ( $percent < 70 ) {
			$classes[] = 'mw-pt-progress--med';
		} elseif ( $percent < 100 ) {
			$classes[] = 'mw-pt-progress--high';
		} else {
			$classes[] = 'mw-pt-progress--complete';
		}
		return $classes;
	}

	/**
	 * Returns translatable page and language stats for the given page.
	 * @return array{page:TranslatablePage,languages:array}|null Returns null if not a translatable page.
	 */
	private static function getTranslatablePageStatus( ?PageReference $pageReference ): ?array {
		if ( $pageReference === null ) {
			return null;
		}
		$title = Title::newFromPageReference( $pageReference );
		// Check if this is a source page or a translation page
		$page = TranslatablePage::newFromTitle( $title );
		if ( $page->getMarkedTag() === null ) {
			$page = TranslatablePage::isTranslationPage( $title );
		}

		if ( $page === false || $page->getMarkedTag() === null ) {
			return null;
		}

		$status = $page->getTranslationPercentages();
		if ( !$status ) {
			return null;
		}

		$messageGroupMetadata = Services::getInstance()->getMessageGroupMetadata();
		// If priority languages have been set, always show those languages
		$priorityLanguages = $messageGroupMetadata->get( $page->getMessageGroupId(), 'prioritylangs' );
		if ( $priorityLanguages !== null && $priorityLanguages !== '' ) {
			$status += array_fill_keys( explode( ',', $priorityLanguages ), 0 );
		}

		return [
			'page' => $page,
			'languages' => $status
		];
	}

	/**
	 * Hooks: LanguageLinks
	 * @param Title $title Title of the page for which links are needed.
	 * @param array &$languageLinks List of language links to modify.
	 */
	public static function addLanguageLinks( Title $title, array &$languageLinks ) {
		global $wgPageTranslationLanguageList;

		if ( $wgPageTranslationLanguageList === 'tag-only' ) {
			return;
		}

		if ( $wgPageTranslationLanguageList === 'sidebar-fallback' ) {
			$pageProps = MediaWikiServices::getInstance()->getPageProps();
			$languageProp = $pageProps->getProperties( $title, self::PAGEPROP_HAS_LANGUAGES_TAG );
			if ( $languageProp !== [] ) {
				return;
			}
		}

		// $wgPageTranslationLanguageList === 'sidebar-always' OR 'sidebar-only'

		$status = self::getTranslatablePageStatus( $title );
		if ( !$status ) {
			return;
		}

		self::$renderingContext = true;
		$context = new ScopedCallback( static function () {
			self::$renderingContext = false;
		} );

		$page = $status[ 'page' ];
		$languages = $status[ 'languages' ];
		$mwServices = MediaWikiServices::getInstance();
		$en = $mwServices->getLanguageFactory()->getLanguage( 'en' );

		// Batch the Title::exists queries used below
		$lb = $mwServices->getLinkBatchFactory()->newLinkBatch();
		foreach ( array_keys( $languages ) as $code ) {
			$title = $page->getTitle()->getSubpage( $code );
			$lb->addObj( $title );
		}
		$lb->execute();
		$languageNameUtils = $mwServices->getLanguageNameUtils();
		foreach ( $languages as $code => $percentage ) {
			$title = $page->getTitle()->getSubpage( $code );
			$placeholderValue = "$code-x-pagetranslation:{$title->getPrefixedText()}";
			$translatedName = $page->getPageDisplayTitle( $code ) ?: $title->getPrefixedText();

			if ( $title->exists() ) {
				$href = $title->getLocalURL();
				$classes = self::tpProgressIcon( (float)$percentage );
				$titleAttribute = wfMessage( 'tpt-languages-nonzero' )
					->params( $translatedName )
					->numParams( 100 * $percentage );
			} else {
				$href = SpecialPage::getTitleFor( 'Translate' )->getLocalURL( [
					'group' => $page->getMessageGroupId(),
					'language' => $code,
				] );
				$classes = [ 'mw-pt-progress--none' ];
				$titleAttribute = wfMessage( 'tpt-languages-zero' );
			}

			self::$languageLinkData[ $placeholderValue ] = [
				'href' => $href,
				'language' => $code,
				'classes' => $classes,
				'autonym' => $en->ucfirst( $languageNameUtils->getLanguageName( $code ) ),
				'title' => $titleAttribute,
			];

			// Insert a placeholder which we will then fix up in SkinTemplateGetLanguageLink hook handler
			$languageLinks[] = $placeholderValue;
		}
	}

	/**
	 * Hooks: SkinTemplateGetLanguageLink
	 * @param array &$link
	 * @param Title $linkTitle
	 * @param Title $pageTitle
	 * @param OutputPage $out
	 */
	public static function formatLanguageLink(
		array &$link,
		Title $linkTitle,
		Title $pageTitle,
		OutputPage $out
	) {
		$data = self::$languageLinkData[$link['text']] ?? null;
		if ( !$data ) {
			return;
		}

		$link['class'] .= ' interwiki-pagetranslation ' . implode( ' ', $data['classes'] );
		$link['href'] = $data['href'];
		$link['text'] = $data['autonym'];
		$link['title'] = $data['title']->inLanguage( $out->getLanguage()->getCode() )->text();
		$link['lang'] = LanguageCode::bcp47( $data['language'] );
		$link['hreflang'] = LanguageCode::bcp47( $data['language'] );

		$out->addModuleStyles( 'ext.translate.tag.languages' );
	}

	/**
	 * Display nice error when editing content.
	 * Hook: EditFilterMergedContent
	 * @param IContextSource $context
	 * @param Content $content
	 * @param Status $status
	 * @param string $summary
	 * @return bool
	 */
	public static function tpSyntaxCheckForEditContent(
		$context,
		$content,
		$status,
		$summary
	) {
		$syntaxErrorStatus = self::tpSyntaxError( $context->getTitle(), $content );

		if ( $syntaxErrorStatus ) {
			$status->merge( $syntaxErrorStatus );
			return $syntaxErrorStatus->isGood();
		}

		return true;
	}

	private static function tpSyntaxError( ?PageIdentity $page, ?Content $content ): ?Status {
		if ( !$page || !self::isAllowedContentModel( $content, $page ) ) {
			return null;
		}

		'@phan-var TextContent $content';
		$text = $content->getText();

		// See T154500
		$text = TextContent::normalizeLineEndings( $text );
		$status = Status::newGood();
		$parser = Services::getInstance()->getTranslatablePageParser();
		if ( $parser->containsMarkup( $text ) ) {
			try {
				$parser->parse( $text );
			} catch ( ParsingFailure $e ) {
				$status->fatal( $e->getMessageSpecification() );
			}
		}

		return $status;
	}

	/**
	 * When attempting to save, last resort. Edit page would only display
	 * edit conflict if there wasn't tpSyntaxCheckForEditPage.
	 * Hook: MultiContentSave
	 * @param RenderedRevision $renderedRevision
	 * @param UserIdentity $user
	 * @param CommentStoreComment $summary
	 * @param int $flags
	 * @param Status $hookStatus
	 * @return bool
	 */
	public static function tpSyntaxCheck(
		RenderedRevision $renderedRevision,
		UserIdentity $user,
		CommentStoreComment $summary,
		$flags,
		Status $hookStatus
	) {
		$content = $renderedRevision->getRevision()->getContent( SlotRecord::MAIN );

		$status = self::tpSyntaxError(
			$renderedRevision->getRevision()->getPage(),
			$content
		);

		if ( $status ) {
			$hookStatus->merge( $status );
			return $status->isGood();
		}

		return true;
	}

	/**
	 * Hook: PageSaveComplete
	 *
	 * @param WikiPage $wikiPage
	 * @param UserIdentity $userIdentity
	 * @param string $summary
	 * @param int $flags
	 * @param RevisionRecord $revisionRecord
	 * @param EditResult $editResult
	 */
	public static function addTranstagAfterSave(
		WikiPage $wikiPage,
		UserIdentity $userIdentity,
		string $summary,
		int $flags,
		RevisionRecord $revisionRecord,
		EditResult $editResult
	) {
		$content = $wikiPage->getContent();

		// Only allow translating configured content models (T360544)
		if ( !self::isAllowedContentModel( $content, $wikiPage ) ) {
			return;
		}

		'@phan-var TextContent $content';
		$text = $content->getText();

		$parser = Services::getInstance()->getTranslatablePageParser();
		if ( $parser->containsMarkup( $text ) ) {
			// Add the ready tag
			$page = TranslatablePage::newFromTitle( $wikiPage->getTitle() );
			$page->addReadyTag( $revisionRecord->getId() );
		}

		// Schedule a deferred status update for the translatable page.
		$tpStatusUpdater = Services::getInstance()->getTranslatablePageStore();
		$tpStatusUpdater->performStatusUpdate( $wikiPage->getTitle() );
	}

	/**
	 * Page moving and page protection (and possibly other things) creates null
	 * revisions. These revisions re-use the previous text already stored in
	 * the database. Those however do not trigger re-parsing of the page and
	 * thus the ready tag is not updated. This watches for new revisions,
	 * checks if they reuse existing text, checks whether the parent version
	 * is the latest version and has a ready tag. If that is the case,
	 * also adds a ready tag for the new revision (which is safe, because
	 * the text hasn't changed). The interface will say that there has been
	 * a change, but shows no change in the content. This lets the user to
	 * re-mark the translations of the page title as outdated (if enabled
	 * for translation).
	 * Hook: RevisionRecordInserted
	 * @param RevisionRecord $rev
	 */
	public static function updateTranstagOnNullRevisions( RevisionRecord $rev ) {
		$parentId = $rev->getParentId();
		if ( $parentId === 0 || $parentId === null ) {
			// No parent, bail out.
			return;
		}

		$prevRev = MediaWikiServices::getInstance()
			->getRevisionLookup()
			->getRevisionById( $parentId );

		if ( !$prevRev || !$rev->hasSameContent( $prevRev ) ) {
			// Not a null revision, bail out.
			return;
		}

		$title = Title::newFromLinkTarget( $rev->getPageAsLinkTarget() );
		$bundleFactory = Services::getInstance()->getTranslatableBundleFactory();
		$bundle = $bundleFactory->getBundle( $title );

		if ( $bundle ) {
			$bundleStore = $bundleFactory->getStore( $bundle );
			$bundleStore->handleNullRevisionInsert( $bundle, $rev );
		}
	}

	/**
	 * Prevent creation of orphan translation units in Translations namespace.
	 * Prevent editing of translation units relating to the source language (these should only be touched by FuzzyBot)
	 * Prevent editing of translation units relating to a page if you're blocked from that page
	 * Prevent editing of translation units relating to a language that the page isn't allowed to be translated into.
	 * Hook: getUserPermissionsErrorsExpensive
	 *
	 * @param Title $title
	 * @param User $user
	 * @param string $action
	 * @param mixed &$result
	 * @return bool
	 */
	public static function onGetUserPermissionsErrorsExpensive(
		Title $title,
		User $user,
		$action,
		&$result
	) {
		$handle = new MessageHandle( $title );

		if ( !$handle->isPageTranslation() || $action === 'read' ) {
			return true;
		}

		$isValid = true;
		$groupId = null;

		if ( $handle->isValid() ) {
			$group = $handle->getGroup();
			$groupId = $group->getId();
			$permissionTitleCheck = null;

			if ( $group instanceof WikiPageMessageGroup ) {
				$permissionTitleCheck = $group->getTitle();
			} elseif ( $group instanceof MessageBundleMessageGroup ) {
				// TODO: This check for MessageBundle related permission should be in
				// the MessageBundleTranslation/Hook
				$permissionTitleCheck = Title::newFromID( $group->getBundlePageId() );
			}

			if ( $permissionTitleCheck ) {
				if ( $handle->getCode() === $group->getSourceLanguage() && !$user->equals( FuzzyBot::getUser() ) ) {
					// Allow the same set of actions allowed for translation pages - in particular
					// if something bad somehow gets marked for translation, deleting
					// revisions everywhere should be possible without deliberately
					// invalidating the unit
					$allowedActionList = [
						'read', 'deletedtext', 'deletedhistory',
						'deleterevision', 'suppressrevision', 'viewsuppressed', // T286884
						'review', // FlaggedRevs
						'patrol', // T151172
					];
					if ( !in_array( $action, $allowedActionList ) ) {
						$result = [ 'tpt-cant-edit-source-language', $permissionTitleCheck ];
						return false;
					}
				}
				// Check for blocks
				$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
				if ( $permissionManager->isBlockedFrom( $user, $permissionTitleCheck ) ) {
					$block = $user->getBlock();
					if ( $block ) {
						$error = new UserBlockedError( $block, $user );
						$errorMessage = $error->getMessageObject();
						$result = array_merge( [ $errorMessage->getKey() ], $errorMessage->getParams() );
						return false;
					}
				}
			}
		}

		// Allow editing units that become orphaned in regular use, so that
		// people can delete them or fix links or other issues in them.
		if ( $action !== 'create' ) {
			return true;
		}

		if ( !$handle->isValid() ) {
			// TODO: These checks may no longer be needed
			// Sometimes the message index can be out of date. Either the rebuild job failed or
			// it just hasn't finished yet. Do a secondary check to make sure we are not
			// inconveniencing translators for no good reason.
			// See https://phabricator.wikimedia.org/T221119
			$translatablePage = self::checkTranslatablePageSlow( $title );
			MediaWikiServices::getInstance()->getStatsFactory()
				->withComponent( 'Translate' )
				->getCounter( 'slow_translatable_page_check' )
				->setLabel( 'valid', $translatablePage ? 'yes' : 'no' )
				->increment();

			if ( $translatablePage ) {
				$groupId = $translatablePage->getMessageGroupId();
			} else {
				$isValid = false;
			}
		}

		if ( $isValid ) {
			$error = self::getTranslationRestrictions( $handle, $groupId );
			$result = $error ?: $result;
			return $error === [];
		}

		// Don't allow editing invalid messages that do not belong to any translatable page
		LoggerFactory::getInstance( LogNames::MAIN )->info(
			'Unknown translation page: {title}',
			[ 'title' => $title->getPrefixedDBkey() ]
		);
		$result = [ 'tpt-unknown-page' ];
		return false;
	}

	private static function checkTranslatablePageSlow( LinkTarget $unit ): ?TranslatablePage {
		$parts = TranslatablePage::parseTranslationUnit( $unit );
		$translationPageTitle = Title::newFromText(
			$parts[ 'sourcepage' ] . '/' . $parts[ 'language' ]
		);
		if ( !$translationPageTitle ) {
			return null;
		}

		$translatablePage = TranslatablePage::isTranslationPage( $translationPageTitle );
		if ( !$translatablePage ) {
			return null;
		}

		$factory = Services::getInstance()->getTranslationUnitStoreFactory();
		$store = $factory->getReader( $translatablePage->getTitle() );
		$units = $store->getNames();

		if ( !in_array( $parts[ 'section' ], $units ) ) {
			return null;
		}

		return $translatablePage;
	}

	/**
	 * Prevent editing of restricted languages when prioritized.
	 *
	 * @param MessageHandle $handle
	 * @param string $groupId
	 * @return array array containing error message if restricted, empty otherwise
	 */
	private static function getTranslationRestrictions( MessageHandle $handle, $groupId ) {
		global $wgTranslateDocumentationLanguageCode;

		// Allow adding message documentation even when translation is restricted
		if ( $handle->getCode() === $wgTranslateDocumentationLanguageCode ) {
			return [];
		}

		$messageGroupMetadata = Services::getInstance()->getMessageGroupMetadata();
		// Check if anything is prevented for the group in the first place
		$force = $messageGroupMetadata->get( $groupId, 'priorityforce' );
		if ( $force !== 'on' ) {
			return [];
		}

		// And finally check whether the language is in the inclusion list
		$languages = $messageGroupMetadata->get( $groupId, 'prioritylangs' );
		$reason = $messageGroupMetadata->get( $groupId, 'priorityreason' );
		if ( !$languages ) {
			if ( $reason ) {
				return [ 'tpt-translation-restricted-no-priority-languages', $reason ];
			}
			return [ 'tpt-translation-restricted-no-priority-languages-no-reason' ];
		}

		$filter = array_flip( explode( ',', $languages ) );
		if ( !isset( $filter[$handle->getCode()] ) ) {
			if ( $reason ) {
				return [ 'tpt-translation-restricted', $reason ];
			}

			return [ 'tpt-translation-restricted-no-reason' ];
		}

		return [];
	}

	/**
	 * Prevent editing of translation pages directly.
	 * Hook: getUserPermissionsErrorsExpensive
	 * @param Title $title
	 * @param User $user
	 * @param string $action
	 * @param bool &$result
	 * @return bool
	 */
	public static function preventDirectEditing( Title $title, User $user, $action, &$result ) {
		if ( self::$allowTargetEdit ) {
			return true;
		}

		$inclusionList = [
			'read', 'deletedtext', 'deletedhistory',
			'deleterevision', 'suppressrevision', 'viewsuppressed', // T286884
			'review', // FlaggedRevs
			'patrol', // T151172
		];
		$needsPageTranslationRight = in_array( $action, [ 'delete', 'undelete' ] );
		if ( in_array( $action, $inclusionList ) ||
			( $needsPageTranslationRight && $user->isAllowed( 'pagetranslation' ) )
		) {
			return true;
		}

		$page = TranslatablePage::isTranslationPage( $title );
		if ( $page !== false && $page->getMarkedTag() ) {
			$mwService = MediaWikiServices::getInstance();
			if ( $needsPageTranslationRight ) {
				$context = RequestContext::getMain();
				$statusFormatter = $mwService->getFormatterFactory()->getStatusFormatter( $context );
				$permissionError = $mwService->getPermissionManager()
					->newFatalPermissionDeniedStatus( 'pagetranslation', $context );
				$result = $statusFormatter->getMessage( $permissionError );
				return false;
			}

			[ , $code ] = Utilities::figureMessage( $title->getText() );

			$translationUrl = $mwService->getUrlUtils()->expand(
				$page->getTranslationUrl( $code ), PROTO_RELATIVE
			);

			$result = [
				'tpt-target-page',
				':' . $page->getTitle()->getPrefixedText(),
				// This url shouldn't get cached
				$translationUrl
			];

			return false;
		}

		return true;
	}

	/**
	 * Redirects the delete action to our own for translatable pages.
	 * Hook: ArticleConfirmDelete
	 *
	 * @param Article $article
	 * @param OutputPage $out
	 * @param string &$reason
	 *
	 * @return bool
	 */
	public static function disableDelete( $article, $out, &$reason ) {
		$title = $article->getTitle();
		$bundle = Services::getInstance()->getTranslatableBundleFactory()->getBundle( $title );
		$isDeletableBundle = $bundle && $bundle->isDeletable();
		if ( $isDeletableBundle || TranslatablePage::isTranslationPage( $title ) ) {
			$new = SpecialPage::getTitleFor(
				'PageTranslationDeletePage',
				$title->getPrefixedText()
			);
			$out->redirect( $new->getFullURL() );
		}

		return true;
	}

	/**
	 * Hook: ArticleViewHeader
	 *
	 * @param Article $article
	 * @param bool|ParserOutput|null &$outputDone
	 * @param bool &$pcache
	 */
	public static function translatablePageHeader( $article, &$outputDone, &$pcache ) {
		if ( $article->getOldID() ) {
			return;
		}

		$articleTitle = $article->getTitle();
		$transPage = TranslatablePage::isTranslationPage( $articleTitle );
		$context = $article->getContext();
		if ( $transPage ) {
			self::translationPageHeader( $context, $transPage );
		} else {
			$viewTranslatablePage = Services::getInstance()->getTranslatablePageView();
			$user = $context->getUser();
			if ( $viewTranslatablePage->canDisplayTranslationSettingsBanner( $articleTitle, $user ) ) {
				$output = $context->getOutput();
				$pageUrl = SpecialPage::getTitleFor( 'PageTranslation' )->getFullURL( [
					'do' => 'settings',
					'target' => $articleTitle->getPrefixedDBkey(),
				] );
				$output->addHTML(
					Html::noticeBox(
						$context->msg( 'pt-cta-mark-translation', $pageUrl )->parse(),
						'translate-cta-pt-mark'
					)
				);
			} else {
				self::sourcePageHeader( $context );
			}
		}
	}

	private static function sourcePageHeader( IContextSource $context ) {
		$linker = MediaWikiServices::getInstance()->getLinkRenderer();

		$language = $context->getLanguage();
		$title = $context->getTitle();

		$page = TranslatablePage::newFromTitle( $title );

		$marked = $page->getMarkedTag();
		$ready = $page->getReadyTag();
		$latest = $title->getLatestRevID();

		$actions = [];
		if ( $marked && $context->getUser()->isAllowed( 'translate' ) ) {
			$actions[] = self::getTranslateLink( $context, $page, null );
		}

		$hasChanges = $ready === $latest && $marked !== $latest;
		if ( $hasChanges ) {
			$diffUrl = $title->getFullURL( [ 'oldid' => $marked, 'diff' => $latest ] );

			if ( $context->getUser()->isAllowed( 'pagetranslation' ) ) {
				$pageTranslation = SpecialPage::getTitleFor( 'PageTranslation' );
				$params = [ 'target' => $title->getPrefixedText(), 'do' => 'mark' ];

				if ( $marked === null ) {
					// This page has never been marked
					$linkDesc = $context->msg( 'translate-tag-markthis' )->text();
					$actions[] = $linker->makeKnownLink( $pageTranslation, $linkDesc, [], $params );
				} else {
					$markUrl = $pageTranslation->getFullURL( $params );
					$actions[] = $context->msg( 'translate-tag-markthisagain', $diffUrl, $markUrl )
						->parse();
				}
			} else {
				$actions[] = $context->msg( 'translate-tag-hasnew', $diffUrl )->parse();
			}
		}

		if ( !count( $actions ) ) {
			return;
		}

		$header = Html::rawElement(
			'div',
			[
				'class' => 'mw-pt-translate-header noprint nomobile',
				'dir' => $language->getDir(),
				'lang' => $language->getHtmlCode(),
			],
			$language->semicolonList( $actions )
		);

		$context->getOutput()->addHTML( $header );
	}

	private static function getTranslateLink(
		IContextSource $context,
		TranslatablePage $page,
		?string $langCode
	): string {
		$linker = MediaWikiServices::getInstance()->getLinkRenderer();

		return $linker->makeKnownLink(
				SpecialPage::getTitleFor( 'Translate' ),
				$context->msg( 'translate-tag-translate-link-desc' )->text(),
				[],
				[
					'group' => $page->getMessageGroupId(),
					'language' => $langCode,
					'action' => 'page',
					'filter' => '',
					'action_source' => 'translate_page'
				]
			);
	}

	private static function translationPageHeader( IContextSource $context, TranslatablePage $page ) {
		global $wgTranslateKeepOutdatedTranslations;

		$title = $context->getTitle();
		if ( !$title->exists() ) {
			return;
		}

		[ , $code ] = Utilities::figureMessage( $title->getText() );

		// Get the translation percentage
		$pers = $page->getTranslationPercentages();
		$per = 0;
		if ( isset( $pers[$code] ) ) {
			$per = $pers[$code] * 100;
		}

		$language = $context->getLanguage();
		$output = $context->getOutput();

		if ( $page->getSourceLanguageCode() === $code ) {
			// If we are on the source language page, link to translate for user's language
			$msg = self::getTranslateLink( $context, $page, $language->getCode() );
		} else {
			$mwService = MediaWikiServices::getInstance();

			$translationUrl = $mwService->getUrlUtils()->expand(
				$page->getTranslationUrl( $code ), PROTO_RELATIVE
			);

			$msg = $context->msg( 'tpt-translation-intro',
				$translationUrl,
				':' . $page->getTitle()->getPrefixedText(),
				$language->formatNum( $per )
			)->parse();
		}

		$header = Html::rawElement(
			'div',
			[
				'class' => 'mw-pt-translate-header noprint',
				'dir' => $language->getDir(),
				'lang' => $language->getHtmlCode(),
			],
			$msg
		);

		$output->addHTML( $header );

		if ( $wgTranslateKeepOutdatedTranslations ) {
			$groupId = $page->getMessageGroupId();
			// This is already calculated and cached by above call to getTranslationPercentages
			$stats = MessageGroupStats::forItem( $groupId, $code );
			if ( $stats[MessageGroupStats::FUZZY] ) {
				// Only show if there is fuzzy messages
				$wrap = Html::rawElement(
					'div',
					[
						'class' => 'mw-pt-translate-header',
						'dir' => $language->getDir(),
						'lang' => $language->getHtmlCode()
					],
					'<span class="mw-translate-fuzzy">$1</span>'
				);

				$output->wrapWikiMsg( $wrap, [ 'tpt-translation-intro-fuzzy' ] );
			}
		}
	}

	private static function isAllowedContentModel( Content $content, PageReference $page ): bool {
		$config = MediaWikiServices::getInstance()->getMainConfig();
		$allowedModels = $config->get( 'PageTranslationAllowedContentModels' );
		$contentModel = $content->getModel();
		$allowed = (bool)( $allowedModels[$contentModel] ?? false );

		// T163254: Disable page translation on non-text pages
		if ( $allowed && !$content instanceof TextContent ) {
			LoggerFactory::getInstance( LogNames::MAIN )->error(
				'Expected {title} to have content of type TextContent, got {contentType}. ' .
				'$wgPageTranslationAllowedContentModels is incorrectly configured with a non-text content model.',
				[
					'title' => (string)$page,
					'contentType' => get_class( $content )
				]
			);
			return false;
		}

		return $allowed;
	}

	/**
	 * Hook: SpecialPage_initList
	 * @param array &$list
	 */
	public static function replaceMovePage( &$list ) {
		$movePageSpec = $list['Movepage'] ?? null;

		// This should never happen, but apparently is happening? See: T296568
		if ( $movePageSpec === null ) {
			return;
		}

		$list['Movepage'] = [
			'class' => MoveTranslatableBundleSpecialPage::class,
			'services' => [
				'ObjectFactory',
				'PermissionManager',
				'Translate:TranslatableBundleMover',
				'Translate:TranslatableBundleFactory',
				'FormatterFactory'
			],
			'args' => [
				$movePageSpec
			]
		];
	}

	/**
	 * Hook: getUserPermissionsErrorsExpensive
	 * @param Title $title
	 * @param User $user
	 * @param string $action
	 * @param mixed &$result
	 * @return bool
	 */
	public static function lockedPagesCheck( Title $title, User $user, $action, &$result ) {
		if ( $action === 'read' ) {
			return true;
		}

		$cache = MediaWikiServices::getInstance()->getObjectCacheFactory()->getInstance( CACHE_ANYTHING );
		$key = $cache->makeKey( 'pt-lock', sha1( $title->getPrefixedText() ) );
		if ( $cache->get( $key ) === 'locked' ) {
			$result = [ 'pt-locked-page' ];

			return false;
		}

		return true;
	}

	/**
	 * Hook: SkinSubPageSubtitle
	 * @param array &$subpages
	 * @param ?Skin $skin
	 * @param OutputPage $out
	 * @return bool
	 */
	public static function replaceSubtitle( &$subpages, ?Skin $skin, OutputPage $out ) {
		$linker = MediaWikiServices::getInstance()->getLinkRenderer();

		$isTranslationPage = TranslatablePage::isTranslationPage( $out->getTitle() );
		if ( !$isTranslationPage
			&& !TranslatablePage::isSourcePage( $out->getTitle() )
		) {
			return true;
		}

		// Copied from Skin::subPageSubtitle()
		$nsInfo = MediaWikiServices::getInstance()->getNamespaceInfo();
		if (
			$out->isArticle() &&
			$nsInfo->hasSubpages( $out->getTitle()->getNamespace() )
		) {
			$ptext = $out->getTitle()->getPrefixedText();
			$links = explode( '/', $ptext );
			if ( count( $links ) > 1 ) {
				array_pop( $links );
				if ( $isTranslationPage ) {
					// Also remove language code page
					array_pop( $links );
				}
				$c = 0;
				$growinglink = '';
				$display = '';
				$sitedir = $skin->getLanguage()->getDir();

				foreach ( $links as $link ) {
					$growinglink .= $link;
					$display .= $link;
					$linkObj = Title::newFromText( $growinglink );

					if ( $linkObj && $linkObj->isKnown() ) {
						$getlink = $linker->makeKnownLink(
							SpecialPage::getTitleFor( 'MyLanguage', $growinglink ),
							$display
						);

						$c++;

						if ( $c > 1 ) {
							$subpages .= $skin->msg( 'pipe-separator' )->escaped();
						} else {
							$subpages .= '&lt; ';
						}

						$subpages .= Html::rawElement( 'bdi', [ 'dir' => $sitedir ], $getlink );
						$display = '';
					} else {
						$display .= '/';
					}

					$growinglink .= '/';
				}
			}

			return false;
		}

		return true;
	}

	/**
	 * Converts the edit tab (if exists) for translation pages to translate tab.
	 * Hook: SkinTemplateNavigation::Universal
	 * @param Skin $skin
	 * @param array &$tabs
	 */
	public static function translateTab( Skin $skin, array &$tabs ) {
		$title = $skin->getTitle();
		$handle = new MessageHandle( $title );
		$code = $handle->getCode();
		$page = TranslatablePage::isTranslationPage( $title );
		// The source language has a subpage too, but cannot be translated
		if ( !$page || $page->getSourceLanguageCode() === $code ) {
			return;
		}

		$user = $skin->getUser();
		if ( isset( $tabs['views']['edit'] ) ) {
			// There is an edit tab, just replace its text and URL with ours, keeping the tooltip and access key
			$tabs['views']['edit']['text'] = $skin->msg( 'tpt-tab-translate' )->text();
			$tabs['views']['edit']['href'] = $page->getTranslationUrl( $code );
		} elseif ( $user->isAllowed( 'translate' ) ) {
			$mwInstance = MediaWikiServices::getInstance();
			$namespaceProtection = $mwInstance->getMainConfig()->get( MainConfigNames::NamespaceProtection );
			$permissionManager = $mwInstance->getPermissionManager();
			if (
				!$permissionManager->userHasAllRights(
					$user, ...(array)( $namespaceProtection[ NS_TRANSLATIONS ] ?? [] )
				)
			) {
				return;
			}

			$tab = [
				'text' => $skin->msg( 'tpt-tab-translate' )->text(),
				'href' => $page->getTranslationUrl( $code ),
			];

			// Get the position of the viewsource tab within the array (if any)
			$viewsourcePos = array_keys( array_keys( $tabs['views'] ), 'viewsource', true )[0] ?? null;

			if ( $viewsourcePos !== null ) {
				// Remove the viewsource tab and insert the translate tab at its place. Showing the tooltip
				// of the viewsource tab for the translate tab would be confusing.
				array_splice( $tabs['views'], $viewsourcePos, 1, [ 'translate' => $tab ] );
			} else {
				// We have neither an edit tab nor a viewsource tab to replace with the translate tab,
				// put the translate tab at the end
				$tabs['views']['translate'] = $tab;
			}
		}
	}

	/**
	 * Hook to update source and destination translation pages on moving translation units
	 * Hook: PageMoveComplete
	 *
	 * @param LinkTarget $oldLinkTarget
	 * @param LinkTarget $newLinkTarget
	 * @param UserIdentity $userIdentity
	 * @param int $oldid
	 * @param int $newid
	 * @param string $reason
	 * @param RevisionRecord $revisionRecord
	 */
	public static function onMovePageTranslationUnits(
		LinkTarget $oldLinkTarget,
		LinkTarget $newLinkTarget,
		UserIdentity $userIdentity,
		int $oldid,
		int $newid,
		string $reason,
		RevisionRecord $revisionRecord
	) {
		$user = MediaWikiServices::getInstance()->getUserFactory()->newFromUserIdentity( $userIdentity );
		// MoveTranslatableBundleJob takes care of handling updates because it performs
		// a lot of moves at once. As a performance optimization, skip this hook if
		// we detect moves from that job. As there isn't a good way to pass information
		// to this hook what originated the move, we use some heuristics.
		if ( defined( 'MEDIAWIKI_JOB_RUNNER' ) && $user->equals( FuzzyBot::getUser() ) ) {
			return;
		}

		$oldTitle = Title::newFromLinkTarget( $oldLinkTarget );
		$newTitle = Title::newFromLinkTarget( $newLinkTarget );
		$groupLast = null;
		foreach ( [ $oldTitle, $newTitle ] as $title ) {
			$handle = new MessageHandle( $title );
			// Documentation pages are never translation pages
			if ( !$handle->isValid() || $handle->isDoc() ) {
				continue;
			}

			$group = $handle->getGroup();
			if ( !$group instanceof WikiPageMessageGroup ) {
				continue;
			}

			$language = $handle->getCode();

			// Ignore pages such as Translations:Page/unit without language code
			if ( $language === '' ) {
				continue;
			}

			// Update the page only once if source and destination units
			// belong to the same page
			if ( $group !== $groupLast ) {
				$groupLast = $group;
				$page = TranslatablePage::newFromTitle( $group->getTitle() );
				self::updateTranslationPage( $page, $language, $user, 0, $reason );
			}
		}
	}

	/**
	 * Hook to update translation page on deleting a translation unit
	 * Hook: ArticleDeleteComplete
	 * @param WikiPage $unit
	 * @param User $user
	 * @param string $reason
	 * @param int $id
	 * @param Content $content
	 * @param ManualLogEntry $logEntry
	 */
	public static function onDeleteTranslationUnit(
		WikiPage $unit,
		User $user,
		$reason,
		$id,
		$content,
		$logEntry
	) {
		$title = $unit->getTitle();
		$handle = new MessageHandle( $title );
		if ( !$handle->isValid() ) {
			return;
		}

		$group = $handle->getGroup();
		if ( !$group instanceof WikiPageMessageGroup ) {
			return;
		}

		$mwServices = MediaWikiServices::getInstance();
		// During deletions this may cause creation of a lot of duplicate jobs. It is expected that
		// job queue will deduplicate them to reduce the number of jobs actually run.
		$mwServices->getJobQueueGroup()->push(
			RebuildMessageGroupStatsJob::newRefreshGroupsJob( [ $group->getId() ] )
		);

		// Logic to update translation pages, skipped if we are in a middle of a deletion
		if ( self::$isDeleteTranslatableBundleJobRunning ) {
			return;
		}

		$target = $group->getTitle();
		$langCode = $handle->getCode();
		$fname = __METHOD__;

		$dbw = $mwServices->getConnectionProvider()->getPrimaryDatabase();
		$callback = function () use (
			$dbw,
			$target,
			$handle,
			$langCode,
			$user,
			$reason,
			$fname
		) {
			$translationPageTitle = $target->getSubpage( $langCode );
			// Do a more thorough check for the translation page in case the translation page is deleted in a
			// different transaction.
			if ( !$translationPageTitle || !$translationPageTitle->exists( IDBAccessObject::READ_LATEST ) ) {
				return;
			}

			$dbw->startAtomic( $fname );

			$page = TranslatablePage::newFromTitle( $target );

			if ( !$handle->isDoc() ) {
				$unitTitle = $handle->getTitle();
				// Assume that $user and $reason for the first deletion is the same for all
				self::updateTranslationPage(
					$page, $langCode, $user, 0, $reason, RenderTranslationPageJob::ACTION_DELETE, $unitTitle
				);
			}

			$dbw->endAtomic( $fname );
		};

		$dbw->onTransactionCommitOrIdle( $callback, __METHOD__ );
	}

	/**
	 * Removes translation pages from the list of page titles to be edited
	 * Hook: ReplaceTextFilterPageTitlesForEdit
	 */
	public static function onReplaceTextFilterPageTitlesForEdit( array &$titles ): void {
		foreach ( $titles as $index => $title ) {
			$handle = new MessageHandle( $title );
			if ( Utilities::isTranslationPage( $handle ) ) {
				unset( $titles[ $index ] );
			}
		}
	}

	/**
	 * Removes translatable and translation pages from the list of titles to be renamed
	 * Hook: ReplaceTextFilterPageTitlesForRename
	 */
	public static function onReplaceTextFilterPageTitlesForRename( array &$titles ): void {
		foreach ( $titles as $index => $title ) {
			$handle = new MessageHandle( $title );
			if (
				TranslatablePage::isSourcePage( $title ) ||
				Utilities::isTranslationPage( $handle )
			) {
				unset( $titles[ $index ] );
			}
		}
	}

	public static function getSpecialManageMessageGroupSubscriptionsLink(
		Context $context,
		Config $config
	): array {
		return [
			'pagelink' => SpecialPage::getTitleFor( 'ManageMessageGroupSubscriptions' )->getPrefixedText()
		];
	}

	/**
	 * Create any redlinked categories marked for translation
	 * Hook: LinksUpdateComplete
	 */
	public static function onLinksUpdateComplete( LinksUpdate $linksUpdate ) {
		$handle = new MessageHandle( $linksUpdate->getTitle() );
		if ( !Utilities::isTranslationPage( $handle ) ) {
			return;
		}
		$code = $handle->getCode();
		$categories = $linksUpdate->getParserOutput()->getCategoryNames();
		$editSummary = wfMessage(
			'translate-category-summary',
			$linksUpdate->getTitle()->getPrefixedText()
		)->inContentLanguage()->text();
		foreach ( $categories as $category ) {
			$categoryTitle = Title::makeTitle( NS_CATEGORY, $category );
			$categoryHandle = new MessageHandle( $categoryTitle );
			// Only create categories for the same language code to reduce
			// the potential for very deep recursion if a category is
			// a member of itself in a different language
			$categoryTranslationPage = TranslatablePage::isTranslationPage( $categoryTitle );
			if (
				$categoryTranslationPage
				&& $categoryHandle->getCode() == $code
				&& !$categoryTitle->exists()
			) {
				self::updateTranslationPage(
					$categoryTranslationPage,
					$code,
					FuzzyBot::getUser(),
					EDIT_FORCE_BOT,
					$editSummary,
					RenderTranslationPageJob::ACTION_CATEGORIZATION
				);
			}
		}
	}
}
