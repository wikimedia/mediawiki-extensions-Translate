( function ( $, mw ) {
	'use strict';

	function TranslateEditor( element ) {
		this.$editTrigger = $( element );
		this.$editor = null;
		this.$messageItem = this.$editTrigger.find( '.tux-message-item' );
		this.shown = false;
		this.dirty = false;
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
			this.getTranslationMemorySuggestions();
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
		 * Mark the message as translated
		 */
		markTranslated: function() {
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

			// XXX: Any validations to be done before proceeding?
			api.postWithEditToken( {
				action: 'edit',
				title: translateEditor.$editTrigger.data( 'title' ),
				text: translation
			}, function ( response ) {
				var $error;
				// OK
				if ( response.edit.result === 'Success' ) {
					translateEditor.markTranslated();
					translateEditor.next();

					// Update the translation
					translateEditor.$editTrigger.data( 'translation', translation );
					translateEditor.$editTrigger.find( '.tux-list-translation' )
						.text( translation );
				} else {
					// FIXME not tested
					$error = $( '<div>' )
						.addClass( 'row highlight' )
						.text( response.warning );

					translateEditor.$editor.find( 'textarea' )
						.before( $error );
				}
			}, function ( err ) {
				// Error
				// FIXME not tested
				var $error;

				$error = $( '<div>' ).
					addClass( 'row highlight' )
					.text( err.warning );

				translateEditor.$editor.find( 'textarea' ).before( $error );
			} );
		},

		/**
		 * Jump to the next translation editor row.
		 *
		 * @returns {Boolean} false if there's no next row, true otherwise.
		 */
		next: function () {
			var $next;

			if ( this.dirty ) {
				this.markUnsaved();
			}

			$next = this.$editTrigger.next( '.tux-message' );

			if ( !$next.length ) {
				return false;
			}

			$next.data( 'translateeditor' ).show();

			return true;
		},

		prepareEditorColumn: function () {
			var translateEditor = this,
				sourceString,
				$editorColumn,
				$messageKeyLabel,
				$textArea,
				$buttonBlock,
				$saveButton,
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

			$textArea = $( '<textarea>' )
				.attr( {
					'placeholder': mw.msg( 'tux-editor-placeholder' ),
					'lang': $messageList.data( 'targetlangcode' ),
					'dir': $messageList.data( 'targetlangdir' )
				} )
				.addClass( 'eleven columns' )
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

			if ( this.$editTrigger.data( 'translation' ) ) {
				$textArea.text( this.$editTrigger.data( 'translation' ) );
			}

			$editorColumn.append( $( '<div>' )
				.addClass( 'row' )
				.append( $textArea )
			);

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

			$skipButton = $( '<button>' )
				.text( mw.msg( 'tux-editor-skip-button-label' ) )
				.attr( {
					'accesskey': 'd',
					'title': mw.util.tooltipAccessKeyPrefix + 'd'
				} )
				.addClass( 'button tux-editor-skip-button' )
				.on( 'click', function () {
					translateEditor.next();
				} );

			$buttonBlock = $( '<div>' )
				.addClass( 'twelve columns' )
				.append( $saveButton, $skipButton );

			$editorColumn.append( $( '<div>' )
				.addClass( 'row' )
				.append( $buttonBlock )
			);

			$editorColumn.append( $( '<div>' )
				.addClass( 'row text-left shortcutinfo' )
				.text( mw.msg( 'tux-editor-shortcut-info',
					$saveButton.attr( 'title' ).toUpperCase(),
					$skipButton.attr( 'title' ).toUpperCase() )
				)
			);

			return $editorColumn;
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
					.addClass( 'row text-left message-desc-edit' )
					.append( $( '<a>' )
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

			$infoColumn.append( $( '<div>' )
				.addClass( 'row text-left help' )
				.append(
					$( '<span>' )
						.text( mw.msg( 'tux-editor-need-more-help' ) ),
					$( '<a>' )
						.attr( 'href', '#' ) // TODO: add help link for message
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

			this.$editor.find( '.infocolumn' ).show();
			this.$editor.find( '.editcolumn' )
				.removeClass( 'twelve' )
				.addClass( 'seven' );

			this.expanded = false;
		},

		expand: function ( toggleIcon ) {
			// Change the icon image
			toggleIcon.removeClass( 'editor-expand' );
			toggleIcon.addClass( 'editor-contract' );

			this.$editor.find( 'div.infocolumn' ).hide();
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
				action: 'query',
				meta: 'messagetranslations',
				mttitle: this.$editTrigger.data( 'title' ),
				format: 'json'
			};

			$.get( apiURL, queryParams ).done( function ( result ) {
				var translations;

				if ( result.query ) {
					translations = result.query.messagetranslations;

					$.each( translations, function ( index ) {
						var $otherLanguage, translationDir,
							translation = translations[index];

						if ( translation.language === mw.config.get( 'wgTranslateDocumentationLanguageCode' ) ) {
							translateEditor.$editor.find( '.message-desc' )
								.text( translation['*'] );
						} else if ( translation.language !== translateEditor.$editTrigger.attr( 'lang' ) ) {
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
										.text( translation['*'] ),
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
						}

						if ( index > 2 ) {
							// FIXME this is wrong way to filter. but to be addressed when
							// there is a translation helper api to do this properly
							return false;
						}
					} );
				}
			} ).fail( function () {
				// what to do?
			} );
		},

		getTranslationMemorySuggestions: function () {
			// API call to get translation memory suggestions.
			// callback should render suggestions to the editor's info column
			var queryParams,
				translateEditor = this,
				apiURL;

			queryParams = {
				action: 'ttmserver',
				sourcelanguage: 'en', // FIXME: don't hardcode it
				targetlanguage: this.$editTrigger.attr( 'lang' ),
				text: this.$editTrigger.data( 'source' ),
				format: 'json'
			};

			apiURL = mw.util.wikiScript( 'api' );

			$.get( apiURL, queryParams ).done( function ( result ) {
				var suggestions;

				if ( result.ttmserver ) {
					suggestions = result.ttmserver;
					$.each( suggestions, function ( index ) {
						var suggestion,
							$suggestion;

						suggestion = suggestions[index];

						$suggestion = $( '<div>' )
							.addClass( 'row tm-suggestion' )
							.append(
								$( '<div>' )
									.addClass( 'nine columns' )
									.text( suggestion.target ),
								$( '<div>' )
									.addClass( 'three columns quality text-right' )
									.text( mw.msg( 'tux-editor-tm-match', Math.round( suggestion.quality * 100 ) ) )
							);

						translateEditor.$editor.find( '.tm-suggestions-title' )
							.removeClass( 'hide' )
							.after( $suggestion );

						if ( index > 2 ) {
							// FIXME this is wrong way to filter. but to be addressed when
							// there is a translation helper api to do this properly
							return false;
						}
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

}( jQuery, mediaWiki ) );
