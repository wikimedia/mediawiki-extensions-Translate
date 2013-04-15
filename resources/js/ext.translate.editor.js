( function ( $, mw ) {
	'use strict';

	/**
	 * TranslateEditor Plugin
	 * Prepare the translation editor UI for a translation unit (message).
	 * This is mainly used with the messagetable plugin,
	 * but it is independent of messagetable.
	 * Example usage:
	 *
	 * $( 'div.messageRow' ).translateeditor( {
	 *	message: messageObject // Mandatory message object
	 * } );
	 *
	 * Assumptions: The jquery element to which translateeditor is applied will
	 * internally contain the editor's generated UI. So it is going to have the same width
	 * and inherited properies of the container.
	 * The container can mark the message item with class 'message'. This is not
	 * mandatory, but if found, when editor is opened the message item will be hidden
	 * and the editor will appear as if the message is replaced by the editor.
	 * See the UI of Translate messagetable for demo.
	 */
	function TranslateEditor( element, options ) {
		this.$editTrigger = $( element );
		this.$editor = null;
		this.options = options;
		this.message = this.options.message;
		this.$messageItem = this.$editTrigger.find( '.message' );
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
			// In case we have already created the editor earlier,
			// don't add a new one. The existing one may have unsaved
			// changes.
			if ( this.$editor ) {
				return;
			}

			this.$editor = $( '<div>' )
				.addClass( 'row tux-message-editor hide' )
				.append(
					this.prepareEditorColumn(),
					this.prepareInfoColumn()
				);

			this.expanded = false;
			this.$editTrigger.append( this.$editor );

			if ( this.message.properties && this.message.properties.status === 'fuzzy' ) {
				this.addWarning(
					mw.message( 'tux-editor-outdated-warning' ).escaped(),
					'diff'
				).append( $( '<span>' )
					// Hide initially.
					// Will be shown if there's a valid diff available.
					.addClass( 'show-diff-link hide' )
					.text( mw.msg( 'tux-editor-outdated-warning-diff-link' ) )
				);
			}

			this.showTranslationHelpers();
		},

		/**
		 * Mark the message as unsaved, can be resumed later
		 */
		markUnsaved: function () {
			this.$editTrigger.find( '.tux-list-status' )
				.children().addClass( 'hide' ).end()
				.append( $( '<span>' )
					.addClass( 'tux-status-unsaved' )
					.text( mw.msg( 'tux-status-unsaved' ) )
				);
		},

		/**
		 * Mark the message as no longer unsaved
		 */
		markUnunsaved: function () {
			this.$editTrigger.find( '.tux-list-status' )
			.find( '.tux-status-unsaved' )
			.remove()
			.end()
			.children().removeClass( 'hide' );
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

			if ( this.message.properties ) {
				$( '.tux-action-bar .tux-statsbar' ).trigger( 'change', [ 'translated', this.message.properties.state ] );
				// TODO: Update any other statsbar for the same group in the page.
			}
		},

		/**
		 * Save the translation
		 */
		save: function () {
			var translateEditor = this,
				api = new mw.Api(),
				translation = translateEditor.$editor.find( '.editcolumn textarea' ).val();

			translateEditor.saving = true;

			// beforeSave callback
			if ( translateEditor.options.beforeSave ) {
				translateEditor.options.beforeSave( translation );
			}

			// For responsiveness and efficiency,
			// immediately move to the next message.
			translateEditor.next();

			// Now the message definitely has a history,
			// so make sure the history menu item is shown
			translateEditor.$editor.find( '.message-tools-history' )
				.removeClass( 'hide' );

			api.postWithEditToken( {
				action: 'edit',
				title: translateEditor.message.title,
				text: translation
			}, function ( response ) {
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

				$( '.tux-editor-clear-translated' )
					.removeClass( 'hide' )
					.prop( 'disabled', false );

				// Save callback
				if ( translateEditor.options.onSave ) {
					translateEditor.options.onSave( translation );
				}

				mw.translate.dirty = false;
			}, function ( errorCode, results ) {
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
			// Only record skips of fuzzy or untranslated as hards
			// @TODO devise better algorithm
			if ( this.$messageItem.is( '.fuzzy, .untranslated' ) ) {
				// We can just ignore the result even if it fails
				new mw.Api().post( {
					action: 'hardmessages',
					title: this.message.title,
					token: mw.user.tokens.get( 'editToken' )
				} );
			}
		},

		/**
		 * Jump to the next translation editor row.
		 */
		next: function () {
			var $next = this.$editTrigger.next( '.tux-message' );

			// skip if the message is hidden. For eg: in a filter result.
			if ( $next.length && $next.hasClass( 'hide' ) ) {
				this.$editTrigger = $next;
				return this.next();
			}
			// If this is the last message, just hide it
			if ( !$next.length ) {
				this.hide();

				return;
			}

			$next.data( 'translateeditor' ).show();

			// Scroll the page a little bit up, slowly.
			if ( $( document ).height() -
				( $( window ).height() + window.pageYOffset + $next.height() ) > 0
			) {
				$( 'html, body' ).stop().animate( {
					scrollTop: $( '.tux-message-editor:visible' ).offset().top - 85
				}, 500 );
			}
		},

		createMessageTools: function () {
			var $historyItem, $translationsItem,
				wgScript = mw.config.get( 'wgScript' ),
				historyUri = new mw.Uri(),
				translationsUri = new mw.Uri();

			historyUri.path = wgScript;
			historyUri.query = {
				title: this.message.title,
				action: 'history'
			};
			$historyItem = $( '<li>' )
				.addClass( 'message-tools-history' +
					( ( this.message.translation === null ) ? ' hide' : '' )
				)
				.append( $( '<a>' )
				.attr( {
					href: historyUri.toString(),
					target: '_blank'
				} )
				.text( mw.msg( 'tux-editor-message-tools-history' ) )
			);

			translationsUri.path = wgScript;
			translationsUri.query = {
				title: 'Special:Translations',
				message: this.message.title
			};
			$translationsItem = $( '<li>' )
				.addClass( 'message-tools-translations' )
				.append( $( '<a>' )
				.attr( {
					href: translationsUri.toString(),
					target: '_blank'
				} )
				.text( mw.msg( 'tux-editor-message-tools-translations' ) )
			);

			return $( '<ul>' )
				.addClass( 'dropdown-menu tux-message-tools-menu hide' )
				.append( $historyItem, $translationsItem );
		},

		prepareEditorColumn: function () {
			var translateEditor = this,
				sourceString,
				originalTranslation,
				$editorColumn,
				$messageKeyLabel,
				$moreWarningsTab,
				$warnings,
				$warningsBlock,
				$editAreaBlock,
				$textarea,
				$controlButtonBlock,
				$editingButtonBlock,
				$pasteOriginalButton,
				$discardChangesButton = $( [] ),
				$saveButton,
				$requestRight,
				$skipButton,
				$cancelButton,
				$sourceString,
				$closeIcon,
				$layoutActions,
				$infoToggleIcon,
				$messageList,
				$messageTools = translateEditor.createMessageTools(),
				canTranslate = mw.translate.canTranslate();

			$editorColumn = $( '<div>' )
				.addClass( 'seven columns editcolumn' );

			$messageKeyLabel = $( '<div>' )
				.addClass( 'ten columns messagekey' )
				.text( this.message.title )
				.append(
					$( '<span>' ).addClass( 'caret' ),
					$messageTools
				)
				.on( 'click', function ( e ) {
					$messageTools.toggleClass( 'hide' );
					e.stopPropagation();
				} );

			$closeIcon = $( '<span>' )
				.addClass( 'one column close' )
				.attr( 'title', mw.msg( 'tux-editor-close-tooltip' ) )
				.on( 'click', function ( e ) {
					translateEditor.hide();
					e.stopPropagation();
				} );

			$infoToggleIcon = $( '<span>' )
				// Initially the editor column is contracted,
				// so show the expand button first
				.addClass( 'one column editor-info-toggle editor-expand' )
				.attr( 'title', mw.msg( 'tux-editor-expand-tooltip' ) )
				.on( 'click', function ( e ) {
					translateEditor.infoToggle( $( this ) );
					e.stopPropagation();
				} );

			$layoutActions = $( '<div>' )
				.addClass( 'two columns layout-actions' )
				.append( $closeIcon, $infoToggleIcon );

			$editorColumn.append( $( '<div>' )
				.addClass( 'row' )
				.append( $messageKeyLabel, $layoutActions )
			);

			$messageList = $( '.tux-messagelist' );
			originalTranslation = this.message.translation;
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

			$textarea = $( '<textarea>' )
				.attr( {
					lang: $messageList.data( 'targetlangcode' ),
					dir: $messageList.data( 'targetlangdir' )
				} )
				.val( this.message.translation || '' );

			if ( mw.translate.isPlaceholderSupported( $textarea ) ) {
				$textarea.prop( 'placeholder', mw.msg( 'tux-editor-placeholder' ) );
			}

			$textarea.on( 'textchange', function () {
				var $textarea = $( this ),
					$saveButton = translateEditor.$editor.find( '.tux-editor-save-button' ),
					$pasteSourceButton = translateEditor.$editor.find( '.tux-editor-paste-original-button' ),
					original = translateEditor.message.translation || '',
					current = $textarea.val() || '';

				if ( original !== '' ) {
					$discardChangesButton.removeClass( 'hide' );
				}

				// Expand the text area height as content grows
				while ( $textarea.outerHeight() <
					this.scrollHeight +
					parseFloat( $textarea.css( 'borderTopWidth' ) ) +
					parseFloat( $textarea.css( 'borderBottomWidth' ) )
				) {
					$textarea.height( $textarea.height() + parseFloat( $textarea.css( 'fontSize' ) ) );
				}

				/* Avoid Unsaved marking when translated message is not changed in content.
				 * - translateEditor.dirty: internal book keeping
				 * - mw.translate.dirty: "you have unchanged edits" warning
				 */
				if ( original === current ) {
					translateEditor.dirty = false;
					mw.translate.dirty = false;
					translateEditor.markUnunsaved();
				} else {
					translateEditor.dirty = true;
					mw.translate.dirty = true;
				}
				adjustSize( $textarea );

				$saveButton.text( mw.msg( 'tux-editor-save-button-label' ) );
				// When there is content in the editor enable the button.
				// But do not enable when some saving is not finished yet.
				if ( $.trim( current ) && !translateEditor.saving ) {
					$pasteSourceButton.addClass( 'hide' );
					$saveButton.prop( 'disabled', false );
				} else {
					$saveButton.prop( 'disabled', true );
					$pasteSourceButton.removeClass( 'hide' );
				}

				delay( function () {
					translateEditor.validateTranslation();
				}, 500 );
			} );

			$warningsBlock = $( '<div>' )
				.addClass( 'tux-warnings-block' )
				.append( $moreWarningsTab, $warnings );

			$editAreaBlock = $( '<div>' )
				.addClass( 'row tux-editor-editarea-block' )
				.append( $( '<div>' )
					.addClass( 'editarea eleven columns' )
					.append( $warningsBlock, $textarea )
				);

			$editorColumn.append( $editAreaBlock );

			if ( canTranslate ) {
				$pasteOriginalButton = $( '<button>' )
					.addClass( 'tux-editor-paste-original-button' )
					.text( mw.msg( 'tux-editor-paste-original-button-label' ) )
					.on( 'click', function () {
						$textarea
							.focus()
							.val( sourceString )
							.trigger( 'input' );

						$pasteOriginalButton.addClass( 'hide' );
					} );

				if ( originalTranslation !== null ) {
					$discardChangesButton = $( '<button>' )
						.addClass( 'tux-editor-discard-changes-button hide' ) // Initially hidden
						.text( mw.msg( 'tux-editor-discard-changes-button-label' ) )
						.on( 'click', function () {
							// Restore the translation
							$textarea
								.focus()
								.val( originalTranslation )
								.trigger( 'input' );

							// and go back to hiding.
							$discardChangesButton.addClass( 'hide' );

							// There's nothing new to save
							$saveButton.prop( 'disabled', true );

							translateEditor.markUnunsaved();
						} );
				}

				if ( this.message.translation ) {
					$pasteOriginalButton.addClass( 'hide' );
				}

				$editingButtonBlock = $( '<div>' )
					.addClass( 'ten columns tux-editor-insert-buttons' )
					.append(
						$pasteOriginalButton,
						$discardChangesButton
					);

				$requestRight = $( [] );

				$saveButton = $( '<button>' )
					.prop( 'disabled', true )
					.addClass( 'blue button tux-editor-save-button' )
					.text( mw.msg( 'tux-editor-save-button-label' ) )
					.on( 'click', function ( e ) {
						translateEditor.save();
						e.stopPropagation();
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
				$textarea.prop( 'readonly', true );

				$saveButton = $( [] );
			}

			$skipButton = $( '<button>' )
				.addClass( 'button tux-editor-skip-button' )
				.text( mw.msg( 'tux-editor-skip-button-label' ) )
				.on( 'click', function ( e ) {
					translateEditor.skip();
					translateEditor.next();
					e.stopPropagation();
				} );

			$cancelButton = $( '<button>' )
				.addClass( 'button tux-editor-cancel-button' )
				.text( mw.msg( 'tux-editor-cancel-button-label' ) )
				.on( 'click', function ( e ) {
					translateEditor.skip();
					translateEditor.hide();
					e.stopPropagation();
				} );

			$controlButtonBlock = $( '<div>' )
				.addClass( 'twelve columns tux-editor-control-buttons' )
				.append( $requestRight, $saveButton, $skipButton, $cancelButton );

			$editorColumn.append( $( '<div>' )
				.addClass( 'row tux-editor-actions-block' )
				.append( $editingButtonBlock, $controlButtonBlock )
			);

			if ( canTranslate ) {
				$editorColumn.append( $( '<div>' )
					.addClass( 'row shortcutinfo' )
					.text( mw.msg( 'tux-editor-shortcut-info',
						( mw.util.tooltipAccessKeyPrefix + 's' ).toUpperCase(),
						( mw.util.tooltipAccessKeyPrefix + 'd' ).toUpperCase() )
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
				$textarea = translateEditor.$editor.find( '.editcolumn textarea' );

			// TODO: We need a better API for this
			url.extend( {
				title: 'Special:Translate/editpage',
				suggestions: 'checks',
				page: translateEditor.message.title,
				loadgroup: translateEditor.message.group
			} );

			$.post( url.toString(), {
				translation: $textarea.val()
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
			var $tuxWarning = this.$editor.find( '.tux-warning' );

			$tuxWarning.find( '.' + type ).remove();
			if ( !$tuxWarning.children().length ) {
				this.$editor.find( '.tux-more-warnings' ).addClass( 'hide' );
			}
		},

		/**
		 * Displays the supplied warning from the bottom up near the translation edit area.
		 *
		 * @param {String} warning used as html for the warning display
		 * @param {String} type used to group the warnings.eg: validation, diff, error
		 * @return {jQuery} the new warning element
		 */
		addWarning: function ( warning, type ) {
			var warningCount,
				$warnings = this.$editor.find( '.tux-warning' ),
				$moreWarningsTab = this.$editor.find( '.tux-more-warnings' ),
				$newWarning = $( '<div>' )
					.addClass( 'tux-warning-message hide ' + type )
					.html( warning );

			$warnings
				.removeClass( 'hide' )
				.append( $newWarning );

			warningCount = $warnings.find( '.tux-warning-message' ).length;

			$warnings.find( '.tux-warning-message:first' ).removeClass( 'hide' );

			if ( warningCount > 1 ) {
				$moreWarningsTab
					.text( mw.msg( 'tux-warnings-more', warningCount - 1 ) )
					.removeClass( 'hide' );
			} else {
				$moreWarningsTab.addClass( 'hide' );
			}

			return $newWarning;
		},

		prepareInfoColumn: function () {
			var $messageDescEditor, $messageDescTextarea,
				$messageDescSaveButton, $messageDescCancelButton,
				$messageDescViewer,
				$infoColumn = $( '<div>' ).addClass( 'infocolumn' ),
				translateEditor = this;

			$infoColumn.append( $( '<div>' )
				.addClass( 'row loading' )
				.text( mw.msg( 'tux-editor-loading' ) )
			);

			if ( mw.config.get( 'wgTranslateDocumentationLanguageCode' ) ) {
				$messageDescSaveButton = $( '<button>' )
					.addClass( 'blue button tux-editor-savedoc-button' )
					.prop( 'disabled', true )
					.text( mw.msg( 'tux-editor-doc-editor-save' ) )
					.on( 'click', function () {
						translateEditor.saveDocumentation();
					} );

				$messageDescCancelButton = $( '<button>' )
					.addClass( 'button tux-editor-skipdoc-button' )
					.text( mw.msg( 'tux-editor-doc-editor-cancel' ) )
					.on( 'click', function () {
						translateEditor.hideDocumentationEditor();
					} );

				$messageDescTextarea = $( '<textarea>' )
					.on( 'textchange', function () {
						$messageDescSaveButton.prop( 'disabled', false );
					} );

				if ( mw.translate.isPlaceholderSupported( $messageDescTextarea ) ) {
					$messageDescTextarea.prop( 'placeholder', mw.msg( 'tux-editor-doc-editor-placeholder' ) );
				}

				$messageDescEditor = $( '<div>' )
					.addClass( 'row message-desc-editor hide' )
					.append(
						$messageDescTextarea,
						$( '<div>' )
							.addClass( 'row' )
							.append(
								$messageDescSaveButton,
								$messageDescCancelButton
							)
					);

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

				if ( !mw.translate.canTranslate() ) {
					$messageDescViewer.find( '.message-desc-control' ).addClass( 'hide' );
				}

				$infoColumn.append(
					$messageDescEditor,
					$messageDescViewer
				);
			}

			$infoColumn.append( $( '<div>' )
				.addClass( 'row uneditable-documentation hide' )
			);

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
			var $next, $textarea;

			if ( !this.$editor ) {
				this.init();
			}

			$textarea = this.$editor.find( '.editcolumn textarea' );
			// Hide all other open editors in the page
			$( '.tux-message.open' ).each( function () {
				$( this ).data( 'translateeditor' ).hide();
			} );

			// The access keys need to be shifted to the editor currently active
			$( '.tux-editor-save-button, .tux-editor-save-button' ).removeAttr( 'accesskey' );
			this.$editor.find( '.tux-editor-save-button' ).attr( 'accesskey', 's' );
			this.$editor.find( '.tux-editor-skip-button' ).attr( 'accesskey', 'd' );
			// @todo access key for the cancel button

			this.$messageItem.addClass( 'hide' );
			this.$editor.removeClass( 'hide' );
			$textarea.focus();
			adjustSize( $textarea );

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
			// If the user has made changes, make sure they are either
			// in process of being saved or highlighted as unsaved.
			if ( this.dirty ) {
				if ( this.saving ) {
					this.markSaving();
				} else {
					this.markUnsaved();
				}
			}

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
				.addClass( 'editor-expand' )
				.attr( 'title', mw.msg( 'tux-editor-expand-tooltip' ) );

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
				.addClass( 'editor-contract' )
				.attr( 'title', mw.msg( 'tux-editor-collapse-tooltip' ) );

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

			// Load the diff styles
			mw.loader.load( 'mediawiki.action.history.diff', undefined, true );

			// TODO add an option to hide diff
			this.$editor.find( '.tux-warning .show-diff-link' )
				.removeClass( 'hide' )
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

	/*
	 * Expand the text area height as content grows
	 */
	function adjustSize( $textarea ) {
		while ( $textarea.outerHeight() <
			( $textarea.prop( 'scrollHeight' ) +
			parseFloat( $textarea.css( 'borderTopWidth' ) ) +
			parseFloat( $textarea.css( 'borderBottomWidth' ) ) )
		) {
			$textarea.height( $textarea.height() +
				parseFloat( $textarea.css( 'fontSize' ) ) +
				parseFloat( $textarea.css( 'paddingBottom' ) ) );
		}
	}

	var delay = ( function () {
		var timer = 0;

		return function ( callback, milliseconds ) {
			clearTimeout( timer );
			timer = setTimeout( callback, milliseconds );
		};
	} () );
}( jQuery, mediaWiki ) );
