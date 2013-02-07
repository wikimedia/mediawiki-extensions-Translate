( function ( $, mw ) {
	'use strict';

	function TranslateEditor( element, options ) {
		this.$editTrigger = $( element );
		this.$editor = null;
		this.message = options.message;
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
				.addClass( 'row tux-message-editor hide' )
				.append(
					this.prepareEditorColumn(),
					this.prepareInfoColumn()
				);

			this.expanded = false;
			this.$editTrigger.append( this.$editor );

			this.showTranslationHelpers();
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

			this.$messageItem.addClass( 'translated' );
			this.dirty = false;

			$( '.tux-action-bar .tux-statsbar' ).trigger( 'change', [ 'translated', this.message.properties.state ] );
			// TODO: Update any other statsbar for the same group in the page.
		},

		/**
		 * Save the translation
		 */
		save: function () {
			var translateEditor = this,
				api = new mw.Api(),
				translation = translateEditor.$editor.find( '.editcolumn textarea' ).val();

			translateEditor.saving = true;

			// For responsiveness and efficiency,
			// immediately move to the next message.
			translateEditor.next();

			api.post( {
				action: 'edit',
				title: translateEditor.message.title,
				text: translation,
				token: mw.user.tokens.get( 'editToken' )
			} ).done( function ( response ) {
				if ( response.edit.result === 'Success' ) {
					translateEditor.markTranslated();

					// Update the translation
					translateEditor.message.translation = translation;
					translateEditor.$editTrigger.find( '.tux-list-translation' )
						.text( translation );
				} else {
					translateEditor.savingError( response.warning );
				}

				translateEditor.saving = false;

				// remove warnings if any.
				translateEditor.removeWarning( 'diff' );
				translateEditor.removeWarning( 'validation' );
			} ).fail( function ( errorCode, results ) {
				translateEditor.savingError( results.error.info );

				translateEditor.saving = false;
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
				title: translateEditor.message.title,
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
			if ( $( document ).height() -
				( $( window ).height() + window.pageYOffset + $next.height() ) > 0
			) {
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
				$editAreaBlock,
				$textArea,
				$controlButtonBlock,
				$editingButtonBlock,
				$pasteOriginalButton,
				$saveButton,
				$requestRight,
				$skipButton,
				$sourceString,
				$closeIcon,
				$layoutActions,
				$infoToggleIcon,
				$messageList,
				canTranslate = mw.translate.canTranslate();

			$editorColumn = $( '<div>' )
				.addClass( 'seven columns editcolumn' );

			$messageKeyLabel = $( '<div>' )
				.addClass( 'ten columns messagekey' )
				.text( this.message.title )
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
			sourceString = this.message.definition;
			$sourceString = $( '<span>' )
				.addClass( 'eleven column sourcemessage' )
				.attr( {
					lang: $messageList.data( 'sourcelangcode' ),
					dir: $messageList.data( 'sourcelangdir' )
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
				.addClass( 'tux-more-warnings hide' )
				.on( 'click', function () {
					var $this = $( this ),
						$moreWarnings = $warnings.children(),
						lastWarningIndex = $moreWarnings.length - 1;

					// If the warning list is not open only one warning is shown
					if ( $this.hasClass( 'open' ) ) {
						$moreWarnings.each( function ( index, element ) {
							// The first element must always be shown
							if ( index ) {
								$( element ).addClass( 'hide' );
							}
						} );

						$this
							.removeClass( 'open' )
							.text( mw.msg( 'tux-warnings-more', lastWarningIndex ) );
					} else {
						$moreWarnings.each( function ( index, element ) {
							// The first element must always be shown
							if ( index ) {
								$( element ).removeClass( 'hide' );
							}
						} );

						$this
							.addClass( 'open' )
							.text( mw.msg( 'tux-warnings-hide' ) );
					}
				} );

			$textArea = $( '<textarea>' )
				.attr( {
					placeholder: mw.msg( 'tux-editor-placeholder' ),
					lang: $messageList.data( 'targetlangcode' ),
					dir: $messageList.data( 'targetlangdir' )
				} )
				.on( 'input propertychange', function () {
					var $this = $( this );

					translateEditor.dirty = true;
					translateEditor.$editor.find( '.tux-editor-save-button' )
						.removeAttr( 'disabled' );

					// Expand the text area height as content grows
					while ( $this.outerHeight() <
						this.scrollHeight +
						parseFloat( $this.css( 'borderTopWidth' ) ) +
						parseFloat( $this.css( 'borderBottomWidth' ) )
					) {
						$this.height( $this.height() + parseFloat( $this.css( 'fontSize' ) ) );
					}
				} );

			$textArea.on( 'input propertychange', function () {
				var $textArea = $( this );

				delay( function () {
					var $saveButton = translateEditor.$editor.find( 'button.tux-editor-save-button' ),
						$pasteSourceButton = translateEditor.$editor.find( '.tux-editor-paste-original-button' );

					translateEditor.validateTranslation();
					$saveButton.text( mw.msg( 'tux-editor-save-button-label' ) );

					// When there is content in the editor
					if ( $.trim( $textArea.val() ) ) {
						$pasteSourceButton.addClass( 'hide' );
						$saveButton.prop( 'disabled', false );
					} else {
						$saveButton.prop( 'disabled', true );
						$pasteSourceButton.removeClass( 'hide' );
					}
				}, 1000 );
			} );

			if ( this.message.translation ) {
				$textArea.text( this.message.translation );
			}

			$warningsBlock = $( '<div>' )
				.addClass( 'tux-warnings-block' )
				.append( $moreWarningsTab, $warnings );

			$editAreaBlock = $( '<div>' )
				.addClass( 'row tux-editor-editarea-block' )
				.append( $( '<div>' )
					.addClass( 'editarea eleven columns' )
					.append( $warningsBlock, $textArea )
				);

			$editorColumn.append( $editAreaBlock );

			if ( canTranslate ) {
				$pasteOriginalButton = $( '<button>' )
					.addClass( 'tux-editor-paste-original-button' )
					.text( mw.msg( 'tux-editor-paste-original-button-label' ) )
					.on( 'click', function () {
						$textArea
							.focus()
							.trigger( 'input' )
							.val( sourceString );

						$pasteOriginalButton.addClass( 'hide' );
					} );

				if ( this.message.translation ) {
					$pasteOriginalButton.addClass( 'hide' );
				}

				$editingButtonBlock = $( '<div>' )
					.addClass( 'ten columns tux-editor-insert-buttons' )
					.append( $pasteOriginalButton );

				$requestRight = $( [] );

				$saveButton = $( '<button>' )
					.attr( {
						accesskey: 's',
						title: mw.util.tooltipAccessKeyPrefix + 's',
						disabled: true
					} )
					.addClass( 'blue button tux-editor-save-button' )
					.text( mw.msg( 'tux-editor-save-button-label' ) )
					.on( 'click', function () {
						translateEditor.save();
					} );

				// When the user opens an outdated translation, the main button should be enabled
				// and display a "confirm translation" label.
				if ( this.$messageItem.hasClass( 'fuzzy' ) ) {
					$saveButton
						.prop( 'disabled', false )
						.text( mw.msg( 'tux-editor-confirm-button-label' ) );
				}
			} else {
				$editingButtonBlock = $( [] );

				$requestRight = $( '<span>' )
					.addClass( 'tux-editor-request-right' )
					.text( mw.msg( 'translate-edit-nopermission' ) )
					.append( $( '<a>' )
						.text( mw.msg( 'translate-edit-askpermission' ) )
						.addClass( 'tux-editor-ask-permission' )
						.attr( {
							href: mw.util.wikiGetlink( mw.config.get( 'wgTranslatePermissionUrl' ) )
						} )
					);

				// Disable the text area if user has no translation rights.
				// Use readonly to allow copy-pasting (except for placeholders)
				$textArea.prop( 'readonly', true );

				$saveButton = $( [] );
			}

			$skipButton = $( '<button>' )
				.attr( {
					accesskey: 'd',
					title: mw.util.tooltipAccessKeyPrefix + 'd'
				} )
				.addClass( 'button tux-editor-skip-button' )
				.text( mw.msg( 'tux-editor-skip-button-label' ) )
				.on( 'click', function () {
					translateEditor.skip();
					translateEditor.next();
				} );

			$controlButtonBlock = $( '<div>' )
				.addClass( 'twelve columns tux-editor-control-buttons' )
				.append( $requestRight, $saveButton, $skipButton );

			$editorColumn.append( $( '<div>' )
				.addClass( 'row tux-editor-actions-block' )
				.append( $editingButtonBlock, $controlButtonBlock )
			);

			if ( canTranslate ) {
				$editorColumn.append( $( '<div>' )
					.addClass( 'row shortcutinfo' )
					.text( mw.msg( 'tux-editor-shortcut-info',
						$saveButton.attr( 'title' ).toUpperCase(),
						$skipButton.attr( 'title' ).toUpperCase() )
					)
				);
			}

			return $editorColumn;
		},

		/**
		 * Validate the current translation using the API
		 * and show the warnings if necessary.
		 */
		validateTranslation: function () {
			var translateEditor = this,
				url = new mw.Uri( mw.config.get( 'wgScript' ) ),
				$textArea = translateEditor.$editor.find( 'textarea' );

			// TODO: We need a better API for this
			url.extend( {
				title: 'Special:Translate/editpage',
				suggestions: 'checks',
				page: translateEditor.message.title,
				loadgroup: translateEditor.message.group
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
					.addClass( 'tux-warning-message hide ' + type )
					.html( warning )
				);

			warningCount = $warnings.find( '.tux-warning-message' ).length;

			$warnings.find( '.tux-warning-message:first' ).removeClass( 'hide' );

			if ( warningCount > 1 ) {
				$moreWarningsTab
					.text( mw.msg( 'tux-warnings-more', warningCount - 1 ) )
					.removeClass( 'hide' );
			} else {
				$moreWarningsTab.addClass( 'hide' );
			}
		},

		prepareInfoColumn: function () {
			var $messageDescEditor,
				$messageDescSaveButton, $messageDescCancelButton,
				$messageDescViewer,
				$infoColumn = $( '<div>' ).addClass( 'infocolumn' ),
				translateEditor = this;

			if ( mw.config.get( 'wgTranslateDocumentationLanguageCode' ) ) {
				if ( mw.translate.canTranslate() ) {
					$messageDescSaveButton = $( '<button>' )
						.addClass( 'blue button tux-editor-save-button' )
						.prop( 'disabled', true )
						.text( mw.msg( 'tux-editor-doc-editor-save' ) )
						.on( 'click', function () {
							translateEditor.saveDocumentation();
						} );

					$messageDescCancelButton = $( '<button>' )
						.addClass( 'button tux-editor-skip-button' )
						.text( mw.msg( 'tux-editor-doc-editor-cancel' ) )
						.on( 'click', function () {
							translateEditor.hideDocumentationEditor();
						} );

					$messageDescEditor = $( '<div>' )
						.addClass( 'row message-desc-editor hide' )
						.append(
							$( '<textarea>' )
								.attr( {
									placeholder: mw.msg( 'tux-editor-doc-editor-placeholder' )
								} )
								.on( 'input propertychange', function () {
									$messageDescSaveButton.prop( 'disabled', false );
								} ),
							$( '<div>' )
								.addClass( 'row' )
								.append(
									$messageDescSaveButton,
									$messageDescCancelButton
								)
						);
				}

				$messageDescViewer = $( '<div>' )
					.addClass( 'message-desc-viewer hide' )
					.append(
						$( '<div>' )
							.addClass( 'row message-desc' ),
						$( '<div>' )
							.addClass( 'row message-desc-control' )
							.append( $( '<a>' )
								.attr( {
									href: mw.translate.getDocumentationEditURL(
										this.message.title.replace( /\/[a-z\-]+$/, '' )
									),
									target: '_blank'
								} )
								.addClass( 'message-desc-edit' )
								.on( 'click', $.proxy( this.showDocumentationEditor, this ) )
							)
					);

				$infoColumn.append(
					$messageDescEditor,
					$messageDescViewer
				);
			}

			$infoColumn.append( $( '<div>' )
				.addClass( 'row tm-suggestions-title hide' )
				.text( mw.msg( 'tux-editor-suggestions-title' ) )
			);

			$infoColumn.append( $( '<div>' )
				.addClass( 'row in-other-languages-title hide' )
				.text( mw.msg( 'tux-editor-in-other-languages' ) )
			);

			// The actual href is set when translationhelpers are loaded
			$infoColumn.append( $( '<div>' )
				.addClass( 'row help hide' )
				.append(
					$( '<span>' )
						.text( mw.msg( 'tux-editor-need-more-help' ) ),
					$( '<a>' )
						.attr( {
							href: '#',
							target: '_blank'
						} )
						.text( mw.msg( 'tux-editor-ask-help' ) )
				)
			);

			return $( '<div>' )
				.addClass( 'five columns infocolumn-block' )
				.append(
					$( '<span>' ).addClass( 'caret' ),
					$infoColumn
				);
		},

		show: function () {
			var $next;

			if ( !this.$editor ) {
				this.init();
			}

			// Hide all other open editors in the page
			$( '.tux-message.open' ).each( function () {
				$( this ).data( 'translateeditor' ).hide();
			} );

			this.$messageItem.addClass( 'hide' );
			this.$editor
				.removeClass( 'hide' )
				.find( 'textarea' )
					.focus();

			this.shown = true;
			this.$editTrigger.addClass( 'open' );

			// don't waste time, get ready with next message
			$next = this.$editTrigger.next( '.tux-message' );

			if ( $next.length ) {
				$next.data( 'translateeditor' ).init();
			}

			return false;
		},

		hide: function () {
			if ( this.$editor ) {
				this.$editor.addClass( 'hide' );
			}

			this.$editTrigger.removeClass( 'open' );
			this.$messageItem.removeClass( 'hide' );
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
			toggleIcon
				.removeClass( 'editor-contract' )
				.addClass( 'editor-expand' );

			this.$editor.find( '.infocolumn-block' )
				.removeClass( 'hide' );
			this.$editor.find( '.editcolumn' )
				.removeClass( 'twelve' )
				.addClass( 'seven' );

			this.expanded = false;
		},

		expand: function ( toggleIcon ) {
			// Change the icon image
			toggleIcon
				.removeClass( 'editor-expand' )
				.addClass( 'editor-contract' );

			this.$editor.find( '.infocolumn-block' )
				.addClass( 'hide' );
			this.$editor.find( '.editcolumn' )
				.removeClass( 'seven' )
				.addClass( 'twelve' );

			this.expanded = true;
		},

		/**
		 * Adds the diff between old and current definitions to the view.
		 * @param {object} definitiondiff A definitiondiff object as returned by API.
		 */
		addDefinitionDiff: function ( definitiondiff ) {

			if ( !definitiondiff || definitiondiff.error ) {
				mw.log( 'Error loading translation diff ' + definitiondiff && definitiondiff.error );
				return;
			}

			// TODO add an option to hide diff
			this.addWarning(
				mw.msg( 'tux-editor-outdated-warning' ) +
					'<span class="show-diff-link">' +
					mw.message( 'tux-editor-outdated-warning-diff-link' ).escaped() +
					'</span>',
				'diff'
			);

			this.$editor.find( '.tux-warning .show-diff-link' )
				.on( 'click', function () {
					$( this ).parent().html( definitiondiff.html );
				} );
		},

		/**
		 * Attach event listeners
		 */
		listen: function () {
			var translateEditor = this;

			this.$editTrigger.find( '.tux-message-item' ).click( function () {
				translateEditor.show();

				return false;
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

	$.fn.translateeditor.Constructor = TranslateEditor;

	var delay = ( function () {
		var timer = 0;

		return function ( callback, milliseconds ) {
			clearTimeout( timer );
			timer = setTimeout( callback, milliseconds );
		};
	} () );
}( jQuery, mediaWiki ) );
