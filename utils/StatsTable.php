<?php
/**
 * @file
 * @author Siebrand Mazeland
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2013 Siebrand Mazeland, Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Implements generation of HTML stats table.
 *
 * Loosely based on the statistics code in phase3/maintenance/language
 *
 * @ingroup Stats
 */
class StatsTable {
	/**
	 * @var Language
	 */
	protected $lang;

	/**
	 * @var Title
	 */
	protected $translate;

	/**
	 * @var string
	 */
	protected $mainColumnHeader;

	/**
	 * @var Message[]
	 */
	protected $extraColumns = [];

	public function __construct() {
		$this->lang = RequestContext::getMain()->getLanguage();
		$this->translate = SpecialPage::getTitleFor( 'Translate' );
	}

	/**
	 * Statistics table element (heading or regular cell)
	 *
	 * @param string $in Element contents.
	 * @param string $bgcolor Backround color in ABABAB format.
	 * @param string $sort Value used for sorting.
	 * @return string Html td element.
	 */
	public function element( $in, $bgcolor = '', $sort = '' ) {
		$attributes = [];

		if ( $sort ) {
			$attributes['data-sort-value'] = $sort;
		}

		if ( $bgcolor ) {
			$attributes['style'] = 'background-color: #' . $bgcolor;
		}

		$element = Html::element( 'td', $attributes, $in );

		return $element;
	}

	public function getBackgroundColor( $percentage, $fuzzy = false ) {
		if ( $fuzzy ) {
			// Steeper scale for fuzzy
			// (0), [0-2), [2-4), ... [12-100)
			$index = min( 7, ceil( 50 * $percentage ) );
			$colors = [
				'', 'fedbd7', 'fecec8', 'fec1b9',
				'fcb5ab', 'fba89d', 'f89b8f', 'f68d81'
			];
			return $colors[ $index ];
		}

		// https://gka.github.io/palettes/#colors=#36c,#eaf3ff|steps=20|bez=1|coL=1
		// Color groups for (0-10], (10-20], ... (90-100], (100)
		$index = floor( $percentage * 10 );
		$colors = [
			'eaf3ff', 'e2ebfc', 'dae3fa', 'd2dbf7', 'c9d4f5',
			'c1ccf2', 'b8c4ef', 'b1bced', 'a8b4ea', '9fade8',
			'96a6e5'
		];

		return $colors[ $index ];
	}

	/**
	 * @return string
	 */
	public function getMainColumnHeader() {
		return $this->mainColumnHeader;
	}

	/**
	 * @param Message $msg
	 */
	public function setMainColumnHeader( Message $msg ) {
		$this->mainColumnHeader = $this->createColumnHeader( $msg );
	}

	/**
	 * @param Message $msg
	 * @return string HTML
	 */
	public function createColumnHeader( Message $msg ) {
		return Html::element( 'th', [], $msg->text() );
	}

	public function addExtraColumn( Message $column ) {
		$this->extraColumns[] = $column;
	}

	/**
	 * @return Message[]
	 */
	public function getOtherColumnHeaders() {
		return array_merge( [
			wfMessage( 'translate-total' ),
			wfMessage( 'translate-untranslated' ),
			wfMessage( 'translate-percentage-complete' ),
			wfMessage( 'translate-percentage-proofread' ),
			wfMessage( 'translate-percentage-fuzzy' ),
		], $this->extraColumns );
	}

