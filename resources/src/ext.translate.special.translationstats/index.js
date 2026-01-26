/*!
 * Entity selector and language selector for Special:TranslationStats that allows users to load
 * messages from typing in a group name.
 * @author Eugene Wang'ombe
 * @license GPL-2.0-or-later
 */

( function () {
	'use strict';

	function activateEntitySelector( group ) {
		if ( !group ) {
			return;
		}
		const initialGroup = new mw.Uri().query.group || '';
		const selectedGroups = initialGroup ? String( initialGroup ).split( ',' )
			.map( ( groupId ) => groupId.trim() )
			.filter( Boolean )
			.map( ( groupId ) => ( {
				value: groupId,
				type: 'group'
			} ) ) : [];

		const { getMultiselectEntitySelector } = require( 'ext.translate.entity.selector' );
		const entitySelector = getMultiselectEntitySelector( {
			entityType: [ 'groups' ],
			inputId: 'mw-entity-selector-input',
			inputName: 'group',
			values: selectedGroups
		} );

		entitySelector.mount( group );
	}

	function activateLanguageSelector( languageInput ) {
		const container = document.getElementById( 'language-selector' );
		if ( !languageInput || !container ) {
			return;
		}

		const { getMultiselectLookupLanguageSelector } = require( 'mediawiki.languageselector' );

		const selectedLanguages = languageInput.value ? languageInput.value.split( ',' ).map(
			( lang ) => lang.trim()
		).filter( Boolean ) : [];

		const app = getMultiselectLookupLanguageSelector( {
			selectedLanguage: selectedLanguages,
			onLanguageChange: function ( languages ) {
				languageInput.value = languages.join( ',' );
			}
		} );

		app.mount( container );
	}

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
		activateEntitySelector( document.querySelector( '#group' ) );
		activateLanguageSelector( document.querySelector( '#language' ) );

		var graphContainer = document.getElementById( 'translationStatsGraphContainer' );

		// Check if the graph container has been loaded
		if ( graphContainer ) {
			var graphBuilder = new mw.translate.TranslationStatsGraphBuilder( $( graphContainer ) );
			graphBuilder.display( getAllOptions() );
		}
	} );
}() );
