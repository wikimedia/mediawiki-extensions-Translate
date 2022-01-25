<?php
/**
 * Contains class with page translation feature hooks.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\PageTranslation\MoveTranslatablePageSpecialPage;
use MediaWiki\Extension\Translate\PageTranslation\ParsingFailure;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\Linker\LinkTarget;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\MutableRevisionRecord;
use MediaWiki\Revision\RenderedRevision;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Storage\EditResult;
use MediaWiki\User\UserIdentity;
use Wikimedia\ScopedCallback;

/**
 * Hooks for page translation.
 *
 * @ingroup PageTranslation
 */
class PageTranslationHooks {
	// Uuugly hacks
	public static $allowTargetEdit = false;
	// Check if job queue is running
	public static $jobQueueRunning = false;
	// Check if we are just rendering tags or such
	public static $renderingContext = false;
	// Used to communicate data between LanguageLinks and SkinTemplateGetLanguageLink hooks.
	private static $languageLinkData = [];

	/**
	 * Hook: ParserBeforeInternalParse
	 *
	 * @param Parser $wikitextParser
	 * @param string &$text
	 * @param-taint $text escapes_htmlnoent
	 * @param string $state
	 */
	public static function renderTagPage( $wikitextParser, &$text, $state ): void {
		$translatablePageParser = Services::getInstance()->getTranslatablePageParser();

		if ( $text === null ) {
			// SMW is unhelpfully sending null text if source contains section tags. Do not explode.
			return;
		}

		if ( $translatablePageParser->containsMarkup( $text ) ) {
			try {
				$parserOutput = $translatablePageParser->parse( $text );
				// If parsing succeeds, replace text and add styles
				$text = $parserOutput->sourcePageTextForRendering(
					$wikitextParser->getTargetLanguage()
				);
				$wikitextParser->getOutput()->addModuleStyles( [
					'ext.translate',
				] );
			} catch ( ParsingFailure $e ) {
				wfDebug( 'ParsingFailure caught; expected' );
			}
		}

		// For section previews, perform additional clean-up, given tags are often
		// unbalanced when we preview one section only.
		if ( $wikitextParser->getOptions()->getIsSectionPreview() ) {
			$text = $translatablePageParser->cleanupTags( $text );
		}

		// Set display title
		$title = $wikitextParser->getTitle();
		$page = TranslatablePage::isTranslationPage( $title );
		if ( !$page ) {
			return;
		}

		self::$renderingContext = true;
		[ , $code ] = TranslateUtils::figureMessage( $title->getText() );
		$name = $page->getPageDisplayTitle( $code );
		if ( $name ) {
			$name = $wikitextParser->recursivePreprocess( $name );

			$langConv = MediaWikiServices::getInstance()->getLanguageConverterFactory()
				->getLanguageConverter( $wikitextParser->getTargetLanguage() );
			$name = $langConv->convert( $name );
			$wikitextParser->getOutput()->setDisplayTitle( $name );
		}
		self::$renderingContext = false;

		$extensionData = [
			'languagecode' => $code,
			'messagegroupid' => $page->getMessageGroupId()
		];
		// Backwards-compatibility. If SemanticMediaWiki is installed, write the whole
		// Title object since prior to https://github.com/SemanticMediaWiki/SemanticMediaWiki/pull/4869
		// SMW could only understand it. To be removed after SMW release.
		if ( ExtensionRegistry::getInstance()->isLoaded( 'SemanticMediaWiki' ) ) {
			$extensionData['sourcepagetitle'] = $page->getTitle();
		} else {
			$extensionData['sourcepagetitle'] = [
				'namespace' => $page->getTitle()->getNamespace(),
				'dbkey' => $page->getTitle()->getDBkey()
			];
		}
		$wikitextParser->getOutput()->setExtensionData(
			'translate-translation-page', $extensionData
		);

		// Disable edit section links
		$wikitextParser->getOutput()->setExtensionData( 'Translate-noeditsection', true );
	}

