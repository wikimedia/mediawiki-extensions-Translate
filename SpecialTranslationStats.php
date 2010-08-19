<?php

class SpecialTranslationStats extends SpecialPage {
	public function __construct() {
		parent::__construct( 'TranslationStats' );
		$this->includable( true );
	}

	public function execute( $par ) {
		global $wgOut, $wgRequest;

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
		$opts->fetchValuesFromRequest( $wgRequest );

		$pars = explode( ';', $par );
		foreach ( $pars as $item ) {
			if ( strpos( $item, '=' ) === false ) continue;
			list( $key, $value ) = array_map( 'trim', explode( '=', $item, 2 ) );
			if ( isset( $opts[$key] ) )
				$opts[$key] = $value;
		}

		$opts->validateIntBounds( 'days', 1, 10000 );
		$opts->validateIntBounds( 'width', 200, 1000 );
		$opts->validateIntBounds( 'height', 200, 1000 );

		$validScales = array( 'months', 'weeks', 'days', 'hours' );
		if ( !in_array( $opts['scale'], $validScales ) ) $opts['scale'] = 'days';
		if ( $opts['scale'] === 'hours' ) $opts->validateIntBounds( 'days', 1, 4 );

		$validCounts = array( 'edits', 'users', 'registrations' );
		if ( !in_array( $opts['count'], $validCounts ) ) $opts['count'] = 'edits';

		foreach ( array( 'group', 'language' ) as $t ) {
			$values = array_map( 'trim', explode( ',', $opts[$t] ) );
			$values = array_splice( $values, 0, 4 );
			if ( $t === 'group' ) {
				$values = preg_replace( '~^page_~', 'page|', $values );
			}
			$opts[$t] = implode( ',', $values );
		}

		if ( $this->including() ) {
			$wgOut->addHTML( $this->image( $opts ) );
		} elseif ( $opts['graphit'] ) {
			// Cache for two hours
			if ( !$opts['preview'] ) {
				$lastMod = $wgOut->checkLastModified( wfTimestamp( TS_MW, time() - 2 * 3600 ) );
				if ( $lastMod ) return;
			}

			$wgOut->disable();

			if ( !class_exists( 'PHPlot' ) ) {
				header( "HTTP/1.0 500 Multi fail" );
				echo "PHPlot not found";
			}

			$this->draw( $opts );
		} else {
			$this->form( $opts );
		}
	}

	protected function form( $opts ) {
		global $wgOut, $wgScript;

		$this->setHeaders();
		$wgOut->addWikiMsg( 'translate-statsf-intro' );

		$wgOut->addHTML(
			Xml::fieldset( wfMsg( 'translate-statsf-options' ) ) .
			Html::openElement( 'form', array( 'action' => $wgScript ) ) .
			Html::hidden( 'title', $this->getTitle()->getPrefixedText() ) .
			Html::hidden( 'preview', 1 ) .
			'<table>'
		);

		$submit = Xml::submitButton( wfMsg( 'translate-statsf-submit' ) );

		$wgOut->addHTML(
			$this->eInput( 'width', $opts ) .
			$this->eInput( 'height', $opts ) .
			'<tr><td colspan="2"><hr /></td></tr>' .
			$this->eInput( 'days', $opts ) .
			$this->eRadio( 'scale', $opts, array( 'months', 'weeks', 'days', 'hours' ) ) .
			$this->eRadio( 'count', $opts, array( 'edits', 'users', 'registrations' ) ) .
			'<tr><td colspan="2"><hr /></td></tr>' .
			$this->eLanguage( 'language', $opts ) .
			$this->eGroup( 'group', $opts ) .
			'<tr><td colspan="2"><hr /></td></tr>' .
			'<tr><td colspan="2">' . $submit . '</td></tr>'
		);

		$wgOut->addHTML(
			'</table>' .
			'</form>' .
			'</fieldset>'
		);

		if ( !$opts['preview'] ) return;

		$spiParams = '';
		foreach ( $opts->getChangedValues() as $key => $v ) {
			if ( $key === 'preview' ) continue;
			if ( $spiParams !== '' ) $spiParams .= ';';
			$spiParams .= wfEscapeWikiText( "$key=$v" );
		}

		if ( $spiParams !== '' ) $spiParams = '/' . $spiParams;

		$titleText = $this->getTitle()->getPrefixedText();

		$wgOut->addHTML(
			Html::element( 'hr' ) .
			Html::element( 'pre', null, "{{{$titleText}{$spiParams}}}" )
		);

		$wgOut->addHTML(
			Html::element( 'hr' ) .
			Html::rawElement( 'div', array( 'style' => 'margin: 1em auto; text-align: center;' ), $this->image( $opts ) )
		);
	}

