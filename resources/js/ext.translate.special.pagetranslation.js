'use strict';

/*!
 * @author Santhosh Thottingal
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

var LanguagesMultiselectWidget = require( './LanguagesMultiselectWidget.js' );

// Needed for OOUI :(
window.LanguagesMultiselectWidget = LanguagesMultiselectWidget;

function configureLanguageInput( $form, $widget ) {
	/** @type {LanguagesMultiselectWidget} */
	var widget = OO.ui.infuse( $widget, { api: new mw.Api() } );

	var $input = $( '<input>' ).prop( {
		type: 'hidden',
		name: 'prioritylangs',
		value: widget.getValue()
	} );

	$form.prepend( $input );
	widget.on( 'change', function () {
		$input.val( widget.getValue() );
	} );
}

function configurePostLinks( $container ) {
	$container.on( 'click', '.mw-translate-jspost', function ( e ) {
		var uri = new mw.Uri( e.target.href );

		var params = uri.query;
		params.token = mw.user.tokens.get( 'csrfToken' );
		$.post( uri.path, params ).done( function () {
			location.reload();
		} );

		e.preventDefault();
	} );
}

function configureDropdownForFuzzySelector( $container ) {
	var $form = $container.find( '.mw-tpt-sp-markform' );
	var $dropdown = $form.find( 'select[name="unit-fuzzy-selector"]' );
	$dropdown.on( 'change', function () {
		// hide the dropdown when it is "all" or "none"
		$form.toggleClass( 'mw-tpt-hide-custom-fuzzy', $( this ).val() !== 'custom' );
	} );
}

function configureHideUnchangedTranslationUnits( $container ) {
	var $form = $container.find( '.mw-tpt-sp-markform' );
	$form.find( 'input[name="unchanged-translation-units"]' ).on( 'change', function () {
		$form.toggleClass( 'mw-tpt-hide-unchanged', $( this ).prop( 'checked' ) );
	} );
}

// Init
$( function () {
	var $widgets = $( '#mw-translate-SpecialPageTranslation-prioritylangs' );

	var $container = $( '#mw-content-text' );
	configurePostLinks( $container );
	configureDropdownForFuzzySelector( $container );
	configureHideUnchangedTranslationUnits( $container );
	if ( $widgets.length ) {
		configureLanguageInput( $( '.mw-tpt-sp-markform' ), $widgets );
	}
} );
