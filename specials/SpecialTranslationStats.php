<?php
/**
 * Contains logic for special page Special:TranslationStats.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0+
 */

/**
 * @defgroup Stats Statistics
 * Collection of code to produce various kinds of statistics.
 */

/**
 * Includable special page for generating graphs on translations.
 *
 * @ingroup SpecialPage TranslateSpecialPage Stats
 */
class SpecialTranslationStats extends SpecialPage {
	/// @since 2012-03-05
	protected static $graphs = array(
		'edits' => 'TranslatePerLanguageStats',
		'users' => 'TranslatePerLanguageStats',
		'registrations' => 'TranslateRegistrationStats',
		'reviews' => 'ReviewPerLanguageStats',
		'reviewers' => 'ReviewPerLanguageStats',
	);

	public function __construct() {
		parent::__construct( 'TranslationStats' );
	}

	public function isIncludable() {
		return true;
	}

	protected function getGroupName() {
		return 'wiki';
	}

	/**
	 * @since 2012-03-05
	 * @return array List of graph types
	 */
	public function getGraphTypes() {
		return array_keys( self::$graphs );
	}

	/**
	 * @since 2012-03-05
	 * @param string $type
	 * @return string
	 */
	public function getGraphClass( $type ) {
		return self::$graphs[$type];
	}

	public function execute( $par ) {
		$this->getOutput()->addModules( 'ext.translate.special.translationstats' );

		$opts = new FormOptions();
		$opts->add( 'graphit', false );
		$opts->add( 'preview', false );
		$opts->add( 'language', '' );
		$opts->add( 'count', 'edits' );
		$opts->add( 'scale', 'days' );
		$opts->add( 'days', 30 );
		$opts->add( 'width', 600 );
		$opts->add( 'height', 400 );
		$opts->add( 'group', '' );
		$opts->add( 'uselang', '' );
		$opts->add( 'start', '' );
		$opts->fetchValuesFromRequest( $this->getRequest() );

		$pars = explode( ';', $par );

		foreach ( $pars as $item ) {
			if ( strpos( $item, '=' ) === false ) {
				continue;
			}

			list( $key, $value ) = array_map( 'trim', explode( '=', $item, 2 ) );
			if ( isset( $opts[$key] ) ) {
				$opts[$key] = $value;
			}
		}

		$opts->validateIntBounds( 'days', 1, 10000 );
		$opts->validateIntBounds( 'width', 200, 1000 );
		$opts->validateIntBounds( 'height', 200, 1000 );

		if ( $opts['start'] !== '' ) {
			$opts['start'] = (string)( wfTimestamp( TS_MW, $opts['start'] ) );
		}

		$validScales = array( 'months', 'weeks', 'days', 'hours' );
		if ( !in_array( $opts['scale'], $validScales ) ) {
			$opts['scale'] = 'days';
		}

		if ( $opts['scale'] === 'hours' ) {
			$opts->validateIntBounds( 'days', 1, 4 );
		}

		$validCounts = $this->getGraphTypes();
		if ( !in_array( $opts['count'], $validCounts ) ) {
			$opts['count'] = 'edits';
		}

		foreach ( array( 'group', 'language' ) as $t ) {
			$values = array_map( 'trim', explode( ',', $opts[$t] ) );
			$values = array_splice( $values, 0, 4 );
			if ( $t === 'group' ) {
				// BC for old syntax which replaced _ to | which was not allowed
				$values = preg_replace( '~^page_~', 'page-', $values );
			}
			$opts[$t] = implode( ',', $values );
		}

		if ( $this->including() ) {
			$this->getOutput()->addHTML( $this->image( $opts ) );
		} elseif ( $opts['graphit'] ) {

			if ( !class_exists( 'PHPlot' ) ) {
				header( 'HTTP/1.0 500 Multi fail' );
				echo 'PHPlot not found';
			}

			if ( !$this->getRequest()->getBool( 'debug' ) ) {
				$this->getOutput()->disable();
				header( 'Content-Type: image/png' );
				header( 'Cache-Control: private, max-age=3600' );
				header( 'Expires: ' . wfTimestamp( TS_RFC2822, time() + 3600 ) );
			}
			$this->draw( $opts );
		} else {
			$this->form( $opts );
		}
	}

