<?php
/**
 * Script for creating graphml xml file of language fallbacks.
 *
 * @author Niklas Laxström
 *
 * @license GPL-2.0-or-later
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

/// Creates graphml xml file of language fallbacks.
class FallbacksCompare extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Creates graphml xml file of language fallbacks.';
	}

	public function execute() {
		$template = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<graphml
	xmlns="http://graphml.graphdrawing.org/xmlns"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://graphml.graphdrawing.org/xmlns
		http://graphml.graphdrawing.org/xmlns/1.0/graphml.xsd"
	xmlns:y="http://www.yworks.com/xml/graphml">

	<key id="code" for="node" yfiles.type="nodegraphics"/>
	<graph id="G" edgedefault="directed">
$1
	</graph>
</graphml>

XML;

		$langs = Language::fetchLanguageNames( null, 'mw' );
		$nodes = $edges = [];
		foreach ( $langs as $code => $name ) {
			$fallbacks = Language::getFallbacksFor( $code );
			if ( $fallbacks === [ 'en' ] ) {
				continue;
			}

			$nodes[$code] = $this->createNode( $code );

			$prev = $code;
			foreach ( $fallbacks as $fb ) {
				$nodes[$fb] = $this->createNode( $fb );
				$edges[$fb . $prev] = Xml::element( 'edge', [ 'source' => $prev, 'target' => $fb ] );
				$prev = $fb;
			}
		}

		$output = array_merge( $nodes, $edges );
		$output = "\t\t" . implode( "\n\t\t", $output );
		echo str_replace( '$1', $output, $template );
	}

	protected function createNode( $code ) {
		return Xml::openElement( 'node', [ 'id' => $code ] )
			. Xml::openElement( 'data', [ 'key' => 'code' ] )
			. Xml::openElement( 'y:Shapenode' )
			. Xml::element(
				'y:Geometry',
				[ 'height' => 30, 'width' => max( 30, 10 * strlen( $code ) ) ],
				''
			)
			. Xml::element( 'y:NodeLabel', [], $code )
			. Xml::element( 'y:BorderStyle', [ 'hasColor' => 'false' ], '' )
			. Xml::element( 'y:Fill', [ 'hasColor' => 'false' ], '' )
			. Xml::closeElement( 'y:Shapenode' )
			. Xml::closeElement( 'data' )
			. Xml::closeElement( 'node' );
	}
}

$maintClass = FallbacksCompare::class;
require_once RUN_MAINTENANCE_IF_MAIN;
