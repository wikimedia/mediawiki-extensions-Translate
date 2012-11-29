( function ( $ ) {
	'use strict';

	var mw, $submit, $select, submitFunction, params;
	mw = mediaWiki;

	$submit = $( '#mw-translate-workflowset' );
	$select = $( '#mw-sp-translate-workflow').find( 'select' );

	$select.find( 'option[value=]' ).attr( 'disabled', 'disabled' );

	submitFunction = function( event ) {
		var successFunction = function( data, textStatus ) {
			if ( data.error ) {
				$submit.val( mw.msg( 'translate-workflow-set-do' ) );
				$submit.attr( 'disabled', false );
				alert( data.error.info );
			} else {
				$submit.val( mw.msg( 'translate-workflow-set-done' ) );
				$select.find( 'option[selected]' ).attr( 'selected', false );
				$select.find( 'option[value=' + event.data.newstate + ']' ).attr( 'selected', 'selected' );
			}
		};

		$submit.attr( 'disabled', 'disable' );
		$submit.val( mw.msg( 'translate-workflow-set-doing' ) );
		params = {
			action: 'groupreview',
			token: $submit.data( 'token' ),
			group: $submit.data( 'group' ),
			language: $submit.data( 'language' ),
			state: event.data.newstate,
			format: 'json'
		};
		$.post( mw.util.wikiScript( 'api' ), params, successFunction );
	};

	$select.change( function( event ) {
		var current = $(this).find( 'option[selected]' ).val(),
			tobe = event.target.value;

		$submit.val( mw.msg( 'translate-workflow-set-do' ) );
		$submit.unbind( 'click' );
		if ( current !== tobe ) {
			$submit.css( 'visibility', 'visible' );
			$submit.attr( 'disabled', false );
			$submit.click( { newstate: tobe }, submitFunction );
		} else {
			$submit.attr( 'disabled', 'disabled' );
		}
	} );
} );

/**
 * A warning to be shown if a user tries to close the page or navigate away
 * from it without saving the written translation.
 *
 * Based on editWarning from the Vector extension, but greatly
 * simplified.
 */

( function ( $, mw ) {
	'use strict';

	function changeGroup( group ) {
		var uri = new mw.Uri( window.location.href );
		uri.extend( {
			action: 'translate',
			group: group
		} );
		window.location.href = uri.toString();
	}

	function groupSelectorHandler( msgGroup ) {
		var $newLink;

		if ( msgGroup.groupcount > 0 ) {
			$newLink = $( '<h3>' ).addClass( 'three columns grouptitle grouplink' )
				.attr( 'data-msggroup', msgGroup.id ).text( 'All' );
			$( '.ext-translate-msggroup-selector .grouplink' ).after( $newLink );
			$newLink.msggroupselector( {
				onSelect: groupSelectorHandler
			} );
		} else {
			changeGroup( msgGroup.id );
		}
	}
	function ourWindowOnBeforeUnloadRegister() {
		pageShowHandler();
		if ( window.addEventListener ) {
			window.addEventListener( 'pageshow', pageShowHandler, false );
		} else if ( window.attachEvent ) {
			window.attachEvent( 'pageshow', pageShowHandler );
		}


		$( '.ext-translate-msggroup-selector .grouplink' ).msggroupselector( {
			onSelect: groupSelectorHandler
		} );
	}

	function pageShowHandler() {
		// Re-add onbeforeunload handler
		window.onbeforeunload = ourWindowOnBeforeUnload;
	}

	function ourWindowOnBeforeUnload() {
		if ( $( '.mw-ajax-dialog:visible' ).length ) {
			// Return our message
			return mw.msg( 'translate-js-support-unsaved-warning' );
		}
	}

	$( document ).ready( ourWindowOnBeforeUnloadRegister );

} )( jQuery, mediaWiki );
