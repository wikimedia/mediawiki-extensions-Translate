<?php
/**
 * Create language models for https://github.com/crodas/LanguageDetector based
 * on translation data in your wiki.
 *
 * @author Niklas Laxström
 *
 * @copyright Copyright © 2013, Niklas Laxström
 * @license GPL-2.0+
 * @file
 */

// Standard boilerplate to define $IP
if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$dir = __DIR__;
	$IP = "$dir/../../..";
}
require_once "$IP/maintenance/Maintenance.php";

class LanguageModelCreator extends Maintenance {
	protected $changes = array();

	public function __construct() {
		parent::__construct();
		$this->mDescription = <<<TXT
Create language models for https://github.com/crodas/LanguageDetector based
on translation data in your wiki. It is safe to kill and restart the script.
List of pages and filtered language data is cached for 24 hours. Json files
present will be used, so don't forget to delete before new run.
TXT;
	}

	public function execute() {
		global $wgTranslateMessageNamespaces;

		ini_set( 'memory_limit', -1 );

		// How many messages per language to use.
		// Language is skipped if it has less than 1000 translations.
		$messages = 5000;

		$languages = TranslateUtils::getLanguageNames( 'en' );
		$cache = wfGetCache( CACHE_DB );
		$key = wfMemcKey( __METHOD__, $messages );

		$pages = $cache->get( $key );
		if ( !is_array( $pages ) ) {
			$dbr = wfGetDB( DB_SLAVE );
			$conds = array();
			$conds[] = 'page_title' . $dbr->buildLike( $dbr->anyString(), '/', $dbr->anyString() );
			$conds['page_namespace'] = $wgTranslateMessageNamespaces;

			echo "Before query\n";
			$res = $dbr->select(
				array( 'page' ),
				array( 'page_title, page_id' ),
				$conds,
				__METHOD__
			);
			echo "After query\n";

			$total = $res->numRows();
			$index = 0;

			foreach ( $res as $row ) {
				$index++;
				$code = substr( $row->page_title, strrpos( $row->page_title, '/' ) + 1 );
				if ( isset( $languages[$code] ) ) {
					$pages[$code][] = $row->page_id;
				}

				if ( $index % 10000 === 0 ) {
					$progress = number_format( $index / $total * 100, 2 );
					echo "$progress%\n";
				}
			}

			echo "\n";

			foreach ( array_keys( $pages ) as $code ) {
				if ( count( $pages[$code] ) > $messages ) {
					$pages[$code] = array_slice( $pages[$code], 0, $messages );
				}

				$pages[$code] = implode( '|', $pages[$code] );
			}

			echo "After code map\n";

			ksort( $pages );

			echo "After sort map\n";

			$cache->set( $key, $pages, 3600*24 );
			echo "After set map\n";
		}

		unset( $pages['qqq'] );
		unset( $pages['de-formal'] );
		unset( $pages['nl-informal'] );
		unset( $pages['en-gb'] );

		$pids = array();
		$threads = 2;
		foreach ( $pages as $code => $pageids ) {
			$pid = ( $threads > 1 ) ? pcntl_fork() : -1;

			if ( $pid === 0 ) {
				// Child, reseed because there is no bug in PHP:
				// http://bugs.php.net/bug.php?id=42465
				mt_srand( getmypid() );
				$this->analyzeLanguage( $code, $pageids );
				exit();
			} elseif ( $pid === -1 ) {
				// Fork failed or one thread, do it serialized
				$this->analyzeLanguage( $code, $pageids );
			} else {
				// Main thread
				$pids[] = $pid;
			}

			// If we hit the thread limit, wait for any child to finish.
			if ( count( $pids ) >= $threads ) {
				$status = 0;
				$pid = pcntl_wait( $status );
				unset( $pids[$pid] );
			}
		}

		foreach ( $pids as $pid ) {
			$status = 0;
			pcntl_waitpid( $pid, $status );
		}

		$this->output( "Combining languages\n" );

		$huge = array();
		foreach ( glob( 'temp-*.json' ) as $file ) {
			$contents = file_get_contents( $file );
			$json = FormatJson::decode( $contents, true );

			$huge = array_merge( $json, $huge );
			$huge['data'] = array_merge( $json['data'], $huge['data'] );
		}

		$json = FormatJson::encode( $huge, true, FormatJson::ALL_OK );
		file_put_contents( 'translatewiki.net.json', $json );
	}

	protected function analyzeLanguage( $code, $ids ) {
		if ( file_exists( "temp-$code.json" ) ) {
			$this->output( "$code MODEL EXISTS\n" );
			return;
		}

		$text = $this->cacheSourceText( $code, $ids );
		if ( $text === '' ) {
			return;
		}

		$config = new LanguageDetector\Config;
		$config->useMb( true );
		$c = new LanguageDetector\Learn( $config );
		$c->addSample( $code, $text );
		$c->addStepCallback( function( $lang, $status ) {
				echo "Learning {$lang}: $status\n";
		} );

		$target = LanguageDetector\AbstractFormat::initFormatByPath( "temp-$code.json" );
		$c->save( $target );
	}

	protected function cacheSourceText( $code, $ids ) {
		$cache = wfGetCache( CACHE_DB );
		$key = wfMemcKey( __CLASS__, 'cc', $code );
		$text = $cache->get( $key );
		if ( !is_string( $text ) ) {

			$snippets = array();

			$ids = explode( '|', $ids );

			$len = count( $ids );

			if ( $len < 1000 ) {
				$this->output( "$code: $len SKIPPED\n" );
				return '';
			} else {
				$this->output( "$code PROCESSING\n" );
			}

			$time = microtime( true );

			foreach ( $ids as $id ) {
				$params = new FauxRequest( array(
					'pageid' => $id,
					'action' => 'parse',
					'prop' => 'text',
					'disablepp' => 'true',
				) );

				$api = new ApiMain( $params );
				$api->execute();

				$result = $api->getResult()->getResultData(
					null,
					array( 'BC' => array() )
				);

				$text = $result['parse']['text']['*'];
				$text = strip_tags( $text );
				$text = str_replace( '!!FUZZY!!', '', $text );
				$text = preg_replace( '/\$[0-9]/', '', $text );
				$text = trim( $text );

				$snippets[] = $text;
			}

			$text = implode( '   ', $snippets );
			$cache->set( $key, $text, 3600*24 );

			$delta = microtime( true ) - $time;
			$this->output( "$code TOOK $delta\n" );
		} else {
			$this->output( "$code FROM CACHE\n" );
		}

		return $text;
	}
}

$maintClass = 'LanguageModelCreator';
require_once RUN_MAINTENANCE_IF_MAIN;
