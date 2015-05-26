( function ( $, mw ) {
	'use strict';

	/**
	 * TranslateEditor Plugin
	 * Prepare the translation editor UI for a translation unit (message).
	 * This is mainly used with the messagetable plugin,
	 * but it is independent of messagetable.
	 * Example usage:
	 *
	 *     $( 'div.messageRow' ).translateeditor( {
	 *         message: messageObject // Mandatory message object
	 *     } );
	 *
	 * Assumptions: The jquery element to which translateeditor is applied will
	 * internally contain the editor's generated UI. So it is going to have the same width
	 * and inherited properies of the container.
	 * The container can mark the message item with class 'message'. This is not
	 * mandatory, but if found, when editor is opened the message item will be hidden
	 * and the editor will appear as if the message is replaced by the editor.
	 * See the UI of Translate messagetable for demo.
	 * @param {HTMLElement} element
	 * @param {Object} options
	 * @param {Function} [options.beforeSave] Callback to call when translation is going to be saved.
	 * @param {Function} [options.onReady] Callback to call when the editor is ready.
	 * @param {Function} [options.onSave] Callback to call when translation has been saved.
	 * @param {Function} [options.onSkip] Callback to call when a message is skipped.
	 * @param {Object} options.message Object as returned by messagecollection api.
	 * @param {TranslationApiStorage} [options.storage]
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
		this.storage = this.options.storage || new mw.translate.TranslationApiStorage();
		this.canDelete = mw.translate.canDelete();
		this.delayValidation = delayer();
		this.delayResize = delayer();
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

			this.render();
			// onReady callback
			if ( this.options.onReady ) {
				this.options.onReady.call( this );
			}
		},

		/**
		 * Render the editor UI
		 */
		render: function () {
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
					'fuzzy'
				);
			}

			this.showTranslationHelpers();
		},

		/**
		 * Mark the message as unsaved because of edits, can be resumed later
		 * @param {string} [highlightClass] Class for background highlighting
		 */
		markUnsaved: function ( highlightClass ) {
			var $tuxListStatus = this.$editTrigger.find( '.tux-list-status' );

			highlightClass = highlightClass || 'tux-highlight';

			$tuxListStatus.children( '.tux-status-unsaved' ).remove();
			$tuxListStatus.children().addClass( 'hide' );
			$( '<span>' )
				.addClass( 'tux-status-unsaved ' + highlightClass )
				.text( mw.msg( 'tux-status-unsaved' ) )
				.appendTo( $tuxListStatus );
		},

		/**
		 * Mark the message as unsaved because of saving failure.
		 */
		markUnsavedFailure: function () {
			this.markUnsaved( 'tux-warning' );
		},

		/**
		 * Mark the message as no longer unsaved
		 */
		markUnunsaved: function () {
			var $tuxListStatus = this.$editTrigger.find( '.tux-list-status' );

			$tuxListStatus.children( '.tux-status-unsaved' ).remove();
			$tuxListStatus.children().removeClass( 'hide' );

			this.dirty = false;
			mw.translate.dirty = false;
		},

		/**
		 * Mark the message as being saved
		 */
		markSaving: function () {
			var $tuxListStatus = this.$editTrigger.find( '.tux-list-status' );

			// Disable the save button
			this.$editor.find( '.tux-editor-save-button' )
				.prop( 'disabled', true );

			// Add a "Saving" indicator
			$tuxListStatus.empty();
			$( '<span>' )
				.addClass( 'tux-status-unsaved' )
				.text( mw.msg( 'tux-status-saving' ) )
				.appendTo( $tuxListStatus );
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

			this.$messageItem
				.addClass( 'translated' )
				.removeClass( 'untranslated' );

			this.dirty = false;

			if ( this.message.properties ) {
				$( '.tux-action-bar .tux-statsbar' ).trigger(
					'change',
					[ 'translated', this.message.properties.status ]
				);
				// TODO: Update any other statsbar for the same group in the page.
			}
		},

		/**
		 * Save the translation
		 */
		save: function () {
			var translation,
				translateEditor = this;

			mw.translateHooks.run( 'beforeSubmit', translateEditor.$editor );
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

			// Show the delete menu item if the user can delete
			if ( this.canDelete ) {
				translateEditor.$editor.find( '.message-tools-delete' )
					.removeClass( 'hide' );
			}

			this.storage.save(
				translateEditor.message.title,
				translation
			).done( function () {
				// Update the translation
				translateEditor.message.translation = translation;
				translateEditor.onSaveSuccess();
			} ).fail( function ( errorCode, response ) {
				translateEditor.onSaveFail( response.error && response.error.info || 'Unknown error' );
				if ( errorCode === 'assertuserfailed' ) {
					window.alert( mw.msg( 'tux-session-expired' ) );
				}
			} );
		},

		/**
		 * Success handler for the translation saving.
		 */
		onSaveSuccess: function () {
			this.markTranslated();
			this.$editTrigger.find( '.tux-list-translation' )
				.text( this.message.translation );
			this.saving = false;

			// remove warnings if any.
			this.removeWarning( 'diff' );
			this.removeWarning( 'fuzzy' );
			this.removeWarning( 'validation' );

			$( '.tux-editor-clear-translated' )
				.removeClass( 'hide' )
				.prop( 'disabled', false );

			// Save callback
			if ( this.options.onSave ) {
				this.options.onSave( this.message.translation );
			}

			mw.translate.dirty = false;
			mw.translateHooks.run( 'afterSubmit', this.$editor );

			if ( mw.track ) {
				mw.track( 'ext.translate.event.translation', this.message );
			}
		},

		/**
		 * Marks that there was a problem saving a translation.
		 * @param {string} error Strings of warnings to display.
		 */
		onSaveFail: function ( error ) {
			this.addWarning(
				mw.msg( 'tux-editor-save-failed', error ),
				'translation-saving'
			);
			this.saving = false;
			this.markUnsavedFailure();
		},

		/**
		 * Skip the current message.
		 * Record it to mark as hard.
		 */
		skip: function () {
			// @TODO devise good algorithm for identifying hard to translate messages
		},

		/**
		 * Jump to the next translation editor row.
		 */
		next: function () {
			var $next = this.$editTrigger.next( '.tux-message' );

			// Skip if the message is hidden. For example in a filter result.
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

		/**
		 * Creates a menu element for the message tools.
		 *
		 * @param {string} className Used as the element's CSS class
		 * @param {Object} query Used as the query in the mw.Uri object
		 * @param {string} message The message of the label of the menu item
		 * @return {jQuery} The new menu item element
		 */
		createMessageToolsItem: function ( className, query, message ) {
			var uri = new mw.Uri();

			uri.path = mw.config.get( 'wgScript' );
			uri.query = query;

			return $( '<li>' )
				.addClass( className )
				.append( $( '<a>' )
					.attr( {
						href: uri.toString(),
						target: '_blank'
					} )
					.text( mw.msg( message ) )
				);
		},

		/**
		 * Creates an element with a dropdown menu including
		 * tools for the translators.
		 *
		 * @return {jQuery} The new message tools menu element
		 */
		createMessageTools: function () {
			var $historyItem, $deleteItem, $translationsItem;

			$historyItem = this.createMessageToolsItem(
				'message-tools-history',
				{
					title: this.message.title,
					action: 'history'
				},
				'tux-editor-message-tools-history'
			);

			$deleteItem = this.createMessageToolsItem(
				'message-tools-delete',
				{
					title: this.message.title,
					action: 'delete'
				},
				'tux-editor-message-tools-delete'
			);

			// Hide these links if the translation doesn't actually exist.
			// They will be shown when a translation will be created.
			if ( this.message.translation === null ) {
				$historyItem.addClass( 'hide' );
				$deleteItem.addClass( 'hide' );
			} else if ( !this.canDelete ) {
				$deleteItem.addClass( 'hide' );
			}

			// A link to Special:Translations,
			// with translations of this message to other languages
			$translationsItem = this.createMessageToolsItem(
				'message-tools-translations',
				{
					title: 'Special:Translations',
					message: this.message.title
				},
				'tux-editor-message-tools-translations'
			);

			return $( '<ul>' )
				.addClass( 'tux-dropdown-menu tux-message-tools-menu hide' )
				.append( $historyItem, $deleteItem, $translationsItem );
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
				targetLangAttrib, targetLangDir, targetLangCode,
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

			targetLangCode = $messageList.data( 'targetlangcode' );
			if ( targetLangCode === mw.config.get( 'wgTranslateDocumentationLanguageCode' ) ) {
				targetLangAttrib = mw.config.get( 'wgContentLanguage' );
				targetLangDir = $.uls.data.getDir( targetLangAttrib );
			} else {
				targetLangAttrib = targetLangCode;
				targetLangDir = $messageList.data( 'targetlangdir' );
			}

			$textarea = $( '<textarea>' )
				.addClass( 'tux-textarea-translation' )
				.attr( {
					lang: targetLangAttrib,
					dir: targetLangDir
				} )
				.val( this.message.translation || '' );

			if ( mw.translate.isPlaceholderSupported( $textarea ) ) {
				$textarea.prop( 'placeholder', mw.msg( 'tux-editor-placeholder' ) );
			}

			// The extra newlines is supposed to leave enough space for the
			// insertion buttons. Seems to work as long as all the buttons
			// are only in one line.
			$textarea.autosize( {append: '\n\n\n' } );

			// Shortcuts for various insertable things
			$textarea.on( 'keyup keydown', function ( e ) {
				var index, info, direction;

				if ( e.type === 'keydown' && e.altKey === true ) {
					// Up and down arrows
					if ( e.keyCode === 38 || e.keyCode === 40 ) {
						direction = e.keyCode === 40 ? 1 : -1;
						info = translateEditor.$editor.find( '.infocolumn' );
						info.scrollTop( info.scrollTop() + 100 * direction );
						translateEditor.showShortcuts();
					}
				}

				// Move zero to last
				index = e.keyCode - 49;
				if ( index === -1 ) {
					index = 9;
				}

				// 0..9 ~ 48..57
				if (
					e.type === 'keydown' &&
					e.altKey === true &&
					e.ctrlKey === false &&
					e.shiftKey === false &&
					index >= 0 && index < 10
				) {
					e.preventDefault();
					e.stopPropagation();
					translateEditor.$editor.find( '.shortcut-activated:visible' ).eq( index ).trigger( 'click' );
					// Update numbers and locations after trigger should be completed
					window.setTimeout( function() { translateEditor.showShortcuts(); }, 100 );
				}

				if ( e.which === 18 && e.type === 'keyup' ) {
					translateEditor.hideShortcuts();
				} else if ( e.which === 18 && e.type === 'keydown' ) {
					translateEditor.showShortcuts();
				}
			} );

			$textarea.on( 'textchange', function () {
				var $textarea = $( this ),
					$saveButton = translateEditor.$editor.find( '.tux-editor-save-button' ),
					$pasteSourceButton = translateEditor.$editor.find( '.tux-editor-paste-original-button' ),
					original = translateEditor.message.translation || '',
					current = $textarea.val() || '';

				if ( original !== '' ) {
					$discardChangesButton.removeClass( 'hide' );
				}

				/* Avoid Unsaved marking when translated message is not changed in content.
				 * - translateEditor.dirty: internal book keeping
				 * - mw.translate.dirty: "you have unchanged edits" warning
				 */
				if ( original === current ) {
					translateEditor.markUnunsaved();
				} else {
					translateEditor.dirty = true;
					mw.translate.dirty = true;
				}

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

				translateEditor.delayValidation( function () {
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
								.val( originalTranslation );

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
							href: mw.util.getUrl(
								mw.config.get( 'wgTranslateUseSandbox' )
								? 'Special:TranslationStash'
								: mw.config.get( 'wgTranslatePermissionUrl' )
							)
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

					if ( translateEditor.options.onSkip ) {
						translateEditor.options.onSkip.call( translateEditor );
					}

					e.stopPropagation();
				} );

			// This appears instead of "Skip" on the last message on the page
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
						( mw.util.tooltipAccessKeyPrefix + 'd' ).toUpperCase(),
						'ALT' )
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
				$textarea = translateEditor.$editor.find( '.tux-textarea-translation' );

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
					warnings = JSON.parse( data );

				translateEditor.removeWarning( 'validation' );
				if ( !warnings || !warnings.length ) {
					return;
				}

				// Remove useless fuzzy warning if we have more details
				translateEditor.removeWarning( 'fuzzy' );

				// Disable confirm translation button, since fuzzy translations
				// cannot be confirmed. The check for dirty state can be removed
				// to prevent translations with warnings.
				if ( !translateEditor.dirty ) {
					translateEditor.$editor.find( '.tux-editor-save-button' )
						.prop( 'disabled', true );
				}

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
						translateEditor.saveDocumentation()
							.done( function () {
								var $descEditLink = $messageDescViewer.find( '.message-desc-edit' );
								$descEditLink.text( mw.msg( 'tux-editor-edit-desc' ) );
							} );
					} );

				$messageDescCancelButton = $( '<button>' )
					.addClass( 'button tux-editor-skipdoc-button' )
					.text( mw.msg( 'tux-editor-doc-editor-cancel' ) )
					.on( 'click', function () {
						translateEditor.hideDocumentationEditor();
					} );

				$messageDescTextarea = $( '<textarea>' )
					.addClass( 'tux-textarea-documentation' )
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

			// Apparently there is still something going on that affects the
			// layout of the text area after this function. Use very small
			// delay to have it settle down and have correct results. Otherwise
			// there will be a size change once the first letter is typed.
			this.delayResize( function() {
				$textarea.trigger( 'autosize.resizeIncludeStyle' );
			}, 1 );

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

			this.hideShortcuts();
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
			var $trigger;

			if ( !definitiondiff || definitiondiff.error ) {
				mw.log( 'Error loading translation diff ' + definitiondiff && definitiondiff.error );
				return;
			}

			// Load the diff styles
			mw.loader.load( 'mediawiki.action.history.diff', undefined, true );

			$trigger = $( '<span>' )
				.addClass( 'show-diff-link' )
				.text( mw.msg( 'tux-editor-outdated-warning-diff-link' ) )
				.on( 'click', function () {
					$( this ).parent().html( definitiondiff.html );
				} );

			this.removeWarning( 'fuzzy' );
			this.addWarning(
				mw.message( 'tux-editor-outdated-warning' ).escaped(),
				'diff'
			).append( $trigger );
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

	function delayer() {
		return (function () {
			var timer = 0;

			return function ( callback, milliseconds ) {
				clearTimeout( timer );
				timer = setTimeout( callback, milliseconds );
			};
		} () );
	}
}( jQuery, mediaWiki ) );
