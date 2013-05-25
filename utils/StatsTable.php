<?php
/**
 * Contains logic for special page Special:LanguageStats.
 *
 * @file
 * @author Siebrand Mazeland
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2013 Siebrand Mazeland, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Implements includable special page Special:LanguageStats which provides
 * translation statistics for all defined message groups.
 *
 * Loosely based on the statistics code in phase3/maintenance/language
 *
 * Use {{Special:LanguageStats/nl/1}} to show for 'nl' and suppres completely
 * translated groups.
 *
 * @ingroup Stats
 */
class StatsTable {
	/// @var Language
	protected $lang;
	/// @var SpecialPage
	protected $translate;
	/// @var string
	protected $mainColumnHeader;
	/// @var array
	protected $extraColumns = array();

	public function __construct() {
		$this->lang = RequestContext::getMain()->getLanguage();
		$this->translate = SpecialPage::getTitleFor( 'Translate' );
	}

	/**
	 * Statistics table element (heading or regular cell)
	 *
	 * @param $in \string Element contents.
	 * @param $bgcolor \string Backround color in ABABAB format.
	 * @param $sort \string Value used for sorting.
	 * @return \string Html td element.
	 */
	public function element( $in, $bgcolor = '', $sort = '' ) {
		$attributes = array();

		if ( $sort ) {
			$attributes['data-sort-value'] = $sort;
		}

		if ( $bgcolor ) {
			$attributes['style'] = "background-color: #" . $bgcolor;
			$attributes['class'] = 'hover-color';
		}

		$element = Html::element( 'td', $attributes, $in );

		return $element;
	}

	public function getBackgroundColor( $subset, $total, $fuzzy = false ) {
		wfSuppressWarnings();
		$v = round( 255 * $subset / $total );
		wfRestoreWarnings();

		if ( $fuzzy ) {
			// Weigh fuzzy with factor 20.
			$v = $v * 20;

			if ( $v > 255 ) {
				$v = 255;
			}

			$v = 255 - $v;
		}

		if ( $v < 128 ) {
			// Red to Yellow
			$red = 'FF';
			$green = sprintf( '%02X', 2 * $v );
		} else {
			// Yellow to Green
			$red = sprintf( '%02X', 2 * ( 255 - $v ) );
			$green = 'FF';
		}
		$blue = '00';

		return $red . $green . $blue;
	}

	public function getMainColumnHeader() {
		return $this->mainColumnHeader;
	}

	public function setMainColumnHeader( Message $msg ) {
		$this->mainColumnHeader = $this->createColumnHeader( $msg );
	}

	public function createColumnHeader( Message $msg ) {
		return Html::element( 'th', array(), $msg->text() );
	}

	public function addExtraColumn( Message $column ) {
		$this->extraColumns[] = $column;
	}

	public function getOtherColumnHeaders() {
		return array_merge( array(
			wfMessage( 'translate-total' ),
			wfMessage( 'translate-untranslated' ),
			wfMessage( 'translate-percentage-complete' ),
			wfMessage( 'translate-percentage-fuzzy' ),
		), $this->extraColumns );
	}

	public function createHeader() {
		// Create table header
		$out = Html::openElement(
			'table',
			array( 'class' => "statstable wikitable mw-sp-translate-table" )
		);

		$out .= "\n\t" . Html::openElement( 'thead' );
		$out .= "\n\t" . Html::openElement( 'tr' );

		$out .= "\n\t\t" . $this->getMainColumnHeader();
		foreach ( $this->getOtherColumnHeaders() as $label ) {
			$out .= "\n\t\t" . $this->createColumnHeader( $label );
		}
		$out .= "\n\t" . Html::closeElement( 'tr' );
		$out .= "\n\t" . Html::closeElement( 'thead' );
		$out .= "\n\t" . Html::openElement( 'tbody' );

		return $out;
	}

	/**
	 * Makes a row with aggregate numbers.
	 * @param Message $message
	 * @param array $stats ( total, translate, fuzzy )
	 * @return string Html
	 */
	public function makeTotalRow( Message $message, $stats ) {
		$out = "\t" . Html::openElement( 'tr' );
		$out .= "\n\t\t" . Html::element( 'td', array(), $message->text() );
		$out .= $this->makeNumberColumns( $stats );
		$out .= "\n\t" . Xml::closeElement( 'tr' ) . "\n";

		return $out;
	}

