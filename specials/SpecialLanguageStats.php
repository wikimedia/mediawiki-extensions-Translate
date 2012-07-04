<?php
/**
 * Contains logic for special page Special:LanguageStats.
 *
 * @file
 * @author Siebrand Mazeland
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2012 Siebrand Mazeland, Niklas Laxström
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
 * @ingroup SpecialPage TranslateSpecialPage Stats
 */
class SpecialLanguageStats extends IncludableSpecialPage {
	/**
	 * @var StatsTable
	 */
	protected $table;

	/**
	 * @var Array
	 */
	protected $targetValueName = array( 'code', 'language' );

	/**
	 * Most of the displayed numbers added together.
	 */
	protected $totals = array( 0, 0, 0 );

	/**
	 * How long spend time calculating missing numbers, before
	 * bailing out.
	 * @var int
	 */
	protected $timelimit = 8;

	/**
	 * Flag to set if nothing to show.
	 * @var bool
	 */
	protected $nothing = false;

	/**
	 * Flag to set if not all numbers are available.
	 * @var bool
	 */
	protected $incomplete = false;

	/**
	 * Whether to hide rows which are fully translated.
	 * @var bool
	 */
	protected $noComplete = true;

	/**
	 * Whether to hide rows which are fully untranslated.
	 * @var bool
	 */
	protected $noEmpty = false;

	/**
	 * The target of stats, language code or group id.
	 */
	protected $target;

	/**
	 * Whether to regenerate stats. Activated by action=purge in query params.
	 * @var bool
	 */
	protected $purge;

	/**
	 * Helper variable to avoid overcounting message groups that appear
	 * multiple times in the list with different parents. Aggregate message
	 * group stats are always excluded from totals.
	 *
	 * @var array
	 */
	protected $statsCounted = array();

	public function __construct() {
		parent::__construct( 'LanguageStats' );

		$this->target = $this->getLanguage()->getCode();
	}

	function execute( $par ) {
		$request = $this->getRequest();

		$this->purge = $request->getVal( 'action' ) === 'purge';
		$this->table = new StatsTable();

		$this->setHeaders();
		$this->outputHeader();

		$out = $this->getOutput();

		$out->addModules( 'ext.translate.special.languagestats' );
		$out->addModules( 'ext.translate.messagetable' );

		$params = explode( '/', $par  );

		if ( isset( $params[0] ) && trim( $params[0] ) ) {
			$this->target = $params[0];
		}

		if ( isset( $params[1] ) ) {
			$this->noComplete = (bool)$params[1];
		}

		if ( isset( $params[2] ) ) {
			$this->noEmpty = (bool)$params[2];
		}

		// Whether the form has been submitted, only relevant if not including
		$submitted = !$this->including() && $request->getVal( 'x' ) === 'D';

		// Default booleans to false if the form was submitted
		foreach ( $this->targetValueName as $key ) {
			$this->target = $request->getVal( $key, $this->target );
		}
		$this->noComplete = $request->getBool( 'suppresscomplete', $this->noComplete && !$submitted );
		$this->noEmpty = $request->getBool( 'suppressempty', $this->noEmpty && !$submitted );

		if ( !$this->including() ) {
			TranslateUtils::addSpecialHelpLink( $out, 'Help:Extension:Translate/Statistics_and_reporting' );
			$out->addHTML( $this->getForm() );
		}

		if ( $this->isValidValue( $this->target ) ) {
			$this->outputIntroduction();
			$output = $this->getTable();
			if ( $this->incomplete ) {
				$out->wrapWikiMsg( "<div class='error'>$1</div>", 'translate-langstats-incomplete' );
			}
			if ( $this->nothing ) {
				$out->wrapWikiMsg( "<div class='error'>$1</div>", 'translate-mgs-nothing' );
			}
			$out->addHTML( $output );
		} elseif ( $submitted ) {
			$this->invalidTarget();
		}
	}

	/**
	 * Return the list of allowed values for target here.
	 * @return array
	 */
	protected function isValidValue( $value ) {
		$langs = Language::getLanguageNames( false );

		return isset( $langs[$value] );
	}

	/// Called when the target is unknown.
	protected function invalidTarget() {
		$this->getOutput()->wrapWikiMsg( "<div class='error'>$1</div>", 'translate-page-no-such-language' );
	}

