/*
 * Translate editor additional helper functionality
 */
( function ( $, mw ) {
	'use strict';

	var translateEditorHelpers = {

		showDocumentationEditor: function () {
			var $infoColumnBlock = this.$editor.find( '.infocolumn-block' ),
				$editColumn = this.$editor.find( '.editcolumn' ),
				$messageDescEditor = $infoColumnBlock.find( '.message-desc-editor' ),
				$messageDescViewer = $infoColumnBlock.find( '.message-desc-viewer' );

			$infoColumnBlock
				.removeClass( 'five' )
				.addClass( 'seven' );
			$editColumn
				.removeClass( 'seven' )
				.addClass( 'five' );

			$messageDescViewer.addClass( 'hide' );

			$messageDescEditor
				.removeClass( 'hide' )
				.find( 'textarea' )
					.focus();

			// So that the link won't be followed
			return false;
		},

		hideDocumentationEditor: function () {
			var $infoColumnBlock = this.$editor.find( '.infocolumn-block' ),
				$editColumn = this.$editor.find( '.editcolumn' ),
				$messageDescEditor = $infoColumnBlock.find( '.message-desc-editor' ),
				$messageDescViewer = $infoColumnBlock.find( '.message-desc-viewer' );

			$infoColumnBlock
				.removeClass( 'seven' )
				.addClass( 'five' );
			$editColumn
				.removeClass( 'five' )
				.addClass( 'seven' );

			$messageDescEditor.addClass( 'hide' );
			$messageDescViewer.removeClass( 'hide' );
		},

		/**
		 * Save the documentation
		 */
		saveDocumentation: function () {
			var translateEditor = this,
				api = new mw.Api(),
				newDocumentation = translateEditor.$editor.find( '.infocolumn-block textarea' ).val();

			api.post( {
				action: 'edit',
				title: translateEditor.message.title
					.replace( /\/[a-z\-]+$/, '/' + mw.config.get( 'wgTranslateDocumentationLanguageCode' ) ),
				text: newDocumentation,
				token: mw.user.tokens.get( 'editToken' )
			} ).done( function ( response ) {
				var $messageDesc = translateEditor.$editor.find( '.infocolumn-block .message-desc' );

				if ( response.edit.result === 'Success' ) {
					api.parse( newDocumentation,
						function ( parsedDocumentation ) {
							$messageDesc.html( parsedDocumentation );
						},
						function ( errorCode, results ) {
							$messageDesc.html( newDocumentation );
							mw.log( 'Error parsing documentation ' + errorCode + ' ' + results.error.info );
						}
					);

					// A collapsible element may have been added
					$( '.mw-identical-title' ).makeCollapsible();

					translateEditor.hideDocumentationEditor();
				} else {
					mw.notify( 'Error saving message documentation' );
					mw.log( 'Error saving documentation', response );
				}
			} ).fail( function ( errorCode, results ) {
				mw.notify( 'Error saving message documentation' );
				mw.log( 'Error saving documentation', errorCode, results );
			} );
		},

		/**
		 * Shows the message documentation.
		 * @param {object} documentation A documentation object as returned by API.
		 */
		showMessageDocumentation: function ( documentation ) {
			var $descEditLink,
				documentationDir,
				expand,
				$messageDescViewer,
				$messageDoc,
				readMore,
				$readMore = null;

			if ( !mw.config.get( 'wgTranslateDocumentationLanguageCode' ) ) {
				return;
			}

			$messageDescViewer = this.$editor.find( '.message-desc-viewer' );
			$descEditLink = $messageDescViewer.find( '.message-desc-edit' );
			$messageDoc = $messageDescViewer.find( '.message-desc' );

			// Display the documentation only if it's not empty and
			// documentation language is configured
			if ( documentation.error ) {
				// TODO: better error handling, especially since the presence of documentation
				// is heavily hinted at in the UI
				return;
			} else if ( documentation.value ) {
				documentationDir = $.uls.data.getDir( documentation.language );

				// Show the documentation and set appropriate
				// lang and dir attributes.
				// The message documentation is assumed to be written
				// in the content language of the wiki.
				// Possible classes:
				// * mw-content-ltr
				// * mw-content-rtl
				// (The direction classes are needed, because the documentation
				// is likely to be MediaWiki-formatted text.)
				$messageDoc
					.attr( {
						lang: documentation.language,
						dir: documentationDir
					} )
					.addClass( 'mw-content-' + documentationDir )
					.html( documentation.html );

				this.$editor.find( '.message-desc-editor textarea' )
					.attr( {
						lang: documentation.language,
						dir: documentationDir
					} )
					.val( documentation.value );

				$descEditLink.text( mw.msg( 'tux-editor-edit-desc' ) );

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

					$messageDescViewer.find( '.message-desc-control' )
						.prepend( $readMore );

					$messageDoc.addClass( 'long compact' ).on( 'hover', expand );
				}

				// Enable the collapsible elements,
				// used in {{Identical}} on translatewiki.net
				$( '.mw-identical-title' ).makeCollapsible();
			} else {
				$descEditLink.text( mw.msg( 'tux-editor-add-desc' ) );
			}

			$messageDescViewer.removeClass( 'hide' );
		},

		/**
		 * Shows uneditable documentation.
		 * @param {Object} documentation A gettext object as returned by API.
		 */
		showUneditableDocumentation: function ( documentation ) {
			var dir;

			if ( documentation.error ) {
				return;
			}

			dir = $.uls.data.getDir( documentation.language );

			this.$editor.find( '.uneditable-documentation' )
				.attr( {
					lang: documentation.language,
					dir: dir
				} )
				.addClass( 'mw-content-' + dir )
				.html( documentation.html )
				.removeClass( 'hide' );
		},

		/**
		 * Shows the translations from other languages
		 * @param {array} translations An inotherlanguages array as returned by the translation helpers API.
		 */
		showAssistantLanguages: function ( translations ) {
			var translateEditor = this,
				$translationTextarea;

			$translationTextarea = this.$editor.find( 'textarea' );

			$.each( translations, function ( index ) {
				var $otherLanguage,
					translationDir,
					translation = translations[index];

				translationDir = $.uls.data.getDir( translation.language );

				$otherLanguage = $( '<div>' )
					.addClass( 'row in-other-language' )
					.append(
						$( '<div>' )
							.addClass( 'nine columns suggestiontext' )
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

				$otherLanguage.on( 'click',
					translateEditor.suggestionAdder( translation.value, $translationTextarea )
				);

				translateEditor.$editor.find( '.in-other-languages-title' )
					.removeClass( 'hide' )
					.after( $otherLanguage );
			} );
		},

		/**
		 * Shows the translation suggestions from Translation Memory
		 * @param {array} suggestions A ttmserver array as returned by API.
		 */
		showTranslationMemory: function ( suggestions ) {
			var $tmSuggestions, $translationTextarea,
				translateEditor = this;

			if ( !suggestions.length ) {
				return;
			}

			$tmSuggestions = $( '<div>' )
				.addClass( 'tm-suggestions' );
			this.$editor.find( '.tm-suggestions-title' )
				.removeClass( 'hide' )
				.after( $tmSuggestions );
			$translationTextarea = this.$editor.find( 'textarea' );

			$.each( suggestions, function ( index, translation ) {
				var $translation,
					alreadyOnTheList = false;

				if ( translation.local && translation.location === translateEditor.message.title ) {
					// Do not add self-suggestions
					return true;
				}

				// See if it is already listed, and increment use count
				$tmSuggestions.find( '.tm-suggestion' ).each( function () {
					var $uses, count,
						$suggestion = $( this );

					if ( $suggestion.find( '.suggestiontext ' ).text() === translation.target ) {
						// Update the message and data value
						$uses = $suggestion.find( '.n-uses' );
						count = $uses.data( 'n' ) + 1;
						$uses.data( 'n', count );
						$uses.text( mw.msg( 'tux-editor-n-uses', count ) + '  〉' );

						// Halt processing
						alreadyOnTheList = true;
						return false;
					}
				} );

				if ( alreadyOnTheList ) {
					// Continue to the next one
					return true;
				}

				$translation = $( '<div>' )
					.addClass( 'row tm-suggestion' )
					.append(
						$( '<div>' )
							.addClass( 'nine columns suggestiontext' )
							.text( translation.target ),
						$( '<div>' )
							.addClass( 'three columns quality text-right' )
							.text( mw.msg( 'tux-editor-tm-match',
								Math.round( translation.quality * 100 ) ) ),
						$( '<div>' )
							.addClass( 'row text-right' )
							.append(
								$( '<a>' )
									.addClass( 'n-uses' )
									.data( 'n', 1 )
							)
					);

				$translation.on( 'click',
					translateEditor.suggestionAdder( translation.target, $translationTextarea )
				);

				$tmSuggestions.append( $translation );
			} );
		},

		/**
		 * Shows the translation from machine translation systems
		 * @param {array} suggestions
		 */
		showMachineTranslations: function ( suggestions ) {
			var $mtSuggestions, $translationTextarea,
				translateEditor = this;

			if ( !suggestions.length ) {
				return;
			}

			$mtSuggestions = this.$editor.find( '.tm-suggestions' );

			if ( !$mtSuggestions.length ) {
				$mtSuggestions = $( '<div>' ).addClass( 'tm-suggestions' );
			}

			this.$editor.find( '.tm-suggestions-title' )
				.removeClass( 'hide' )
				.after( $mtSuggestions );
			$translationTextarea = this.$editor.find( 'textarea' );

			$.each( suggestions, function ( index, translation ) {
				var $translation;

				$translation = $( '<div>' )
					.addClass( 'row tm-suggestion' )
					.append(
						$( '<div>' )
							.addClass( 'nine columns suggestiontext' )
							.text( translation.target ),
						$( '<div>' )
							.addClass( 'three columns text-right service' )
							.text( translation.service )
					);

				$translation.on( 'click',
					translateEditor.suggestionAdder( translation.target, $translationTextarea )
				);

				$mtSuggestions.append( $translation );
			} );
		},

		/**
		 * Returns a function that can be bind to click event. The function
		 * allows inserting suggestion to $target while also allowing text
		 * selection without triggering insertion.
		 *
		 * @param {String} suggestion Text to add
		 * @param {jQuery} $target Target element (textarea or input)
		 * @return {Function}
		 */
		suggestionAdder: function ( suggestion, $target ) {
			return function () {
				var selection;
				if ( window.getSelection ) {
					selection = window.getSelection().toString();
				} else if ( document.selection && document.selection.type !== 'Control' ) {
					selection = document.selection.createRange().text;
				}

				if ( !selection ) {
					$target.val( suggestion ).focus().trigger( 'input' );
				}
			};
		},

		/**
		 * Shows the support options for the translator.
		 * @param {object} support A support object as returned by API.
		 */
		showSupportOptions: function ( support ) {
			// Support URL
			if ( support.url ) {
				this.$editor.find( '.help' )
					.find( 'a' )
						.attr( 'href', support.url )
						.end()
					.removeClass( 'hide' );
			}
		},

		/**
		 * Loads and shows the translation helpers.
		 */
		showTranslationHelpers: function () {
			// API call to get translation suggestions from other languages
			// callback should render suggestions to the editor's info column
			var translateEditor = this,
				api = new mw.Api();

			api.get( {
				action: 'translationaids',
				title: this.message.title,
				format: 'json'
			} ).done( function ( result ) {
				translateEditor.$editor.find( '.infocolumn .loading' ).remove();

				if ( !result.helpers ) {
					mw.log( 'API did not return any translation helpers.' );
					return false;
				}

				translateEditor.showMessageDocumentation( result.helpers.documentation );
				translateEditor.showUneditableDocumentation( result.helpers.gettext );
				translateEditor.showAssistantLanguages( result.helpers.inotherlanguages );
				translateEditor.showTranslationMemory( result.helpers.ttmserver );
				translateEditor.showMachineTranslations( result.helpers.mt );
				translateEditor.showSupportOptions( result.helpers.support );
				translateEditor.addDefinitionDiff( result.helpers.definitiondiff );

				// Load the possible warnings as soon as possible, do not wait
				// for the user to make changes. Otherwise users might try confirming
				// translations which fail checks. Confirmation seems to work but
				// the message will continue to appear outdated.
				if ( translateEditor.message.properties &&
					translateEditor.message.properties.status === 'fuzzy'
				) {
					translateEditor.validateTranslation();
				}

				mw.translateHooks.run( 'showTranslationHelpers', result.helpers, translateEditor.$editor );
				mw.translateHooks.run( 'afterRegisterFeatures', translateEditor.$editor );

			} ).fail( function ( errorCode, results ) {
				mw.log( 'Error loading translation aids', errorCode, results );
			} );
		}
	};

	mw.translate = mw.translate || {};

	mw.translate = $.extend( mw.translate, {
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
				title: title + '/' + mw.config.get( 'wgTranslateDocumentationLanguageCode' )
			};

			return descUri.toString();
		}
	} );

	// Extend the translate editor
	$.extend( $.fn.translateeditor.Constructor.prototype, translateEditorHelpers );

}( jQuery, mediaWiki ) );
