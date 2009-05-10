<?php

class PageTranslationHooks {

	// Uuugly hack
	static $allowTargetEdit = false;

	public static function renderTagPage( $parser, &$text, $state ) {
		$title = $parser->getTitle();
		if ( strpos( $text, '</translate>' ) !== false ) {
			$cb = array( __CLASS__, 'replaceTagCb' );
			# Remove the tags nicely, trying to not leave excess whitespace lying around
			$text = preg_replace_callback( '~(\n?<translate>\s*?)(.*?)(\s*?</translate>)~s', $cb, $text );
			# Replace variable markers
			$text = preg_replace_callback( '~(<tvar[^<>]+>)(.*)(</>)~s', $cb, $text );
		}
		return true;
	}

	public static function replaceTagCb( $matches ) {
		return $matches[2];
	}

	public static function injectCss( $outputpage, $text ) {
		TranslateUtils::injectCSS();
		return true;
	}

	public static function onSectionSave( $article, $user, $text, $summary, $minor,
		$_, $_, $flags, $revision ) {
		$title = $article->getTitle();

		// Some checks

		// We are only interested in the translations namespace
		if ( $title->getNamespace() != NS_TRANSLATIONS ) return true;
		// Do not trigger renders for fuzzy
		if ( strpos( $text, TRANSLATE_FUZZY ) !== false ) return true;

		// Figure out the group
		$groupKey = MessageIndex::titleToGroup( $title );
		$group = MessageGroups::getGroup( $groupKey );
		if ( !$group instanceof WikiPageMessageGroup ) return;

		// Finally we know the title and can construct a Translatable page
		$page = TranslatablePage::newFromTitle( $group->title );

		// Add a tracking mark
		if ( $revision !== null ) {
			self::addSectionTag( $title, $revision->getId(), $page->getMarkedTag() );
		}

		// Update the target translation page
		list(, $code ) = TranslateUtils::figureMessage( $title->getDBkey() );
		self::updateTranslationPage( $page, $group, $code, $user, $flags, $summary );

		return true;
	}

	protected static function addSectionTag( Title $title, $revision, $pageRevision ) {
		if ( $pageRevision === null ) throw new MWException( 'Page revision is null' );

		$dbw = wfGetDB( DB_MASTER );

		// Can this be done in one query?
		$id = $dbw->selectField( 'revtag_type', 'rtt_id',
			array( 'rtt_name' => 'tp:transver' ), __METHOD__ );

		$conds = array(
			'rt_page' => $title->getArticleId(),
			'rt_type' => $id,
			'rt_revision' => $revision
		);
		$dbw->delete( 'revtag', $conds, __METHOD__ );

		$conds['rt_value'] = $pageRevision;

		$dbw->insert( 'revtag', $conds, __METHOD__ );
	}

	public static function updateTranslationPage( TranslatablePage $page,
		MessageGroup $group, $code, $user, $flags, $summary ) {

		$source = $page->getTitle();
		$target = Title::makeTitle( $source->getNamespace(), $source->getDBkey() . "/$code" );

		$collection = $group->initCollection( $code );
		$group->fillCollection( $collection );

		$text = $page->getParse()->getTranslationPageText( $collection );

		// Same as in renderSourcePage
		$cb = array( __CLASS__, 'replaceTagCb' );
		$text = preg_replace_callback( '~(\n?<translate>\s*?)(.*?)(\s*?</translate>)~s', $cb, $text );

		#$flags |= EDIT_SUPPRESS_RC; // We can filter using CleanChanges
		$flags &= ~EDIT_NEW & ~EDIT_UPDATE; // We don't know

		$article = new Article( $target );

		self::$allowTargetEdit = true;
		$article->doEdit( $text, $summary, $flags );
		self::$allowTargetEdit = false;
	}

	public static function addSidebar( $out, $tpl ) {
		// TODO: fixme
		return true;
		global $wgLang;

		// Sort by translation percentage
		arsort( $status, SORT_NUMERIC );

		foreach ( $status as $code => $percent ) {
			$name = TranslateUtils::getLanguageName( $code, false, $wgLang->getCode() );
			$percent = $wgLang->formatNum( round( 100 * $percent ) );
			$label = "$name ($percent%)";

			$_title = TranslateTagUtils::codefyTitle( $title, $code );

			$items[] = array(
				'text' => $label,
				'href' => $_title->getFullURL(),
				'id' => 'foo',
			);
		}

		$sidebar = $out->buildSidebar();
		$sidebar['TRANSLATIONS'] = $items;

		$tpl->set( 'sidebar', $sidebar );

		return true;
	}

