<?php

class SpecialTranslationStats extends SpecialPage {

	public function __construct() {
		parent::__construct( 'TranslationStats' );
		$this->includable( true );
		$this->listed( false );
	}

	public function execute( $par ) {
		global $wgOut, $wgRequest;


		$opts = new FormOptions();
		$opts->add( 'language', '' );
		$opts->add( 'days', 30 );
		$opts->add( 'width', 600 );
		$opts->add( 'height', 400 );
		$opts->add( 'group', '' );
		$opts->fetchValuesFromRequest( $wgRequest );

		$pars = explode( ';', $par );
		foreach ( $pars as $item ) {
			if ( strpos( $item, '=' ) === false ) continue;
			list( $key, $value ) = array_map( 'trim', explode( '=', $item, 2 ) );
			if ( isset($opts[$key]) )
				$opts[$key] = $value;
		}

		$opts->validateIntBounds( 'days', 1, 300 );
		$opts->validateIntBounds( 'width', 200, 1000 );
		$opts->validateIntBounds( 'height', 200, 1000 );

		$title = $this->getTitle();
		$cgiparams = wfArrayToCgi( $opts->getAllValues() );
		$href = $title->getLocalUrl( $cgiparams );


		if ( $this->including() ) {
			$wgOut->addHTML(
				Xml::element( 'img',
					array(
						'src' => $href,
						'width' => $opts['width'],
						'height' => $opts['height'],
					)
				)
			);
		} else {
			// Cache for two hours
			$lastMod = $wgOut->checkLastModified( wfTimestamp( TS_MW, time() - 2*3600 ) );
			if ( $lastMod ) return;

			$wgOut->disable();

			if ( !class_exists('PHPlot') ) {
				header("HTTP/1.0 500 Multi fail");
				echo "PHPlot not found";
			}

			$this->draw( $opts );
		}

	}

	protected function getData( FormOptions $opts ) {
		global $wgLang, $wgTranslateMessageNamespaces;
		$dbr = wfGetDb(DB_SLAVE);

		$now = time();
		$cutoff = $now - ( 3600 * 24 * $opts->getValue('days') -1 );
		$cutoff -= ($cutoff % 86400);
		$cutoffDb = $dbr->timestamp( $cutoff );

		$so = new TranslatePerLanguageStats( $opts );


		$tables = array( 'recentchanges' );
		$fields = array( 'rc_timestamp' );

		$conds = array(
			"rc_timestamp >= '$cutoffDb'",
			'rc_namespace' => $wgTranslateMessageNamespaces,
			'rc_bot' => 0
		);

		$type = __METHOD__;
		$options = array( 'ORDER BY' => 'rc_timestamp' );

		$so->preQuery( $tables, $fields, $conds, $type, $options );
		$res = $dbr->select( $tables, $fields, $conds, $type, $options );


		// Initialisations
		$so->postQuery( $res );

		$data = array();
		while ( $cutoff < $now ) {
			$date = $wgLang->sprintfDate( 'Y-m-d', wfTimestamp( TS_MW, $cutoff )  );
			$so->preProcess( $data[$date] );
			$cutoff += 24 * 3600;
		}

		// Processing
		foreach ( $res as $row ) {
			$date = $wgLang->sprintfDate( 'Y-m-d', $row->rc_timestamp );
			$index = $so->indexOf( $row );
			if ( $index < 0 ) continue;

			if ( !isset($data[$date][$index]) ) $data[$date][$index] = 0;
			$data[$date][$index]++;
		}

		$labels = null;
		$so->labels( $labels );

		//var_dump( $data );
		return array($labels, $data);

	}

	public function draw( FormOptions $opts ) {
		wfLoadExtensionMessages( 'Translate' );
		global $wgTranslatePHPlotFont;

		$width = $opts->getValue( 'width' );
		$height = $opts->getValue( 'height' );
		//Define the object
		$plot = new PHPlot($width, $height);

		list( $legend, $resData ) = $this->getData($opts);
		$count = count($resData);
		$skip = intval($count / ($width/60) -1);
		$i = $count;
		foreach ( $resData as $date => $edits ) {
			if ( $skip > 0 ) {
				if ( ($count-$i)%$skip !== 0 ) { $date = ''; }
			}
			array_unshift( $edits, $date );
			$data[] = $edits;
			$i--;
		}

		$plot->SetDefaultTTFont($wgTranslatePHPlotFont);

		$plot->SetDataValues( $data );

		if ( $legend !== null )
			$plot->SetLegend($legend);

		$plot->setFont( 'x_label', null, 8 );
		$plot->setFont( 'y_label', null, 8 );

		//Turn off X axis ticks and labels because they get in the way:
		$plot->SetYTitle( wfMsg( 'translate-stats-edits' ) );
		$plot->SetXTickLabelPos('none');
		$plot->SetXTickPos('none');
		$plot->SetXLabelAngle(45);

		$max = max( array_map( 'max', $resData ) );
		$yTick = 5;
		while ( $max / $yTick > $height/20 ) $yTick *= 2;

		$plot->SetYTickIncrement($yTick);

		$plot->SetTransparentColor('white');
		$plot->SetBackgroundColor('white');
		//$plot->SetFileFormat('gif');

		//Draw it
		$plot->DrawGraph();

	}

}


class TranslatePerLanguageStats {
	protected $opts;
	protected $cache;
	protected $index;
	protected $filters;

	public function __construct( FormOptions $opts ) {
		$this->opts = $opts;
	}

	public function preQuery( &$tables, &$fields, &$conds, &$type, &$options ) {
		$db = wfGetDb();

		$groups = array_map( 'trim', explode(',', $this->opts['group']) );
		$codes = array_map( 'trim', explode(',', $this->opts['language']) );

		$filters['language'] = trim($this->opts['language']) !== '';
		$filters['group'] = trim($this->opts['group']) !== '';

		foreach ( $groups as $group )
			foreach ( $codes as $code )
				$this->cache[$group . $code] = count($this->cache);

		if ( $filters['language'] ) {
			$myconds = array();
			foreach( $codes as $code ) {
				$myconds[] = 'rc_title like \'%%/' . $db->escapeLike( $code ) . "'";
			}

			$conds[] = $db->makeList( $myconds, LIST_OR );
		}

		if ( max($filters) ) $fields[] = 'rc_title';
		if ( $filters['group'] ) $fields[] = 'rc_namespace';

		$type .= '-perlang';

		$this->filters = $filters;

	}

	public function postQuery( $rows ) {}

	public function preProcess( &$initial ) {
		$initial = array_pad( array(), max(1, count($this->cache)), 0 );
	}

	public function indexOf( $row ) {
		global $wgContLang;

		if ( !max($this->filters) ) return 0;
		if ( strpos( $row->rc_title, '/' ) === false ) return -1;

		list( $key, $code ) = explode('/', $wgContLang->lcfirst($row->rc_title), 2);
		$indexKey = '';

		if ( $this->filters['group'] ) {
			if ( $this->index === null ) $this->index = TranslateUtils::messageIndex();

			$key = strtolower($row->rc_namespace. ':' . $key);
			$group = @$this->index[$key];
			if ( is_null($group) ) return -1;
			$indexKey .= $group;
		}

		if ( $this->filters['language'] ) {
			$indexKey .= $code;
		}

		if ( count($this->cache) ) {
			return isset($this->cache[$indexKey]) ? $this->cache[$indexKey] : -1;
		} else {
			return 0;
		}
	}

	public function labels( &$labels ) {
		if ( count($this->cache) > 1 ) {
			$labels = array_keys($this->cache);
		}
	}

}