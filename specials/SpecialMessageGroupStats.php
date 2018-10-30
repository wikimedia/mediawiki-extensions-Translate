<?php
/**
 * Contains logic for special page Special:MessageGroupStats.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 */

/**
 * Implements includable special page Special:MessageGroupStats which provides
 * translation statistics for all languages for a group.
 *
 * @ingroup SpecialPage TranslateSpecialPage Stats
 */
class SpecialMessageGroupStats extends SpecialLanguageStats {
	/// Overwritten from SpecialLanguageStats
	protected $targetValueName = [ 'group' ];
	/// Overwritten from SpecialLanguageStats
	protected $noComplete = false;
	/// Overwritten from SpecialLanguageStats
	protected $noEmpty = true;

	protected $names;

	protected $translate;

	public function __construct() {
		SpecialPage::__construct( 'MessageGroupStats' );
		$this->totals = MessageGroupStats::getEmptyStats();
	}

	/// Overwritten from SpecialPage
	public function getDescription() {
		return $this->msg( 'translate-mgs-pagename' )->text();
	}

	/// Overwritten from SpecialLanguageStats
	protected function loadStatistics( $target, $flags = 0 ) {
		return MessageGroupStats::forGroup( $target, $flags );
	}

	/// Overwritten from SpecialLanguageStats
	protected function getCacheRebuildJobParameters( $target ) {
		return [ 'groupid' => $target ];
	}

	/// Overwritten from SpecialLanguageStats
	protected function isValidValue( $value ) {
		$group = MessageGroups::getGroup( $value );
		if ( $group ) {
			if ( MessageGroups::isDynamic( $group ) ) {
				/* Dynamic groups are not listed, but it is possible to end up
				 * on this page with a dynamic group by navigating from
				 * translation or proofreading activity or by giving group id
				 * of dynamic group explicitly. Ignore dynamic group to avoid
				 * throwing exceptions later. */
				$group = false;
			} else {
				$this->target = $group->getId();
			}
		}

		return (bool)$group;
	}

	/// Overwritten from SpecialLanguageStats
	protected function invalidTarget() {
		$this->getOutput()->wrapWikiMsg(
			"<div class='error'>$1</div>",
			[ 'translate-mgs-invalid-group', $this->target ]
		);
	}

	/// Overwritten from SpecialLanguageStats
	protected function outputIntroduction() {
		$priorityLangs = TranslateMetadata::get( $this->target, 'prioritylangs' );
		if ( $priorityLangs ) {
			$this->getOutput()->addWikiMsg( 'tpt-priority-languages', $priorityLangs );
		}
	}

	/// Overwriten from SpecialLanguageStats
	protected function addForm() {
		$formDescriptor = [
			'select' => [
				'type' => 'select',
				'name' => 'group',
				'id' => 'group',
				'label' => $this->msg( 'translate-mgs-group' )->text(),
				'options' => $this->getGroupOptions(),
				'default' => $this->target
			],
			'nocomplete-check' => [
				'type' => 'check',
				'name' => 'suppresscomplete',
				'id' => 'suppresscomplete',
				'label' => $this->msg( 'translate-mgs-nocomplete' )->text(),
				'default' => $this->noComplete,
			],
			'noempty-check' => [
				'type' => 'check',
				'name' => 'suppressempty',
				'id' => 'suppressempty',
				'label' => $this->msg( 'translate-mgs-noempty' )->text(),
				'default' => $this->noEmpty,
			]
		];

		$htmlForm = HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() );

		/* Since these pages are in the tabgroup with Special:Translate,
		 * it makes sense to retain the selected group/language parameter
		 * on post requests even when not relevant to the current page. */
		$val = $this->getRequest()->getVal( 'language' );
		if ( $val !== null ) {
			$htmlForm->addHiddenField( 'language', $val );
		}