	public static function languages( $data, $params, $parser ) {
		$title = $parser->getTitle();

		// Check if this is a source page or a translation page
		$page = TranslatablePage::newFromTitle( $title );
		if ( $page->getMarkedTag() === false ) {
			$title = Title::makeTitle( $title->getNamespace(), $title->getBaseText() );
			$page = TranslatablePage::newFromTitle( $title );
		}
		if ( $page->getMarkedTag() === false )  return '';


		$status = $page->getTranslationPercentages();
		if ( !$status ) return '';

		// Sort by language code, which seems to be the only sane method
		ksort( $status );

		$sk = $parser->mOptions->getSkin();

		/* We rely on $wgLang, which should not matter as
		 * languages are cached per language. However it
		 * would be nicer to use $parser->getFunctionLang();
		 * but that needs to be set correct first. */
		// $lobj = $parser->getFunctionLang();
		global $wgTranslateCssLocation, $wgLang;

		$languages = array();
		foreach ( $status as $code => $percent ) {
			$name = TranslateUtils::getLanguageName( $code, false, $wgLang->getCode() );

			/* Percentages are too accurate and take more
			 * space than plain images */
			$percent *= 100;
			if     ( $percent < 20 ) $image = 1;
			elseif ( $percent < 40 ) $image = 2;
			elseif ( $percent < 60 ) $image = 3;
			elseif ( $percent < 80 ) $image = 4;
			else                     $image = 5;

			$percent = Xml::element( 'img', array(
				'src'   => "$wgTranslateCssLocation/images/prog-$image.png",
				'alt'   => "$percent%",
				'title' => "$percent%",
			) );
			$label = "$name $percent";

			// Add links to other languages
			$suffix = ( $code === 'en' ) ? '' : "/$code";
			$_title = Title::makeTitle( $title->getNamespace(), $title->getDBKey() . $suffix );

			// For some reason self-links are not done automatically
			if ( $parser->getTitle()->getText() === $_title->getText() ) {
				$languages[] = "<b>$label</b>";
			} else {
				$languages[] = $sk->link( $_title, $label );
			}
		}

		wfLoadExtensionMessages( 'PageTranslation' );
		$legend = wfMsg( 'tpt-languages-legend' );
		$languages = implode( '&nbsp;• ', $languages );

		return <<<FOO
<div class="mw-pt-languages">
<table><tbody>

<tr valign="top">
<td class="mw-pt-languages-label"><b>$legend</b></td>
<td class="mw-pt-languages-list">$languages</td></tr>

</tbody></table>
</div>
FOO;
	}

	// When attempting to save
	public static function tpSyntaxCheck( $article, $user, $text, $summary,
			$minor, $_, $_, $flags, $status ) {
		// Quick escape on normal pages
		if ( strpos( $text, '</translate>' ) === false ) return true;

		$page = TranslatablePage::newFromText( $article->getTitle(), $text );
		try {
			/* This does not catch all problems yet,
			 * like markup spanning between sections. */
			$page->getParse();
		} catch ( TPException $e ) {
			call_user_func_array( array( $status, 'fatal' ), $ret );
			return false;
		}

		return true;
	}

	public static function addTranstag( $article, $user, $text, $summary,
			$minor, $_, $_, $flags, $revision ) {
		// We are not interested in null revisions
		if ( $revision === null ) return true;

		// Quick escape on normal pages
		if ( strpos( $text, '</translate>' ) === false ) return true;

		// Add the ready tag
		$page = TranslatablePage::newFromTitle( $article->getTitle() );
		$page->addReadyTag( $revision->getId() );

		return true;
	}

	// Here we disable editing of some existing or unknown pages
	public static function translationsCheck( $title, $user, $action, &$result ) {

		// Case 1: Unknown section translations
		if ( $title->getNamespace() == NS_TRANSLATIONS && $action === 'edit' ) {
			$group = MessageIndex::titleToGroup( $title );
			if ( $group === null ) {
				// No group means that the page is currently not 
				// registered to any page translation message groups
				wfLoadExtensionMessages( 'PageTranslation' );
				$result = array( 'tpt-unknown-page' );
				return false;
			}

		// Case 2: Target pages
		} elseif( $title->getBaseText() != $title->getText() ) {
			$newtitle = Title::makeTitle( $title->getNamespace(), $title->getBaseText() );

			// Base page does not exists, cannot be translatable page
			if ( !$newtitle || !$newtitle->exists() ) return true;

			// Local override of fuzzybot is allowed
			global $wgTranslateFuzzyBotName;
			if ( self::$allowTargetEdit ||
			     $user->getName() === $wgTranslateFuzzyBotName ) return true;

			// Proceed to check whether we need to block
			$page = TranslatablePage::newFromTitle( $newtitle );
			if ( $page->getMarkedTag() ) {
				wfLoadExtensionMessages( 'PageTranslation' );
				$result = array(
					'tpt-target-page',
					$newtitle->getPrefixedText(),
					$page->getTranslationUrl( $title->getSubpageText() ) 
				);
				return false;
			}
		}

		return true;
	}

