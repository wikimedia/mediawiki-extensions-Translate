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

$codes = array_keys( Language::getLanguageNames( false ) );
sort( $codes );

// Exclude this special language
if ( $wgTranslateDocumentationLanguageCode )
	unset( $codes[$wgTranslateDocumentationLanguageCode] );

$supportedTypes = array('mediawiki');
$problematic = array();

$groups = MessageGroups::singleton()->getGroups();
foreach ( $groups as $g ) {
	# Don't bother checking groups we have no checks for!
	if ( !in_array($g->getType(), $supportedTypes) ) continue;

	$id = $g->getId();
	STDOUT( "Working with $id: ", true );

	foreach ( $codes as $code ) {
		STDOUT( "$code " );

		// Initialise messages, using unique definitions if appropriate
		$collection = $g->initCollection( $code, $g->isMeta() );
		$g->fillCollection( $collection );
		$namespace = $g->namespaces[0];

		// Remove untranslated messages from the list
		foreach ( $collection->keys() as $key ) {
			$prob = MessageChecks::doFastChecks( $collection[$key] );
			if ( $prob ) {
				// Print it
				$nsText = $wgContLang->getNsText( $namespace );
				STDOUT( "# [[$nsText:$key/$code]]\n", true );

				// Add it to the array
				$key = strtolower( "$namespace:$key" );
				$problematic[$code][] = $key;
			}
		}

	}
}

// Store the results
file_put_contents( TRANSLATE_CHECKFILE, serialize( $problematic ) );
