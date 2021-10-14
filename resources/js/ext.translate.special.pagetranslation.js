'use strict';
/* eslint-disable no-implicit-globals */

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

// Init
$( function () {
	var $widgets = $( '#mw-translate-SpecialPageTranslation-prioritylangs' );

	configurePostLinks( $( '#mw-content-text' ) );

	if ( $widgets.length ) {
		configureLanguageInput( $( '.mw-tpt-sp-markform' ), $widgets );
	}
} );
