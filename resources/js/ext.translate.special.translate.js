( function ( $, mw ) {
	'use strict';

	/* Workflow selector code */
	function prepareWorkflowSelector() {
		var $submit, $select, submitFunction;

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
			mw.translate.changeWorkflowStatus( $submit.data( 'group' ),
				$submit.data( 'language' ),
				event.data.newstate,
				$submit.data( 'token' )
			).done( successFunction );
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

		/**
		 * Change the group that is currently displayed
		 * in the TUX translation editor.
		 * @param {Object} group a message group object.
		 */
		changeGroup: function ( group ) {
			var $loader = $( '.tux-messagetable-loader' ),
				api = new mw.Api(),
				$description = $( '.tux-editor-header .description' );

			$loader
				.data( 'messagegroup', group.id )
				.data( 'remaining', mw.translate.getStatsForGroup( group.id ).total )
				.removeData( 'offset' )
				.removeAttr( 'data-offset' )
				.removeClass( 'hide' );

			// Clear the current messages
			$( '.tux-message' ).remove();

			// Update the group description in the header
			api.parse(
				group.description
			).done( function ( parsedDescription ) {
				// The parsed text is returned in a <p> tag,
				// so it's removed here.
				$description.html( $( parsedDescription ).html() );
			} ).fail( function ( errorCode, results ) {
				$description.html( group.description );
				mw.log( 'Error parsing description for group ' +
					group.id + ': ' + errorCode + ' ' + results.error.info );
			} );

			mw.translate.loadMessages();
			mw.translate.changeUrl( {
				group: group.id,
				filter: mw.Uri().query.filter || '!translated'
			} );
		},

		changeLanguage: function ( language ) {
			var $loader;

			$loader = $( '.tux-messagetable-loader' ).removeClass( 'hide' );

			$( '.ext-translate-language-selector > .uls' ).text( $.uls.data.getAutonym( language ) );

			$loader.data( 'remaining', mw.translate.getStatsForGroup( $loader.data( 'messagegroup' ) ).total )
				.removeData( 'offset' )
				.removeAttr( 'data-offset' );

			$( '.tux-messagelist' ).data( 'targetlangcode', language );

			// clear current messages;
			$( '.tux-message' ).remove();
			mw.translate.loadMessages();
			mw.translate.changeUrl( {
				'language': language
			} );
		},

		changeFilter: function ( filter ) {
			var $loader;

			$loader = $( '.tux-messagetable-loader' ).removeClass( 'hide' );

			$loader.data( 'remaining', mw.translate.getStatsForGroup( $loader.data( 'messagegroup' ) ).total )
				.data( 'filter', filter )
				.removeData( 'offset' )
				.removeAttr( 'data-offset' );

			// clear current messages;
			$( '.tux-message' ).remove();
			mw.translate.changeUrl( {
				'filter': filter
			} );
			mw.translate.loadMessages();
		},

		changeUrl: function ( params ) {
			var uri = new mw.Uri( window.location.href );

			uri.extend( params );

			// Change the URL with this URI, but don't leave the page.
			if ( history.pushState ) {
				// IE<10 does not support pushState. Never mind.
				history.pushState( uri, null, uri.toString() );
			} else {
				// For old browsers, just reload
				window.location.href = uri.toString();
			}
		},

		changeWorkflowStatus: function ( group, language, state, token ) {
			var api = new mw.Api(),
				params = {
					action: 'groupreview',
					group: group,
					language: language,
					state: state,
					token: token,
					format: 'json'
				};

			return api.post( params );
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
			mw.translate.changeGroup( msgGroup );
		}
	}

	$( document ).ready( function () {
		var uiLanguage, $translateContainer,
			docLanguageAutonym, docLanguageCode, ulsOptions;

		uiLanguage = mw.config.get( 'wgUserLanguage' );

		ourWindowOnBeforeUnloadRegister();
		prepareWorkflowSelector();

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

		// Workflow state selector
		$translateContainer.find( '.tux-workflow-status' )
			.on( 'click', function () {
				$( this ).next( 'ul' ).toggleClass( 'hide' );
			} );

		$translateContainer.find( '.tux-workflow-status-selector li' )
			.on( 'click', function () {
				var state, stateText, $selector;

				state = $( this ).data( 'state' );
				stateText = $( this ).text();
				$selector  = $translateContainer.find( '.tux-workflow-status' );
				$( this ).parent().find( '.selected' ).removeClass( 'selected' );
				$( this ).addClass( 'selected' )
					.parent().addClass( 'hide' );
				$selector.text( mw.msg( 'translate-workflow-set-doing' ) );
				mw.translate.changeWorkflowStatus( $selector.data( 'group' ),
					$selector.data( 'language' ),
					state,
					$selector.data( 'token' )
				).done( function() {
					$selector.text( mw.msg( 'translate-workflowstatus', stateText ) );
				} );
			} );

		// Message filter click handler
		$translateContainer.find( '.row.tux-message-selector > li' ).on( 'click', function () {
			var $this = $( this );

			if ( $this.hasClass( 'more' ) ) {
				return false;
			}

			// Remove the 'selected' class from all the items.
			// Some of them could have been moved to under the "more" menu,
			// so everything under .row.tux-message-selector is searched.
			$translateContainer.find( '.row.tux-message-selector .selected' )
				.removeClass( 'selected' );
			mw.translate.changeFilter( $this.data( 'filter' ) );
			$this.addClass( 'selected' );

			return false;
		} );

		// Don't let clicking the items in the "more" menu
		// affect the rest of it.
		$( '.row.tux-message-selector .more ul' )
			.on( 'click', function ( e ) {
				e.stopPropagation();
			} );

		$( '#tux-option-optional' )
			.on( 'change', function ( e ) {
				var checked = $( this ).prop( 'checked' );

				mw.translate.changeUrl( { optional: checked ? 1 : 0 } );


			} );
	} );

}( jQuery, mediaWiki ) );
