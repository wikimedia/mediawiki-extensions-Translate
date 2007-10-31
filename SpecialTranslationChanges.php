<?php

class SpecialTranslationChanges extends SpecialPage {

	function __construct() {
		SpecialPage::SpecialPage( 'TranslationChanges' );
	}

	/** Access point for this special page */
	public function execute( $parameters ) {
		global $wgOut, $wgScriptPath, $wgJsMimeType, $wgStyleVersion;
		wfLoadExtensionMessages( 'Translate' );

		$wgOut->addScript(
			Xml::openElement( 'script', array( 'type' => $wgJsMimeType, 'src' =>
			"$wgScriptPath/extensions/CleanChanges/cleanchanges.js?$wgStyleVersion" )
			) . '</script>'
		);


		#$this->setup();
		$this->setHeaders();

		$rows = $this->runQuery();
		$wgOut->addHTMl( $this->output( $rows ) );
	}

	protected function runQuery() {
		$dbr = wfGetDB( DB_SLAVE );
		$recentchanges = $dbr->tableName( 'recentchanges' );

		$from = '';
		$days = 1;
		$cutoff_unixtime = time() - ( $days * 86400 );
		$cutoff_unixtime = $cutoff_unixtime - ($cutoff_unixtime % 86400);
		$cutoff = $dbr->timestamp( $cutoff_unixtime );
		if(preg_match('/^[0-9]{14}$/', $from) and $from > wfTimestamp(TS_MW,$cutoff)) {
			$cutoff = $dbr->timestamp($from);
		}


		$sql = "SELECT *, substring_index(rc_title, '/', -1) as lang FROM $recentchanges " .
		"WHERE rc_timestamp >= '{$cutoff}' " .
		"AND rc_namespace = 8 " .
		"ORDER BY lang ASC, rc_timestamp DESC";

		$res = $dbr->query( $sql, __METHOD__ );

		// Fetch results, prepare a batch link existence check query
		$rows = array();
		$batch = new LinkBatch;
		while( $row = $dbr->fetchObject( $res ) ){
			$rows[] = $row;
			// User page link
			$title = Title::makeTitleSafe( NS_USER, $row->rc_user_text );
			$batch->addObj( $title );

			// User talk
			$title = Title::makeTitleSafe( NS_USER_TALK, $row->rc_user_text );
			$batch->addObj( $title );
		}
		$dbr->freeResult( $res );
		$batch->execute();
		return $rows;
	}

	protected function output( Array $rows ) {
		global $wgLang;
		$index = 0;
		$lastLanguage = '';
		$output = '';
		$rowTitle = '';
		$rowId = '';
		$rowLang = '';
		$rowTl = '';
		$rowCache = array();
		foreach ( $rows as $row ) {

			if ( $row->lang !== $lastLanguage ) {
				$lastLanguage = $row->lang;
				if ( count($rowCache) ) {
					$output .= self::makeBlock( $rowLang, $rowTl, $rowCache, $rowId );
					$rowCache = array();
				}

				$rci = 'RCI' . $row->lang . $index;
				$rcl = 'RCL' . $row->lang . $index;
				$rcm = 'RCM' . $row->lang . $index;
				$toggleLink = "javascript:toggleVisibilityE('$rci', '$rcm', '$rcl', 'block')";
				$rowTl =
				Xml::tags( 'span', array( 'id' => $rcm ),
					Xml::tags('a', array( 'href' => $toggleLink ), $this->sideArrow() ) ) .
				Xml::tags( 'span', array( 'id' => $rcl, 'style' => 'display: none;' ),
					Xml::tags('a', array( 'href' => $toggleLink ), $this->downArrow() ) );

				$rowLang = $row->lang;
				$rowId = $rci;
			}

			# New row
			$date = $wgLang->timeAndDate( $row->rc_timestamp, /* adj */ true, /* format */ true );
			$rowCache[] = Xml::element( 'li', null, "$date $row->rc_title by $row->rc_user_text" );

			$index++;
		}

		if ( count($rowCache) ) {
			$output .= self::makeBlock( $rowLang, $rowTl, $rowCache, $rowId  );
			$rowCache = array();
		}

		return $output;
	}

	private static function makeBlock( $tl, $lang, $rowCache, $rowId ) {
		$changes = count($rowCache);
		$output = Xml::tags( 'h2', null, "$tl $lang ($changes changes)" );
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