	/**
	 * @return string HTML
	 */
	public function createHeader() {
		// Create table header
		$out = Html::openElement(
			'table',
			[ 'class' => 'statstable' ]
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
		$out .= "\n\t\t" . Html::element( 'td', [], $message->text() );
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
		$proofread = $stats[MessageGroupStats::PROOFREAD];

		if ( $total === null ) {
			$na = "\n\t\t" . Html::element( 'td', [ 'data-sort-value' => -1 ], '...' );
			$nap = "\n\t\t" . $this->element( '...', 'AFAFAF', -1 );
			$out = $na . $na . $nap . $nap;

			return $out;
		}

		$out = "\n\t\t" . Html::element( 'td',
			[ 'data-sort-value' => $total ],
			$this->lang->formatNum( $total ) );

		$out .= "\n\t\t" . Html::element( 'td',
			[ 'data-sort-value' => $total - $translated ],
			$this->lang->formatNum( $total - $translated ) );

		if ( $total === 0 ) {
			$transRatio = 0;
			$fuzzyRatio = 0;
			$proofRatio = 0;
		} else {
			$transRatio = $translated / $total;
			$fuzzyRatio = $fuzzy / $total;
			$proofRatio = $translated === 0 ? 0 : $proofread / $translated;
		}

		$out .= "\n\t\t" . $this->element( $this->formatPercentage( $transRatio, 'floor' ),
			$this->getBackgroundColor( $transRatio ),
			sprintf( '%1.5f', $transRatio ) );

		$out .= "\n\t\t" . $this->element( $this->formatPercentage( $proofRatio, 'floor' ),
			$this->getBackgroundColor( $proofRatio ),
			sprintf( '%1.5f', $proofRatio ) );

		$out .= "\n\t\t" . $this->element( $this->formatPercentage( $fuzzyRatio, 'ceil' ),
			$this->getBackgroundColor( $fuzzyRatio, true ),
			sprintf( '%1.5f', $fuzzyRatio ) );

		return $out;
	}

	/**
	 * Makes a nice print from plain float.
	 * @param number $num
	 * @param string $to floor or ceil
	 * @return string Plain text
	 */
	public function formatPercentage( $num, $to = 'floor' ) {
		$num = $to === 'floor' ? floor( 100 * $num ) : ceil( 100 * $num );
		$fmt = $this->lang->formatNum( $num );

		return wfMessage( 'percent', $fmt )->text();
	}

	/**
	 * Gets the name of group with some extra formatting.
	 * @param MessageGroup $group
	 * @return string Html
	 */
	public function getGroupLabel( MessageGroup $group ) {
		$groupLabel = htmlspecialchars( $group->getLabel() );

		// Bold for meta groups.
		if ( $group->isMeta() ) {
			$groupLabel = Html::rawElement( 'b', [], $groupLabel );
		}

		return $groupLabel;
	}

	/**
	 * Gets the name of group linked to translation tool.
	 * @param MessageGroup $group
	 * @param string $code Language code
	 * @param array $params Any extra query parameters.
	 * @return string Html
	 */
	public function makeGroupLink( MessageGroup $group, $code, $params ) {
		$queryParameters = $params + [
			'group' => $group->getId(),
			'language' => $code
		];

		$attributes = [];

		$translateGroupLink = Linker::link(
			$this->translate, $this->getGroupLabel( $group ), $attributes, $queryParameters
		);

		return $translateGroupLink;
	}

	/**
	 * Check whether translations in given group in given language
	 * has been disabled.
	 * @param string $groupId Message group id
	 * @param string $code Language code
	 * @return bool
	 */
	public function isBlacklisted( $groupId, $code ) {
		global $wgTranslateBlacklist;

		$blacklisted = null;

		$checks = [
			$groupId,
			strtok( $groupId, '-' ),
			'*'
		];

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

		$include = Hooks::run( 'Translate:MessageGroupStats:isIncluded', [ $groupId, $code ] );
		if ( !$include ) {
			$blacklisted = true;
		}

		return $blacklisted;
	}

	/**
	 * Used to circumvent ugly tooltips when newlines are used in the
	 * message content ("x\ny" becomes "x y").
	 * @param string $text
	 * @return string
	 */
	public static function formatTooltip( $text ) {
		$wordSeparator = wfMessage( 'word-separator' )->text();

		$text = strtr( $text, [
			"\n" => $wordSeparator,
			"\r" => $wordSeparator,
			"\t" => $wordSeparator,
		] );

		return $text;
	}
}