	protected function eInput( $name, FormOptions $opts ) {
		$value = $opts[$name];

		return
			'<tr><td>' . $this->eLabel( $name ) . '</td><td>' .
			Xml::input( $name, 4, $value, array( 'id' => $name ) ) .
			'</td></tr>' . "\n";
	}

	protected function eLabel( $name ) {
		$label = 'translate-statsf-' . $name;
		$label = wfMsgExt( $label, array( 'parsemag', 'escapenoentities' ) );
		return Xml::tags( 'label', array( 'for' => $name ), $label );
	}

	protected function eRadio( $name, FormOptions $opts, array $alts ) {
		$label = 'translate-statsf-' . $name;
		$label = wfMsgExt( $label, array( 'parsemag', 'escapenoentities' ) );
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

	protected function eLanguage( $name, FormOptions $opts ) {
		$value = $opts[$name];

		$select = $this->languageSelector();
		$select->setTargetId( 'language' );

		return
			'<tr><td>' . $this->eLabel( $name ) . '</td><td>' .
			Xml::input( $name, 20, $value, array( 'id' => $name ) ) .
			$select->getHtmlAndPrepareJs() .
			'</td></tr>' . "\n";
	}

	protected function languageSelector() {
		global $wgLang;
		if ( is_callable( array( 'LanguageNames', 'getNames' ) ) ) {
			$languages = LanguageNames::getNames( $wgLang->getCode(),
				LanguageNames::FALLBACK_NORMAL,
				LanguageNames::LIST_MW_AND_CLDR
			);
		} else {
			$languages = Language::getLanguageNames( false );
		}

		ksort( $languages );

		$selector = new XmlSelect( 'mw-language-selector', 'mw-language-selector' );
		foreach ( $languages as $code => $name ) {
			$selector->addOption( "$code - $name", $code );
		}

		$jsSelect = new JsSelectToInput( $selector );
		$jsSelect->setSourceId( 'mw-language-selector' );
		return $jsSelect;
	}

	protected function eGroup( $name, FormOptions $opts ) {
		$value = $opts[$name];

		$select = $this->groupSelector();
		$select->setTargetId( 'group' );

		return
			'<tr><td>' . $this->eLabel( $name ) . '</td><td>' .
			Xml::input( $name, 20, $value, array( 'id' => $name ) ) .
			$select->getHtmlAndPrepareJs() .
			'</td></tr>' . "\n";
	}

	protected function groupSelector() {
		$groups = MessageGroups::singleton()->getGroups();
		foreach ( $groups as $key => $group ) {
			if ( !$group->exists() ) {
				unset( $groups[$key] );
				continue;
			}
		}

		ksort( $groups );

		$selector = new XmlSelect( 'mw-group-selector', 'mw-group-selector' );
		foreach ( $groups as $code => $name ) {
			$selector->addOption( $name->getLabel(), $code );
		}

		$jsSelect = new JsSelectToInput( $selector );
		$jsSelect->setSourceId( 'mw-group-selector' );
		return $jsSelect;
	}

	protected function image( $opts ) {
		$title = $this->getTitle();
		$cgiparams = wfArrayToCgi( array( 'graphit' => true ), $opts->getAllValues() );
		$href = $title->getLocalUrl( $cgiparams );
		return Xml::element( 'img',
			array(
				'src' => $href,
				'width' => $opts['width'],
				'height' => $opts['height'],
			)
		);
	}

	protected function getData( FormOptions $opts ) {
		global $wgLang;
		$dbr = wfGetDB( DB_SLAVE );

		if ( $opts['count'] === 'registrations' ) {
			$so = new TranslateRegistrationStats( $opts );
		} else {
			$so = new TranslatePerLanguageStats( $opts );
		}

		$now = time();
		$cutoff = $now - ( 3600 * 24 * $opts->getValue( 'days' ) - 1 );
		if ( $opts['scale'] === 'days' ) $cutoff -= ( $cutoff % 86400 );


		$tables = array();
		$fields = array();
		$conds = array();
		$type = __METHOD__;
		$options = array();

		$so->preQuery( $tables, $fields, $conds, $type, $options, $cutoff );
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
		while ( $cutoff <= $now + 10 ) {
			$date = $wgLang->sprintfDate( $dateFormat, wfTimestamp( TS_MW, $cutoff ) );
			$cutoff += $increment;
			$data[$date] = $defaults;
		}

		// Processing
		$labelToIndex = array_flip( $labels );
		foreach ( $res as $row ) {
			$indexLabels = $so->indexOf( $row );
			if ( $indexLabels === false ) continue;

			foreach ( (array) $indexLabels as $i ) {
				if ( !isset( $labelToIndex[$i] ) ) continue;
				$date = $wgLang->sprintfDate( $dateFormat, $so->getTimestamp( $row ) );
				// Ignore values outside range
				if ( !isset( $data[$date] ) ) continue;
				$data[$date][$labelToIndex[$i]]++;
			}
		}

		// Don't display dummy label
		if ( count( $labels ) === 1 && $labels[0] === 'all' ) {
			$labels = array();
		}

		return array( $labels, $data );
	}

	public function draw( FormOptions $opts ) {
		global $wgTranslatePHPlotFont, $wgLang;

		$width = $opts->getValue( 'width' );
		$height = $opts->getValue( 'height' );
		// Define the object
		$plot = new PHPlot( $width, $height );

		list( $legend, $resData ) = $this->getData( $opts );
		$count = count( $resData );
		$skip = intval( $count / ( $width / 60 ) - 1 );
		$i = $count;
		foreach ( $resData as $date => $edits ) {
			if ( $skip > 0 ) {
				if ( ( $count - $i ) % $skip !== 0 ) $date = '';
			}
			if ( strpos( $date, ';' ) !== false ) {
				list( , $date ) = explode( ';', $date, 2 );
			}
			array_unshift( $edits, $date );
			$data[] = $edits;
			$i--;
		}

		$font = FCFontFinder::find( $wgLang->getCode() );
		if ( $font ) {
			$plot->SetDefaultTTFont( $font );
		} else {
			$plot->SetDefaultTTFont( $wgTranslatePHPlotFont );
		}
		$plot->SetDataValues( $data );

		if ( $legend !== null )
			$plot->SetLegend( $legend );

		$numberFont = FCFontFinder::find( 'en' );

		$plot->setFont( 'x_label', $numberFont, 8 );
		$plot->setFont( 'y_label', $numberFont, 8 );

		$yTitle = wfMsg( 'translate-stats-' . $opts['count'] );

		// Turn off X axis ticks and labels because they get in the way:
		$plot->SetYTitle( $yTitle );
		$plot->SetXTickLabelPos( 'none' );
		$plot->SetXTickPos( 'none' );
		$plot->SetXLabelAngle( 45 );


		$max = max( array_map( 'max', $resData ) );
		$max = self::roundToSignificant( $max, 1 );
		$max = round( $max, intval( -log( $max, 10 ) ) );

		$yTick = 10;
		while ( $max / $yTick > $height / 20 ) $yTick *= 2;
		$yTick = self::roundToSignificant( $yTick );
		$plot->SetYTickIncrement( $yTick );
		$plot->SetPlotAreaWorld( null, 0, null, $max );


		$plot->SetTransparentColor( 'white' );
		$plot->SetBackgroundColor( 'white' );

		// Draw it
		$plot->DrawGraph();
	}

	public static function roundToSignificant( $number, $significant = 1 ) {
		$log = (int) log( $number, 10 );
		$nonSignificant =  max( 0, $log - $significant + 1 );
		$factor = pow( 10, $nonSignificant );
		return intval( ceil( $number / $factor ) * $factor );
	}

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

interface TranslationStatsInterface {
	public function __construct( FormOptions $opts );
	public function preQuery( &$tables, &$fields, &$conds, &$type, &$options, $cutoff );
	public function indexOf( $row );
	public function labels();
	public function getTimestamp( $row );
	public function getDateFormat();
}

abstract class TranslationStatsBase implements TranslationStatsInterface {
	/** FormOptions */
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
}

class TranslatePerLanguageStats extends TranslationStatsBase {
	protected $usercache;

	public function __construct( FormOptions $opts ) {
		parent::__construct( $opts );
		// This query is slow... ensure a slower limit
		$opts->validateIntBounds( 'days', 1, 200 );
	}

	public function preQuery( &$tables, &$fields, &$conds, &$type, &$options, $cutoff ) {
		global $wgTranslateMessageNamespaces;

		$db = wfGetDB( DB_SLAVE );

		$tables = array( 'recentchanges' );
		$fields = array( 'rc_timestamp' );

		$conds = array(
			"rc_timestamp >= '{$db->timestamp( $cutoff )}'",
			'rc_namespace' => $wgTranslateMessageNamespaces,
			'rc_bot' => 0
		);

		$options = array( 'ORDER BY' => 'rc_timestamp' );

		$this->groups = array_filter( array_map( 'trim', explode( ',', $this->opts['group'] ) ) );
		$this->codes = array_filter( array_map( 'trim', explode( ',', $this->opts['language'] ) ) );

		$namespaces = array();
		$languages = array();

		foreach ( $this->groups as $id ) {
			$group = MessageGroups::getGroup( $id );
			if ( $group ) {
				$namespaces[] = $group->getNamespace();
			}
		}

		foreach ( $this->codes as $code ) {
			$languages[] = 'rc_title ' . $db->buildLike( $db->anyString(), "/$code" );
		}

		if ( count( $namespaces ) ) {
			$namespaces = array_unique( $namespaces );
			$conds['rc_namespace'] = $namespaces;
		}

		if ( count( $languages ) ) {
			$languages = array_unique( $languages );
			$conds[] = $db->makeList( $languages, LIST_OR );
		}

		$fields[] = 'rc_title';
		if ( $this->groups ) $fields[] = 'rc_namespace';
		if ( $this->opts['count'] === 'users' ) $fields[] = 'rc_user_text';

		$type .= '-perlang';
	}

	public function indexOf( $row ) {
		// We need to check that there is only one user per day
		if ( $this->opts['count'] === 'users' ) {
			$date = $this->formatTimestamp( $row->rc_timestamp );

			if ( isset( $this->usercache[$date][$row->rc_user_text] ) ) {
				return -1;
			} else {
				$this->usercache[$date][$row->rc_user_text] = 1;
			}
		}

		// Don't consider language-less pages
		if ( strpos( $row->rc_title, '/' ) === false ) return false;

		// No filters, just one key to track
		if ( !$this->groups && !$this->codes ) return 'all';

		// The key-building needs to be in sync with ::labels()

		list( $key, $code ) = TranslateUtils::figureMessage( $row->rc_title );

		$groups = array();
		$codes = array();

		if ( $this->groups ) {
			/* Get list of keys that the message belongs to, and filter
			 * out those which are not requested */
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

	protected function makeLabel( $group, $code ) {
		if ( $code ) {
			global $wgLang;
			$code = TranslateUtils::getLanguageName( $code, false, $wgLang->getCode() ) . " ($code)";
		}

		if ( $group && $code ) {
			return "$group @ $code";
		} elseif ( $group || $code ) {
			return "$group$code";
		} else {
			return 'all';
		}
	}

	protected function combineTwoArrays( $groups, $codes ) {
		if ( !count( $groups ) ) $groups[] = false;
		if ( !count( $codes ) ) $codes[] = false;

		$items = array();
		foreach ( $groups as $group ) {
		foreach ( $codes as $code ) {
			$items[] = $this->makeLabel( $group, $code );
		}
		}
		return $items;
	}

	protected function formatTimestamp( $timestamp ) {
		global $wgContLang;
		switch ( $this->opts['scale'] ) {
		case 'hours' : $cut = 2; break;
		case 'days'  : $cut = 4; break;
		case 'months': $cut = 8; break;
		default      : return $wgContLang->sprintfDate( $this->getDateFormat(), $timestamp );
		}

		return substr( $timestamp, 0, -$cut );
	}

}

class TranslateRegistrationStats extends TranslationStatsBase {

	public function preQuery( &$tables, &$fields, &$conds, &$type, &$options, $cutoff ) {
		$db = wfGetDB( DB_SLAVE );
		$tables = 'user';
		$fields = 'user_registration';
		$conds = array( "user_registration >= '{$db->timestamp( $cutoff )}'" );
		$type .= '-registration';
		$options = array();
	}

	public function getTimestamp( $row ) {
		return $row->user_registration;
	}

}
