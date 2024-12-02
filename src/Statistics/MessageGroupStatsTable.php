<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupReviewStore;
use MediaWiki\Extension\Translate\MessageProcessing\MessageGroupMetadata;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Html\Html;
use MediaWiki\Language\Language;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;
use MessageGroup;
use MessageLocalizer;

/**
 * Used to build the table displayed on Special:MessageGroupStats
 * @author Abijeet Patro
 * @since 2023.01
 * @license GPL-2.0-or-later
 */
class MessageGroupStatsTable {
	private LinkRenderer $linkRenderer;
	private MessageLocalizer $localizer;
	private Language $interfaceLanguage;
	private StatsTable $table;
	private MessageGroupReviewStore $groupReviewStore;
	private MessageGroupMetadata $messageGroupMetadata;
	/** Flag to set if not all numbers are available. */
	private bool $incompleteStats;
	private array $languageNames;
	private Title $translateTitle;
	/** Keys are state names and values are numbers */
	private array $states;
	private bool $haveTranslateWorkflowStates;

	public function __construct(
		StatsTable $table,
		LinkRenderer $linkRenderer,
		MessageLocalizer $localizer,
		Language $interfaceLanguage,
		MessageGroupReviewStore $groupReviewStore,
		MessageGroupMetadata $messageGroupMetadata,
		bool $haveTranslateWorkflowStates
	) {
		$this->table = $table;
		$this->linkRenderer = $linkRenderer;
		$this->incompleteStats = false;
		$this->localizer = $localizer;
		$this->interfaceLanguage = $interfaceLanguage;
		$this->groupReviewStore = $groupReviewStore;
		$this->messageGroupMetadata = $messageGroupMetadata;
		$this->haveTranslateWorkflowStates = $haveTranslateWorkflowStates;
		$this->languageNames = Utilities::getLanguageNames( $this->interfaceLanguage->getCode() );
		$this->translateTitle = SpecialPage::getTitleFor( 'Translate' );
	}

	public function get(
		array $stats,
		MessageGroup $group,
		bool $noComplete,
		bool $noEmpty
	): ?string {
		$out = '';
		$rowCount = 0;
		$totals = MessageGroupStats::getEmptyStats();
		$groupId = $group->getId();

		$languages = array_keys(
			Utilities::getLanguageNames( $this->interfaceLanguage->getCode() )
		);
		sort( $languages );
		$this->filterPriorityLangs( $languages, $groupId, $stats );

		// If workflow states are configured, adds a workflow states column
		if ( $this->haveTranslateWorkflowStates ) {
			$this->table->addExtraColumn( $this->localizer->msg( 'translate-stats-workflow' ) );
		}

		foreach ( $languages as $code ) {
			if ( $this->table->isExcluded( $group, $code ) ) {
				continue;
			}

			$languageStats = $stats[$code];
			$row = $this->makeRow(
				$this->table,
				$code,
				$languageStats,
				$group,
				$rowCount,
				$noComplete,
				$noEmpty
			);
			if ( $row ) {
				$rowCount += 1;
				$out .= $row;
				$totals = MessageGroupStats::multiAdd( $totals, $languageStats );
			}
		}

		if ( $out ) {
			$this->table->setMainColumnHeader( $this->localizer->msg( 'translate-mgs-column-language' ) );
			$out = $this->table->createHeader() . "\n" . $out;
			$out .= Html::closeElement( 'tbody' );

			$out .= Html::openElement( 'tfoot' );
			$out .= $this->table->makeTotalRow(
				$this->localizer->msg( 'translate-mgs-totals' )->numParams( $rowCount ),
				$totals
			);
			$out .= Html::closeElement( 'tfoot' );

			$out .= Html::closeElement( 'table' );

			return $out;
		} else {
			return null;
		}
	}

	public function areStatsIncomplete(): bool {
		return $this->incompleteStats;
	}

	private function makeRow(
		StatsTable $table,
		string $languageCode,
		array $stats,
		MessageGroup $group,
		int $rowCount,
		bool $noComplete,
		bool $noEmpty
	): ?string {
		$total = $stats[MessageGroupStats::TOTAL];
		$translated = $stats[MessageGroupStats::TRANSLATED];
		$fuzzy = $stats[MessageGroupStats::FUZZY];
		$extra = [];

		if ( $total === null ) {
			$this->incompleteStats = true;
		} else {
			if ( $noComplete && $fuzzy === 0 && $translated === $total ) {
				return null;
			}

			if ( $noEmpty && $translated === 0 && $fuzzy === 0 ) {
				return null;
			}

			// Skip below 2% if "don't show without translations" is checked.
			if ( $noEmpty && ( $translated / $total ) < 0.02 ) {
				return null;
			}

			if ( $translated === $total ) {
				$extra = [ 'action' => 'proofread' ];
			}
		}

		$rowParams = [];
		if ( $rowCount % 2 === 0 ) {
			$rowParams[ 'class' ] = 'tux-statstable-even';
		}

		$out = "\t" . Html::openElement( 'tr', $rowParams );
		$out .= "\n\t\t" . $this->getMainColumnCell( $languageCode, $extra, $group->getId() );
		$out .= $table->makeNumberColumns( $stats );
		$out .= $this->getWorkflowStateCell( $table, $languageCode, $group );

		$out .= "\n\t" . Html::closeElement( 'tr' ) . "\n";

		return $out;
	}

	private function getMainColumnCell( string $code, array $params, string $groupId ): string {
		if ( isset( $this->languageNames[$code] ) ) {
			$text = "$code: {$this->languageNames[$code]}";
		} else {
			$text = $code;
		}

		// Do not render links when generating table for MessagePrefixMessageGroup
		// as this is a dynamic group whose contents are based on user input
		if ( $groupId === '!prefix' ) {
			return Html::rawElement( 'td', [], $text );
		}

		$queryParameters = $params + [
			'group' => $groupId,
			'language' => $code
		];

		$link = $this->linkRenderer->makeKnownLink(
			$this->translateTitle,
			$text,
			[],
			$queryParameters
		);

		return Html::rawElement( 'td', [], $link );
	}

	/** If workflow states are configured, adds a cell with the workflow state to the row */
	private function getWorkflowStateCell( StatsTable $table, string $language, MessageGroup $group ): string {
		if ( !$this->haveTranslateWorkflowStates ) {
			return '';
		}

		$this->states ??= $this->groupReviewStore->getWorkflowStatesForGroup( $group->getId() );
		return $table->makeWorkflowStateCell( $this->states[$language] ?? null, $group, $language );
	}

	/**
	 * Filter an array of languages based on whether a priority set of
	 * languages present for the passed group. If priority languages are
	 * present, to that list add languages with more than 0% translation.
	 */
	private function filterPriorityLangs( array &$languages, string $group, array $cache ): void {
		$filterLangs = $this->messageGroupMetadata->get( $group, 'prioritylangs' );
		if ( $filterLangs === false || strlen( $filterLangs ) === 0 ) {
			// No restrictions, keep everything
			return;
		}
		$filter = array_flip( explode( ',', $filterLangs ) );
		foreach ( $languages as $id => $code ) {
			if ( isset( $filter[$code] ) ) {
				continue;
			}
			$translated = $cache[$code][1];
			if ( $translated === 0 ) {
				unset( $languages[$id] );
			}
		}
	}
}
