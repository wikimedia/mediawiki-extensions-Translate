/* global autosize */

( function () {
	'use strict';

	/**
	 * Dictionary of classes that will be used by different types of notices
	 * TODO: Should probably review and rename these classes in the future to
	 * be more unique to the translate extension? Some themes use warning,
	 * error classes to style elements, and we do take help from these.
	 */
	var noticeTypes = {
		warning: 'warning',
		error: 'error',
		translateFail: 'translation-saving',
		diff: 'diff',
		fuzzy: 'fuzzy',
		getAllClasses: function () {
			var classes = [];

			for ( var prop in this ) {
				if ( typeof this[ prop ] === 'string' ) {
					classes.push( this[ prop ] );
				}
			}

			return classes;
		}
	};

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
	 * mandatory, but if found, when the editor is opened, the message item will be hidden
	 * and the editor will appear as if the message is replaced by the editor.
	 * See the UI of Translate messagetable for a demo.
	 *
	 * @param {HTMLElement} element
	 * @param {Object} options
	 * @param {Function} [options.beforeSave] Callback to call when translation is going to be saved.
	 * @param {Function} [options.onReady] Callback to call when the editor is ready.
	 * @param {Function} [options.onSave] Callback to call when translation has been saved.
	 * @param {Function} [options.onSkip] Callback to call when a message is skipped.
	 * @param {Object} options.message Object as returned by messagecollection api.
	 * @param {mw.translate.TranslationApiStorage} [options.storage]
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
		this.editFontClass = 'mw-editfont-' + mw.user.options.get( 'editfont' );
		this.delayValidation = delayer();
		this.validating = null;
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
				this.addNotice(
					mw.message( 'tux-editor-outdated-notice' ).escaped(),
					noticeTypes.fuzzy
				);
			}

			this.showTranslationHelpers();
		},

		/**
		 * Mark the message as unsaved because of edits, can be resumed later
		 *
		 * @param {string} [highlightClass] Class for background highlighting
		 */
		markUnsaved: function ( highlightClass ) {
			var $tuxListStatus = this.$editTrigger.find( '.tux-list-status' );

			highlightClass = highlightClass || 'tux-highlight';

			$tuxListStatus.children( '.tux-status-unsaved' ).remove();
			$tuxListStatus.children().addClass( 'hide' );
			// `highlightClass` documented above
			// eslint-disable-next-line mediawiki/class-doc
			$( '<span>' )
				.addClass( 'tux-status-unsaved ' + highlightClass )
				.text( mw.msg( 'tux-status-unsaved' ) )
				.appendTo( $tuxListStatus );
		},

		/**
		 * Mark the message as unsaved because of saving failure.
		 */
		markUnsavedFailure: function () {
			this.markUnsaved( 'tux-notice' );
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
				.removeClass( 'untranslated translated fuzzy proofread' )
				.addClass( 'translated' );

			this.dirty = false;

			if ( this.message.properties ) {
				$( '.tux-action-bar .tux-statsbar' ).trigger(
					'change',
					[ 'translated', this.message.properties.status ]
				);

				this.message.properties.status = 'translated';
				// TODO: Update any other statsbar for the same group in the page.
			}
		},

		/**
		 * Save the translation
		 */
		save: function () {
			var translateEditor = this;

			mw.hook( 'mw.translate.editor.beforeSubmit' ).fire( translateEditor.$editor );
			var translation = translateEditor.$editor.find( '.tux-textarea-translation' ).val();
			var editSummary = translateEditor.$editor.find( '.tux-input-editsummary' ).val() || '';

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
				translation,
				editSummary
			).done( function ( response, xhr ) {
				var editResp = response.edit;
				if ( editResp.result === 'Success' ) {
					translateEditor.message.translation = translation;
					translateEditor.onSaveSuccess();
				} else {
					translateEditor.onSaveFail( mw.msg( 'tux-save-unknown-error' ) );
					mw.log( response, xhr );
				}
			} ).fail( function ( errorCode, response ) {
				translateEditor.removeNotices( noticeTypes.translateFail );

				if ( errorCode === 'assertuserfailed' ) {
					// eslint-disable-next-line no-alert
					alert( mw.msg( 'tux-session-expired' ) );
				} else if ( errorCode === 'translate-validation-failed' ) {
					// Cancel the translation check API call to avoid extra notices
					// from appearing.
					if ( translateEditor.validating ) {
						translateEditor.validating.abort();
					} else {
						// Cancel the translation check API call that might be made in the future.
						translateEditor.delayValidation( false );
					}

					translateEditor.removeNotices( [ noticeTypes.error, noticeTypes.warning ] );

					if ( response.error && response.error.validation ) {
						translateEditor.displayNotices( response.error.validation.warnings, noticeTypes.warning );
						translateEditor.displayNotices( response.error.validation.errors, noticeTypes.error );
					}
				}

				// This is placed at the bottom to ensure that the save error appears at the
				// top of the notices
				translateEditor.onSaveFail(
					response.error && response.error.info || mw.msg( 'tux-save-unknown-error' )
				);

				// Display all the notices whenever an error occurs.
				translateEditor.showMoreNotices();
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

			// remove notices if any.
			this.removeNotices( noticeTypes.getAllClasses() );

			this.$editor.find( '.tux-notice' ).empty();
			this.$editor.find( '.tux-more-notices' )
				.addClass( 'hide' )
				.empty();

			$( '.tux-editor-clear-translated' )
				.removeClass( 'hide' )
				.prop( 'disabled', false );

			this.$editor.find( '.tux-input-editsummary' )
				.val( '' )
				.prop( 'disabled', true );

			// Save callback
			if ( this.options.onSave ) {
				this.options.onSave( this.message.translation );
			}

			mw.translate.dirty = false;
			mw.hook( 'mw.translate.editor.afterSubmit' ).fire( this.$editor );

			if ( mw.track ) {
				mw.track( 'ext.translate.event.translation', this.message );
			}
		},

		/**
		 * Marks that there was a problem saving a translation.
		 *
		 * @param {string} error Strings of notices to display.
		 */
		onSaveFail: function ( error ) {
			this.addNotice(
				mw.msg( 'tux-editor-save-failed', error ),
				noticeTypes.translateFail
			);
			this.saving = false;
			this.markUnsavedFailure();

			// Enable the save button again
			this.$editor.find( '.tux-editor-save-button' ).prop( 'disabled', false );
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

			// Determine the next message to show. The immediate next one maybe hidden
			// for example in case of filtering
			while ( $next.length && $next.hasClass( 'hide' ) ) {
				$next = $next.next( '.tux-message' );
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
			var $editItem = this.createMessageToolsItem(
				'message-tools-edit',
				{
					title: this.message.title,
					action: 'edit'
				},
				'tux-editor-message-tools-show-editor'
			);

			if ( !mw.translate.canTranslate() ) {
				$editItem.addClass( 'hide' );
			}

			var $historyItem = this.createMessageToolsItem(
				'message-tools-history',
				{
					title: this.message.title,
					action: 'history'
				},
				'tux-editor-message-tools-history'
			);

			var $deleteItem = this.createMessageToolsItem(
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
			var $translationsItem = this.createMessageToolsItem(
				'message-tools-translations',
				{
					title: 'Special:Translations',
					message: this.message.title
				},
				'tux-editor-message-tools-translations'
			);

			var $linkToThisItem = this.createMessageToolsItem(
				'message-tools-linktothis',
				{
					title: 'Special:Translate',
					showMessage: this.message.key,
					group: this.message.primaryGroup,
					language: this.message.targetLanguage
				},
				'tux-editor-message-tools-linktothis'
			);

			return $( '<ul>' )
				.addClass( 'tux-dropdown-menu tux-message-tools-menu hide' )
				.append( $editItem, $historyItem, $deleteItem, $translationsItem, $linkToThisItem );
		},

		prepareEditorColumn: function () {
			var translateEditor = this,
				$discardChangesButton = $( [] ),
				$saveButton = $( [] ),
				$messageTools = translateEditor.createMessageTools(),
				canTranslate = mw.translate.canTranslate();

			var $editorColumn = $( '<div>' )
				.addClass( 'seven columns editcolumn' );

			var $messageKeyLabel = $( '<div>' )
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

			var $closeIcon = $( '<span>' )
				.addClass( 'one column close' )
				.attr( 'title', mw.msg( 'tux-editor-close-tooltip' ) )
				.on( 'click', function ( e ) {
					translateEditor.hide();
					e.stopPropagation();
				} );

			var $infoToggleIcon = $( '<span>' )
				// Initially the editor column is contracted,
				// so show the expand button first
				.addClass( 'one column editor-info-toggle editor-expand' )
				.attr( 'title', mw.msg( 'tux-editor-expand-tooltip' ) )
				.on( 'click', function ( e ) {
					translateEditor.infoToggle( $( this ) );
					e.stopPropagation();
				} );

			var $layoutActions = $( '<div>' )
				.addClass( 'two columns layout-actions' )
				.append( $closeIcon, $infoToggleIcon );

			$editorColumn.append( $( '<div>' )
				.addClass( 'row tux-editor-titletools' )
				.append( $messageKeyLabel, $layoutActions )
			);

			var $messageList = $( '.tux-messagelist' );
			var originalTranslation = this.message.translation;
			var sourceString = this.message.definition;
			// The following classes are used here:
			// * mw-editfont-serif
			// * mw-editfont-sans-serif
			// * mw-editfont-monospace
			var $sourceString = $( '<span>' )
				.addClass( 'twelve columns sourcemessage ' + this.editFontClass )
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

			var $notices = $( '<div>' )
				.addClass( 'tux-notice hide' );

			var $moreNoticesTab = $( '<div>' )
				.addClass( 'tux-more-notices hide' )
				.on( 'click', function () {
					var $this = $( this ),
						$moreNotices = $notices.children(),
						lastNoticeIndex = $moreNotices.length - 1;

					// If the notice list is not open, only one notice is shown
					if ( $this.hasClass( 'open' ) ) {
						$moreNotices.each( function ( index, element ) {
							// The first element must always be shown
							if ( index ) {
								$( element ).addClass( 'hide' );
							}
						} );

						$this
							.removeClass( 'open' )
							.text( mw.msg( 'tux-notices-more', lastNoticeIndex ) );
					} else {
						$moreNotices.each( function ( index, element ) {
							// The first element must always be shown
							if ( index ) {
								$( element ).removeClass( 'hide' );
							}
						} );

						$this
							.addClass( 'open' )
							.text( mw.msg( 'tux-notices-hide' ) );
					}

					translateEditor.toggleMoreButtonClass();
				} );

			var $textarea = this.getTranslationEditor( this.message.targetLanguage );

			// Shortcuts for various insertable things
			$textarea.on( 'keyup keydown', function ( e ) {
				var index, $info, direction;

				if ( e.type === 'keydown' && e.altKey === true ) {
					// Up and down arrows
					if ( e.keyCode === 38 || e.keyCode === 40 ) {
						direction = e.keyCode === 40 ? 1 : -1;
						$info = translateEditor.$editor.find( '.infocolumn' );
						$info.scrollTop( $info.scrollTop() + 100 * direction );
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
					window.setTimeout( function () {
						translateEditor.showShortcuts();
					}, 100 );
				}

				if ( e.which === 18 && e.type === 'keyup' ) {
					translateEditor.hideShortcuts();
				} else if ( e.which === 18 && e.type === 'keydown' ) {
					translateEditor.showShortcuts();
				}
			} );

			$textarea.on( 'input', function () {
				var $pasteSourceButton = translateEditor.$editor.find( '.tux-editor-paste-original-button' ),
					original = translateEditor.message.translation || '',
					current = $textarea.val() || '';

				if ( original !== '' ) {
					$discardChangesButton.removeClass( 'hide' );
				}

				/* Avoid Unsaved marking when translated message is not changed in content.
				 * - translateEditor.dirty: internal book keeping
				 * - mw.translate.dirty: "you have unchanged edits" notice
				 */
				if ( original === current ) {
					translateEditor.markUnunsaved();
				} else {
					translateEditor.dirty = true;
					mw.translate.dirty = true;
				}

				translateEditor.makeSaveButtonJustSave( $saveButton );

				// When there is content in the editor enable the button.
				// But do not enable when some saving is not finished yet.
				if ( current.trim() && !translateEditor.saving ) {
					$pasteSourceButton.addClass( 'hide' );
					$saveButton.prop( 'disabled', false );
				} else {
					$saveButton.prop( 'disabled', true );
					$pasteSourceButton.removeClass( 'hide' );
				}

				translateEditor.resizeInsertables( $textarea );

				translateEditor.delayValidation( function () {
					translateEditor.validateTranslation();
				}, 500 );
			} );

			var $noticesBlock = $( '<div>' )
				.addClass( 'tux-notices-block' )
				.append( $moreNoticesTab, $notices );

			var $editAreaBlock = $( '<div>' )
				.addClass( 'row tux-editor-editarea-block' )
				.append( $( '<div>' )
					.addClass( 'editarea twelve columns' )
					.append( $noticesBlock, $textarea )
				);

			$editorColumn.append( $editAreaBlock );

			var $editingButtonBlock, $editSummaryBlock, $requestRight, $skipButton;
			if ( canTranslate ) {
				var $pasteOriginalButton = $( '<button>' )
					.addClass( 'tux-editor-paste-original-button' )
					.text( mw.msg( 'tux-editor-paste-original-button-label' ) )
					.on( 'click', function () {
						$textarea
							.trigger( 'focus' )
							.val( sourceString )
							.trigger( 'input' );

						$pasteOriginalButton.addClass( 'hide' );
					} );

				var $editSummary = $( '<input>' )
					.addClass( 'tux-input-editsummary' )
					.attr( {
						maxlength: 255,
						disabled: true,
						placeholder: mw.msg( 'tux-editor-editsummary-placeholder' )
					} )
					.val( '' );

				// Enable edit summary if there was a change to translation area
				// or disable if there is no text in translation area
				$textarea.on( 'input', function () {
					if ( $editSummary.prop( 'disabled' ) ) {
						$editSummary.prop( 'disabled', false );
					}
					if ( $textarea.val().trim() === '' ) {
						$editSummary.prop( 'disabled', true );
					}
				} ).on( 'keydown', function ( e ) {
					if ( !e.ctrlKey || e.keyCode !== 13 ) {
						return;
					}

					if ( !$saveButton.is( ':disabled' ) ) {
						$saveButton.trigger( 'click' );
						return;
					}
					$skipButton.trigger( 'click' );
				} );

				if ( originalTranslation !== null ) {
					$discardChangesButton = $( '<button>' )
						.addClass( 'tux-editor-discard-changes-button hide' ) // Initially hidden
						.text( mw.msg( 'tux-editor-discard-changes-button-label' ) )
						.on( 'click', function () {
							// Restore the translation
							$textarea
								.trigger( 'focus' )
								.val( originalTranslation );

							// and go back to hiding.
							$discardChangesButton.addClass( 'hide' );

							// There's nothing new to save...
							$editSummary.val( '' ).prop( 'disabled', true );
							$saveButton.prop( 'disabled', true );
							// ...unless there is other action
							translateEditor.makeSaveButtonContextSensitive( $saveButton );

							translateEditor.markUnunsaved();
							translateEditor.resizeInsertables( $textarea );
						} );
				}

				if ( this.message.translation ) {
					$pasteOriginalButton.addClass( 'hide' );
				}

				$editingButtonBlock = $( '<div>' )
					.addClass( 'twelve columns tux-editor-insert-buttons' )
					.append(
						$pasteOriginalButton,
						$discardChangesButton
					);

				$editSummaryBlock = $( '<div>' )
					.addClass( 'row tux-editor-editsummary-block' )
					.append(
						$( '<div>' )
							.addClass( 'twelve columns' )
							.append( $editSummary )
					);

				$requestRight = $( [] );

				$saveButton = $( '<button>' )
					.prop( 'disabled', true )
					.addClass( 'tux-editor-save-button mw-ui-button mw-ui-progressive' )
					.text( mw.msg( 'tux-editor-save-button-label' ) )
					.on( 'click', function ( e ) {
						translateEditor.save();
						e.stopPropagation();
					} );

				this.makeSaveButtonContextSensitive( $saveButton, this.$messageItem );
			} else {
				$editingButtonBlock = $( [] );

				$editSummaryBlock = $( [] );

				$requestRight = $( '<span>' )
					.addClass( 'tux-editor-request-right' )
					.text( mw.msg( 'translate-edit-nopermission' ) );
				// Make sure wgTranslatePermissionUrl setting is not 'false'
				if ( mw.config.get( 'wgTranslatePermissionUrl' ) !== false ) {
					$requestRight
						.append( $( '<a>' )
							.text( mw.msg( 'translate-edit-askpermission' ) )
							.addClass( 'tux-editor-ask-permission' )
							.attr( {
								href: mw.util.getUrl(
									mw.config.get( 'wgTranslateUseSandbox' ) ?
										'Special:TranslationStash' :
										mw.config.get( 'wgTranslatePermissionUrl' )
								)
							} )
						);
				}
				// Disable the text area if user has no translation rights.
				// Use readonly to allow copy-pasting (except for placeholders)
				$textarea.prop( 'readonly', true );

				$saveButton = $( [] );
			}

			$skipButton = $( '<button>' )
				.addClass( 'tux-editor-skip-button mw-ui-button mw-ui-quiet' )
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
			var $cancelButton = $( '<button>' )
				.addClass( 'tux-editor-cancel-button mw-ui-button mw-ui-quiet' )
				.text( mw.msg( 'tux-editor-cancel-button-label' ) )
				.on( 'click', function ( e ) {
					translateEditor.skip();
					translateEditor.hide();

					e.stopPropagation();
				} );

			var $controlButtonBlock = $( '<div>' )
				.addClass( 'twelve columns tux-editor-control-buttons' )
				.append( $requestRight, $saveButton, $skipButton, $cancelButton );

			$editorColumn.append( $( '<div>' )
				.addClass( 'row tux-editor-actions-block' )
				.append( $editingButtonBlock )
			);

			$editorColumn.append( $editSummaryBlock );

			$editorColumn.append( $( '<div>' )
				.addClass( 'row tux-editor-actions-block' )
				.append( $controlButtonBlock )
			);

			if ( canTranslate ) {
				var prefix = $.fn.updateTooltipAccessKeys.getAccessKeyPrefix();
				$editorColumn.append( $( '<div>' )
					.addClass( 'row shortcutinfo' )
					.text( mw.msg(
						'tux-editor-shortcut-info',
						'CTRL-ENTER',
						( prefix + 'd' ).toUpperCase(),
						'ALT',
						( prefix + 'b' ).toUpperCase()
					) )
				);
			}

			return $editorColumn;
		},

		/**
		 * Modifies the save button to provide suitable default action for *unchanged*
		 * message. It will revert back to normal save button if the text is changed.
		 *
		 * @param {jQuery} $button The save button.
		 */
		makeSaveButtonContextSensitive: function ( $button ) {
			var self = this;

			if ( this.message.properties.status === 'fuzzy' ) {
				$button.prop( 'disabled', false );
				$button.text( mw.msg( 'tux-editor-confirm-button-label' ) );
				$button.off( 'click' );
				$button.on( 'click', function ( e ) {
					self.save();
					e.stopPropagation();
				} );
			} else if ( this.message.proofreadable ) {
				$button.prop( 'disabled', false );
				$button.text( mw.msg( 'tux-editor-proofread-button-label' ) );
				$button.off( 'click' );
				$button.on( 'click', function ( e ) {
					$button.prop( 'disabled', true );
					self.message.proofreadAction();
					self.next();
					e.stopPropagation();
				} );
			}
		},

		/**
		 * Modifies the save button to just save the translation as usual. Whether the
		 * button is enabled or not is controlled elsewhere.
		 *
		 * @param {jQuery} $button The save button.
		 */
		makeSaveButtonJustSave: function ( $button ) {
			var self = this;

			$button.text( mw.msg( 'tux-editor-save-button-label' ) );
			$button.off( 'click' );
			$button.on( 'click', function ( e ) {
				self.save();
				e.stopPropagation();
			} );
		},

		/**
		 * Validate the current translation using the API
		 * and show the notices.
		 */
		validateTranslation: function () {
			var translateEditor = this,
				$textarea = translateEditor.$editor.find( '.tux-textarea-translation' );

			var api = new mw.Api();

			this.validating = api.post( {
				action: 'translationcheck',
				title: this.message.title,
				translation: $textarea.val()
			} ).done( function ( data ) {
				var warnings = data.validation.warnings,
					errors = data.validation.errors;

				translateEditor.removeNotices( [
					noticeTypes.error,
					noticeTypes.warning,
					noticeTypes.translateFail
				] );

				if ( ( !warnings || !warnings.length ) &&
					( !errors || !errors.length ) ) {
					return;
				}

				// Remove useless fuzzy notice if we have more details
				translateEditor.removeNotices( noticeTypes.fuzzy );

				// Disable confirm translation button, since fuzzy translations
				// cannot be confirmed. The check for dirty state can be removed
				// to prevent translations with notices.
				if ( !translateEditor.dirty ) {
					translateEditor.$editor.find( '.tux-editor-save-button' )
						.prop( 'disabled', true );
				}

				// Don't allow users to save if there are errors but allow admins to save
				// even if there are errors.
				if ( !mw.translate.canManage() ) {
					if ( errors && errors.length > 0 ) {
						translateEditor.$editor.find( '.tux-editor-save-button' )
							.prop( 'disabled', true );
					}
				}

				translateEditor.displayNotices( warnings, noticeTypes.warning );
				translateEditor.displayNotices( errors, noticeTypes.error );

			} ).always( function () {
				translateEditor.validating = null;
			} );
		},

		/**
		 * Remove all notices of given types
		 *
		 * @param {(string|string[])} types
		 */
		removeNotices: function ( types ) {
			var $tuxNotice = this.$editor.find( '.tux-notice' ),
				stringTypes = [],
				allNoticeTypes = noticeTypes.getAllClasses();

			if ( typeof types === 'string' ) {
				stringTypes.push( types );
			} else {
				stringTypes = types;
			}

			for ( var index = 0; index < stringTypes.length; index++ ) {
				if ( allNoticeTypes.indexOf( stringTypes[ index ] ) === -1 ) {
					var errMsg = 'tux: Invalid notice type removeNotice - ' + stringTypes[ index ];
					mw.log.error( errMsg );
					throw new Error( errMsg );
				}
				$tuxNotice.find( '.' + stringTypes[ index ] ).remove();
			}

			var $currentNotices = $tuxNotice.children();
			// If a single notice is shown, we can hide the more notice button,
			// and display the hidden notice.
			if ( $currentNotices.length <= 1 ) {
				this.$editor.find( '.tux-more-notices' ).addClass( 'hide' );
				$currentNotices.removeClass( 'hide' );
			}
			this.toggleMoreButtonClass();
		},

		/**
		 * Displays the supplied notice above the translation edit area.
		 * Newer notices are added to the top while older notices are
		 * added to the bottom. This also means that older notices will
		 * not be shown by default unless the user clicks "more notices" tab.
		 *
		 * @param {string} notice used as html for the notices display
		 * @param {string} type used to group the notices.eg: warning, diff, error
		 * @return {jQuery} the new notice element
		 */
		addNotice: function ( notice, type ) {
			var $notices = this.$editor.find( '.tux-notice' ),
				$moreNoticesTab = this.$editor.find( '.tux-more-notices' ),
				// `noticeTypes` documented above
				// eslint-disable-next-line mediawiki/class-doc
				$newNotice = $( '<div>' )
					.addClass( 'tux-notice-message ' + type )
					.html( notice );

			this.$editor.find( '.tux-notice-message' ).addClass( 'hide' );

			$notices
				.removeClass( 'hide' )
				.prepend( $newNotice );

			var noticeCount = $notices.find( '.tux-notice-message' ).length;

			if ( noticeCount > 1 ) {
				$moreNoticesTab
					.text( mw.msg( 'tux-notices-more', noticeCount - 1 ) )
					.removeClass( 'hide open' );
			} else {
				$moreNoticesTab.addClass( 'hide' );
			}
			this.toggleMoreButtonClass();

			return $newNotice;
		},

		/**
		 * Toggles the class on the more button based on the types of notice displayed, and whether
		 * the more section is expanded. This is done in order to change the background color of the
		 * button.
		 */
		toggleMoreButtonClass: function () {
			var $allNotices = this.$editor.find( '.tux-notice-message' ),
				errorCount = $allNotices.filter( '.tux-notice-message.' + noticeTypes.error ).length +
					$allNotices.filter( '.tux-notice-message.' + noticeTypes.translateFail ).length,
				otherErrorsCount = $allNotices.length - errorCount,
				$moreButton = this.$editor.find( '.tux-more-notices' );

			if ( errorCount === 0 ) {
				// if no error, no classes needed.
				$moreButton.removeClass( 'tux-has-errors' );
			} else if ( otherErrorsCount > 0 && $moreButton.hasClass( 'open' ) ) {
				// there are other notices, and more section is expanded.
				$moreButton.removeClass( 'tux-has-errors' );
			} else {
				$moreButton.addClass( 'tux-has-errors' );
			}
		},

		prepareInfoColumn: function () {
			var $infoColumn = $( '<div>' ).addClass( 'infocolumn' ),
				translateEditor = this;

			$infoColumn.append( $( '<div>' )
				.addClass( 'row loading' )
				.text( mw.msg( 'tux-editor-loading' ) )
			);

			if ( mw.config.get( 'wgTranslateDocumentationLanguageCode' ) ) {
				var $messageDescSaveButton = $( '<button>' )
					.addClass( 'tux-editor-savedoc-button mw-ui-button mw-ui-progressive' )
					.prop( 'disabled', true )
					.text( mw.msg( 'tux-editor-doc-editor-save' ) )
					.on( 'click', function () {
						translateEditor.saveDocumentation()
							.done( function () {
								var $descEditLink = $messageDescViewer.find( '.message-desc-edit' );
								$descEditLink.text( mw.msg( 'tux-editor-edit-desc' ) );
							} );
					} );

				var $messageDescCancelButton = $( '<button>' )
					.addClass( 'tux-editor-skipdoc-button mw-ui-button mw-ui-quiet' )
					.text( mw.msg( 'tux-editor-doc-editor-cancel' ) )
					.on( 'click', function () {
						translateEditor.hideDocumentationEditor();
					} );

				var $messageDescTextarea = $( '<textarea>' )
					.addClass( 'tux-textarea-documentation' )
					.on( 'input', function () {
						$messageDescSaveButton.prop( 'disabled', false );
					} )
					.prop( 'placeholder', mw.msg( 'tux-editor-doc-editor-placeholder' ) );

				var $messageDescEditor = $( '<div>' )
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

				var $messageDescViewer = $( '<div>' )
					.addClass( 'message-desc-viewer hide' )
					.append(
						$( '<div>' )
							.addClass( 'row message-desc' ),
						$( '<div>' )
							.addClass( 'row message-desc-control' )
							.append( $( '<a>' )
								.attr( {
									href: mw.translate.getDocumentationEditURL(
										this.message.title.replace( /\/[a-z-]+$/, '' )
									),
									target: '_blank'
								} )
								.addClass( 'message-desc-edit' )
								.on( 'click', this.showDocumentationEditor.bind( this ) )
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
				.addClass( 'row uneditable-documentation hide mw-parser-output' )
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
					$( '<span>' ).addClass( 'tux-message-editor__caret' ),
					$infoColumn
				);
		},

		show: function () {
			if ( !this.$editor ) {
				this.init();
			}

			var $textarea = this.$editor.find( '.editcolumn textarea' );
			// Hide all other open editors in the page
			$( '.tux-message.open' ).each( function () {
				$( this ).data( 'translateeditor' ).hide();
			} );

			// The access keys need to be shifted to the editor currently active
			$( '.tux-editor-save-button, .tux-editor-save-button' ).removeAttr( 'accesskey' );
			this.$editor.find( '.tux-editor-save-button' ).attr( 'accesskey', 's' );
			this.$editor.find( '.tux-editor-skip-button' ).attr( 'accesskey', 'd' );
			this.$editor.find( '.tux-input-editsummary' ).attr( 'accesskey', 'b' );
			// @todo access key for the cancel button

			this.$messageItem.addClass( 'hide' );
			this.$editor.removeClass( 'hide' );
			$textarea.trigger( 'focus' );

			autosize( $textarea );
			this.resizeInsertables( $textarea );

			this.shown = true;
			this.$editTrigger.addClass( 'open' );

			// don't waste time, get ready with next message
			var $next = this.$editTrigger.next( '.tux-message' );

			if ( $next.length ) {
				$next.data( 'translateeditor' ).init();
			}

			mw.hook( 'mw.translate.editor.afterEditorShown' ).fire( this.$editor );

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

			this.$editor.removeClass( 'tux-message-editor--expanded' );
			this.expanded = false;
		},

		expand: function ( toggleIcon ) {
			// Change the icon image
			toggleIcon
				.removeClass( 'editor-expand' )
				.addClass( 'editor-contract' )
				.attr( 'title', mw.msg( 'tux-editor-collapse-tooltip' ) );

			this.$editor.addClass( 'tux-message-editor--expanded' );
			this.expanded = true;
		},

		/**
		 * Adds the diff between old and current definitions to the view.
		 *
		 * @param {Object} definitiondiff A definitiondiff object as returned by API.
		 */
		addDefinitionDiff: function ( definitiondiff ) {
			if ( !definitiondiff || definitiondiff.error ) {
				mw.log( 'Error loading translation diff ' + definitiondiff && definitiondiff.error );
				return;
			}

			// Load the diff styles
			mw.loader.load( 'mediawiki.diff.styles' );

			var $trigger = $( '<span>' )
				.addClass( 'show-diff-link' )
				.text( mw.msg( 'tux-editor-outdated-notice-diff-link' ) )
				.on( 'click', function () {
					$( this ).parent().html( definitiondiff.html );
				} );

			this.removeNotices( noticeTypes.fuzzy );
			this.addNotice(
				mw.message( 'tux-editor-outdated-notice' ).escaped(),
				noticeTypes.diff
			).append( $trigger );
		},

		/**
		 * Attach event listeners
		 */
		listen: function () {
			var translateEditor = this;

			this.$editTrigger.find( '.tux-message-item' ).on( 'click', function () {
				translateEditor.show();

				return false;
			} );
		},

		/**
		 * Makes the textarea large enough for insertables and positions the insertables.
		 *
		 * @param {jQuery} $textarea Text area.
		 */
		resizeInsertables: function ( $textarea ) {
			var $buttonArea = this.$editor.find( '.tux-editor-insert-buttons' );
			var buttonAreaHeight = $buttonArea.height();
			$textarea.css( 'padding-bottom', buttonAreaHeight + 5 );
			$buttonArea.css( 'top', -buttonAreaHeight );
			autosize.update( $textarea );
		},

		/**
		 * Utility method to display a list of notices on the UI
		 *
		 * @param {Array} notices
		 * @param {string} noticeType
		 */
		displayNotices: function ( notices, noticeType ) {
			for ( var index = 0; index < notices.length; ++index ) {
				this.addNotice( notices[ index ], noticeType );
			}
		},

		/**
		 * Ensures that all the notices are displayed
		 */
		showMoreNotices: function () {
			var $moreNoticesTab = this.$editor.find( '.tux-more-notices' );
			if ( $moreNoticesTab.hasClass( 'open' ) ) {
				return;
			}

			$moreNoticesTab.trigger( 'click' );
		},

		/**
		 * Generates the translation editor element based on target language
		 *
		 * @param {string} targetLangCode
		 * @return {Object} Returns translation editor element
		 */
		getTranslationEditor: function ( targetLangCode ) {
			var targetLangAttrib, placeholder;
			if ( targetLangCode === mw.config.get( 'wgTranslateDocumentationLanguageCode' ) ) {
				targetLangAttrib = mw.config.get( 'wgContentLanguage' );
				placeholder = mw.msg( 'tux-editor-placeholder-documentation' );
			} else {
				targetLangAttrib = targetLangCode;
				placeholder = mw.msg( 'tux-editor-placeholder-language', $.uls.data.getAutonym( targetLangCode ) );
			}

			var targetLangDir = $.uls.data.getDir( targetLangAttrib );

			// The following classes are used here:
			// * mw-editfont-serif
			// * mw-editfont-sans-serif
			// * mw-editfont-monospace
			return $( '<textarea>' )
				.addClass( 'tux-textarea-translation ' + this.editFontClass )
				.attr( {
					lang: targetLangAttrib,
					dir: targetLangDir
				} )
				.val( this.message.translation || '' )
				.prop( 'placeholder', placeholder );
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
				data[ options ].call( $this );
			}
		} );
	};

	mw.translate.editor = mw.translate.editor || {};
	mw.translate.editor = $.extend( TranslateEditor.prototype, mw.translate.editor );

	function delayer() {
		return ( function () {
			var timer = 0;

			return function ( callback, milliseconds ) {
				clearTimeout( timer );

				if ( callback === false ) {
					// sometimes we need to just cancel the timer without
					// setting up another one
					return;
				}

				timer = setTimeout( callback, milliseconds );
			};
		}() );
	}
}() );
