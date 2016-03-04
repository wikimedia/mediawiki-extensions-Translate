<?php
/**
 * Script for comparing supported YAML parser implementations
 *
 * @author Niklas Laxström
 *
 * @copyright Copyright © 2010, Niklas Laxström
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

class YamlTests extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Script for comparing supported YAML parser implementations.';
	}

	public function execute() {
		global $wgTranslateGroupFiles, $wgTranslateYamlLibrary;
		$documents = array();
		$times = array();
		$mems = array();
		$mempeaks = array();

		foreach ( array( 'syck', 'spyc', 'phpyaml' ) as $driver ) {
			$mempeaks[$driver] = -memory_get_peak_usage( true );
			$mems[$driver] = -memory_get_usage( true );
			$times[$driver] = -microtime( true );
			$wgTranslateYamlLibrary = $driver;
			$documents[$driver] = array();
			foreach ( $wgTranslateGroupFiles as $file ) {
				foreach ( self::parseGroupFile( $file ) as $id => $docu ) {
					$documents[$driver]["$file-$id"] = $docu;
				}
			}

			$times[$driver] += microtime( true );
			$mems[$driver] += memory_get_usage( true );
			$mempeaks[$driver] += memory_get_peak_usage( true );

			self::sortNestedArrayAssoc( $documents[$driver] );
			file_put_contents( "yaml-test-$driver.txt", var_export( $documents[$driver], true ) );
			file_put_contents( "yaml-output-$driver.txt", TranslateYaml::dump( $documents[$driver] ) );
		}
		var_dump( $times );
		var_dump( $mems );
		var_dump( $mempeaks );
	}

	public static function parseGroupFile( $filename ) {
		$data = file_get_contents( $filename );
		$documents = preg_split( "/^---$/m", $data, -1, PREG_SPLIT_NO_EMPTY );
		$groups = array();
		$template = false;
		foreach ( $documents as $document ) {
			$document = TranslateYaml::loadString( $document );
			if ( isset( $document['TEMPLATE'] ) ) {
				$template = $document['TEMPLATE'];
			} else {
				if ( !isset( $document['BASIC']['id'] ) ) {
					trigger_error( 'No path ./BASIC/id (group id not defined) ' .
						"in yaml document located in $filename" );
					continue;
				}
				$groups[$document['BASIC']['id']] = $document;
			}
		}

		foreach ( $groups as $i => $group ) {
			$groups[$i] = MessageGroupConfigurationParser::mergeTemplate( $template, $group );
		}

		return $groups;
	}

	public static function sortNestedArrayAssoc( &$a ) {
		ksort( $a );
		foreach ( $a as &$value ) {
			if ( is_array( $value ) ) {
				self::sortNestedArrayAssoc( $value );
			}
		}
	}
}

$maintClass = 'YamlTests';
require_once RUN_MAINTENANCE_IF_MAIN;
