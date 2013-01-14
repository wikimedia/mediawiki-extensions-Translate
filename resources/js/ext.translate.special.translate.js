( function ( $, mw ) {
	'use strict';

	/* Workflow selector code */
	function prepareWorkflowSelector() {
		var $submit, $select, submitFunction, params;

		$submit = $( '#mw-translate-workflowset' );
		$select = $( '#mw-sp-translate-workflow' ).find( 'select' );

		$select.find( 'option[value=]' ).prop( 'disabled', true );

		submitFunction = function ( event ) {
			var successFunction = function ( data ) {
				if ( data.error ) {
					$submit.val( mw.msg( 'translate-workflow-set-do' ) );
					$submit.prop( 'disabled', false );
					window.alert( data.error.info );
				} else {
					$submit.val( mw.msg( 'translate-workflow-set-done' ) );
					$select.find( 'option[selected]' ).prop( 'selected', false );
					$select.find( 'option[value=' + event.data.newstate + ']' ).prop( 'selected', true );
				}
			};

			$submit.prop( 'disabled', true );
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

		$select.change( function ( event ) {
			var current = $( this ).find( 'option[selected]' ).val(),
				tobe = event.target.value;

			$submit.val( mw.msg( 'translate-workflow-set-do' ) );
			$submit.unbind( 'click' );
			if ( current !== tobe ) {
				$submit.css( 'visibility', 'visible' );
				$submit.prop( 'disabled', false );
				$submit.click( { newstate: tobe }, submitFunction );
			} else {
				$submit.prop( 'disabled', true );
			}
		} );
	}

	mw.translate = mw.translate || {};

	mw.translate = $.extend( mw.translate, {

		changeGroup: function ( group ) {
			mw.translate.changeUrl( {
				'group': group,
				filter: mw.Uri().query.filter || '!translated'
			} );
		},

		changeLanguage: function ( language ) {
			mw.translate.changeUrl( { 'language': language } );
		},

		changeUrl: function ( params ) {
			var uri;
			uri = new mw.Uri( window.location.href );
			uri.extend( params );
			window.location.href = uri.toString();
		},

		canTranslate: function () {
			return mw.config.get( 'TranslateRight' );
		},

		/**
		 * Get the documentation edit URL for a title
		 *
		 * @param {String} title Message title with namespace
		 * @return {String} URL for editing the documentation
		 */
		getDocumentationEditURL: function ( title ) {
			var descUri = new mw.Uri( window.location.href );

			descUri.path = mw.config.get( 'wgScript' );
			descUri.query = {
				action: 'edit',
				title: title + '/' +  mw.config.get( 'wgTranslateDocumentationLanguageCode' )
			};

			return descUri.toString();
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
			$( '.ext-translate-msggroup-selector .tail' ).remove();
			$newLink = $( '<span>' ).addClass( 'grouptitle grouplink tail' )
				.text( mw.msg( 'translate-msggroupselector-search-all' ) );
			$( '.ext-translate-msggroup-selector .grouplink:last' ).after( $newLink );
			$newLink.data( 'msggroupid', msgGroup.id );
			$newLink.msggroupselector( {
				onSelect: groupSelectorHandler
			} );
		} else {
			mw.translate.changeGroup( msgGroup.id );
		}
	}

	$( document ).ready( function () {
		var uiLanguage, $translateContainer,
			docLanguageAutonym, docLanguageCode, ulsOptions;

		uiLanguage = mw.config.get( 'wgUserLanguage' );

		ourWindowOnBeforeUnloadRegister();
		prepareWorkflowSelector();

		$( '#tux-option-optional' ).click( function () {
			mw.translate.changeUrl( { 'optional': $( this ).prop( 'checked' ) ? 1 : 0 } );
		} );

		$.when(
			// Get ready with language stats
			$.fn.languagestatsbar.Constructor.prototype.getStats( uiLanguage )
		).then( function () {
				$( '.ext-translate-msggroup-selector .grouplink' ).msggroupselector( {
					onSelect: groupSelectorHandler
				} );
				$( '.tux-message-list-statsbar' ).languagestatsbar( {
					language: uiLanguage,
					group: $( '.tux-message-list-statsbar' ).data( 'messagegroup' )
				} );
		} );

		// Use ULS for language selection if it's available
		if ( $.uls ) {
			ulsOptions = {
				onSelect: function ( language ) {
					mw.translate.changeLanguage( language );
				},
				languages: mw.config.get( 'wgULSLanguages' ),
				searchAPI: mw.util.wikiScript( 'api' ) + '?action=languagesearch',
				quickList: function () {
					return mw.uls.getFrequentLanguageList();
				}
			};

			// If a documentation pseudo-language is defined,
			// add it to the language selector
			docLanguageCode = mw.config.get( 'wgTranslateDocumentationLanguageCode' );
			if ( docLanguageCode ) {
				docLanguageAutonym = mw.msg( 'translate-documentation-language' );
				ulsOptions.languages[docLanguageCode] = docLanguageAutonym;

				$.uls.data.addLanguage( docLanguageCode, {
					script: $.uls.data.getScript( mw.config.get( 'wgContentLanguage' ) ),
					regions: ['SP'],
					autonym: docLanguageAutonym
				} );

				ulsOptions.showRegions = ['WW', 'SP', 'AM', 'EU', 'ME', 'AF', 'AS', 'PA'];
			}

			$( '.ext-translate-language-selector .uls' ).uls( ulsOptions );
		}

		if ( $.fn.translateeditor ) {
			// New translation editor
			$( '.tux-message' ).translateeditor();
		}

		$translateContainer = $( '.ext-translate-container' );
		$translateContainer.find( '.tux-editor-clear-translated' )
			.click( function () {
				$translateContainer.find( '.tux-message-item' ).filter( '.translated, .proofread' ).remove();
			} );

	} );

}( jQuery, mediaWiki ) );