	/**
	 * HTML for the top form.
	 * @return \string HTML
	 * @todo duplicated code
	 */
	protected function getForm() {
		global $wgScript;

		$out = Html::openElement( 'div' );
		$out .= Html::openElement( 'form', array( 'method' => 'get', 'action' => $wgScript ) );
		$out .= Html::hidden( 'title', $this->getTitle()->getPrefixedText() );
		$out .= Html::hidden( 'x', 'D' ); // To detect submission
		$out .= Html::openElement( 'fieldset' );
		$out .= Html::element( 'legend', null, $this->msg( 'translate-language-code' )->escaped() );
		$out .= Html::openElement( 'table' );

		$out .= Html::openElement( 'tr' );
		$out .= Html::openElement( 'td', array( 'class' => 'mw-label' ) );
		$out .= Xml::label( $this->msg( 'translate-language-code-field-name' )->escaped(), 'language' );
		$out .= Html::closeElement( 'td' );
		$out .= Html::openElement( 'td', array( 'class' => 'mw-input' ) );
		$out .= Xml::input( 'language', 10, $this->target, array( 'id' => 'language' ) );
		$out .= Html::closeElement( 'td' );
		$out .= Html::closeElement( 'tr' );

		$out .= Html::openElement( 'tr' );
		$out .= Html::openElement( 'td', array( 'colspan' => 2 ) );
		$out .= Xml::checkLabel( $this->msg( 'translate-suppress-complete' )->escaped(), 'suppresscomplete', 'suppresscomplete', $this->noComplete );
		$out .= Html::closeElement( 'td' );
		$out .= Html::closeElement( 'tr' );

		$out .= Html::openElement( 'tr' );
		$out .= Html::openElement( 'td', array( 'colspan' => 2 ) );
		$out .= Xml::checkLabel( $this->msg( 'translate-ls-noempty' )->escaped(), 'suppressempty', 'suppressempty', $this->noEmpty );
		$out .= Html::closeElement( 'td' );
		$out .= Html::closeElement( 'tr' );

		$out .= Html::openElement( 'tr' );
		$out .= Html::openElement( 'td', array( 'class' => 'mw-input', 'colspan' => 2 ) );
		$out .= Xml::submitButton( $this->msg( 'translate-ls-submit' )->escaped() );
		$out .= Html::closeElement( 'td' );
		$out .= Html::closeElement( 'tr' );

		$out .= Html::closeElement( 'table' );
		$out .= Html::closeElement( 'fieldset' );
		/* Since these pages are in the tabgroup with Special:Translate,
		 * it makes sense to retain the selected group/language parameter
		 * on post requests even when not relevant to the current page. */
		$val = $this->getRequest()->getVal( 'group' );
		if ( $val !== null ) {
			$out .= Html::hidden( 'group', $val );
		}
		$out .= Html::closeElement( 'form' );
		$out .= Html::closeElement( 'div' );

		return $out;
	}

	/**
	 * Output something helpful to guide the confused user.
	 */
	protected function outputIntroduction() {
		$languageName = TranslateUtils::getLanguageName( $this->target, false, $this->getLanguage()->getCode() );
		$rcInLangLink = Linker::link(
			SpecialPage::getTitleFor( 'Translate', '!recent' ),
			$this->msg( 'languagestats-recenttranslations' )->escaped(),
			array(),
			array(
				'task' => $this->getUser()->isAllowed( 'translate-messagereview' ) ? 'acceptqueue' : 'reviewall',
				'language' => $this->target
			)
		);

		$out = $this->msg( 'languagestats-stats-for', $languageName )->rawParams( $rcInLangLink )->parseAsBlock();
		$this->getOutput()->addHTML( $out );
	}

	/**
	 * If workflow states are configured, adds a workflow states column
	 */
	function addWorkflowStatesColumn() {
		global $wgTranslateWorkflowStates;

		if ( $wgTranslateWorkflowStates ) {
			$this->states = self::getWorkflowStates();

			// An array where keys are state names and values are numbers
			$this->table->addExtraColumn( $this->msg( 'translate-stats-workflow' )->escaped() );
		}

		return;
	}

	/**
	 * If workflow states are configured, adds a cell with the workflow state to the row,
	 * @param $target Whose workflow state do we want, such as language code or group id.
	 * @return string
	 */
	function getWorkflowStateCell( $target ) {
		// This will be set by addWorkflowStatesColumn if needed
		if ( !isset( $this->states ) ) {
			return '';
		}

		if ( $this instanceof SpecialMessageGroupStats ) {
			// Same for every language
			$group = MessageGroups::getGroup( $this->target );
			$stateConfig = $group->getWorkflowConfiguration();
		} else {
			// The message group for this row
			$group = MessageGroups::getGroup( $target );
			$stateConfig = $group->getWorkflowConfiguration();
		}


		$state = isset( $this->states[$target] ) ? $this->states[$target] : '';

		$sortValue = -1;
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

		$stateMessage = $this->msg( "translate-workflow-state-$state" )->escaped();
		$stateText = $stateMessage->isBlank() ? $state : $stateMessage->text();

		return "\n\t\t" . $this->table->element(
			$stateText,
			$stateColor,
			$sortValue
		);
	}