	/**
	 * Constructs the form which can be used to generate custom graphs.
	 * @param FormOptions $opts
	 */
	protected function form( FormOptions $opts ) {
		global $wgScript;

		$this->setHeaders();
		$out = $this->getOutput();
		$out->addHelpLink( 'Help:Extension:Translate/Statistics_and_reporting' );
		$out->addWikiMsg( 'translate-statsf-intro' );

		$out->addHTML(
			Xml::fieldset( $this->msg( 'translate-statsf-options' )->text() ) .
				Html::openElement( 'form', array( 'action' => $wgScript ) ) .
				Html::hidden( 'title', $this->getPageTitle()->getPrefixedText() ) .
				Html::hidden( 'preview', 1 ) .
				'<table>'
		);

		$submit = Xml::submitButton( $this->msg( 'translate-statsf-submit' )->text() );

		$out->addHTML(
			$this->eInput( 'width', $opts ) .
				$this->eInput( 'height', $opts ) .
				'<tr><td colspan="2"><hr /></td></tr>' .
				$this->eInput( 'start', $opts, 16 ) . // Should fit yyyymmddhhmmss
				$this->eInput( 'days', $opts ) .
				$this->eRadio( 'scale', $opts, array( 'months', 'weeks', 'days', 'hours' ) ) .
				$this->eRadio( 'count', $opts, $this->getGraphTypes() ) .
				'<tr><td colspan="2"><hr /></td></tr>' .
				$this->eLanguage( 'language', $opts ) .
				$this->eGroup( 'group', $opts ) .
				'<tr><td colspan="2"><hr /></td></tr>' .
				'<tr><td colspan="2">' . $submit . '</td></tr>'
		);

		$out->addHTML(
			'</table>' .
				'</form>' .
				'</fieldset>'
		);

		if ( !$opts['preview'] ) {
			return;
		}

		$spiParams = '';
		foreach ( $opts->getChangedValues() as $key => $v ) {
			if ( $key === 'preview' ) {
				continue;
			}

			if ( $spiParams !== '' ) {
				$spiParams .= ';';
			}

			$spiParams .= wfEscapeWikiText( "$key=$v" );
		}

		if ( $spiParams !== '' ) {
			$spiParams = '/' . $spiParams;
		}

		$titleText = $this->getPageTitle()->getPrefixedText();

		$out->addHTML(
			Html::element( 'hr' ) .
				Html::element( 'pre', array(), "{{{$titleText}{$spiParams}}}" )
		);

		$out->addHTML(
			Html::element( 'hr' ) .
				Html::rawElement(
					'div',
					array( 'style' => 'margin: 1em auto; text-align: center;' ),
					$this->image( $opts )
				)
		);
	}

	/**
	 * Constructs a table row with label and input in two columns.
	 * @param string $name Option name.
	 * @param FormOptions $opts
	 * @param int $width
	 * @return string Html.
	 */
	protected function eInput( $name, FormOptions $opts, $width = 4 ) {
		$value = $opts[$name];

		return
			'<tr><td>' . $this->eLabel( $name ) . '</td><td>' .
			Xml::input( $name, $width, $value, array( 'id' => $name ) ) .
			'</td></tr>' . "\n";
	}

	/**
	 * Constructs a label for option.
	 * @param string $name Option name.
	 * @return string Html.
	 */
	protected function eLabel( $name ) {
		// Give grep a chance to find the usages:
		// translate-statsf-width, translate-statsf-height, translate-statsf-start,
		// translate-statsf-days, translate-statsf-scale, translate-statsf-count,
		// translate-statsf-language, translate-statsf-group
		$label = 'translate-statsf-' . $name;
		$label = $this->msg( $label )->escaped();

		return Xml::tags( 'label', array( 'for' => $name ), $label );
	}

	/**
	 * Constructs a table row with label and radio input in two columns.
	 * @param string $name Option name.
	 * @param FormOptions $opts
	 * @param string[] $alts List of alternatives.
	 * @return string Html.
	 */
	protected function eRadio( $name, FormOptions $opts, array $alts ) {
		// Give grep a chance to find the usages:
		// translate-statsf-scale, translate-statsf-count
		$label = 'translate-statsf-' . $name;
		$label = $this->msg( $label )->escaped();
		$s = '<tr><td>' . $label . '</td><td>';

		$options = array();
		foreach ( $alts as $alt ) {
			$id = "$name-$alt";
			$radio = Xml::radio( $name, $alt, $alt === $opts[$name],
				array( 'id' => $id ) ) . ' ';
			$options[] = $radio . ' ' . $this->eLabel( $id );
		}

		$s .= implode( ' ', $options );
		$s .= '</td></tr>' . "\n";

		return $s;
	}

