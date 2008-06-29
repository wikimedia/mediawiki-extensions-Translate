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

require( dirname(__FILE__) . '/cli.inc' );

$codes = Language::getLanguageNames( false );

// Exclude this special language
if ( $wgTranslateDocumentationLanguageCode )
	unset( $codes[$wgTranslateDocumentationLanguageCode] );

$codes = array_keys( $codes );
sort( $codes );


$supportedTypes = array('mediawiki');
$problematic = array();

$groups = MessageGroups::singleton()->getGroups();
foreach ( $groups as $g ) {
	$type = $g->getType();

	$id = $g->getId();
	STDOUT( "Working with $id: ", true );

	foreach ( $codes as $code ) {
		STDOUT( "$code ", true );

		// Initialise messages, using unique definitions if appropriate
		$collection = $g->initCollection( $code, $g->isMeta() );
		$g->fillCollection( $collection );
		$namespace = $g->namespaces[0];

		// Remove untranslated messages from the list
		foreach ( $collection->keys() as $key ) {
			$prob = MessageChecks::doFastChecks( $collection[$key], $type );
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
}

// Store the results
file_put_contents( TRANSLATE_CHECKFILE, serialize( $problematic ) );
