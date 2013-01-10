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

					// remove warnings if any.
					translateEditor.removeWarning( 'diff' );
					translateEditor.removeWarning( 'validation' );
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
		 * @param {string} error Strings of warnings to display.
		 */
		savingError: function ( error ) {
			this.addWarning(
				mw.msg( 'tux-editor-save-failed', error ),
				'translation-saving'
			);

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
				this.hide();
				return;
			}

			$next.data( 'translateeditor' ).show();
			// scroll the page a little bit up, slowly.
			if ( $( document ).height()
				- ( $( window ).height() + window.pageYOffset + $next.height() ) > 0 ) {
				$( 'html, body' ).stop().animate( {
					scrollTop: $( '.tux-message-editor:visible' ).offset().top - 55
				}, 500 );
			}
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
				.append( $( '<span>' ).addClass( 'caret' ) );

			$closeIcon = $( '<span>' )
				.addClass( 'one column close' )
				.on( 'click', function () {
					translateEditor.hide();
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
				.addClass( 'tux-warning hide' );

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
				.on( 'input propertychange', function () {
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
					.on( 'click', function () {
						translateEditor.save();
					} );
			} else {
				$requestRight = $( '<span>' )
					.text( mw.msg( 'translate-edit-nopermission' ) )
					.addClass( 'tux-editor-request-right' )
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
				.on( 'click', function () {
					translateEditor.skip();
					translateEditor.next();
				} );

			$buttonBlock = $( '<div>' )
				.addClass( 'twelve columns' )
				.append( $requestRight, $saveButton, $skipButton );

			$editorColumn.append( $( '<div>' )
				.addClass( 'row' )
				.append( $buttonBlock )
			);

			if ( mw.translate.canTranslate() ) {
				$editorColumn.append( $( '<div>' )
					.addClass( 'row text-left shortcutinfo' )
					.text( mw.msg( 'tux-editor-shortcut-info',
						$saveButton.attr( 'title' ).toUpperCase(),
						$skipButton.attr( 'title' ).toUpperCase() )
					)
				);
			}

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

				$.post( url.toString(), {
					translation: $textArea.val()
				}, function ( data ) {
					var warningIndex,
						warnings = jQuery.parseJSON( data );

					if ( !warnings ) {
						return;
					}

					translateEditor.removeWarning( 'validation' );
					for ( warningIndex = 0; warningIndex < warnings.length; warningIndex++ ) {
						translateEditor.addWarning( warnings[warningIndex], 'validation' );
					}
				} );
			}, 1000 );
		},

		/**
		 * Remove all warning of given type
		 * @param type
		 */
		removeWarning: function ( type ) {
			this.$editor.find( '.tux-warning' ).find( '.' + type ).remove();
		},

		/**
		 * Displays the supplied warning from the bottom up near the translation edit area.
		 *
		 * @param {String} warning used as html for the warning display
		 * @param {String} type used to group the warnings.eg: validation, diff, error
		 */
		addWarning: function ( warning, type ) {
			var warningCount,
				$warnings = this.$editor.find( '.tux-warning' ),
				$moreWarningsTab = this.$editor.find( '.tux-more-warnings' );

			$warnings
				.removeClass( 'hide' )
				.append( $( '<div>' )
					.addClass( 'tux-warning-message hide' )
					.addClass( type )
					.html( warning )
			);

			warningCount = $warnings.find( '.tux-warning-message' ).length;

			$warnings.find( '.tux-warning-message:first' ).removeClass( 'hide' );

			if ( warningCount > 1 ) {
				$moreWarningsTab
					.text( mw.msg( 'tux-warnings-more', warningCount - 1 ) )
					.show();
			} else {
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
				.addClass( 'infocolumn' );

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
							href: mw.translate.getDocumentationEditURL( this.$editTrigger.data( 'title' )
								.replace( /\/[a-z\-]+$/, '' ) ),
							target: '_blank'
					} )
					.text( mw.msg( 'tux-editor-add-desc' ) ) )
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

			// Hide all other open editors in the page
			$( '.tux-message.open' ).each( function () {
				$( this ).data( 'translateeditor' ).hide();
			} );

			this.$messageItem.hide();
			this.$editor.show();

			// Focus the textarea.
			this.$editor.find( 'textarea' ).focus();
			this.shown = true;
			this.$editTrigger.addClass( 'open' );
			return false;
		},

		hide: function () {
			if ( this.$editor ) {
				this.$editor.hide();
			}
			this.$editTrigger.removeClass( 'open' );
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
					expand,
					readMore,
					$readMore = null,
					contentLanguageDir;

				// TODO This returns an error for 'Page display title' in translatable pages.
				// Something smarter must be done with it.
				if ( !result.helpers ) {
					return false; // That is unlikely. but to be safe.
				}

				// Message documentation
				documentation = result.helpers.documentation;

				// Display the documentation only if it's not empty
				if ( documentation.value ) {
					$messageDoc = translateEditor.$editor.find( '.message-desc' );

					contentLanguageDir = $.uls.data.getDir( documentation.language );
					// Show the documentation and set appropriate
					// lang and dir attributes.
					// The message documentation is assumed to be written
					// in the content language of the wiki.
					$messageDoc
						.attr( {
							lang: documentation.language,
							dir: contentLanguageDir
						} )
						.addClass( contentLanguageDir ) // hack
						.html( documentation.html );

					translateEditor.$editor.find( '.message-desc-edit' )
						.text( mw.msg( 'tux-editor-edit-desc' ) );

					if ( documentation.value.length > 500 ) {
						expand = function () {
							$messageDoc.removeClass( 'compact' );
							$readMore.text( mw.msg( 'tux-editor-message-desc-less' ) );
						};

						readMore = function () {
							if ( $messageDoc.hasClass( 'compact' ) ) {
								expand();
							} else {
								$messageDoc.addClass( 'compact' );
								$readMore.text( mw.msg( 'tux-editor-message-desc-more' ) );
							}
						};

						$readMore = $( '<span>' )
							.addClass( 'read-more column' )
							.text( mw.msg( 'tux-editor-message-desc-more' ) )
							.click( readMore );

						translateEditor.$editor.find( '.message-desc-control' )
							.prepend( $readMore );

						$messageDoc.addClass('long compact').on( 'hover', expand );
					}
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
											.val( translation.target )
											.trigger( 'input' );
									} )
						)
					);

					translateEditor.$editor.find( '.tm-suggestions-title' )
						.removeClass( 'hide' )
						.after( $translation );
				} );

				// Support URL
				if ( result.helpers.support.url ) {
					translateEditor.$editor.find( '.help' )
						.find( 'a' ).attr( 'href', result.helpers.support.url )
						.end().removeClass( 'hide' );
				}

				// Diff for outdated message
				if ( result.helpers.definitiondiff && !result.helpers.definitiondiff.error ) {
					translateEditor.addWarning( mw.msg( 'tux-editor-outdated-warning' )
						+ '<span class="show-diff-link">'
						+ mw.message( 'tux-editor-outdated-warning-diff-link' ).escaped()
						+ '</span>', 'diff' );

					translateEditor.$editor.find( '.tux-warning .show-diff-link' )
						.on( 'click', function () {
							$( this ).parent().html( result.helpers.definitiondiff.html );
						} );
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
