( function ( $, mw ) {
	'use strict';

	function TranslateEditor( element ) {
		this.$editTrigger = $( element );
		this.$editor = null;
		this.$messageItem = this.$editTrigger.find( '.tux-message-item' );
		this.shown = false;
		this.dirty = false;
		this.saving = false;
		this.expanded = false;
		this.listen();
	}

	TranslateEditor.prototype = {

		/**
		 * Initialize the plugin
		 */
		init: function () {
			this.$editor = $( '<div>' )
				.addClass( 'row tux-message-editor' )
				.append(
					this.prepareEditorColumn(),
					this.prepareInfoColumn()
				);

			this.expanded = false;
			this.$editTrigger.append( this.$editor );
			this.$editor.hide();

			this.getTranslationSuggestions();
		},

		/**
		 * Mark the message as unsaved, can be resumed later
		 */
		markUnsaved: function () {
			this.$editTrigger.find( '.tux-list-status' )
				.empty()
				.append( $( '<span>' )
					.addClass( 'tux-status-unsaved' )
					.text( mw.msg( 'tux-status-unsaved' ) )
				);
		},

		/**
		 * Mark the message as being saved
		 */
		markSaving: function () {
			// Disable the save button
			this.$editor.find( '.tux-editor-save-button' )
				.prop( 'disabled', true );

			// Add a "Saving" indicator
			this.$editTrigger.find( '.tux-list-status' )
				.empty()
				.append( $( '<span>' )
					.addClass( 'tux-status-unsaved' )
					.text( mw.msg( 'tux-status-saving' ) )
				);
		},

		/**
		 * Mark the message as translated and successfully saved.
		 */
		markTranslated: function () {
			this.$editTrigger.find( '.tux-list-status' )
				.empty()
				.append( $( '<span>' )
					.addClass( 'tux-status-translated' )
					.text( mw.msg( 'tux-status-translated' ) )
				);

			this.dirty = false;
		},

		/**
		 * Save the translation
		 */
		save: function () {
			var translateEditor = this,
				api = new mw.Api(),
				translation = translateEditor.$editor.find( 'textarea' ).val();

			translateEditor.saving = true;

			// For responsiveness and efficiency,
			// immediately move to the next message.
			// TODO: Handle last message
			translateEditor.next();

			// XXX: Any validations to be done before proceeding?
			api.post( {
				action: 'edit',
				title: translateEditor.$editTrigger.data( 'title' ),
				text: translation,
				token: mw.user.tokens.get( 'editToken' )
			}, {
				ok: function ( response ) {
					if ( response.edit.result === 'Success' ) {
						translateEditor.markTranslated();

						// Update the translation
						translateEditor.$editTrigger.data( 'translation', translation );
						translateEditor.$editTrigger.find( '.tux-list-translation' )
							.text( translation );
					} else {
						translateEditor.savingError( response.warning );
					}

					translateEditor.saving = false;
				},
				// TODO: Should also handle complete failure,
				// for example client or server going offline.
				err: function ( errorCode, results ) {
					translateEditor.savingError( results.error.info );

					translateEditor.saving = false;
				}
			} );
		},

		/**
		 * Marks that there was a problem saving a translation.
		 * @param string error Strings of warnings to display.
		 */
		savingError: function ( error ) {
			this.populateWarningsBoxes( [
				mw.msg( 'tux-editor-save-failed', error )
			] );

			this.markUnsaved();
		},

		/**
		 * Skip the current message.
		 * Record it to mark as hard.
		 */
		skip: function () {
			var translateEditor = this,
				api = new mw.Api();

			api.post( {
				action: 'hardmessages',
				title: translateEditor.$editTrigger.data( 'title' ),
				token: mw.user.tokens.get( 'editToken' )
			} );
			// We don't care about the result of the above ajax call
		},

		/**
		 * Jump to the next translation editor row.
		 */
		next: function () {
			var $next;

			if ( this.dirty ) {
				if ( this.saving ) {
					this.markSaving();
				} else {
					this.markUnsaved();
				}
			}

			$next = this.$editTrigger.next( '.tux-message' );

			if ( !$next.length ) {
				return;
			}

			$next.data( 'translateeditor' ).show();
		},

		prepareEditorColumn: function () {
			var translateEditor = this,
				sourceString,
				$editorColumn,
				$messageKeyLabel,
				$moreWarningsTab,
				$warnings,
				$warningsBlock,
				$textArea,
				$buttonBlock,
				$saveButton = $( [] ),
				$requestRight = $( [] ),
				$skipButton,
				$sourceString,
				$closeIcon,
				$layoutActions,
				$infoToggleIcon,
				$messageList;

			$editorColumn = $( '<div>' )
				.addClass( 'seven columns editcolumn' );

			$messageKeyLabel = $( '<div>' )
				.addClass( 'ten columns text-left messagekey' )
				.text( this.$editTrigger.data( 'title' ) )
				.append( $('<span>').addClass( 'caret' ) );

			$closeIcon = $( '<span>' )
				.addClass( 'one column close' )
				.on( 'click', function ( e ) {
					translateEditor.hide();
					e.stopPropagation();
				} );

			$infoToggleIcon = $( '<span>' )
				// Initially the editor column is contracted,
				// so show the expand button first
				.addClass( 'one column editor-info-toggle editor-expand' )
				.on( 'click', function () {
					translateEditor.infoToggle( $( this ) );
				} );

			$layoutActions = $( '<div>' )
				.addClass( 'two columns layout-actions' )
				.append( $closeIcon, $infoToggleIcon );

			$editorColumn.append( $( '<div>' )
				.addClass( 'row' )
				.append( $messageKeyLabel, $layoutActions )
			);

			$messageList = $( '.tux-messagelist' );
			sourceString = this.$editTrigger.data( 'source' );
			$sourceString = $( '<span>' )
				.addClass( 'eleven column sourcemessage' )
				.attr( {
					'lang': $messageList.data( 'sourcelangcode' ),
					'dir': $messageList.data( 'sourcelangdir' )
				} )
				.text( sourceString );

			// Adjust the font size for the message string based on the length
			if ( sourceString.length > 100 && sourceString.length < 200 ) {
				$sourceString.addClass( 'long' );
			}

			if ( sourceString.length > 200 ) {
				$sourceString.addClass( 'longer' );
			}

			$editorColumn.append( $( '<div>' )
				.addClass( 'row' )
				.append( $sourceString )
			);

			$warnings = $( '<div>' )
				.addClass( 'tux-warning' )
				.hide();

			$moreWarningsTab = $( '<div>' )
				.addClass( 'tux-more-warnings' )
				.on( 'click', function () {
					var $this = $( this ),
						$moreWarnings = $warnings.children(),
						lastWarningIndex = $moreWarnings.length - 1;

					// If the warning list is not open only one warning is shown
					// TODO: This class is now removed from CSS files and is used only for identifying the tab's state.
					// It's not necessarily bad, but there may be something more robust.
					if ( $this.hasClass( 'open' ) ) {
						$moreWarnings.each( function ( index, element ) {
							// The first element must always be shown
							if ( index ) {
								// TODO: Suggestion by Santhosh: For hiding and showing, use the grid frameworks 'hide' class
								$( element ).hide();
							}
						} );

						$this.removeClass( 'open' );
						$this.text( mw.msg( 'tux-warnings-more', lastWarningIndex ) );
					} else {
						$moreWarnings.each( function ( index, element ) {
							// The first element must always be shown
							if ( index ) {
								// TODO: Suggestion by Santhosh: For hiding and showing, use the grid frameworks 'hide' class
								$( element ).show();
							}
						} );

						$this.addClass( 'open' );
						$this.text( mw.msg( 'tux-warnings-hide' ) );
					}
				} )
				.hide();

			$textArea = $( '<textarea>' )
				.attr( {
					'placeholder': mw.msg( 'tux-editor-placeholder' ),
					'lang': $messageList.data( 'targetlangcode' ),
					'dir': $messageList.data( 'targetlangdir' )
				} )
				.on( 'keypress keyup keydown', function () {
					translateEditor.dirty = true;
					translateEditor.$editor.find( '.tux-editor-save-button' )
						.removeAttr( 'disabled' );

					// Expand the text area height as content grows
					while ( $( this ).outerHeight() < this.scrollHeight
						+ parseFloat( $( this ).css( 'borderTopWidth' ) )
						+ parseFloat( $( this ).css( 'borderBottomWidth' ) ) ) {
						$( this ).height( $( this ).height()
							+ parseFloat( $( this ).css( 'fontSize' ) ) );
					}
				} );

			$textArea.keyup( function () {
				translateEditor.keyup();
			} );

			if ( this.$editTrigger.data( 'translation' ) ) {
				$textArea.text( this.$editTrigger.data( 'translation' ) );
			}

			$warningsBlock = $( '<div>' )
				.addClass( 'tux-warnings-block' )
				.append( $moreWarningsTab, $warnings );

			$editorColumn.append( $( '<div>' )
				.addClass( 'editarea eleven columns' )
				.append( $warningsBlock, $textArea )
			);

			if ( mw.translate.canTranslate() ) {
				$saveButton = $( '<button>' )
					.text( mw.msg( 'tux-editor-save-button-label' ) )
					.attr( {
						'accesskey': 's',
						'title': mw.util.tooltipAccessKeyPrefix + 's',
						'disabled': true
					} )
					.addClass( 'blue button tux-editor-save-button' )
					.on( 'click', function ( e ) {
						translateEditor.save();
						e.stopPropagation();
					} );
			} else {
				$requestRight = $( '<span>' )
					.text( mw.msg( 'translate-edit-nopermission' ) )
					.append( $( '<a>' )
						.text( mw.msg( 'translate-edit-askpermission' ) )
						.addClass( 'tux-editor-ask-permission' )
						.attr( {
							'href': mw.util.wikiGetlink( mw.config.get( 'wgTranslatePermissionUrl' ) )
						} )
					);
			}

			$skipButton = $( '<button>' )
				.text( mw.msg( 'tux-editor-skip-button-label' ) )
				.attr( {
					'accesskey': 'd',
					'title': mw.util.tooltipAccessKeyPrefix + 'd'
				} )
				.addClass( 'button tux-editor-skip-button' )
				.on( 'click', function ( e ) {
					translateEditor.skip();
					translateEditor.next();
					e.stopPropagation();
				} );

			$buttonBlock = $( '<div>' )
				.addClass( 'twelve columns' )
				.append( $requestRight, $saveButton, $skipButton );

			$editorColumn.append( $( '<div>' )
				.addClass( 'row' )
				.append( $buttonBlock )
			);

			$editorColumn.append( $( '<div>' )
				.addClass( 'row text-left shortcutinfo' )
				.text( mw.msg( 'tux-editor-shortcut-info',
					// Save button object may be null if user has no rights.
					// So cannot depend its title attribute here.
					( mw.util.tooltipAccessKeyPrefix + 's' ).toUpperCase(),
					$skipButton.attr( 'title' ).toUpperCase() )
				)
			);

			return $editorColumn;
		},

		/**
		 * Handle the keypress events in the translation editor.
		 * After a few millisecond delay, validates the translation.
		 */
		keyup: function () {
			var translateEditor = this;

			delay( function () {
				var url = new mw.Uri( mw.config.get( 'wgScript' ) ),
					$textArea = translateEditor.$editor.find( 'textarea' );

				// TODO: We need a better API for this
				url.extend( {
					title: 'Special:Translate/editpage',
					suggestions: 'checks',
					page: translateEditor.$editTrigger.data( 'title' ),
					loadgroup: translateEditor.$editTrigger.data( 'group' )
				} );

				$.post( url.toString(),  {
						translation: $textArea.val()
					}, function ( data ) {
						translateEditor.populateWarningsBoxes( data );
				} );
			}, 1000 );
		},

		/**
		 * Displays the supplied warnings from the bottom up near the translation edit area.
		 * If no warnings are supplied, the warnings area is cleaned.
		 *
		 * @param {Array|string} warnings Strings of warnings to display. If it's not an array, it's assumed to be a JSON string.
		 */
		populateWarningsBoxes: function ( warnings ) {
			var warningIndex, $newWarning,
				$warnings = this.$editTrigger.find( '.tux-warning' ),
				$moreWarningsTab = this.$editTrigger.find( '.tux-more-warnings' );

			// TODO: We need an api that gives json always and avoid explicit json parsing here.
			if ( !$.isArray( warnings ) ) {
				warnings = jQuery.parseJSON( warnings );
			}

			this.$editTrigger.find( '.tux-warning' ).empty();

			// TODO: if warnings is undefined, second part of this condition will throw error.
			if ( warnings === null || warnings.length === 0 ) {
				$moreWarningsTab.hide();
				return;
			}

			for ( warningIndex = 0; warningIndex < warnings.length; warningIndex++ ) {
				$newWarning = $( '<div>' )
					.addClass( 'tux-warning-message' )
					.html( warnings[warningIndex] );

				// Initially hide all the warnings except the first one
				if ( warningIndex ) {
					$newWarning.hide();
				}

				$warnings.append( $newWarning );
			}

			$warnings.show();

			if ( warnings.length > 1 ) {
				$moreWarningsTab
					.text( mw.msg( 'tux-warnings-more', warnings.length - 1 ) )
					.show();
			} else {
				// TODO: Hide this by default and show when there are more warnings.
				$moreWarningsTab.hide();
			}
		},

		prepareInfoColumn: function () {
			var $infoColumn,
				$infoColumnBlock,
				translateDocumentationLanguageCode;

			$infoColumnBlock = $( '<div>' )
				.addClass( 'five columns infocolumn-block' );

			$infoColumnBlock.append( $( '<span>' ).addClass( 'caret' ) );

			$infoColumn = $( '<div>' )
				.addClass( 'infocolumn');

			$infoColumn.append( $( '<div>' )
				.addClass( 'row text-left message-desc' )
				.text( mw.msg( 'tux-editor-no-message-doc' ) )
			);

			// By default translateDocumentationLanguageCode is false.
			// It's defined as the MediaWiki global $wgTranslateDocumentationLanguageCode.
			translateDocumentationLanguageCode = mw.config.get( 'wgTranslateDocumentationLanguageCode' );
			if ( translateDocumentationLanguageCode ) {
				$infoColumn.append( $( '<div>' )
					.addClass( 'row text-left message-desc-control' )
					.append( $( '<a>' )
						.addClass( 'text-left message-desc-edit' )
						.attr( {
							href: ( new mw.Uri( window.location.href ) ).extend( {
								language: translateDocumentationLanguageCode
							} ).toString(), // FIXME: this link is not correct
							target: '_blank'
						} )
						.text( mw.msg( 'tux-editor-edit-desc' ) ) )
				);
			}

			$infoColumn.append( $( '<div>' )
				.addClass( 'row text-left tm-suggestions-title hide' )
				.text( mw.msg( 'tux-editor-suggestions-title' ) )
			);

			$infoColumn.append( $( '<div>' )
				.addClass( 'row text-left in-other-languages-title hide' )
				.text( mw.msg( 'tux-editor-in-other-languages' ) )
			);

			// The actual href is set when translationhelpers are loaded
			$infoColumn.append( $( '<div>' )
				.addClass( 'row text-left help hide' )
				.append(
					$( '<span>' )
						.text( mw.msg( 'tux-editor-need-more-help' ) ),
					$( '<a>' )
						.attr( 'href', '#' )
						.attr( 'target', '_blank' )
						.text( mw.msg( 'tux-editor-ask-help' ) )
				)
			);

			$infoColumnBlock.append( $infoColumn );
			return $infoColumnBlock;
		},

		show: function () {
			if ( !this.$editor ) {
				this.init();
			}

			// Hide all other editors in the page
			$( '.tux-message' ).each( function () {
				$( this ).data( 'translateeditor' ).hide();
			} );

			this.$messageItem.hide();
			this.$editor.show();

			// Focus the textarea.
			this.$editor.find( 'textarea' ).focus();
			this.shown = true;

			return false;
		},

		hide: function () {
			if ( this.$editor ) {
				this.$editor.hide();
			}

			this.$messageItem.show();
			this.shown = false;

			return false;
		},

		infoToggle: function ( toggleIcon ) {
			if ( this.expanded ) {
				this.contract( toggleIcon );
			} else {
				this.expand( toggleIcon );
			}
		},

		contract: function ( toggleIcon ) {
			// Change the icon image
			toggleIcon.removeClass( 'editor-contract' );
			toggleIcon.addClass( 'editor-expand' );

			this.$editor.find( '.infocolumn-block' ).show();
			this.$editor.find( '.editcolumn' )
				.removeClass( 'twelve' )
				.addClass( 'seven' );

			this.expanded = false;
		},

		expand: function ( toggleIcon ) {
			// Change the icon image
			toggleIcon.removeClass( 'editor-expand' );
			toggleIcon.addClass( 'editor-contract' );

			this.$editor.find( 'div.infocolumn-block' ).hide();
			this.$editor.find( 'div.editcolumn' )
				.removeClass( 'seven' )
				.addClass( 'twelve' );

			this.expanded = true;
		},

		getTranslationSuggestions: function () {
			// API call to get translation suggestions from other languages
			// callback should render suggestions to the editor's info column
			var queryParams,
				translateEditor = this,
				apiURL = mw.util.wikiScript( 'api' );

			queryParams = {
				action: 'translationaids',
				title: this.$editTrigger.data( 'title' ),
				format: 'json'
			};

			$.get( apiURL, queryParams ).done( function ( result ) {
				var translations,
					$messageDoc,
					documentation,
					readMore,
					$readMore = null;

				if ( !result.helpers ) {
					return false; // That is unlikely. but to be safe.
				}

				// Message documentation
				documentation = result.helpers.documentation;
				$messageDoc = translateEditor.$editor.find( '.message-desc' );
				$messageDoc.html( documentation.html );

				if ( documentation.value.length > 500 ) {

					readMore = function () {
						$messageDoc.css( {
							'height': '200px',
							'overflow': 'auto',
							'text-overflow': 'inherit'
						} );
						$readMore.remove();
					};

					$messageDoc.css( {
						'height': '100px',
						'overflow': 'hidden',
						'text-overflow': 'ellipsis'
					} );

					$readMore = $( '<span>' )
						.addClass( 'read-more column' )
						.text( mw.msg( 'tux-editor-message-desc-more' ) )
						.click( readMore );

					translateEditor.$editor.find( '.message-desc-control')
						.prepend(  $readMore );
					$messageDoc.on( 'hover', readMore );
				}

				// In other languages
				translations = result.helpers.inotherlanguages;
				$.each( translations, function ( index ) {
					var $otherLanguage,
						translationDir,
						translation = translations[index];

						translationDir = $.uls.data.getDir( translation.language );

						$otherLanguage = $( '<div>' )
							.addClass( 'row in-other-language' )
							.append(
								$( '<div>' )
									.addClass( 'nine columns' )
									.attr( {
										lang: translation.language,
										dir: translationDir
									} )
									.text( translation.value ),
								$( '<div>' )
									.addClass( 'three columns language text-right' )
									.attr( {
										lang: translation.language,
										dir: translationDir
									} )
									.text( $.uls.data.getAutonym( translation.language ) )
							);

						translateEditor.$editor.find( '.in-other-languages-title' )
							.removeClass( 'hide' )
							.after( $otherLanguage );
				} );

				// Translation memory suggestions
				translations = result.helpers.ttmserver;
				$.each( translations, function ( index ) {
					var translation,
						$translation;

					translation = translations[index];

					$translation = $( '<div>' )
					.addClass( 'row tm-suggestion' )
					.append(
						$( '<div>' )
							.addClass( 'row tm-suggestion-top' )
							.append(
								$( '<div>' )
									.addClass( 'nine columns' )
									.text( translation.target ),
								$( '<div>' )
									.addClass( 'three columns quality text-right' )
									.text( mw.msg( 'tux-editor-tm-match',
										Math.round( translation.quality * 100 ) ) )
							),
						$( '<div>' )
							.addClass( 'row tm-suggestion-bottom' )
							.append(
								$( '<a>' )
									.addClass( 'nine columns use-this-translation' )
									.text( mw.msg( 'tux-editor-use-this-translation' ) )
									.on( 'click', function () {
										translateEditor.$editor.find( 'textarea' )
											.val( translation.target );
									} )
							)
					);

					translateEditor.$editor.find( '.tm-suggestions-title' )
						.removeClass( 'hide' )
						.after( $translation );
				} );

				if ( result.helpers.support.url ) {
					translateEditor.$editor.find( '.help' )
						.find( 'a' ).attr( 'href', result.helpers.support.url )
						.end().removeClass( 'hide' );
				}

			} ).fail( function () {
				// what to do?
			} );
		},

		/**
		 * Attach event listeners
		 */
		listen: function () {
			var translateEditor = this;

			this.$editTrigger.find( '.tux-message-item' ).click( function () {
				translateEditor.show();
			} );
		}
	};

	/*
	 * translateeditor PLUGIN DEFINITION
	 */

	$.fn.translateeditor = function ( options ) {
		return this.each( function () {
			var $this = $( this ),
				data = $this.data( 'translateeditor' );

			if ( !data ) {
				$this.data( 'translateeditor',
					( data = new TranslateEditor( this, options ) )
				);
			}

			if ( typeof options === 'string' ) {
				data[options].call( $this );
			}
		} );
	};

	var delay = ( function () {
		var timer = 0;

		return function ( callback, milliseconds ) {
			clearTimeout( timer );
			timer = setTimeout( callback, milliseconds );
		};
	} () );

}( jQuery, mediaWiki ) );
