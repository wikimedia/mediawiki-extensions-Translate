'use strict';

/*!
 * Display translation stats via a form.
 * @author Amir E. Aharoni
 * @author Siebrand Mazeland
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013 Amir E. Aharoni, Siebrand Mazeland
 * @license GPL-2.0-or-later
 */
function getAllOptions() {
	/**
	 * @param {HTMLInputElement} input
	 * @return {string|null}
	 */
	function getOptionalDate( input ) {
		return input.valueAsDate !== null ? input.valueAsDate.toISOString() : null;
	}

	/**
	 * @param {HTMLInputElement} input
	 * @return {string[]}
	 */
	function getSplitValues( input ) {
		var values = input.value.trim();

		if ( values !== '' ) {
			return values.split( ',' ).map( function ( value ) {
				return value.trim();
			} );
		} else {
			return [];
		}
	}

	/** @type {HTMLFormElement} */
	var form = document.getElementById( 'translationStatsConfig' );
	return {
		measure: form.elements.namedItem( 'count' ).value,
		days: form.elements.namedItem( 'days' ).valueAsNumber,
		start: getOptionalDate( form.elements.namedItem( 'start' ) ),
		granularity: form.elements.namedItem( 'scale' ).value,
		group: getSplitValues( form.elements.namedItem( 'group' ) ),
		language: getSplitValues( form.elements.namedItem( 'language' ) ),
		height: form.elements.namedItem( 'height' ).valueAsNumber,
		width: form.elements.namedItem( 'width' ).valueAsNumber
	};
}

$( function () {
	var graphContainer = document.getElementById( 'translationStatsGraphContainer' );

	// Check if the graph container has been loaded
	if ( graphContainer ) {
		var graphBuilder = new mw.translate.TranslationStatsGraphBuilder( $( graphContainer ) );
		graphBuilder.display( getAllOptions() );
	}
} );