	/**
	 * Constructs a table row with label and language selector in two columns.
	 * @param string $name Option name.
	 * @param FormOptions $opts
	 * @return string Html.
	 */
	protected function eLanguage( $name, FormOptions $opts ) {
		$value = $opts[$name];

		$select = $this->languageSelector();
		$select->setTargetId( 'language' );

		return
			'<tr><td>' . $this->eLabel( $name ) . '</td><td>' .
			$select->getHtmlAndPrepareJS() . '<br />' .
			Xml::input( $name, 20, $value, array( 'id' => $name ) ) .
			'</td></tr>' . "\n";
	}

	/**
	 * Constructs a JavaScript enhanced language selector.
	 * @return JsSelectToInput
	 */
	protected function languageSelector() {
		$languages = TranslateUtils::getLanguageNames( $this->getLanguage()->getCode() );

		ksort( $languages );

		$selector = new XmlSelect( 'mw-language-selector', 'mw-language-selector' );
		foreach ( $languages as $code => $name ) {
			$selector->addOption( "$code - $name", $code );
		}

		$jsSelect = new JsSelectToInput( $selector );

		return $jsSelect;
	}

	/**
	 * Constructs a table row with label and group selector in two columns.
	 * @param string $name Option name.
	 * @param FormOptions $opts
	 * @return string Html.
	 */
	protected function eGroup( $name, FormOptions $opts ) {
		$value = $opts[$name];

		$select = $this->groupSelector();
		$select->setTargetId( 'group' );

		return
			'<tr><td>' . $this->eLabel( $name ) . '</td><td>' .
			$select->getHtmlAndPrepareJS() . '<br />' .
			Xml::input( $name, 20, $value, array( 'id' => $name ) ) .
			'</td></tr>' . "\n";
	}

	/**
	 * Constructs a JavaScript enhanced group selector.
	 * @return JsSelectToInput
	 */
	protected function groupSelector() {
		$groups = MessageGroups::singleton()->getGroups();
		/**
		 * @var $group MessageGroup
		 */
		foreach ( $groups as $key => $group ) {
			if ( !$group->exists() ) {
				unset( $groups[$key] );
				continue;
			}
		}

		ksort( $groups );

		$selector = new XmlSelect( 'mw-group-selector', 'mw-group-selector' );
		/**
		 * @var MessageGroup $name
		 */
		foreach ( $groups as $code => $name ) {
			$selector->addOption( $name->getLabel(), $code );
		}

		$jsSelect = new JsSelectToInput( $selector );

		return $jsSelect;
	}

	/**
	 * Returns an \<img> tag for graph.
	 * @param FormOptions $opts
	 * @return string Html.
	 */
	protected function image( FormOptions $opts ) {
		$title = $this->getPageTitle();
		$cgiparams = wfArrayToCgi( array( 'graphit' => true ), $opts->getAllValues() );
		$href = $title->getLocalURL( $cgiparams );

		return Xml::element( 'img',
			array(
				'src' => $href,
				'width' => $opts['width'],
				'height' => $opts['height'],
			)
		);
	}

