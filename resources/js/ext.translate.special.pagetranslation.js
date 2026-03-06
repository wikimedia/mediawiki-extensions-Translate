'use strict';

/*!
 * @author Santhosh Thottingal
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
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
	var $input = $form.find( 'input[name="unchanged-translation-units"]' );
	// Set the form class now (rather than relying on it being set by the PHP code)
	// so checking the checkbox before JS loads works properly
	$form.toggleClass( 'mw-tpt-hide-unchanged', $input.prop( 'checked' ) );
	$input.on( 'change', function () {
		$form.toggleClass( 'mw-tpt-hide-unchanged', $( this ).prop( 'checked' ) );
	} );
}

// Init
$( function () {
	var $container = $( '#mw-content-text' );
	configurePostLinks( $container );
	configureDropdownForFuzzySelector( $container );
	configureHideUnchangedTranslationUnits( $container );
} );