	public static function onLoadExtensionSchemaUpdates() {
		global $wgExtNewTables;
		$dir = dirname( __FILE__ ) . '/..';
		$wgExtNewTables[] = array( 'translate_sections', "$dir/translate.sql" );
		$wgExtNewTables[] = array( 'revtag_type', "$dir/revtags.sql" );

		// Add our tags if they are not registered yet
		// tp:tag is called also the ready tag
		$tags = array( 'tp:mark', 'tp:tag', 'tp:transver' );

		$dbw = wfGetDB( DB_MASTER );
		foreach ( $tags as $tag ) {
			// TODO: use insert ignore
			$field = array( 'rtt_name' => $tag );
			$ret = $dbw->selectField( 'revtag_type', 'rtt_name', $field, __METHOD__ );
			if ( $ret !== $tag ) $dbw->insert( 'revtag_type', $field, __METHOD__ );
		}
		return true;
	}

	// TODO: fix the name
	public static function test(&$article, &$outputDone, &$pcache) {
		global $wgOut;
		if ( !$article->getOldID() ) {
			self::header( $article->getTitle() );
		}
		return true;
	}

	public static function header( Title $title ) {
		global $wgLang, $wgUser;


		$page = TranslatablePage::newFromTitle( $title );
		$marked = $page->getMarkedTag();
		$ready = $page->getReadyTag();

		if ( $marked || $ready ) {
			self::sourcePageHeader( $page, $marked, $ready );
		} else  {
			self::translationPageHeader( $title );
		}
	}

	protected static function sourcePageHeader( TranslatablePage $page,
		$marked, $ready ) {

		global $wgUser, $wgLang;
		wfLoadExtensionMessages( 'PageTranslation' );

		$title = $page->getTitle();
		$sk = $wgUser->getSkin();

		$latest = $title->getLatestRevId();
		$canmark = $ready === $latest && $marked !== $latest;

		$actions = array();

		if ( $marked && $wgUser->isAllowed('translate') ) {
			$par = array(
				'group' => 'page|' . $title->getPrefixedText(),
				'language' => $wgLang->getCode(),
				'task' => 'view'
			);
			$translate = SpecialPage::getTitleFor( 'Translate' );
			$linkDesc  = wfMsgHtml( 'translate-tag-translate-link-desc' );
			$actions[] = $sk->link( $translate, $linkDesc, array(), $par);
		}

		if ( $canmark && $wgUser->isAllowed('pagetranslation') ) {
			$par = array(
				'target' => $title->getPrefixedText()
			);
			$translate = SpecialPage::getTitleFor( 'PageTranslation' );
			$linkDesc  = wfMsgHtml( 'translate-tag-markthis' );
			$actions[] = $sk->link( $translate, $linkDesc, array(), $par);
		}

		if ( !count($actions) ) return;
		$legend  = "<div style=\"font-size: x-small; text-align: center\">";
		$legend .= $wgLang->semicolonList( $actions );
		$legend .= '</div><hr />';
		
		global $wgOut;
		$wgOut->addHTML( $legend );

	}

	protected static function translationPageHeader( Title $title ) {
		global $wgOut;

		// Check if applicable
		$page = TranslatablePage::isTranslationPage( $title );
		if ( $page === false ) return;

		// Get the translation percentage
		$pers = $page->getTranslationPercentages();
		$per = @$pers[$title->getSubpageText()];
		$per = ($per === null) ? 0 : $per * 100;
		$titleText = $page->getTitle()->getText();
		$url = $page->getTranslationUrl( $title->getSubpageText() );

		// Output
		wfLoadExtensionMessages( 'PageTranslation' );
		$wrap = '<div style="font-size: x-small; text-align: center">$1</div><hr />';
		$wgOut->wrapWikiMsg( $wrap, array( 'tpt-translation-intro', $url, $titleText, $per)  );

	}

}