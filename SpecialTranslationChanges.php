<?php

class SpecialTranslationChanges extends SpecialPage {
	const MSG = 'translationchanges-';

	function __construct() {
		SpecialPage::SpecialPage( 'TranslationChanges' );
	}

	/** Access point for this special page */
	public function execute( $parameters ) {
		global $wgOut, $wgScriptPath, $wgJsMimeType, $wgStyleVersion, $wgRequest;
		wfLoadExtensionMessages( 'Translate' );

		$wgOut->addScript(
			Xml::openElement( 'script', array( 'type' => $wgJsMimeType, 'src' =>
			"$wgScriptPath/extensions/CleanChanges/cleanchanges.js?$wgStyleVersion" )
			) . '</script>'
		);

		$this->setHeaders();

		$rows = $this->runQuery( $wgRequest->getInt( 'hours', 24 ) );
		$wgOut->addHTMl( $this->settingsForm() . $this->output( $rows ) );
	}

	protected function runQuery( $hours = '' ) {
		$dbr = wfGetDB( DB_SLAVE );
		$recentchanges = $dbr->tableName( 'recentchanges' );
		$hours = intval( $hours );
		$cutoff_unixtime = time() - ( $hours * 3600 );
		#$cutoff_unixtime = $cutoff_unixtime - ($cutoff_unixtime % 86400);
		$cutoff = $dbr->timestamp( $cutoff_unixtime );

		$fields = 'rc_title, rc_timestamp, rc_user_text';

		$sql = "SELECT $fields, substring_index(rc_title, '/', -1) as lang FROM $recentchanges " .
		"WHERE rc_timestamp >= '{$cutoff}' " .
		"AND rc_namespace = 8 " .
		"ORDER BY lang ASC, rc_timestamp DESC";

		$res = $dbr->query( $sql, __METHOD__ );

		// Fetch results, prepare a batch link existence check query
		$rows = array();
		while( $row = $dbr->fetchObject( $res ) ){
			$rows[] = $row;
		}
		$dbr->freeResult( $res );
		return $rows;
	}

	/**
	 * GLOBALS: $wgTitle, $wgScript
	 */
	protected function settingsForm() {
		global $wgTitle, $wgScript;

		$limit = $this->timeLimitSelector();
		$button = Xml::submitButton( wfMsg( TranslateUtils::MSG . 'submit' ) );

		$form = Xml::tags( 'form',
			array(
				'action' => $wgScript,
				'method' => 'get'
			),
			Xml::hidden( 'title', $wgTitle->getPrefixedText() ) . $limit . $button
		);
		return $form;
	}

	protected function timeLimitSelector( $selected = 24 ) {
		$items = array( 3, 6, 12, 24, 48, 72, 168  );
		return TranslateUtils::simpleSelector( 'hours' , $items, $selected );
	}

	protected function sort( Array $rows ) {
		global $wgContLang;
		$sorted = array();
		$index = TranslateUtils::messageIndex();
		$batch = new LinkBatch;
		foreach ( $rows as $row ) {
			$pieces = explode('/', $wgContLang->lcfirst($row->rc_title), 2);

			$group = 'Unknown';
			$mg = @$index[$pieces[0]];
			if ( !is_null($mg) ) $group = $mg;

			$lang = 'site';
			if ( strpos( $row->rc_title, '/' ) !== false ) {
				$lang = $row->lang;
			}

			switch ($group) {
				case 'core': $class = 'core'; break;
				case 'out-freecol': $class = 'freecol'; break;
				default: $class = 'extension'; break;
			}

			if ( $lang === 'site' ) {
				$class = 'site';
			}

			$sorted[$class][$group][$lang][] = $row;

			$batch->add( NS_USER,           $row->rc_user_text );
			$batch->add( NS_USER_TALK,      $row->rc_user_text );
			if ( $group !== 'core' ) {
			$batch->add( NS_MEDIAWIKI,      $row->rc_title );
			}
			$batch->add( NS_MEDIAWIKI_TALK, $row->rc_title );


		}
		ksort($sorted);
		if ( isset($sorted['extension']) ) {
			ksort($sorted['extension']);
		}

		$batch->execute();
		return $sorted;
	}