	/**
	 * Fetches and preprocesses graph data that can be fed to graph drawer.
	 * @param FormOptions $opts
	 * @return array ( string => array ) Data indexed by their date labels.
	 */
	protected function getData( FormOptions $opts ) {
		$dbr = wfGetDB( DB_SLAVE );

		$class = $this->getGraphClass( $opts['count'] );
		$so = new $class( $opts );

		$fixedStart = $opts->getValue( 'start' ) !== '';

		$now = time();
		$period = 3600 * 24 * $opts->getValue( 'days' );

		if ( $fixedStart ) {
			$cutoff = wfTimestamp( TS_UNIX, $opts->getValue( 'start' ) );
		} else {
			$cutoff = $now - $period;
		}
		$cutoff = self::roundTimestampToCutoff( $opts['scale'], $cutoff, 'earlier' );

		$start = $cutoff;

		if ( $fixedStart ) {
			$end = self::roundTimestampToCutoff( $opts['scale'], $start + $period, 'later' ) - 1;
		} else {
			$end = null;
		}

		$tables = array();
		$fields = array();
		$conds = array();
		$type = __METHOD__;
		$options = array();

		$so->preQuery( $tables, $fields, $conds, $type, $options, $start, $end );
		$res = $dbr->select( $tables, $fields, $conds, $type, $options );
		wfDebug( __METHOD__ . "-queryend\n" );

		// Start processing the data
		$dateFormat = $so->getDateFormat();
		$increment = self::getIncrement( $opts['scale'] );

		$labels = $so->labels();
		$keys = array_keys( $labels );
		$values = array_pad( array(), count( $labels ), 0 );
		$defaults = array_combine( $keys, $values );

		$data = array();
		// Allow 10 seconds in the future for processing time
		$lastValue = $end !== null ? $end : $now + 10;
		$lang = $this->getLanguage();
		while ( $cutoff <= $lastValue ) {
			$date = $lang->sprintfDate( $dateFormat, wfTimestamp( TS_MW, $cutoff ) );
			$cutoff += $increment;
			$data[$date] = $defaults;
		}

		// Processing
		$labelToIndex = array_flip( $labels );

		foreach ( $res as $row ) {
			$indexLabels = $so->indexOf( $row );
			if ( $indexLabels === false ) {
				continue;
			}

			foreach ( (array)$indexLabels as $i ) {
				if ( !isset( $labelToIndex[$i] ) ) {
					continue;
				}
				$date = $lang->sprintfDate( $dateFormat, $so->getTimestamp( $row ) );
				// Ignore values outside range
				if ( !isset( $data[$date] ) ) {
					continue;
				}

				$data[$date][$labelToIndex[$i]]++;
			}
		}

		// Don't display dummy label
		if ( count( $labels ) === 1 && $labels[0] === 'all' ) {
			$labels = array();
		}

		foreach ( $labels as &$label ) {
			if ( strpos( $label, '@' ) === false ) {
				continue;
			}
			list( $groupId, $code ) = explode( '@', $label, 2 );
			if ( $code && $groupId ) {
				$code = TranslateUtils::getLanguageName( $code, $lang->getCode() ) . " ($code)";
				$group = MessageGroups::getGroup( $groupId );
				$group = $group ? $group->getLabel() : $groupId;
				$label = "$group @ $code";
			} elseif ( $code ) {
				$label = TranslateUtils::getLanguageName( $code, $lang->getCode() ) . " ($code)";
			} elseif ( $groupId ) {
				$group = MessageGroups::getGroup( $groupId );
				$label = $group ? $group->getLabel() : $groupId;
			}
		}

		if ( $end === null ) {
			$last = array_splice( $data, -1, 1 );
			// Indicator that the last value is not full
			$data[key( $last ) . '*'] = current( $last );
		}

		return array( $labels, $data );
	}

	/**
	 * Gets the closest earlieast timestamp that corresponds to start of a
	 * period in given scale, like, midnight, monday or first day of the month.
	 * @param string $scale One of hours, days, weeks, months
	 * @param int $cutoff Timestamp in unix format.
	 * @param string $direction One of earlier, later
	 * @return int
	 */
	protected static function roundTimestampToCutoff( $scale, $cutoff, $direction = 'earlier' ) {
		$dir = $direction === 'earlier' ? -1 : 1;

		/* Ensure that the first item in the graph has full data even
		* if it doesn't align with the given 'days' boundary */
		if ( $scale === 'hours' ) {
			$cutoff += self::roundingAddition( $cutoff, 3600, $dir );
		} elseif ( $scale === 'days' ) {
			$cutoff += self::roundingAddition( $cutoff, 86400, $dir );
		} elseif ( $scale === 'weeks' ) {
			/* Here we assume that week starts on monday, which does not
			* always hold true. Go Xwards day by day until we are on monday */
			while ( date( 'D', $cutoff ) !== 'Mon' ) {
				$cutoff += $dir * 86400;
			}
			// Round to nearest day
			$cutoff -= ( $cutoff % 86400 );
		} elseif ( $scale === 'months' ) {
			// Go Xwards/ day by day until we are on the first day of the month
			while ( date( 'j', $cutoff ) !== '1' ) {
				$cutoff += $dir * 86400;
			}
			// Round to nearest day
			$cutoff -= ( $cutoff % 86400 );
		}

		return $cutoff;
	}

