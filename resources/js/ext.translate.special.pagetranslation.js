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
	var widget, $input;

	/** @type {LanguagesMultiselectWidget} */
	widget = OO.ui.infuse( $widget, { api: new mw.Api() } );

	$input = $( '<input>' ).prop( {
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
		var params,
			uri = new mw.Uri( e.target.href );

		params = uri.query;
		params.token = mw.user.tokens.get( 'csrfToken' );
		$.post( uri.path, params ).done( function () {
			location.reload();
		} );

		e.preventDefault();
	} );
}

// Init
$( function () {
	var mediaWikiVersion = mw.config.get( 'wgVersion' ),
		$widgets = $( '#mw-translate-SpecialPageTranslation-prioritylangs' );

	configurePostLinks( $( '#mw-content-text' ) );

	if ( $widgets.length ) {
		// On MW 1.34, pre-selected priority languages are not being displayed when using
		// LanguagesMultiselectWidget, which in turn uses MenuTagMultiselectWidget.
		// This could be due to an older version of OOUI.
		// Use a normal textarea and remove the loading input.
		if ( ( /^1\.34\./ ).test( mediaWikiVersion ) ) {
			$widgets.find( '.oo-ui-textInputWidget' ).last().remove();
			return;
		}

		configureLanguageInput( $( '.mw-tpt-sp-markform' ), $widgets );
	}
} );
