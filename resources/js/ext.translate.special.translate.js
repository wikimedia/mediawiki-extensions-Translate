( function ( $, mw ) {
	'use strict';

	var $submit, $select, submitFunction, params;

	mw.translate = mw.translate || {};

	mw.translate = $.extend( mw.translate, {
		changeGroup: function( group ) {
			var uri = new mw.Uri( window.location.href );
			uri.extend( {
				group: group
			} );
			window.location.href = uri.toString();
		},

		changeLanguage: function( language ) {
			var uri = new mw.Uri( window.location.href );
			uri.extend( {
				language: language
			} );
			window.location.href = uri.toString();
		}
	} );

	$submit = $( '#mw-translate-workflowset' );
	$select = $( '#mw-sp-translate-workflow' ).find( 'select' );

	$select.find( 'option[value=]' ).attr( 'disabled', 'disabled' );

	submitFunction = function( event ) {
		var successFunction = function( data ) {
			if ( data.error ) {
				$submit.val( mw.msg( 'translate-workflow-set-do' ) );
				$submit.attr( 'disabled', false );
				window.alert( data.error.info );
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
		var current = $( this ).find( 'option[selected]' ).val(),
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

	/**
	 * A warning to be shown if a user tries to close the page or navigate away
	 * from it without saving the written translation.
	 *
	 * Based on editWarning from the Vector extension, but greatly
	 * simplified.
	 */
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

	function groupSelectorHandler( msgGroup ) {
		var $newLink;

		if ( msgGroup.groups && msgGroup.groups.length > 0 ) {
			$newLink = $( '<span>' ).addClass( 'grouptitle grouplink' )
				.text( mw.msg( 'translate-msggroupselector-search-all' ) );
			$( '.ext-translate-msggroup-selector .grouplink' ).after( $newLink );
			$newLink.data( 'msggroupid', msgGroup.id );
			$newLink.msggroupselector( {
				onSelect: groupSelectorHandler
			} );
		} else {
			mw.translate.changeGroup( msgGroup.id );
		}
	}

	$( document ).ready( function () {
		var uiLanguage;

		uiLanguage = mw.config.get( 'wgUserLanguage' );

		ourWindowOnBeforeUnloadRegister();

		$.when(
			// Get ready with language stats
			$.fn.languagestatsbar.Constructor.prototype.getStats( uiLanguage )
		).then( function () {
				$( '.ext-translate-msggroup-selector .grouplink' ).msggroupselector( {
					onSelect: groupSelectorHandler
				} );
			} );

		// Use ULS for language selection if it's available
		if ( $.uls ) {
			$( '.ext-translate-language-selector.uls' ).uls( {
				onSelect: function ( language ) {
					mw.translate.changeLanguage( language );
				},
				languages: mw.config.get( 'wgULSLanguages' ),
				searchAPI: mw.util.wikiScript( 'api' ) + '?action=languagesearch',
				quickList: function () {
					return mw.uls.getFrequentLanguageList();
				}
			} );

			if ( $.fn.translateeditor ) {
				// New translation editor
				$( '.tux-message' ).translateeditor();
			}
		}
	} );

}( jQuery, mediaWiki ) );
