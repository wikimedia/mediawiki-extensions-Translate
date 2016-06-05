<?php
/**
 * Contains logic for special page Special:MessageGroupStats.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0+
 */

/**
 * Implements includable special page Special:MessageGroupStats which provides
 * translation statistics for all languages for a group.
 *
 * @ingroup SpecialPage TranslateSpecialPage Stats
 */
class SpecialMessageGroupStats extends SpecialLanguageStats {
	/// Overwritten from SpecialLanguageStats
	protected $targetValueName = array( 'group' );
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

	protected function getGroupName() {
		return 'wiki';
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
			array( 'translate-mgs-invalid-group', $this->target )
		);
	}

	/// Overwritten from SpecialLanguageStats
	protected function outputIntroduction() {
		$group = $this->getRequest()->getVal( 'group' );
		$priorityLangs = TranslateMetadata::get( $group, 'prioritylangs' );
		if ( $priorityLangs ) {
			$this->getOutput()->addWikiMsg( 'tpt-priority-languages', $priorityLangs );
		}
	}

	/// Overwriten from SpecialLanguageStats
	protected function getform() {
		global $wgScript;

		$out = Html::openElement( 'div' );
		$out .= Html::openElement( 'form', array( 'method' => 'get', 'action' => $wgScript ) );
		$out .= Html::hidden( 'title', $this->getPageTitle()->getPrefixedText() );
		$out .= Html::hidden( 'x', 'D' ); // To detect submission
		$out .= Html::openElement( 'fieldset' );
		$out .= Html::element( 'legend', array(), $this->msg( 'translate-mgs-fieldset' )->text() );
		$out .= Html::openElement( 'table' );

		$out .= Html::openElement( 'tr' );
		$out .= Html::openElement( 'td', array( 'class' => 'mw-label' ) );
		$out .= Xml::label( $this->msg( 'translate-mgs-group' )->text(), 'group' );
		$out .= Html::closeElement( 'td' );
		$out .= Html::openElement( 'td', array( 'class' => 'mw-input' ) );
		$out .= $this->getGroupSelector( $this->target )->getHTML();
		$out .= Html::closeElement( 'td' );
		$out .= Html::closeElement( 'tr' );

		$out .= Html::openElement( 'tr' );
		$out .= Html::openElement( 'td', array( 'colspan' => 2 ) );
		$out .= Xml::checkLabel(
			$this->msg( 'translate-mgs-nocomplete' )->text(),
			'suppresscomplete',
			'suppresscomplete',
			$this->noComplete
		);
		$out .= Html::closeElement( 'td' );
		$out .= Html::closeElement( 'tr' );

		$out .= Html::openElement( 'tr' );
		$out .= Html::openElement( 'td', array( 'colspan' => 2 ) );
		$out .= Xml::checkLabel(
			$this->msg( 'translate-mgs-noempty' )->text(),
			'suppressempty',
			'suppressempty',
			$this->noEmpty
		);
		$out .= Html::closeElement( 'td' );
		$out .= Html::closeElement( 'tr' );

		$out .= Html::openElement( 'tr' );
		$out .= Html::openElement( 'td', array( 'class' => 'mw-input', 'colspan' => 2 ) );
		$out .= Xml::submitButton( $this->msg( 'translate-mgs-submit' )->text() );
		$out .= Html::closeElement( 'td' );
		$out .= Html::closeElement( 'tr' );

		$out .= Html::closeElement( 'table' );
		$out .= Html::closeElement( 'fieldset' );
		/* Since these pages are in the tabgroup with Special:Translate,
		 * it makes sense to retain the selected group/language parameter
		 * on post requests even when not relevant to the current page. */
		$val = $this->getRequest()->getVal( 'language' );
		if ( $val !== null ) {
			$out .= Html::hidden( 'language', $val );
		}
		$out .= Html::closeElement( 'form' );
		$out .= Html::closeElement( 'div' );

		return $out;
	}

	/**
	 * Overwriten from SpecialLanguageStats
	 *
	 * @return string
	 */
	protected function getTable() {
		$table = $this->table;

		$this->addWorkflowStatesColumn();
		$out = '';

		if ( $this->purge ) {
			MessageGroupStats::clearGroup( $this->target );
		}

		MessageGroupStats::setTimeLimit( $this->timelimit );
		$cache = MessageGroupStats::forGroup( $this->target );

		$languages = array_keys(
			TranslateUtils::getLanguageNames( $this->getLanguage()->getCode() )
		);
		sort( $languages );
		$this->filterPriorityLangs( $languages, $this->target, $cache );
		foreach ( $languages as $code ) {
			if ( $table->isBlacklisted( $this->target, $code ) !== null ) {
				continue;
			}
			$out .= $this->makeRow( $code, $cache );
		}

		if ( $out ) {
			$table->setMainColumnHeader( $this->msg( 'translate-mgs-column-language' ) );
			$out = $table->createHeader() . "\n" . $out;
			$out .= Html::closeElement( 'tbody' );

			$out .= Html::openElement( 'tfoot' );
			$out .= $table->makeTotalRow( $this->msg( 'translate-mgs-totals' ), $this->totals );
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
	 * @param $languages Array of Languages to be filtered
	 * @param $group
	 * @param $cache
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
	 * @param $code
	 * @param $cache
	 * @return string
	 */
	protected function makeRow( $code, $cache ) {
		$stats = $cache[$code];
		$total = $stats[MessageGroupStats::TOTAL];
		$translated = $stats[MessageGroupStats::TRANSLATED];
		$fuzzy = $stats[MessageGroupStats::FUZZY];

		if ( $total === null ) {
			$this->incomplete = true;
			$extra = array();
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
				$extra = array( 'action' => 'proofread' );
			} else {
				$extra = array();
			}
		}

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
	 * @param $code
	 * @param $params
	 * @return string
	 */
	protected function getMainColumnCell( $code, $params ) {
		if ( !isset( $this->names ) ) {
			$this->names = TranslateUtils::getLanguageNames( $this->getLanguage()->getCode() );
			$this->translate = SpecialPage::getTitleFor( 'Translate' );
		}

		$queryParameters = $params + array(
			'group' => $this->target,
			'language' => $code
		);

		if ( isset( $this->names[$code] ) ) {
			$text = htmlspecialchars( "$code: {$this->names[$code]}" );
		} else {
			$text = htmlspecialchars( $code );
		}
		$link = Linker::linkKnown( $this->translate, $text, array(), $queryParameters );

		return Html::rawElement( 'td', array(), $link );
	}

	// @codingStandardsIgnoreStart PHP CodeSniffer warns "Useless method overriding
	// detected", but that's not the case.
	/**
	 * @param string $field
	 * @param string $filter
	 * @return array
	 */
	protected function getWorkflowStates( $field = 'tgr_lang', $filter = 'tgr_group' ) {
		return parent::getWorkflowStates( $field, $filter );
	} // @codingStandardsIgnoreEnd

	/**
	 * Creates a simple message group selector.
	 *
	 * @param string|bool $default Group id of the group chosen by default. Optional.
	 * @return XmlSelect
	 */
	protected function getGroupSelector( $default = false ) {
		$groups = MessageGroups::getAllGroups();
		$selector = new XmlSelect( 'group', 'group', $default );

		foreach ( $groups as $id => $class ) {
			if ( MessageGroups::getGroup( $id )->exists() ) {
				$selector->addOption( $class->getLabel(), $id );
			}
		}

		return $selector;
	}
}
