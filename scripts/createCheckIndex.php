<?php
/**
 * Creates serialised database of messages that need checking for problems.
 *
 * @author Niklas Laxstrom
 *
 * @copyright Copyright © 2008, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @file
 */

$optionsWithArgs = array('groups' );
require( dirname(__FILE__) . '/cli.inc' );

$codes = Language::getLanguageNames( false );

// Exclude this special language
if ( $wgTranslateDocumentationLanguageCode )
	unset( $codes[$wgTranslateDocumentationLanguageCode] );

$codes = array_keys( $codes );
sort( $codes );

if ( isset($options['groups'] ) ) {
	$reqGroups = array_map( 'trim', explode( ',', $options['groups'] ) );
} else {
	$reqGroups = false;
}

$groups = MessageGroups::singleton()->getGroups();

foreach ( $groups as $g ) {
	$id = $g->getId();

	// Skip groups that are not requested
	if ( $reqGroups && !in_array($id, $reqGroups) ) continue;

	$problematic = array();
	$type = $g->getType();

	STDOUT( "Working with $id: ", true );

	foreach ( $codes as $code ) {
		STDOUT( "$code ", true );

		// Initialise messages, using unique definitions if appropriate
		$collection = $g->initCollection( $code, $g->isMeta() );
		$g->fillCollection( $collection );
		$namespace = $g->namespaces[0];

		foreach ( $collection->keys() as $key ) {
			$prob = MessageChecks::doFastChecks( $collection[$key], $type, $code );
			if ( $prob ) {
				// Print it
				$nsText = $wgContLang->getNsText( $namespace );
				STDOUT( "# [[$nsText:$key/$code]]" );

				// Add it to the array
				$key = strtolower( "$namespace:$key" );
				$problematic[$code][] = $key;
			}
		}

	}

	// Store the results
	$file = TRANSLATE_CHECKFILE . "-$id";
	wfMkdirParents( dirname($file) );
	file_put_contents( $file, serialize( $problematic ) );
}
