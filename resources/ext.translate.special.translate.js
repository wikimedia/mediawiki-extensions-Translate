jQuery( function( $ ) {
	"use strict";
	var mw = mediaWiki;

	// BC for MW < 1.18
	if ( !mw.util.wikiScript ) {
		mw.util.wikiScript = function( str ) {
			return mw.config.get( 'wgScriptPath' ) + '/' + ( str || 'index' ) + mw.config.get( 'wgScriptExtension' );
		};
	}

	var $submit = $( "input#mw-translate-workflowset" );
	var $select = $( "#mw-sp-translate-workflow select" );
	$select.find( "option[value=]" ).attr( "disabled", "disabled" );

	var submitFunction = function( event ) {
		var successFunction = function( data, textStatus ) {
			if ( data.error ) {
				$submit.val( mw.msg( "translate-workflow-set-do" ) );
				$submit.attr( "disabled", false );
				alert( data.error.info );
			} else {
				$submit.val( mw.msg( "translate-workflow-set-done" ) );
				$select.find( "option[selected]" ).attr( "selected", false );
				$select.find( "option[value=" + event.data.newstate +"]" ).attr( "selected", "selected" );
			}
		};

		$submit.attr( "disabled", "disable" );
		$submit.val( mw.msg( "translate-workflow-set-doing" ) );
		var params = {
			action: "groupreview",
			token: $submit.data( "token" ),
			group: $submit.data( "group" ),
			language: $submit.data( "language" ),
			state: event.data.newstate,
			format: "json"
		};
		$.post( mw.util.wikiScript( "api" ), params, successFunction );
	};

	$select.change( function( event ) {
		var current = $(this).find( "option[selected]" ).val();
		var tobe = event.target.value;

		$submit.val( mw.msg( "translate-workflow-set-do" ) );
		$submit.unbind( "click" );
		if ( current !== tobe ) {
			$submit.css( "visibility", "visible" );
			$submit.attr( "disabled", false );
			$submit.click( { newstate: tobe }, submitFunction );
		} else {
			$submit.attr( "disabled", "disabled" );
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
	"use strict";
	function ourWindowOnBeforeUnloadRegister() {
		pageShowHandler();
		if ( window.addEventListener ) {
			window.addEventListener( 'pageshow', pageShowHandler, false );
		} else if ( window.attachEvent ) {
			window.attachEvent( 'pageshow', pageShowHandler );
		}
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