	/**
	 * @param int $ts
	 * @param int $amount
	 * @param int $dir
	 * @return int
	 */
	protected static function roundingAddition( $ts, $amount, $dir ) {
		if ( $dir === -1 ) {
			return -1 * ( $ts % $amount );
		} else {
			return $amount - ( $ts % $amount );
		}
	}

	/**
	 * Adds raw image data of the graph to the output.
	 * @param FormOptions $opts
	 */
	public function draw( FormOptions $opts ) {
		global $wgTranslatePHPlotFont;

		$width = $opts->getValue( 'width' );
		$height = $opts->getValue( 'height' );
		// Define the object
		$plot = new PHPlot( $width, $height );

		list( $legend, $resData ) = $this->getData( $opts );
		$count = count( $resData );
		$skip = (int)( $count / ( $width / 60 ) - 1 );
		$i = $count;
		$data = array();

		foreach ( $resData as $date => $edits ) {
			if ( $skip > 0 &&
				( $count - $i ) % $skip !== 0
			) {
				$date = '';
			}

			if ( strpos( $date, ';' ) !== false ) {
				list( , $date ) = explode( ';', $date, 2 );
			}

			array_unshift( $edits, $date );
			$data[] = $edits;
			$i--;
		}

		$font = FCFontFinder::findFile( $this->getLanguage()->getCode() );

		if ( $font ) {
			$plot->SetDefaultTTFont( $font );
		} else {
			$plot->SetDefaultTTFont( $wgTranslatePHPlotFont );
		}
		$plot->SetDataValues( $data );

		if ( $legend !== null ) {
			$plot->SetLegend( $legend );
		}

		$numberFont = FCFontFinder::findFile( 'en' );

		$plot->setFont( 'x_label', $numberFont, 8 );
		$plot->setFont( 'y_label', $numberFont, 8 );
		// Give grep a chance to find the usages:
		// translate-stats-edits, translate-stats-users, translate-stats-registrations,
		// translate-stats-reviews, translate-stats-reviewers
		$yTitle = $this->msg( 'translate-stats-' . $opts['count'] )->escaped();

		// Turn off X axis ticks and labels because they get in the way:
		$plot->SetYTitle( $yTitle );
		$plot->SetXTickLabelPos( 'none' );
		$plot->SetXTickPos( 'none' );
		$plot->SetXLabelAngle( 45 );

		$max = max( array_map( 'max', $resData ) );
		$max = self::roundToSignificant( $max, 1 );
		$max = round( $max, (int)( -log( $max, 10 ) ) );

		$yTick = 10;
		while ( $max / $yTick > $height / 20 ) {
			$yTick *= 2;
		}

		// If we have very small case, ensure that there is at least one tick
		$yTick = min( $max, $yTick );
		$yTick = self::roundToSignificant( $yTick );
		$plot->SetYTickIncrement( $yTick );
		$plot->SetPlotAreaWorld( null, 0, null, max( $max, 10 ) );

		$plot->SetTransparentColor( 'white' );
		$plot->SetBackgroundColor( 'white' );

		// Draw it
		$plot->DrawGraph();
	}

	/**
	 * Enhanced version of round that supports rounding up to a given scale
	 * relative to the number itself. Examples:
	 * - roundToSignificant( 1234, 0 ) = 10000
	 * - roundToSignificant( 1234, 1 ) = 2000
	 * - roundToSignificant( 1234, 2 ) = 1300
	 * - roundToSignificant( 1234, 3 ) = 1240
	 *
	 * @param int $number Number to round.
	 * @param int $significant How many signficant numbers to keep.
	 * @return int Rounded number.
	 */
	public static function roundToSignificant( $number, $significant = 1 ) {
		$log = (int)log( $number, 10 );
		$nonSignificant = max( 0, $log - $significant + 1 );
		$factor = pow( 10, $nonSignificant );

		return (int)( ceil( $number / $factor ) * $factor );
	}