	/**
	 * Makes partial row from completion numbers
	 * @param array $stats
	 * @return string Html
	 */
	public function makeNumberColumns( $stats ) {
		$total = $stats[MessageGroupStats::TOTAL];
		$translated = $stats[MessageGroupStats::TRANSLATED];
		$fuzzy = $stats[MessageGroupStats::FUZZY];

		if ( $total === null ) {
			$na = "\n\t\t" . Html::element( 'td', array( 'data-sort-value' => -1 ), '...' );
			$nap = "\n\t\t" . $this->element( '...', 'AFAFAF', -1 );
			$out = $na . $na . $nap . $nap;

			return $out;
		}

		$out = "\n\t\t" . Html::element( 'td',
			array( 'data-sort-value' => $total ),
			$this->lang->formatNum( $total ) );

		$out .= "\n\t\t" . Html::element( 'td',
			array( 'data-sort-value' => $total - $translated ),
			$this->lang->formatNum( $total - $translated ) );

		if ( $total === 0 ) {
			$transRatio = 0;
			$fuzzyRatio = 0;
		} else {
			$transRatio = $translated / $total;
			$fuzzyRatio = $fuzzy / $total;
		}

		$out .= "\n\t\t" . $this->element( $this->formatPercentage( $transRatio, 'floor' ),
			$this->getBackgroundColor( $translated, $total ),
			sprintf( '%1.5f', $transRatio ) );

		$out .= "\n\t\t" . $this->element( $this->formatPercentage( $fuzzyRatio, 'ceil' ),
			$this->getBackgroundColor( $fuzzy, $total, true ),
			sprintf( '%1.5f', $fuzzyRatio ) );

		return $out;
	}

	/**
	 * Makes a nice print from plain float.
	 * @param $num float
	 * @param $to string floor or ceil
	 * @return string Plain text
	 */
	public function formatPercentage( $num, $to = 'floor' ) {
		$num = $to === 'floor' ? floor( 100 * $num ) : ceil( 100 * $num );
		$fmt = $this->lang->formatNum( $num );

		return wfMessage( 'percent', $fmt )->text();
	}

	/**
	 * Gets the name of group with some extra formatting.
	 * @param $group MessageGroup
	 * @return string Html
	 */
	public function getGroupLabel( MessageGroup $group ) {
		$groupLabel = htmlspecialchars( $group->getLabel() );

		// Bold for meta groups.
		if ( $group->isMeta() ) {
			$groupLabel = Html::rawElement( 'b', array(), $groupLabel );
		}

		return $groupLabel;
	}

	/**
	 * Gets the name of group linked to translation tool.
	 * @param $group MessageGroup
	 * @param $code string Language code
	 * @param $params array Any extra query parameters.
	 * @return string Html
	 */
	public function makeGroupLink( MessageGroup $group, $code, $params ) {
		$queryParameters = $params + array(
			'group' => $group->getId(),
			'language' => $code
		);

		$attributes = array(
			'title' => $this->getGroupDescription( $group )
		);

		$translateGroupLink = Linker::link(
			$this->translate, $this->getGroupLabel( $group ), $attributes, $queryParameters
		);

		return $translateGroupLink;
	}

	/**
	 * Gets the description of a group. This is a bit slow thing to do for
	 * thousand+ groups, so some caching is involved.
	 * @param $group MessageGroup
	 * @return string Plain text
	 */
	public function getGroupDescription( MessageGroup $group ) {
		$code = $this->lang->getCode();

		$cache = wfGetCache( CACHE_ANYTHING );
		$key = wfMemckey( "translate-groupdesc-$code-" . $group->getId() );
		$desc = $cache->get( $key );

		if ( is_string( $desc ) ) {
			return $desc;
		}

		$realFunction = array( 'MessageCache', 'singleton' );

		if ( is_callable( $realFunction ) ) {
			$mc = MessageCache::singleton();
		} else {
			global $wgMessageCache;

			$mc = $wgMessageCache;
		}

		$desc = $mc->transform( $group->getDescription(), true, $this->lang );
		$cache->set( $key, $desc );

		return $desc;
	}

	/**
	 * Check whether translations in given group in given language
	 * has been disabled.
	 * @param $groupId string Message group id
	 * @param $code string Language code
	 * @return bool
	 */
	public function isBlacklisted( $groupId, $code ) {
		global $wgTranslateBlacklist;

		$blacklisted = null;

		$checks = array(
			$groupId,
			strtok( $groupId, '-' ),
			'*'
		);

		foreach ( $checks as $check ) {
			if ( isset( $wgTranslateBlacklist[$check] ) && isset( $wgTranslateBlacklist[$check][$code] ) ) {
				$blacklisted = $wgTranslateBlacklist[$check][$code];
			}

			if ( $blacklisted !== null ) {
				break;
			}
		}

		$group = MessageGroups::getGroup( $groupId );
		$languages = $group->getTranslatableLanguages();
		if ( $languages !== null && !isset( $languages[$code] ) ) {
			$blacklisted = true;
		}

		$include = wfRunHooks( 'Translate:MessageGroupStats:isIncluded', array( $groupId, $code ) );
		if ( !$include ) {
			$blacklisted = true;
		}

		return $blacklisted;
	}

	/**
	 * Used to circumvent ugly tooltips when newlines are used in the
	 * message content ("x\ny" becomes "x y").
	 * @param $text
	 * @return string
	 */
	public static function formatTooltip( $text ) {
		$wordSeparator = wfMessage( 'word-separator' )->text();

		$text = strtr( $text, array(
			"\n" => $wordSeparator,
			"\r" => $wordSeparator,
			"\t" => $wordSeparator,
		) );

		return $text;
	}
}