	/**
	 * Hook: ParserOutputPostCacheTransform
	 * @param ParserOutput $out
	 * @param string &$text
	 * @param array &$options
	 */
	public static function onParserOutputPostCacheTransform(
		ParserOutput $out, &$text, array &$options
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

		$templateTitle = Title::castFromLinkTarget( $templateLink );

		$templateTranslationPage = TranslatablePage::isTranslationPage( $templateTitle );
		if ( $templateTranslationPage ) {
			// Template is referring to a translation page, fetch it and incase it doesn't
			// exist, fetch the source fallback
			$revRecord = $templateTranslationPage->getRevisionRecordWithFallback();
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
				Title::castFromLinkTarget( $contextLink )->getPageLanguage()->getCode()
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
	 *
	 * @param Title $title
	 * @param Language|StubUserLang|string &$pageLang
	 * @return true
	 */
	public static function onPageContentLanguage( Title $title, &$pageLang ) {
		// For translation pages, parse plural, grammar etc with correct language,
		// and set the right direction
		if ( TranslatablePage::isTranslationPage( $title ) ) {
			[ , $code ] = TranslateUtils::figureMessage( $title->getText() );
			$pageLang = Language::factory( $code );
		}

		return true;
	}

	/**
	 * Display an edit notice for translatable source pages if it's enabled
	 * Hook: TitleGetEditNotices
	 *
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
		}
	}

	/**
	 * Hook: BeforePageDisplay
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return true
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
				$out->addModules( 'ext.translate.edit.documentation' );
			}

			if ( $isTranslation ) {
				// Source pages get this module via <translate>, but for translation
				// pages we need to add it manually.
				$out->addModuleStyles( 'ext.translate' );
				$out->addJsConfigVars( 'wgTranslatePageTranslation', 'translation' );
			} else {
				$out->addJsConfigVars( 'wgTranslatePageTranslation', 'source' );
			}
		}

		return true;
	}

	/**
	 * This is triggered after saves to translation unit pages
	 * @param WikiPage $wikiPage
	 * @param User $user
	 * @param TextContent $content
	 * @param string $summary
	 * @param bool $minor
	 * @param int $flags
	 * @param MessageHandle $handle
	 * @return true
	 */
	public static function onSectionSave( WikiPage $wikiPage, User $user, TextContent $content,
		$summary, $minor, $flags, MessageHandle $handle
	) {
		// FuzzyBot may do some duplicate work already worked on by other jobs
		if ( $user->equals( FuzzyBot::getUser() ) ) {
			return true;
		}

		$group = $handle->getGroup();
		if ( !$group instanceof WikiPageMessageGroup ) {
			return true;
		}

		// Finally we know the title and can construct a Translatable page
		$page = TranslatablePage::newFromTitle( $group->getTitle() );

		// Update the target translation page
		if ( !$handle->isDoc() ) {
			$code = $handle->getCode();
			DeferredUpdates::addCallableUpdate(
				function () use ( $page, $code, $user, $flags, $summary ) {
					self::updateTranslationPage( $page, $code, $user, $flags, $summary );
				}
			);
		}

		return true;
	}

	public static function updateTranslationPage(
		TranslatablePage $page, $code, $user, $flags, $summary
	) {
		$source = $page->getTitle();
		$target = $source->getSubpage( $code );

		// We don't know and don't care
		$flags &= ~EDIT_NEW & ~EDIT_UPDATE;

		// Update the target page
		$job = TranslateRenderJob::newJob( $target );
		$job->setUser( $user );
		$job->setSummary( $summary );
		$job->setFlags( $flags );
		TranslateUtils::getJobQueueGroup()->push( $job );

		// Invalidate caches so that language bar is up-to-date
		$pages = $page->getTranslationPages();
		foreach ( $pages as $title ) {
			if ( $title->equals( $target ) ) {
				// Handled by the TranslateRenderJob
				continue;
			}

			$wikiPage = WikiPage::factory( $title );
			$wikiPage->doPurge();
		}
		$sourceWikiPage = WikiPage::factory( $source );
		$sourceWikiPage->doPurge();
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

		// Add a dummy language link that is removed in self::addLanguageLinks.
		if ( $wgPageTranslationLanguageList === 'sidebar-fallback' ) {
			$parser->getOutput()->addLanguageLink( 'x-pagetranslation-tag' );
		}

		$currentTitle = $parser->getTitle();
		$pageStatus = self::getTranslatablePageStatus( $currentTitle );
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
			$name = TranslateUtils::getLanguageName( $code, null );

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
			if ( $currentTitle->equals( $subpage ) ) {
				$classes[] = 'mw-pt-languages-selected';
				$classes = array_merge( $classes, self::tpProgressIcon( $percent ) );
				$attribs = [
					'class' => $classes,
					'lang' => $lang->getHtmlCode(),
					'dir' => $lang->getDir(),
				];

				$contents = Html::Element( 'span', $attribs, $name );
			} elseif ( $subpage->isKnown() ) {
				$pagename = $page->getPageDisplayTitle( $code );
				if ( !is_string( $pagename ) ) {
					$pagename = $subpage->getPrefixedText();
				}

				$classes = array_merge( $classes, self::tpProgressIcon( $percent ) );

				$title = wfMessage( 'tpt-languages-nonzero' )
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
				 * but translation does not yet exists, link directly to the
				 * translation view. */
				$specialTranslateTitle = SpecialPage::getTitleFor( 'Translate' );
				$params = [
					'group' => $page->getMessageGroupId(),
					'language' => $code,
					'task' => 'view'
				];

				$classes[] = 'new'; // For red link color

				$attribs = [
					'title' => wfMessage( 'tpt-languages-zero' )->inLanguage( $userLang )->text(),
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
			'class' => 'mw-pt-languages noprint',
			'lang' => $userLang->getHtmlCode(),
			'dir' => $userLang->getDir()
		] );
		$out .= Html::rawElement( 'div', [ 'class' => 'mw-pt-languages-label' ],
			wfMessage( 'tpt-languages-legend' )->inLanguage( $userLang )->escaped()
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
	protected static function tpProgressIcon( $percent ) {
		$classes = [ 'mw-pt-progress' ];
		$percent *= 100;
		if ( $percent < 20 ) {
			$classes[] = 'mw-pt-progress--stub';
		} elseif ( $percent < 40 ) {
			$classes[] = 'mw-pt-progress--low';
		} elseif ( $percent < 60 ) {
			$classes[] = 'mw-pt-progress--med';
		} elseif ( $percent < 80 ) {
			$classes[] = 'mw-pt-progress--high';
		} else {
			$classes[] = 'mw-pt-progress--complete';
		}
		return $classes;
	}

	/**
	 * Returns translatable page and language stats for given title.
	 * @param Title $title
	 * @return array|null Returns null if not a translatable page.
	 */
	private static function getTranslatablePageStatus( Title $title ) {
		// Check if this is a source page or a translation page
		$page = TranslatablePage::newFromTitle( $title );
		if ( $page->getMarkedTag() === false ) {
			$page = TranslatablePage::isTranslationPage( $title );
		}

		if ( $page === false || $page->getMarkedTag() === false ) {
			return null;
		}

		$status = $page->getTranslationPercentages();
		if ( !$status ) {
			return null;
		}

		// If priority languages have been set always show those languages
		$priorityLangs = TranslateMetadata::get( $page->getMessageGroupId(), 'prioritylangs' );
		$priorityForce = TranslateMetadata::get( $page->getMessageGroupId(), 'priorityforce' );
		$filter = null;
		if ( strlen( $priorityLangs ) > 0 ) {
			$filter = array_flip( explode( ',', $priorityLangs ) );
		}
		if ( $filter !== null ) {
			// If translation is restricted to some languages, only show them
			if ( $priorityForce === 'on' ) {
				// Do not filter the source language link
				$filter[$page->getMessageGroup()->getSourceLanguage()] = true;
				$status = array_intersect_key( $status, $filter );
			}
			foreach ( $filter as $langCode => $value ) {
				if ( !isset( $status[$langCode] ) ) {
					// We need to show all priority languages even if no translation started
					$status[$langCode] = 0;
				}
			}
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

		$hasLanguagesTag = false;
		foreach ( $languageLinks as $index => $name ) {
			if ( $name === 'x-pagetranslation-tag' ) {
				$hasLanguagesTag = true;
				unset( $languageLinks[ $index ] );
			}
		}

		if ( $wgPageTranslationLanguageList === 'tag-only' ) {
			return;
		}

		if ( $wgPageTranslationLanguageList === 'sidebar-fallback' && $hasLanguagesTag ) {
			return;
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
		$en = Language::factory( 'en' );

		$newLanguageLinks = [];

		// Batch the Title::exists queries used below
		$lb = new LinkBatch();
		foreach ( array_keys( $languages ) as $code ) {
			$title = $page->getTitle()->getSubpage( $code );
			$lb->addObj( $title );
		}
		$lb->execute();

		foreach ( $languages as $code => $percentage ) {
			$title = $page->getTitle()->getSubpage( $code );
			$key = "x-pagetranslation:{$title->getPrefixedText()}";
			$translatedName = $page->getPageDisplayTitle( $code ) ?: $title->getPrefixedText();

			if ( $title->exists() ) {
				$href = $title->getLocalURL();
				$classes = self::tpProgressIcon( $percentage );
				$title = wfMessage( 'tpt-languages-nonzero' )
					->params( $translatedName )
					->numParams( 100 * $percentage );
			} else {
				$href = SpecialPage::getTitleFor( 'Translate' )->getLocalURL( [
					'group' => $page->getMessageGroupId(),
					'language' => $code,
				] );
				$classes = [ 'mw-pt-progress--none' ];
				$title = wfMessage( 'tpt-languages-zero' );
			}

			self::$languageLinkData[ $key ] = [
				'href' => $href,
				'language' => $code,
				'percentage' => $percentage,
				'classes' => $classes,
				'autonym' => $en->ucfirst( Language::fetchLanguageName( $code ) ),
				'title' => $title,
			];

			$newLanguageLinks[ $key ] = self::$languageLinkData[ $key ][ 'autonym' ];
		}

		asort( $newLanguageLinks );
		$languageLinks = array_merge( array_keys( $newLanguageLinks ), $languageLinks );
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
		if ( substr( $link[ 'text' ], 0, 18 ) !== 'x-pagetranslation:' ) {
			return;
		}

		if ( !isset( self::$languageLinkData[ $link[ 'text' ] ] ) ) {
			return;
		}

		$data = self::$languageLinkData[ $link[ 'text' ] ];

		$link[ 'class' ] .= ' ' . implode( ' ', $data[ 'classes' ] );
		$link[ 'href' ] = $data[ 'href' ];
		$link[ 'text' ] = $data[ 'autonym' ];
		$link[ 'title' ] = $data[ 'title' ]->inLanguage( $out->getLanguage()->getCode() )->text();
		$link[ 'lang'] = LanguageCode::bcp47( $data[ 'language' ] );
		$link[ 'hreflang'] = LanguageCode::bcp47( $data[ 'language' ] );

		$out->addModuleStyles( 'ext.translate.tag.languages' );
	}

	/**
	 * Display nice error when editing content.
	 * Hook: EditFilterMergedContent
	 * @param IContextSource $context
	 * @param Content $content
	 * @param Status $status
	 * @param string $summary
	 * @return true
	 */
	public static function tpSyntaxCheckForEditContent( $context, $content, $status, $summary ) {
		$e = self::tpSyntaxError( $context->getTitle(), $content );

		if ( $e ) {
			$msg = $e->getMsg();
			// $msg is an array containing a message key followed by any parameters.
			// @todo Use Message object instead.

			call_user_func_array( [ $status, 'fatal' ], $msg );
			// @todo Remove this line after this extension do not support mediawiki version 1.36 and before
			$status->value = EditPage::AS_HOOK_ERROR_EXPECTED;
			return false;
		}

		return true;
	}

	/**
	 * @param mixed|null $title Should be ?PageIdentity once the extension is MW 1.36+
	 * @param Content|null $content
	 * @return TPException|null
	 */
	protected static function tpSyntaxError( $title, ?Content $content ): ?TPException {
		// T163254: Ignore translation markup on non-wikitext pages
		if ( !$content instanceof WikitextContent || !$title ) {
			return null;
		}

		$text = $content->getText();

		// See T154500
		$text = str_replace( [ "\r\n", "\r" ], "\n", rtrim( $text ) );

		$exception = null;
		$parser = Services::getInstance()->getTranslatablePageParser();
		if ( $parser->containsMarkup( $text ) ) {
			try {
				$parser->parse( $text );
			} catch ( ParsingFailure $e ) {
				$exception = new TPException( $e->getMessageSpecification() );
			}
		}

		return $exception;
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
		RenderedRevision $renderedRevision, UserIdentity $user, CommentStoreComment $summary, $flags, Status $hookStatus
	) {
		$content = $renderedRevision->getRevision()->getContent( SlotRecord::MAIN );

		$e = self::tpSyntaxError( $renderedRevision->getRevision()->getPageAsLinkTarget(), $content );
		if ( $e ) {
			call_user_func_array( [ $hookStatus, 'fatal' ], $e->getMsg() );

			return false;
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
	 * @return true
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

		// T163254: Disable page translation on non-wikitext pages
		if ( $content instanceof WikitextContent ) {
			$text = $content->getText();
		} else {
			// Not applicable
			return true;
		}

		$parser = Services::getInstance()->getTranslatablePageParser();
		if ( $parser->containsMarkup( $text ) ) {
			// Add the ready tag
			$page = TranslatablePage::newFromTitle( $wikiPage->getTitle() );
			$page->addReadyTag( $revisionRecord->getId() );
		}

		return true;
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
	 * @since 2012-05-08
	 * @param RevisionRecord $rev
	 */
	public static function updateTranstagOnNullRevisions( RevisionRecord $rev ) {
		$parentId = $rev->getParentId();
		if ( $parentId === 0 || $parentId === null ) {
			// No parent, bail out.
			return;
		}

		$prevRev = MediaWikiServices::getInstance()->getRevisionLookup()
			->getRevisionById( $parentId );

		if ( !$prevRev || !$rev->hasSameContent( $prevRev ) ) {
			// Not a null revision, bail out.
			return;
		}

		$title = Title::newFromLinkTarget( $rev->getPageAsLinkTarget() );
		$page = TranslatablePage::newFromTitle( $title );
		if ( $page->getReadyTag() === $parentId ) {
			$page->addReadyTag( $rev->getId() );
		}
	}

	/**
	 * Prevent creation of orphan translation units in Translations namespace.
	 * Hook: getUserPermissionsErrorsExpensive
	 *
	 * @param Title $title
	 * @param User $user
	 * @param string $action
	 * @param mixed &$result
	 * @return bool
	 */
	public static function onGetUserPermissionsErrorsExpensive(
		Title $title, User $user, $action, &$result
	) {
		$handle = new MessageHandle( $title );

		// Check only when someone tries to create translation units.
		// Allow editing units that become orphaned in regular use, so that
		// people can delete them or fix links or other issues in them.
		if ( $action !== 'create' || !$handle->isPageTranslation() ) {
			return true;
		}

		$isValid = true;
		$groupId = null;

		if ( $handle->isValid() ) {
			$groupId = $handle->getGroup()->getId();
		} else {
			// Sometimes the message index can be out of date. Either the rebuild job failed or
			// it just hasn't finished yet. Do a secondary check to make sure we are not
			// inconveniencing translators for no good reason.
			// See https://phabricator.wikimedia.org/T221119
			MediaWikiServices::getInstance()->getStatsdDataFactory()
				->increment( 'translate.slow_translatable_page_check' );
			$translatablePage = self::checkTranslatablePageSlow( $title );
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
		LoggerFactory::getInstance( 'Translate' )->info(
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

		// Check if anything is prevented for the group in the first place
		$force = TranslateMetadata::get( $groupId, 'priorityforce' );
		if ( $force !== 'on' ) {
			return [];
		}

		// And finally check whether the language is in the inclusion list
		$languages = TranslateMetadata::get( $groupId, 'prioritylangs' );
		$filter = array_flip( explode( ',', $languages ) );
		if ( !isset( $filter[$handle->getCode()] ) ) {
			$reason = TranslateMetadata::get( $groupId, 'priorityreason' );
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
			'read', 'delete', 'undelete', 'deletedtext', 'deletedhistory',
			'deleterevision', 'suppressrevision', 'viewsuppressed', // T286884
			'review', // FlaggedRevs
			'patrol', // T151172
		];
		if ( in_array( $action, $inclusionList ) ) {
			return true;
		}

		$page = TranslatablePage::isTranslationPage( $title );
		if ( $page !== false && $page->getMarkedTag() ) {
			[ , $code ] = TranslateUtils::figureMessage( $title->getText() );
			$result = [
				'tpt-target-page',
				':' . $page->getTitle()->getPrefixedText(),
				// This url shouldn't get cached
				wfExpandUrl( $page->getTranslationUrl( $code ) )
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
		$translatablePage = TranslatablePage::newFromTitle( $title );

		if (
			$translatablePage->getMarkedTag() !== false ||
			TranslatablePage::isTranslationPage( $title )
		) {
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
	 * @return bool
	 */
	public static function translatablePageHeader( $article, &$outputDone, &$pcache ) {
		if ( $article->getOldID() ) {
			return true;
		}

		$transPage = TranslatablePage::isTranslationPage( $article->getTitle() );
		$context = $article->getContext();
		if ( $transPage ) {
			self::translationPageHeader( $context, $transPage );
		} else {
			// Check for pages that are tagged or marked
			self::sourcePageHeader( $context );
		}

		return true;
	}

	protected static function sourcePageHeader( IContextSource $context ) {
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

				if ( $marked === false ) {
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
				]
			);
	}

	protected static function translationPageHeader(
		IContextSource $context, TranslatablePage $page
	) {
		global $wgTranslateKeepOutdatedTranslations;

		$title = $context->getTitle();
		if ( !$title->exists() ) {
			return;
		}

		[ , $code ] = TranslateUtils::figureMessage( $title->getText() );

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
			$url = wfExpandUrl( $page->getTranslationUrl( $code ), PROTO_RELATIVE );
			$msg = $context->msg( 'tpt-translation-intro',
				$url,
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

	/**
	 * Hook: SpecialPage_initList
	 * @param array &$list
	 * @return true
	 */
	public static function replaceMovePage( &$list ) {
		$movePageSpec = $list['Movepage'] ?? null;

		// This should never happen, but apparently is happening? See: T296568
		if ( $movePageSpec === null ) {
			return true;
		}

		$list['Movepage'] = [
			'class' => MoveTranslatablePageSpecialPage::class,
			'services' => [
				'ObjectFactory',
				'PermissionManager',
				'Translate:TranslatablePageMover'
			],
			'args' => [
				$movePageSpec
			]
		];

		return true;
	}

	/**
	 * Hook: getUserPermissionsErrorsExpensive
	 * @param Title $title
	 * @param User $user
	 * @param string $action
	 * @param array &$result
	 * @return bool
	 */
	public static function lockedPagesCheck( Title $title, User $user, $action, &$result ) {
		if ( $action === 'read' ) {
			return true;
		}

		$cache = ObjectCache::getInstance( CACHE_ANYTHING );
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
			if ( strpos( $ptext, '/' ) !== false ) {
				$links = explode( '/', $ptext );
				array_pop( $links );
				if ( $isTranslationPage ) {
					// Also remove language code page
					array_pop( $links );
				}
				$c = 0;
				$growinglink = '';
				$display = '';
				$lang = $skin->getLanguage();

				foreach ( $links as $link ) {
					$growinglink .= $link;
					$display .= $link;
					$linkObj = Title::newFromText( $growinglink );

					if ( is_object( $linkObj ) && $linkObj->isKnown() ) {
						$getlink = $linker->makeKnownLink(
							SpecialPage::getTitleFor( 'MyLanguage', $growinglink ),
							$display
						);

						$c++;

						if ( $c > 1 ) {
							$subpages .= $lang->getDirMarkEntity() . $skin->msg( 'pipe-separator' )->escaped();
						} else {
							$subpages .= '&lt; ';
						}

						$subpages .= $getlink;
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
	 * Hook: SkinTemplateNavigation
	 * @since 2013.06
	 * @param Skin $skin
	 * @param array &$tabs
	 * @return true
	 */
	public static function translateTab( Skin $skin, array &$tabs ) {
		$title = $skin->getTitle();
		$handle = new MessageHandle( $title );
		$code = $handle->getCode();
		$page = TranslatablePage::isTranslationPage( $title );
		if ( !$page ) {
			return true;
		}
		// The source language has a subpage too, but cannot be translated
		if ( $page->getSourceLanguageCode() === $code ) {
			return true;
		}

		if ( isset( $tabs['views']['edit'] ) ) {
			$tabs['views']['edit']['text'] = $skin->msg( 'tpt-tab-translate' )->text();
			$tabs['views']['edit']['href'] = $page->getTranslationUrl( $code );
		}

		return true;
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
		$user = User::newFromIdentity( $userIdentity );
		// TranslatablePageMoveJob takes care of handling updates because it performs
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
			if ( !$handle->isValid() ) {
				continue;
			}

			// Documentation pages are never translation pages
			if ( $handle->isDoc() ) {
				continue;
			}

			$group = $handle->getGroup();
			if ( !$group instanceof WikiPageMessageGroup ) {
				continue;
			}

			$language = $handle->getCode();

			// Ignore pages such as Translations:Page/unit without language code
			if ( (string)$language === '' ) {
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
	 * @since 2016.05
	 * @param WikiPage $unit
	 * @param User $user
	 * @param string $reason
	 * @param int $id
	 * @param Content $content
	 * @param ManualLogEntry $logEntry
	 */
	public static function onDeleteTranslationUnit( WikiPage $unit, User $user, $reason,
		$id, $content, $logEntry
	) {
		// Do the update. In case job queue is doing the work, the update is not done here
		if ( self::$jobQueueRunning ) {
			return;
		}
		$title = $unit->getTitle();

		$handle = new MessageHandle( $title );
		if ( !$handle->isValid() ) {
			return;
		}

		$group = $handle->getGroup();
		if ( !$group instanceof WikiPageMessageGroup ) {
			return;
		}

		// There could be interfaces which may allow mass deletion (eg. Nuke). Since they could
		// delete many units in one request, it may do several unnecessary edits and cause several
		// other unnecessary updates to be done slowing down the user. To avoid that, we push this
		// to a queue that is run after the current transaction is committed so that we can see the
		// version that is after all the deletions has been done. This allows us to do just one edit
		// per translation page after the current deletions has been done. This is sort of hackish
		// but this is better user experience and is also more efficent.
		static $queuedPages = [];
		$target = $group->getTitle();
		$langCode = $handle->getCode();
		$targetPage = $target->getSubpage( $langCode )->getPrefixedText();

		if ( isset( $queuedPages[ $targetPage ] ) ) {
			return;
		}

		$queuedPages[ $targetPage ] = true;
		$fname = __METHOD__;

		$dbw = wfGetDB( DB_PRIMARY );
		$callback = function () use (
			$dbw, $queuedPages, $targetPage, $target, $handle, $langCode, $user, $reason, $fname
		) {
			$dbw->startAtomic( $fname );

			$page = TranslatablePage::newFromTitle( $target );

			MessageGroupStats::forItem(
				$page->getMessageGroupId(),
				$langCode,
				MessageGroupStats::FLAG_NO_CACHE
			);

			if ( !$handle->isDoc() ) {
				// Assume that $user and $reason for the first deletion is the same for all
				self::updateTranslationPage( $page, $langCode, $user, 0, $reason );
			}

			// If a unit was deleted after the edit here is done, this allows us
			// to add the page back to the queue again and so we can make another
			// edit here with the latest changes.
			unset( $queuedPages[ $targetPage ] );

			$dbw->endAtomic( $fname );
		};

		$dbw->onTransactionCommitOrIdle( $callback, __METHOD__ );
	}
}