	/**
	 * Returns an increment in seconds for a given scale.
	 * The increment must be small enough that we will hit every item in the
	 * scale when using different multiples of the increment. It should be
	 * large enough to avoid hitting the same item multiple times.
	 * @param string $scale Either months, weeks, days or hours.
	 * @return int Number of seconds in the increment.
	 */
	public static function getIncrement( $scale ) {
		$increment = 3600 * 24;
		if ( $scale === 'months' ) {
			/* We use increment to fill up the values. Use number small enough
			 * to ensure we hit each month */
			$increment = 3600 * 24 * 15;
		} elseif ( $scale === 'weeks' ) {
			$increment = 3600 * 24 * 7;
		} elseif ( $scale === 'hours' ) {
			$increment = 3600;
		}

		return $increment;
	}
}

/**
 * Interface for producing different kinds of graphs.
 * The graphs are based on data queried from the database.
 * @ingroup Stats
 */
interface TranslationStatsInterface {
	/**
	 * Constructor. The implementation can access the graph options, but not
	 * define new ones.
	 * @param FormOptions $opts
	 */
	public function __construct( FormOptions $opts );

	/**
	 * Query details that the graph must fill.
	 * @param array $tables Empty list. Append table names.
	 * @param array $fields Empty list. Append field names.
	 * @param array $conds Empty array. Append select conditions.
	 * @param string $type Append graph type (used to identify queries).
	 * @param array $options Empty array. Append extra query options.
	 * @param string $start Precalculated start cutoff timestamp
	 * @param string $end Precalculated end cutoff timestamp
	 */
	public function preQuery( &$tables, &$fields, &$conds, &$type, &$options, $start, $end );

	/**
	 * Return the indexes which this result contributes to.
	 * Return 'all' if only one variable is measured. Return false if none.
	 * @param array $row Database Result Row
	 */
	public function indexOf( $row );

	/**
	 * Return the names of the variables being measured.
	 * Return 'all' if only one variable is measured. Must match indexes
	 * returned by indexOf() and contain them all.
	 * @return string[]
	 */
	public function labels();

	/**
	 * Return the timestamp associated with this result row.
	 * @param array $row Database Result Row
	 * @return \string Timestamp.
	 */
	public function getTimestamp( $row );

	/**
	 * Return time formatting string.
	 * @see Language::sprintfDate()
	 * @return string
	 */
	public function getDateFormat();
}

/**
 * Provides some hand default implementations for TranslationStatsInterface.
 * @ingroup Stats
 */
abstract class TranslationStatsBase implements TranslationStatsInterface {
	/**
	 * @var FormOptions Graph options.
	 */
	protected $opts;

	public function __construct( FormOptions $opts ) {
		$this->opts = $opts;
	}

	public function indexOf( $row ) {
		return array( 'all' );
	}

	public function labels() {
		return array( 'all' );
	}

	public function getDateFormat() {
		$dateFormat = 'Y-m-d';
		if ( $this->opts['scale'] === 'months' ) {
			$dateFormat = 'Y-m';
		} elseif ( $this->opts['scale'] === 'weeks' ) {
			$dateFormat = 'Y-\WW';
		} elseif ( $this->opts['scale'] === 'hours' ) {
			$dateFormat .= ';H';
		}

		return $dateFormat;
	}

	protected static function makeTimeCondition( $field, $start, $end ) {
		$db = wfGetDB( DB_SLAVE );

		$conds = array();
		if ( $start !== null ) {
			$conds[] = "$field >= '{$db->timestamp( $start )}'";
		}
		if ( $end !== null ) {
			$conds[] = "$field <= '{$db->timestamp( $end )}'";
		}

		return $conds;
	}

	/// @since 2012-03-05
	protected static function namespacesFromGroups( $groupIds ) {
		$namespaces = array();
		foreach ( $groupIds as $id ) {
			$group = MessageGroups::getGroup( $id );
			if ( $group ) {
				$namespace = $group->getNamespace();
				$namespaces[$namespace] = true;
			}
		}

		return array_keys( $namespaces );
	}
}

/**
 * Graph which provides statistics on active users and number of translations.
 * @ingroup Stats
 */
class TranslatePerLanguageStats extends TranslationStatsBase {
	/// array ( string => bool ) Cache used to count active users only once per day.
	protected $usercache;

	protected $codes, $groups;

