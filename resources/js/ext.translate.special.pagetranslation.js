'use strict';

/*!
 * @author Santhosh Thottingal
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
function configurePostLinks( $container ) {
	$container.on( 'click', '.mw-translate-encourage, .mw-translate-discourage', function ( e ) {
		const $this = $( e.currentTarget );
		const dis = $this.hasClass( 'mw-translate-discourage' );
		const $li = $this.closest( '.mw-tpt-pagelist-item' );
		const api = new mw.Api();
		api.postWithToken( 'csrf', {
			action: 'discouragetranslation',
			title: $li.attr( 'data-target' ),
			do: dis ? 'discourage' : 'encourage'
		} ).then( updateLinks, function ( _code, data ) {
			if ( data.error.code === 'discouragetranslation-alreadydone' ) {
				updateLinks();
			} else {
				mw.notify( data.error.info, { type: 'error' } );
			}
		} );

		function updateLinks() {
			// Update the link
			if ( dis ) {
				$this.removeClass( 'mw-translate-discourage' )
					.addClass( 'mw-translate-encourage' )
					.attr( 'title', mw.msg( 'tpt-rev-encourage-tooltip' ) )
					.text( mw.msg( 'tpt-rev-encourage' ) );
			} else {
				$this.removeClass( 'mw-translate-encourage' )
					.addClass( 'mw-translate-discourage' )
					.attr( 'title', mw.msg( 'tpt-rev-discourage-tooltip' ) )
					.text( mw.msg( 'tpt-rev-discourage' ) );
			}
			// Now update the (discouraged | old syntax | no transclusion support) list.
			const $acts = $li.find( '.mw-tpt-actions' );
			if ( dis ) {
				$acts.prepend( $( '<li>' ).addClass( 'mw-tpt-actions-discouraged' ).text( mw.msg( 'tpt-tag-discouraged' ) ) );
			} else {
				$acts.find( '.mw-tpt-actions-discouraged' ).remove();
			}
		}
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
