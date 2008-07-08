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
		$opts->add( 'language', 'en' );
		$opts->add( 'days', 30 );
		$opts->add( 'width', 600 );
		$opts->add( 'height', 400 );
		$opts->add( 'ts', 0 );
		$opts->fetchValuesFromRequest( $wgRequest );

		$pars = explode( ';', $par );
		foreach ( $pars as $item ) {
			if ( strpos( $item, '=' ) === false ) continue;
			list( $key, $value ) = array_map( 'trim', explode( '=', $item, 2 ) );
			if ( isset($opts[$key]) )
				$opts[$key] = $value;
		}

		$opts->validateIntBounds( 'days', 1, 180 );
		$opts->validateIntBounds( 'width', 200, 1000 );
		$opts->validateIntBounds( 'height', 200, 1000 );

		$title = $this->getTitle();
		$cgiparams = wfArrayToCgi( array( 'ts' => time() ), $opts->getAllValues() );
		$href = $title->getLocalUrl( $cgiparams );


		if ( $this->including() ) {
			$wgOut->addHTML(
				Xml::element( 'img', array( 'src' => $href ) )
			);
		} else {
			if ( $opts['ts'] === 0 ) {
				$wgOut->redirect( $href );
				return;
			}

			if ( time() - $opts['ts'] < 3600*2 ) {
				$lastMod = $wgOut->checkLastModified( wfTimestamp( TS_MW, $opts['ts'] ) );
			 	if ( $lastMod ) return;
			}

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
		$code = $dbr->escapeLike( $opts->getValue('language') );

		$res = $dbr->select(
			'recentchanges',
			'rc_timestamp',
			array(
				"rc_timestamp >= '$cutoffDb'",
				'rc_namespace' => $wgTranslateMessageNamespaces,
				"rc_title like '%%/$code'",
				'rc_bot' => 0
			),
			__METHOD__,
			array( 'ORDER BY' => 'rc_timestamp' )
		);

		$data = array();
		while ( $cutoff < $now ) {
			$date = $wgLang->sprintfDate( 'Y-m-d', wfTimestamp( TS_MW, $cutoff )  );
			$data[$date] = 0;
			$cutoff += 24 * 3600;
		}

		foreach ( $res as $row ) {
			$date = $wgLang->sprintfDate( 'Y-m-d', $row->rc_timestamp );
			if ( !isset($data[$date]) ) $data[$date] = 0;
			$data[$date]++;
		}

		return $data;

	}

	public function draw( FormOptions $opts ) {
		wfLoadExtensionMessages( 'Translate' );
		global $wgTranslatePHPlotFont;

		$width = $opts->getValue( 'width' );
		$height = $opts->getValue( 'height' );
		//Define the object
		$plot = new PHPlot($width, $height);
		$code = 'nl';

		$resData = $this->getData($opts);
		$count = count($resData);
		$skip = intval($count / ($width/60) -1);
		$i = $count;
		foreach ( $resData as $date => $edits ) {
			if ( $skip > 0 ) {
				if ( ($count-$i)%$skip !== 0 ) { $date = ''; }
			}
			$data[] = array( $date, $edits );
			$i--;
		}

		$plot->SetDefaultTTFont($wgTranslatePHPlotFont);

		$plot->SetDataValues( $data );
		$plot->setFont( 'x_label', null, 8 );
		$plot->setFont( 'y_label', null, 8 );

		//Turn off X axis ticks and labels because they get in the way:
		$plot->SetYTitle( wfMsg( 'translate-stats-edits' ) );
		$plot->SetXTickLabelPos('none');
		$plot->SetXTickPos('none');
		$plot->SetXLabelAngle(45);

		$plot->SetTransparentColor('white');
		$plot->SetBackgroundColor('white');
		$plot->SetFileFormat('gif');

		//Draw it
		$plot->DrawGraph();

	}

}