	public function __construct( FormOptions $opts ) {
		parent::__construct( $opts );
		// This query is slow... ensure a lower limit.
		$opts->validateIntBounds( 'days', 1, 200 );
	}

	public function preQuery( &$tables, &$fields, &$conds, &$type, &$options, $start, $end ) {
		global $wgTranslateMessageNamespaces;

		$db = wfGetDB( DB_SLAVE );

		$tables = array( 'recentchanges' );
		$fields = array( 'rc_timestamp' );

		$conds = array(
			'rc_namespace' => $wgTranslateMessageNamespaces,
			'rc_bot' => 0,
			'rc_type != ' . RC_LOG,
		);

		$timeConds = self::makeTimeCondition( 'rc_timestamp', $start, $end );
		$conds = array_merge( $conds, $timeConds );

		$options = array( 'ORDER BY' => 'rc_timestamp' );

		$this->groups = array_filter( array_map( 'trim', explode( ',', $this->opts['group'] ) ) );
		$this->groups = array_map( 'MessageGroups::normalizeId', $this->groups );
		$this->codes = array_filter( array_map( 'trim', explode( ',', $this->opts['language'] ) ) );

		$namespaces = self::namespacesFromGroups( $this->groups );
		if ( count( $namespaces ) ) {
			$conds['rc_namespace'] = $namespaces;
		}

		$languages = array();
		foreach ( $this->codes as $code ) {
			$languages[] = 'rc_title ' . $db->buildLike( $db->anyString(), "/$code" );
		}
		if ( count( $languages ) ) {
			$conds[] = $db->makeList( $languages, LIST_OR );
		}

		$fields[] = 'rc_title';

		if ( $this->groups ) {
			$fields[] = 'rc_namespace';
		}

		if ( $this->opts['count'] === 'users' ) {
			$fields[] = 'rc_user_text';
		}

		$type .= '-perlang';
	}

	public function indexOf( $row ) {
		// We need to check that there is only one user per day.
		if ( $this->opts['count'] === 'users' ) {
			$date = $this->formatTimestamp( $row->rc_timestamp );

			if ( isset( $this->usercache[$date][$row->rc_user_text] ) ) {
				return -1;
			} else {
				$this->usercache[$date][$row->rc_user_text] = 1;
			}
		}

		// Do not consider language-less pages.
		if ( strpos( $row->rc_title, '/' ) === false ) {
			return false;
		}

		// No filters, just one key to track.
		if ( !$this->groups && !$this->codes ) {
			return 'all';
		}

		// The key-building needs to be in sync with ::labels().
		list( $key, $code ) = TranslateUtils::figureMessage( $row->rc_title );

		$groups = array();
		$codes = array();

		if ( $this->groups ) {
			/*
			 * Get list of keys that the message belongs to, and filter
			 * out those which are not requested.
			 */
			$groups = TranslateUtils::messageKeyToGroups( $row->rc_namespace, $key );
			$groups = array_intersect( $this->groups, $groups );
		}

		if ( $this->codes ) {
			$codes = array( $code );
		}

		return $this->combineTwoArrays( $groups, $codes );
	}

	public function labels() {
		return $this->combineTwoArrays( $this->groups, $this->codes );
	}

	public function getTimestamp( $row ) {
		return $row->rc_timestamp;
	}

	/**
	 * Makes a label for variable. If group or language code filters, or both
	 * are used, combine those in a pretty way.
	 * @param string $group Group name.
	 * @param string $code Language code.
	 * @return string Label.
	 */
	protected function makeLabel( $group, $code ) {
		if ( $group || $code ) {
			return "$group@$code";
		} else {
			return 'all';
		}
	}

	/**
	 * Cross-product of two lists with string results, where either
	 * list can be empty.
	 * @param string[] $groups Group names.
	 * @param string[] $codes Language codes.
	 * @return string[] Labels.
	 */
	protected function combineTwoArrays( $groups, $codes ) {
		if ( !count( $groups ) ) {
			$groups[] = false;
		}

		if ( !count( $codes ) ) {
			$codes[] = false;
		}

		$items = array();
		foreach ( $groups as $group ) {
			foreach ( $codes as $code ) {
				$items[] = $this->makeLabel( $group, $code );
			}
		}

		return $items;
	}