		$htmlForm
			->addHiddenField( 'x', 'D' ) // To detect submission
			->setMethod( 'get' )
			->setSubmitTextMsg( 'translate-mgs-submit' )
			->setWrapperLegendMsg( 'translate-mgs-fieldset' )
			->prepareForm()
			->displayForm( false );
	}

	/// Overwritten from SpecialLanguageStats
	protected function getTable( $stats ) {
		$table = $this->table;

		$this->addWorkflowStatesColumn();
		$out = '';

		$this->numberOfShownLanguages = 0;
		$languages = array_keys(
			TranslateUtils::getLanguageNames( $this->getLanguage()->getCode() )
		);
		sort( $languages );
		$this->filterPriorityLangs( $languages, $this->target, $stats );
		foreach ( $languages as $code ) {
			if ( $table->isBlacklisted( $this->target, $code ) !== null ) {
				continue;
			}
			$out .= $this->makeRow( $code, $stats );
		}

		if ( $out ) {
			$table->setMainColumnHeader( $this->msg( 'translate-mgs-column-language' ) );
			$out = $table->createHeader() . "\n" . $out;
			$out .= Html::closeElement( 'tbody' );

			$out .= Html::openElement( 'tfoot' );
			$out .= $table->makeTotalRow(
				$this->msg( 'translate-mgs-totals' )
					->numParams( $this->numberOfShownLanguages ),
				$this->totals
			);
			$out .= Html::closeElement( 'tfoot' );

			$out .= Html::closeElement( 'table' );

			return $out;
		} else {
			$this->nothing = true;

			return '';
		}
	}

	/**
	 * Filter an array of languages based on whether a priority set of
	 * languages present for the passed group. If priority languages are
	 * present, to that list add languages with more than 0% translation.
	 * @param array &$languages Array of Languages to be filtered
	 * @param string $group
	 * @param array $cache
	 */
	protected function filterPriorityLangs( &$languages, $group, $cache ) {
		$filterLangs = TranslateMetadata::get( $group, 'prioritylangs' );
		if ( strlen( $filterLangs ) === 0 ) {
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

	/**
	 * @param string $code
	 * @param array $cache
	 * @return string
	 */
	protected function makeRow( $code, $cache ) {
		$stats = $cache[$code];
		$total = $stats[MessageGroupStats::TOTAL];
		$translated = $stats[MessageGroupStats::TRANSLATED];
		$fuzzy = $stats[MessageGroupStats::FUZZY];

		if ( $total === null ) {
			$this->incomplete = true;
			$extra = [];
		} else {
			if ( $this->noComplete && $fuzzy === 0 && $translated === $total ) {
				return '';
			}

			if ( $this->noEmpty && $translated === 0 && $fuzzy === 0 ) {
				return '';
			}

			// Skip below 2% if "don't show without translations" is checked.
			if ( $this->noEmpty && ( $translated / $total ) < 0.02 ) {
				return '';
			}

			if ( $translated === $total ) {
				$extra = [ 'action' => 'proofread' ];
			} else {
				$extra = [];
			}
		}
		$this->numberOfShownLanguages += 1;
		$this->totals = MessageGroupStats::multiAdd( $this->totals, $stats );

		$out = "\t" . Html::openElement( 'tr' );
		$out .= "\n\t\t" . $this->getMainColumnCell( $code, $extra );
		$out .= $this->table->makeNumberColumns( $stats );
		$state = $this->getWorkflowStateValue( $code );
		$out .= $this->getWorkflowStateCell( $code, $state );

		$out .= "\n\t" . Html::closeElement( 'tr' ) . "\n";

		return $out;
	}

	/**
	 * @param string $code
	 * @param array $params
	 * @return string
	 */
	protected function getMainColumnCell( $code, $params ) {
		if ( !isset( $this->names ) ) {
			$this->names = TranslateUtils::getLanguageNames( $this->getLanguage()->getCode() );
			$this->translate = SpecialPage::getTitleFor( 'Translate' );
		}

		$queryParameters = $params + [
			'group' => $this->target,
			'language' => $code
		];

		if ( isset( $this->names[$code] ) ) {
			$text = "$code: {$this->names[$code]}";
		} else {
			$text = $code;
		}
		$link = $this->getLinkRenderer()->makeKnownLink(
			$this->translate,
			$text,
			[],
			$queryParameters
		);

		return Html::rawElement( 'td', [], $link );
	}

	/**
	 * @param string $field
	 * @param string $filter
	 * @return array
	 */
	protected function getWorkflowStates( $field = 'tgr_lang', $filter = 'tgr_group' ) {
		return parent::getWorkflowStates( $field, $filter );
	}

	/**
	 * Creates a simple message group options.
	 *
	 * @return array $options
	 */
	protected function getGroupOptions() {
		$options = [];
		$groups = MessageGroups::getAllGroups();

		foreach ( $groups as $id => $class ) {
			if ( MessageGroups::getGroup( $id )->exists() ) {
				$options[$class->getLabel()] = $id;
			}
		}

		return $options;
	}
}