	/**
	 * Returns the table itself.
	 * @return \string HTML
	 */
	function getTable() {
		$table = $this->table;

		$this->addWorkflowStatesColumn();
		$out = '';

		if ( $this->purge ) {
			MessageGroupStats::clearLanguage( $this->target );
		}

		MessageGroupStats::setTimeLimit( $this->timelimit );
		$cache = MessageGroupStats::forLanguage( $this->target );

		$structure = MessageGroups::getGroupStructure();
		foreach ( $structure as $item ) {
			$out .= $this->makeGroupGroup( $item, $cache );
		}

		if ( $out ) {
			$table->setMainColumnHeader( $this->msg( 'translate-ls-column-group' )->escaped() );
			$out = $table->createHeader() . "\n" . $out;
			$out .= Html::closeElement( 'tbody' );

			$out .= Html::openElement( 'tfoot' );
			$out .= $table->makeTotalRow( $this->msg( 'translate-languagestats-overall' )->escaped(), $this->totals );
			$out .= Html::closeElement( 'tfoot' );

			$out .= Html::closeElement( 'table' );

			return $out;
		} else {
			$this->nothing = true;

			return '';
		}

		/// @todo Allow extra message here, once total translated volume goes
		///       over a certain percentage? (former live hack at translatewiki)
		/// if ( $this->totals['2'] && ( $this->totals['1'] / $this->totals['2'] ) > 0.95 ) {
		/// 	$out .= wfMessage( 'translate-somekey' );
		/// }
	}

	/**
	 * Creates a html table row for given (top-level) message group.
	 * If $item is an array, meaning that the first group is an
	 * AggregateMessageGroup and the latter are its children, it will recurse
	 * and create rows for them too.
	 * @param $item Array|MessageGroup
	 * @param $cache Array Cache as returned by MessageGroupStats::forLanguage
	 * @param $parent MessageGroup (do not use, used internally only)
	 * @return string
	 */
	protected function makeGroupGroup( $item, array $cache, MessageGroup $parent = null ) {
		if ( !is_array( $item ) ) {
			return $this->makeGroupRow( $item, $cache, $parent );
		}

		// The first group in the array is the parent AggregateMessageGroup
		$out = '';
		$top = array_shift( $item );
		$out .= $this->makeGroupRow( $top, $cache, $parent );

		// Rest are children
		foreach ( $item as $subgroup ) {
			$out .= $this->makeGroupGroup( $subgroup, $cache, $top );
		}

		return $out;
	}

	/**
	 * Actually creates the table for single message group, unless it
	 * is blacklisted or hidden by filters.
	 * @return string
	 */
	protected function makeGroupRow( MessageGroup $group, array $cache, MessageGroup $parent = null ) {
		$groupId = $group->getId();

		$translatableLanguages = $group->getTranslatableLanguages();
		if ( $this->table->isBlacklisted( $groupId, $this->target ) !== null
			|| !isset( $translatableLanguages[$this->target] ) )
		{
			return '';
		}

		$stats = $cache[$groupId];

		list( $total, $translated, $fuzzy ) = $stats;
		if ( $total === null ) {
			$this->incomplete = true;
			$extra = array();
		} else {
			if( $total === 0 ) {
				error_log( $groupId . ' has 0 messages.' );
			}

			if ( $this->noComplete && $fuzzy === 0 && $translated === $total ) {
				return '';
			}

			if ( $this->noEmpty && $translated === 0 && $fuzzy === 0 ) {
				return '';
			}

			if ( $translated === $total ) {
				$extra = array( 'task' => 'reviewall' );
			} else {
				$extra = array();
			}
		}

		if ( !$group instanceof AggregateMessageGroup  ) {
			if ( !isset( $this->statsCounted[$groupId] ) ) {
				$this->totals = MessageGroupStats::multiAdd( $this->totals, $stats );
				$this->statsCounted[$groupId] = true;
			}
		}

		$rowParams = array();
		$rowParams['data-groupid'] = $groupId;
		$rowParams['class'] = get_class( $group );
		if ( $parent ) {
			$rowParams['data-parentgroup'] = $parent->getId();
		}

		$out  = "\t" . Html::openElement( 'tr', $rowParams );
		$out .= "\n\t\t" . Html::rawElement( 'td', array(),
			$this->table->makeGroupLink( $group, $this->target, $extra ) );
		$out .= $this->table->makeNumberColumns( $fuzzy, $translated, $total );
		$out .= $this->getWorkflowStateCell( $groupId );

		$out .= "\n\t" . Html::closeElement( 'tr' ) . "\n";

		return $out;
	}

	protected function getWorkflowStates() {
		$db = wfGetDB( DB_SLAVE );
		$key = array_pop( $this->targetValueName );
		switch ( $key ) {
			case 'group':
				$targetCol = 'tgr_group';
				$selectKey = 'tgr_lang';
				break;
			case 'language':
				$targetCol = 'tgr_lang';
				$selectKey = 'tgr_group';
				break;
		}

		$res = $db->select(
			'translate_groupreviews',
			array( 'tgr_state', $selectKey ),
			array( $targetCol => $this->target ),
			__METHOD__
		);

		$states = array();

		foreach ( $res as $row ) {
			$states[$row->$selectKey] = $row->tgr_state;
		}

		return $states;
	}
}