	protected function output( Array $rows ) {
		$groupObjects = MessageGroups::singleton()->getGroups();
		global $wgLang, $wgUser;
		$index = -1;
		$output = '';
		$skin = $wgUser->getSkin();

		$changes = $this->sort( $rows );
		foreach ( $changes as $class => $groups ) {
			#$output .= Xml::element( 'h2', null, $class );
			foreach ( $groups as $group => $languages ) {

				$label = $group;
				if ( isset( $groupObjects[$group] ) ) {
					$label = $groupObjects[$group]->getLabel();
				}
				$output .= Xml::element( 'h3', null, $label );

				foreach ( $languages as $language => $rows ) {
					$index++;
					$rci = 'RCI' . $language . $index;
					$rcl = 'RCL' . $language . $index;
					$rcm = 'RCM' . $language . $index;
					$toggleLink = "javascript:toggleVisibilityE('$rci', '$rcm', '$rcl', 'block')";

					$rowTl =
					Xml::tags( 'span', array( 'id' => $rcm ),
						Xml::tags('a', array( 'href' => $toggleLink ), $this->sideArrow() ) ) .
					Xml::tags( 'span', array( 'id' => $rcl, 'style' => 'display: none;' ),
						Xml::tags('a', array( 'href' => $toggleLink ), $this->downArrow() ) );

					$nchanges = wfMsgExt( 'nchanges', array( 'parsemag', 'escape'),
						$wgLang->formatNum( count($rows) ));

					$exportLabel = wfMsg( self::MSG . 'export' );
					$titleText = 'Special:' . SpecialPage::getLocalNameFor( 'Translate' );
					$export = $skin->makeKnownLink( $titleText, $exportLabel,
						"task=export-to-file&language=$language&group=$group" );

					$languageName = TranslateUtils::getLanguageName( $language );
					if ( !$languageName ) $languageName = $language;

					$output .= Xml::tags( 'h4', null, "$rowTl $language <small>($languageName)</small> ($nchanges) ($export)" );
					$output .= Xml::openElement( 'ul',
						array( 'id' => $rci, 'style' => 'display: none' ) );

					foreach ( $rows as $row ) {
						$date = $wgLang->timeAndDate( $row->rc_timestamp, /* adj */ true, /* format */ true );
						$msg = wfMsgExt( self::MSG . 'change', array( 'parsemag' ),
							$date, wfEscapeWikiText($row->rc_title), wfEscapeWikiText($row->rc_user_text)
						);
						$output .= Xml::tags( 'li', null, $msg );
					}

					$output .= Xml::closeElement( 'ul' );

				}
			}
		}

		return $output;
	}

	/**
	 * GLOBALS: $wgLang
	 */
	private static function makeBlock( $tl, $lang, $rowCache, $rowId ) {
		global $wgLang;
		$changes = count($rowCache);
		$output = Xml::tags( 'h3', null, "$tl $lang ($nchanges)" );
		$output .= Xml::tags( 'ul',
			array( 'id' => $rowId, 'style' => 'display: none' ),
			implode( "\n", $rowCache )
		);
		return $output;
	}

	/**
	 * Generate HTML for an arrow or placeholder graphic
	 * @param string $dir one of '', 'd', 'l', 'r'
	 * @param string $alt text
	 * @return string HTML <img> tag
	 * @access private
	 */
	function arrow( $dir, $alt='' ) {
		global $wgStylePath;
		$encUrl = htmlspecialchars( $wgStylePath . '/common/images/Arr_' . $dir . '.png' );
		$encAlt = htmlspecialchars( $alt );
		return "<img src=\"$encUrl\" width=\"12\" height=\"12\" alt=\"$encAlt\" />";
	}

	/**
	 * Generate HTML for a right- or left-facing arrow,
	 * depending on language direction.
	 * @return string HTML <img> tag
	 * @access private
	 */
	function sideArrow() {
		global $wgContLang;
		$dir = $wgContLang->isRTL() ? 'l' : 'r';
		return $this->arrow( $dir, '+' );
	}

	/**
	 * Generate HTML for a down-facing arrow
	 * depending on language direction.
	 * @return string HTML <img> tag
	 * @access private
	 */
	function downArrow() {
		return $this->arrow( 'd', '-' );
	}


}