	/**
	 * Returns unique index for given item in the scale being used.
	 * Called a lot, so performance intensive.
	 * @param string $timestamp Timestamp in mediawiki format.
	 * @return string
	 */
	protected function formatTimestamp( $timestamp ) {
		global $wgContLang;

		switch ( $this->opts['scale'] ) {
			case 'hours' :
				$cut = 4;
				break;
			case 'days' :
				$cut = 6;
				break;
			case 'months':
				$cut = 8;
				break;
			default :
				return $wgContLang->sprintfDate( $this->getDateFormat(), $timestamp );
		}

		return substr( $timestamp, 0, -$cut );
	}
}

/**
 * Graph which provides statistics about amount of registered users in a given time.
 * @ingroup Stats
 */
class TranslateRegistrationStats extends TranslationStatsBase {
	public function preQuery( &$tables, &$fields, &$conds, &$type, &$options, $start, $end ) {
		$tables = 'user';
		$fields = 'user_registration';
		$conds = self::makeTimeCondition( 'user_registration', $start, $end );
		$type .= '-registration';
		$options = array();
	}

	public function getTimestamp( $row ) {
		return $row->user_registration;
	}
}

/**
 * Graph which provides statistics on number of reviews and reviewers.
 * @since 2012-03-05
 * @ingroup Stats
 */
class ReviewPerLanguageStats extends TranslatePerLanguageStats {
	public function preQuery( &$tables, &$fields, &$conds, &$type, &$options, $start, $end ) {
		global $wgTranslateMessageNamespaces;

		$db = wfGetDB( DB_SLAVE );

		$tables = array( 'logging' );
		$fields = array( 'log_timestamp' );

		$conds = array(
			'log_namespace' => $wgTranslateMessageNamespaces,
			'log_action' => 'message',
		);

		$timeConds = self::makeTimeCondition( 'log_timestamp', $start, $end );
		$conds = array_merge( $conds, $timeConds );

		$options = array( 'ORDER BY' => 'log_timestamp' );

		$this->groups = array_filter( array_map( 'trim', explode( ',', $this->opts['group'] ) ) );
		$this->codes = array_filter( array_map( 'trim', explode( ',', $this->opts['language'] ) ) );

		$namespaces = self::namespacesFromGroups( $this->groups );
		if ( count( $namespaces ) ) {
			$conds['log_namespace'] = $namespaces;
		}

		$languages = array();
		foreach ( $this->codes as $code ) {
			$languages[] = 'log_title ' . $db->buildLike( $db->anyString(), "/$code" );
		}
		if ( count( $languages ) ) {
			$conds[] = $db->makeList( $languages, LIST_OR );
		}

		$fields[] = 'log_title';

		if ( $this->groups ) {
			$fields[] = 'log_namespace';
		}

		if ( $this->opts['count'] === 'reviewers' ) {
			$fields[] = 'log_user_text';
		}

		$type .= '-reviews';
	}

	public function indexOf( $row ) {
		// We need to check that there is only one user per day.
		if ( $this->opts['count'] === 'reviewers' ) {
			$date = $this->formatTimestamp( $row->log_timestamp );

			if ( isset( $this->usercache[$date][$row->log_user_text] ) ) {
				return -1;
			} else {
				$this->usercache[$date][$row->log_user_text] = 1;
			}
		}

		// Do not consider language-less pages.
		if ( strpos( $row->log_title, '/' ) === false ) {
			return false;
		}

		// No filters, just one key to track.
		if ( !$this->groups && !$this->codes ) {
			return 'all';
		}

		// The key-building needs to be in sync with ::labels().
		list( $key, $code ) = TranslateUtils::figureMessage( $row->log_title );

		$groups = array();
		$codes = array();

		if ( $this->groups ) {
			/* Get list of keys that the message belongs to, and filter
			 * out those which are not requested. */
			$groups = TranslateUtils::messageKeyToGroups( $row->log_namespace, $key );
			$groups = array_intersect( $this->groups, $groups );
		}

		if ( $this->codes ) {
			$codes = array( $code );
		}

		return $this->combineTwoArrays( $groups, $codes );
	}

	public function labels() {
		return $this->combineTwoArrays( $this->groups, $this->codes );
	}

	public function getTimestamp( $row ) {
		return $row->log_timestamp;
	}
}
