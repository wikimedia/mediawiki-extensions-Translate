<?php
declare( strict_types=1 );

namespace MediaWiki\Extension\Translate\Statistics;

use HtmlArmor;
use MediaWiki\Extension\Translate\MessageProcessing\MessageGroupMetadata;
use MediaWiki\Extension\Translate\Utilities\ConfigHelper;
use MediaWiki\Html\Html;
use MediaWiki\Language\Language;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Message\Message;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\TitleValue;
use MediaWiki\Xml\Xml;
use MessageGroup;
use MessageLocalizer;

/**
 * Implements generation of HTML stats table.
 * @author Siebrand Mazeland
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
class StatsTable {
	protected TitleValue $translate;
	private LinkRenderer $linkRenderer;
	private ConfigHelper $configHelper;
	private MessageLocalizer $messageLocalizer;
	protected Language $language;
	protected string $mainColumnHeader;
	/** @var Message[] */
	protected array $extraColumns = [];
	private MessageGroupMetadata $messageGroupMetadata;

	public function __construct(
		LinkRenderer $linkRenderer,
		ConfigHelper $configHelper,
		MessageLocalizer $messageLocalizer,
		Language $language,
		MessageGroupMetadata $messageGroupMetadata
	) {
		$this->translate = SpecialPage::getTitleValueFor( 'Translate' );
		$this->linkRenderer = $linkRenderer;
		$this->configHelper = $configHelper;
		$this->messageLocalizer = $messageLocalizer;
		$this->language = $language;
		$this->messageGroupMetadata = $messageGroupMetadata;
	}

	/**
	 * Statistics table element (heading or regular cell)
	 * @param string $in Element contents.
	 * @param string $bgcolor Backround color in ABABAB format.
	 * @param string $sort Value used for sorting.
	 * @return string Html td element.
	 */
	public function element( string $in, string $bgcolor = '', string $sort = '' ): string {
		$attributes = [];

		if ( $sort ) {
			$attributes['data-sort-value'] = $sort;
		}

		if ( $bgcolor ) {
			$attributes['class'] = 'notheme skin-invert';
			$attributes['style'] = 'background-color: #' . $bgcolor;
		}

		$element = Html::element( 'td', $attributes, $in );

		return $element;
	}

	// FIXME: should be moved to .less file to prevent potential Linter errors
	public function getBackgroundColor( float $percentage, bool $fuzzy = false ): string {
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
		$index = (int)floor( $percentage * 10 );
		$colors = [
			'eaf3ff', 'e2ebfc', 'dae3fa', 'd2dbf7', 'c9d4f5',
			'c1ccf2', 'b8c4ef', 'b1bced', 'a8b4ea', '9fade8',
			'96a6e5'
		];

		return $colors[ $index ];
	}

	private function getMainColumnHeader(): string {
		return $this->mainColumnHeader;
	}

	public function setMainColumnHeader( Message $msg ): void {
		$this->mainColumnHeader = $this->createColumnHeader( $msg );
	}

	/** @return string HTML */
	private function createColumnHeader( Message $msg ): string {
		return Html::element( 'th', [], $msg->text() );
	}

	public function addExtraColumn( Message $column ): void {
		$this->extraColumns[] = $column;
	}

	/** @return Message[] */
	private function getOtherColumnHeaders(): array {
		return array_merge( [
			$this->messageLocalizer->msg( 'translate-total' ),
			$this->messageLocalizer->msg( 'translate-untranslated' ),
			$this->messageLocalizer->msg( 'translate-percentage-complete' ),
			$this->messageLocalizer->msg( 'translate-percentage-proofread' ),
			$this->messageLocalizer->msg( 'translate-percentage-fuzzy' ),
		], $this->extraColumns );
	}

	/** @return string HTML */
	public function createHeader(): string {
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
	 * @return string HTML
	 */
	public function makeTotalRow( Message $message, array $stats ): string {
		$out = "\t" . Html::openElement( 'tr' );
		$out .= "\n\t\t" . Html::element( 'td', [], $message->text() );
		$out .= $this->makeNumberColumns( $stats );
		$out .= "\n\t" . Xml::closeElement( 'tr' ) . "\n";

		return $out;
	}

	/**
	 * Makes partial row from completion numbers
	 * @return string HTML
	 */
	public function makeNumberColumns( array $stats ): string {
		$total = $stats[MessageGroupStats::TOTAL];
		$translated = $stats[MessageGroupStats::TRANSLATED];
		$fuzzy = $stats[MessageGroupStats::FUZZY];
		$proofread = $stats[MessageGroupStats::PROOFREAD];

		if ( $total === null ) {
			$na = "\n\t\t" . Html::element( 'td', [ 'data-sort-value' => -1 ], '...' );
			$nap = "\n\t\t" . $this->element( '...', 'AFAFAF', '-1' );
			$out = $na . $na . $nap . $nap;

			return $out;
		}

		$out = "\n\t\t" . Html::element( 'td',
			[ 'data-sort-value' => $total ],
			$this->language->formatNum( $total ) );

		$out .= "\n\t\t" . Html::element( 'td',
			[ 'data-sort-value' => $total - $translated ],
			$this->language->formatNum( $total - $translated ) );

		if ( $total === 0 ) {
			$transRatio = 0;
			$fuzzyRatio = 0;
			$proofRatio = 0;
		} else {
			$transRatio = $translated / $total;
			$fuzzyRatio = $fuzzy / $total;
			$proofRatio = $translated ? $proofread / $translated : 0;
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

	public function makeWorkflowStateCell( ?string $state, MessageGroup $group, string $language ): string {
		if ( $state === null || $group->getSourceLanguage() === $language ) {
			return "\n\t\t" . $this->element( '', '', '-1' );
		}

		$stateConfig = $group->getMessageGroupStates()->getStates();
		$sortValue = '-1';
		$stateColor = '';
		if ( isset( $stateConfig[$state] ) ) {
			$sortIndex = array_flip( array_keys( $stateConfig ) );
			$sortValue = $sortIndex[$state] + 1;

			if ( is_string( $stateConfig[$state] ) ) {
				// BC for old configuration format
				$stateColor = $stateConfig[$state];
			} elseif ( isset( $stateConfig[$state]['color'] ) ) {
				$stateColor = $stateConfig[$state]['color'];
			}
		}

		$stateMessage = $this->messageLocalizer->msg( "translate-workflow-state-$state" );
		$stateText = $stateMessage->isBlank() ? $state : $stateMessage->text();

		return "\n\t\t" . $this->element(
			$stateText,
			$stateColor,
			(string)$sortValue
		);
	}

	/**
	 * Makes a nice print from plain float.
	 * @param int|float $num
	 * @param string $to floor or ceil
	 * @return string Plain text
	 */
	public function formatPercentage( $num, string $to = 'floor' ): string {
		$num = $to === 'floor' ? floor( 100 * $num ) : ceil( 100 * $num );
		$fmt = $this->language->formatNum( $num );

		return $this->messageLocalizer->msg( 'percent', $fmt )->text();
	}

	/**
	 * Gets the name of group with some extra formatting.
	 * @return string HTML
	 */
	private function getGroupLabel( MessageGroup $group ): string {
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
	 * @return string HTML
	 */
	public function makeGroupLink( MessageGroup $group, string $code, array $params ): string {
		$queryParameters = $params + [
			'group' => $group->getId(),
			'language' => $code
		];

		return $this->linkRenderer->makeLink(
			$this->translate,
			new HtmlArmor( $this->getGroupLabel( $group ) ),
			[],
			$queryParameters
		);
	}

	/**
	 * Check whether translations in given group in given language
	 * has been disabled.
	 * @param MessageGroup $group Message group
	 * @param string $code Language code
	 */
	public function isExcluded( MessageGroup $group, string $code ): bool {
		return $this->configHelper->isTargetLanguageDisabled( $group, $code )
			|| $this->messageGroupMetadata->isExcluded( $group->getId(), $code );
	}
}
