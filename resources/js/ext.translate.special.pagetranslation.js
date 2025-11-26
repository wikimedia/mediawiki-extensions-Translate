'use strict';

/*!
 * @author Santhosh Thottingal
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

const { getMultiselectLookupLanguageSelector } = require( 'mediawiki.languageselector' );

function configureLanguageInput( $form, $widget ) {
	const priorityLanguageData = $widget.data( 'ooui' );

	const $input = $( '<input>' ).prop( {
		type: 'hidden',
		name: 'prioritylangs'
	} );

	const languageSelectorApp = getMultiselectLookupLanguageSelector( {
		selectableLanguages: priorityLanguageData.languages,
		selectedLanguage: priorityLanguageData.selected,
		onLanguageChange: ( languages ) => {
			$input.val( languages.join( ',' ) );
		}
	} );

	$form.prepend( $input );

	const container = document.createElement( 'div' );
	$widget.after( container );
	languageSelectorApp.mount( container );
	$widget.remove();
}

function configurePostLinks( $container ) {
	$container.on( 'click', '.mw-translate-jspost', function ( e ) {
		const url = new URL( e.target.href );
		// In future use Object.fromEntries()
		const params = {
			token: mw.user.tokens.get( 'csrfToken' )
		};
		for ( const [ key, value ] of url.searchParams.entries() ) {
			params[ key ] = value;
		}

		$.post( url.pathname, params ).done( function () {
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
