<?php
/**
 * Contains logic for special page Special:TranslationStats.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extensions\Translate\Statistics\ReviewPerLanguageStats;
use MediaWiki\Extensions\Translate\Statistics\TranslatePerLanguageStats;
use MediaWiki\Extensions\Translate\Statistics\TranslateRegistrationStats;

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
	protected static $graphs = [
		'edits' => TranslatePerLanguageStats::class,
		'users' => TranslatePerLanguageStats::class,
		'registrations' => TranslateRegistrationStats::class,
		'reviews' => ReviewPerLanguageStats::class,
		'reviewers' => ReviewPerLanguageStats::class,
	];

	public function __construct() {
		parent::__construct( 'TranslationStats' );
	}

	public function isIncludable() {
		return true;
	}

	protected function getGroupName() {
		return 'translation';
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
		$opts->add( 'imagescale', 1.0 );
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
		$opts->validateBounds( 'imagescale', 1.0, 4.0 );

		if ( $opts['start'] !== '' ) {
			$opts['start'] = rtrim( wfTimestamp( TS_ISO_8601, $opts['start'] ), 'Z' );
		}

		$validScales = [ 'months', 'weeks', 'days', 'hours' ];
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

		foreach ( [ 'group', 'language' ] as $t ) {
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
			if ( !class_exists( PHPlot::class ) ) {
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
	 * @suppress SecurityCheck-DoubleEscaped Intentionally outputting what user should type
	 */
	protected function form( FormOptions $opts ) {
		global $wgScript;

		$this->setHeaders();
		$out = $this->getOutput();
		$out->addHelpLink( 'Help:Extension:Translate/Statistics_and_reporting' );
		$out->addWikiMsg( 'translate-statsf-intro' );

		$out->addHTML(
			Xml::fieldset( $this->msg( 'translate-statsf-options' )->text() ) .
				Html::openElement( 'form', [ 'action' => $wgScript ] ) .
				Html::hidden( 'title', $this->getPageTitle()->getPrefixedText() ) .
				Html::hidden( 'preview', 1 ) .
				'<table>'
		);

		$submit = Xml::submitButton( $this->msg( 'translate-statsf-submit' )->text() );

		$out->addHTML(
			$this->eInput( 'width', $opts ) .
				$this->eInput( 'height', $opts ) .
				'<tr><td colspan="2"><hr /></td></tr>' .
				$this->eInput( 'start', $opts, 24 ) .
				$this->eInput( 'days', $opts ) .
				$this->eRadio( 'scale', $opts, [ 'months', 'weeks', 'days', 'hours' ] ) .
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
				Html::element( 'pre', [], "{{{$titleText}{$spiParams}}}" )
		);

		$out->addHTML(
			Html::element( 'hr' ) .
				Html::rawElement(
					'div',
					[ 'style' => 'margin: 1em auto; text-align: center;' ],
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

		return '<tr><td>' . $this->eLabel( $name ) . '</td><td>' .
			Xml::input( $name, $width, $value, [ 'id' => $name ] ) .
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

		return Xml::tags( 'label', [ 'for' => $name ], $label );
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

		$options = [];
		foreach ( $alts as $alt ) {
			$id = "$name-$alt";
			$radio = Xml::radio( $name, $alt, $alt === $opts[$name],
				[ 'id' => $id ] ) . ' ';
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

		return '<tr><td>' . $this->eLabel( $name ) . '</td><td>' .
			$select->getHtmlAndPrepareJS() . '<br />' .
			Xml::input( $name, 20, $value, [ 'id' => $name ] ) .
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

		return '<tr><td>' . $this->eLabel( $name ) . '</td><td>' .
			$select->getHtmlAndPrepareJS() . '<br />' .
			Xml::input( $name, 20, $value, [ 'id' => $name ] ) .
			'</td></tr>' . "\n";
	}

	/**
	 * Constructs a JavaScript enhanced group selector.
	 * @return JsSelectToInput
	 */
	protected function groupSelector() {
		$groups = MessageGroups::singleton()->getGroups();
		/**
		 * @var MessageGroup $group
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

		$params = $opts->getChangedValues();
		$params[ 'graphit' ] = true;
		$src = $title->getLocalURL( $params );

		$srcsets = [];
		foreach ( [ 1.5, 2, 3 ] as $scale ) {
			$params[ 'imagescale' ] = $scale;
			$srcsets[] = "{$title->getLocalURL( $params )} {$scale}x";
		}

		return Xml::element( 'img',
			[
				'src' => $src,
				'srcset' => implode( ', ', $srcsets ),
				'width' => $opts['width'],
				'height' => $opts['height'],
			]
		);
	}

	/**
	 * Fetches and preprocesses graph data that can be fed to graph drawer.
	 * @param FormOptions $opts
	 * @return array ( string => array ) Data indexed by their date labels.
	 */
	protected function getData( FormOptions $opts ) {
		$dbr = wfGetDB( DB_REPLICA );

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

		$tables = [];
		$fields = [];
		$conds = [];
		$type = __METHOD__;
		$options = [];
		$joins = [];

		$so->preQuery( $tables, $fields, $conds, $type, $options, $joins, $start, $end );
		$res = $dbr->select( $tables, $fields, $conds, $type, $options, $joins );
		wfDebug( __METHOD__ . "-queryend\n" );

		// Start processing the data
		$dateFormat = $so->getDateFormat();
		$increment = self::getIncrement( $opts['scale'] );

		$labels = $so->labels();
		$keys = array_keys( $labels );
		$values = array_pad( [], count( $labels ), 0 );
		$defaults = array_combine( $keys, $values );

		$data = [];
		// Allow 10 seconds in the future for processing time
		$lastValue = $end ?? $now + 10;
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
			$labels = [];
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

		return [ $labels, $data ];
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

		$imageScale = $opts->getValue( 'imagescale' );
		$width = $opts->getValue( 'width' );
		$height = $opts->getValue( 'height' );
		// Define the object
		$plot = new PHPlot( $width * $imageScale, $height * $imageScale );

		list( $legend, $resData ) = $this->getData( $opts );
		$count = count( $resData );
		$skip = (int)( $count / ( $width / 60 ) - 1 );
		$i = $count;
		$data = [];

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
		if ( !$font ) {
			$font = $wgTranslatePHPlotFont;
		}
		$numberFont = FCFontFinder::findFile( 'en' );
		$plot->SetDefaultTTFont( $font );
		$plot->SetFontTTF( 'generic', $font, 12 * $imageScale );
		$plot->SetFontTTF( 'legend', $font, 12 * $imageScale );
		$plot->SetFontTTF( 'x_title', $font, 10 * $imageScale );
		$plot->SetFontTTF( 'y_title', $font, 10 * $imageScale );
		$plot->SetFontTTF( 'x_label', $numberFont, 8 * $imageScale );
		$plot->SetFontTTF( 'y_label', $numberFont, 8 * $imageScale );

		$plot->SetDataValues( $data );

		if ( $legend !== null ) {
			$plot->SetLegend( $legend );
		}

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
