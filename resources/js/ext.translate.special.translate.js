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
			var changes,
				api = new mw.Api(),
				$description = $( '.tux-editor-header .description' );

			changes = {
				group: group.id
			};

			// Update the group description in the header
			api.parse(
				group.description
			).done( function ( parsedDescription ) {
				// The parsed text is returned in a <p> tag,
				// so it's removed here.
				$description.html( $( parsedDescription ).html() );
			} ).fail( function () {
				$description.html( group.description );
				mw.log( 'Error parsing description for group ' + group.id );
			} );

			mw.translate.loadMessages( changes );
			mw.translate.changeUrl( changes );
			mw.translate.prepareWorkflowSelector( group );
			updateGroupWarning();
		},

		changeLanguage: function ( language ) {
			var changes = {
				language: language
			};

			$( '.ext-translate-language-selector > .uls' ).text( $.uls.data.getAutonym( language ) );
			$( '.tux-messagelist' ).data( 'targetlangcode', language );

			mw.translate.changeUrl( changes );
			$( '.tux-statsbar' ).trigger( 'refresh', language );
			mw.translate.loadMessages();
			updateGroupWarning();
		},

		changeFilter: function ( filter ) {
			var realFilters, uri;

			realFilters = [ '!ignored' ];
			uri = new mw.Uri( window.location.href );
			if ( uri.query.optional !== '1' ) {
				realFilters.push( '!optional' );
			}
			if ( filter ) {
				realFilters.push( filter );
			}

			mw.translate.changeUrl( { filter: filter } );
			mw.translate.loadMessages( { filter: realFilters.join( '|' ) } );
		},

		changeUrl: function ( params ) {
			var uri = new mw.Uri( window.location.href );

			uri.extend( params );

			if ( uri.toString() === window.location.href ) {
				return;
			}

			// Change the URL with this URI, but don't leave the page.
			if ( history.pushState && $( '.tux-messagelist' ).length ) {
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
		},


		prepareWorkflowSelector: function ( group ) {
			var $workflowStateSelector,
				$workflowStatusTrigger = $( '.tux-workflow-status' );

			if ( !group.workflowstates ) {
				$workflowStatusTrigger.addClass( 'hide' );

				return;
			}

			$workflowStateSelector = $( 'ul.tux-workflow-status-selector' );

			$workflowStateSelector.empty();

			$.each( group.workflowstates, function ( id, workflowstate ) {
				if ( workflowstate._canchange ) {
					workflowstate.id = id;

					$workflowStateSelector.append( $( '<li>' )
						.data( 'state', workflowstate )
						.text( workflowstate._name )
						.on( 'click', function () {
							var $this = $( this );

							$workflowStateSelector.find( '.selected' ).removeClass( 'selected' );
							$this.addClass( 'selected' )
								.parent().addClass( 'hide' );
							workflowSelectionHandler( $this.data( 'state' ) );
						} )
					);
				}
			} );

			$workflowStatusTrigger
				.text( mw.msg( 'translate-workflow-state-' ) )
				.removeClass( 'hide' );
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

	// Returns an array of jQuery objects of rows of translated
	// and proofread messages in the TUX editors.
	// Used several times.
	function getTranslatedMessages( $translateContainer ) {
		$translateContainer = $translateContainer || $( '.ext-translate-container' );
		return $translateContainer.find( '.tux-message-item' )
			.filter( '.translated, .proofread' );
	}

	function workflowSelectionHandler( state ) {
		var $status = $( '.tux-workflow-status' );

		$status.text( mw.msg( 'translate-workflow-set-doing' ) );
		mw.translate.changeWorkflowStatus( $status.data( 'group' ),
			$status.data( 'language' ),
			state.id,
			$status.data( 'token' )
		).done( function() {
			$status.text( mw.msg( 'translate-workflowstatus', state._name ) );
		} );
	}

	function updateGroupWarning() {
		/*jshint loopfunc:true */
		var preferredLanguages,
			$groupWarning = $( '.tux-editor-header .group-warning' ),
			targetLanguage = $( '.tux-messagelist' ).data( 'targetlangcode' ),
			msgGroupData = mw.translate.getGroup(
				$( '.tux-messagetable-loader' ).data( 'messagegroup' )
			);

		if ( msgGroupData.prioritylangs &&
			$.inArray( targetLanguage, msgGroupData.prioritylangs ) === -1
		) {
			preferredLanguages = $.map( msgGroupData.prioritylangs, function ( code ) {
				return $.uls.data.getAutonym( code );
			} );

			new mw.Api().parse(
				mw.message( msgGroupData.priorityforce ?
					'tpt-discouraged-language-force' :
					'tpt-discouraged-language',
					'',
					$.uls.data.getAutonym( targetLanguage ),
					preferredLanguages.join( ', ' )
				).parse()
			).done( function ( parsedWarning ) {
				$groupWarning.html( parsedWarning );
			} );
		} else {
			$groupWarning.empty();
		}
	}

	$( document ).ready( function () {
		var $translateContainer, $hideTranslatedButton, $controlOwnButton, $messageList,
			targetLanguage, docLanguageAutonym, docLanguageCode, ulsOptions, filter, uri;

		$messageList = $( '.tux-messagelist' );
		if ( $messageList.length ) {
			uri = new mw.Uri( window.location.href );
			filter = uri.query.filter;

			if ( filter === undefined ) {
				filter = '!translated';
			}

			mw.translate.changeFilter( filter );
			$( '.tux-message-selector li' ).each( function () {
				var $this = $( this );

				if ( $this.data( 'filter' ) === filter ) {
					$this.addClass( 'selected' );
				}
			} );
		}

		targetLanguage = $messageList.data( 'targetlangcode' ) // for tux=1
			|| mw.config.get( 'wgUserLanguage' ); // for tux=0

		ourWindowOnBeforeUnloadRegister();
		prepareWorkflowSelector();
		$( '.ext-translate-msggroup-selector .grouplink' ).msggroupselector( {
			onSelect: mw.translate.changeGroup
		} );

		$.when(
			// Get ready with language stats
			mw.translate.loadLanguageStats( targetLanguage ),
			// Get ready with message groups
			mw.translate.loadMessageGroups()
		).then( function () {
			$( '.ext-translate-msggroup-selector .grouplink' ).trigger( 'dataready.translate' );
			$( '.tux-message-list-statsbar' ).languagestatsbar( {
				language: targetLanguage,
				group: $( '.tux-message-list-statsbar' ).data( 'messagegroup' )
			} );

			updateGroupWarning();
		} );

		$( '.tux-messagelist' ).messagetable();

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
				mw.translate.addDocumentationLanguage();
				ulsOptions.showRegions = ['WW', 'SP', 'AM', 'EU', 'ME', 'AF', 'AS', 'PA'];
			}

			$( '.ext-translate-language-selector .uls' ).uls( ulsOptions );
		}

		if ( $.fn.translateeditor ) {
			// New translation editor
			$( '.tux-message' ).translateeditor();
		}

		$translateContainer = $( '.ext-translate-container' );

		if ( mw.translate.canProofread() ) {
			$translateContainer.find( '.tux-proofread-button' ).removeClass( 'hide' );
		}

		$hideTranslatedButton = $translateContainer.find( '.tux-editor-clear-translated' );
		$hideTranslatedButton
			.prop( 'disabled', !getTranslatedMessages( $translateContainer ).length )
			.click( function () {
				getTranslatedMessages( $translateContainer ).remove();
				$( this ).prop( 'disabled', true );
			} );

		$controlOwnButton = $translateContainer.find( '.tux-proofread-own-translations-button' );
		$controlOwnButton.click( function () {
			var $this = $( this ),
				ownTranslatedMessages = $translateContainer.find( '.own-translation' ),
				hideMessage = mw.msg( 'tux-editor-proofreading-hide-own-translations' ),
				showMessage = mw.msg( 'tux-editor-proofreading-show-own-translations' );

			if ( $this.hasClass( 'down' ) ) {
				ownTranslatedMessages.removeClass( 'hide' );
				$this.removeClass( 'down' ).text( hideMessage );
			} else {
				ownTranslatedMessages.addClass( 'hide' );
				$this.addClass( 'down' ).text( showMessage );
			}
		} );

		// Workflow state selector

		// Toggling menu visibility
		$translateContainer.find( '.tux-workflow-status' )
			.on( 'click', function ( e ) {
				$( this ).next( 'ul' ).toggleClass( 'hide' );
				e.preventDefault();
			} );

		// Handling state changes
		$translateContainer.find( '.tux-workflow-status-selector li' )
			.on( 'click', function () {
				var state, stateText, $selector,
					$this = $( this );

				state = $this.data( 'state' );
				stateText = $this.text();
				$selector = $translateContainer.find( '.tux-workflow-status' );
				$this.parent().find( '.selected' ).removeClass( 'selected' );
				$this.addClass( 'selected' )
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

		// Hide the workflow selector when clicking outside of it
		$( 'html' ).on( 'click', function ( e ) {
			if ( !e.isDefaultPrevented() ) {
				$( 'ul.tux-workflow-status-selector' ).addClass( 'hide' );
			}
		} );

		// Message filter click handler
		$translateContainer.find( '.row.tux-message-selector > li' ).on( 'click', function () {
			var newFilter,
				$this = $( this );

			if ( $this.hasClass( 'more' ) ) {
				return false;
			}

			newFilter = $this.data( 'filter' );

			// Remove the 'selected' class from all the items.
			// Some of them could have been moved to under the "more" menu,
			// so everything under .row.tux-message-selector is searched.
			$translateContainer.find( '.row.tux-message-selector .selected' )
				.removeClass( 'selected' );
			mw.translate.changeFilter( newFilter );
			$this.addClass( 'selected' );

			// TODO: this could should be in messagetable
			if ( newFilter === '!translated' ) {
				$hideTranslatedButton
					.removeClass( 'hide' )
					.prop( 'disabled', !getTranslatedMessages( $translateContainer ).length );
			} else {
				$hideTranslatedButton.addClass( 'hide' );
			}

			return false;
		} );

		// TODO: this could should be in messagetable
		if ( $( '.tux-messagetable-loader' ).data( 'filter' ) === '!translated' ) {
			$hideTranslatedButton.removeClass( 'hide' );
		} else {
			$hideTranslatedButton.addClass( 'hide' );
		}

		// Don't let clicking the items in the "more" menu
		// affect the rest of it.
		$( '.row.tux-message-selector .more ul' )
			.on( 'click', function ( e ) {
				e.stopPropagation();
			} );

		$( '#tux-option-optional' ).on( 'change', function () {
			var uri = new mw.Uri( window.location.href ),
				checked = $( this ).prop( 'checked' );

			mw.translate.changeUrl( { optional: checked ? 1 : 0 } );
			mw.translate.changeFilter( uri.query.filter );
		} );
	} );

}( jQuery, mediaWiki ) );
