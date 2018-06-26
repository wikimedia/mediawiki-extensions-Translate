/*!
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

			$messageDescEditor.removeClass( 'hide' );
			$messageDescEditor.find( '.tux-textarea-documentation' ).focus();

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
		 *
		 * @return {jQuery.Promise}
		 */
		saveDocumentation: function () {
			var translateEditor = this,
				api = new mw.Api(),
				newDocumentation = translateEditor.$editor.find( '.tux-textarea-documentation' ).val();

			return api.postWithToken( 'csrf', {
				action: 'edit',
				title: translateEditor.message.title
					.replace( /\/[a-z-]+$/, '/' + mw.config.get( 'wgTranslateDocumentationLanguageCode' ) ),
				text: newDocumentation
			} ).done( function ( response ) {
				var $messageDesc = translateEditor.$editor.find( '.infocolumn-block .message-desc' );

				if ( response.edit.result === 'Success' ) {
					api.parse(
						newDocumentation
					).done( function ( parsedDocumentation ) {
						$messageDesc.html( parsedDocumentation );
					} ).fail( function ( errorCode, results ) {
						$messageDesc.html( newDocumentation );
						mw.log( 'Error parsing documentation ' + errorCode + ' ' + results.error.info );
					} );
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
		 *
		 * @param {Object} documentation A documentation object as returned by API.
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
				// Possible classes:
				// * mw-content-ltr
				// * mw-content-rtl
				// (The direction classes are needed, because the documentation
				// is likely to be MediaWiki-formatted text.)
				$messageDoc
					.prop( mw.translate.getLanguageProps( documentation.language ) )
					.addClass( 'mw-content-' + documentationDir )
					.html( documentation.html );

				$messageDoc.find( 'a[href]' ).prop( 'target', '_blank' );

				this.$editor.find( '.tux-textarea-documentation' )
					.prop( mw.translate.getLanguageProps( documentation.language ) )
					.val( documentation.value );

				$descEditLink.text( mw.msg( 'tux-editor-edit-desc' ) );

				if ( documentation.html.length > 500 ) {
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

					$messageDoc.addClass( 'long compact' ).on( 'mouseenter mouseleave', expand );
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
		 *
		 * @param {Object} documentation A gettext object as returned by API.
		 */
		showUneditableDocumentation: function ( documentation ) {
			if ( documentation.error ) {
				return;
			}

			this.$editor.find( '.uneditable-documentation' )
				.prop( mw.translate.getLanguageProps( documentation.language ) )
				.addClass( 'mw-content-' + dir )
				.html( documentation.html )
				.removeClass( 'hide' );
		},

		/**
		 * Shows the translations from other languages
		 *
		 * @param {Array} translations An inotherlanguages array as returned by the translation helpers API.
		 */
		showAssistantLanguages: function ( translations ) {
			var translateEditor = this;

			if ( translations.error ) {
				// Do not proceed if errored/unsupported
				return;
			}

			$.each( translations, function ( index ) {
				var $otherLanguage,
					translation = translations[ index ];

				$otherLanguage = $( '<div>' )
					.addClass( 'row in-other-language' )
					.append(
						$( '<div>' )
							.addClass( 'nine columns suggestiontext' )
							.prop( mw.translate.getLanguageProps( translation.language ) )
							.text( translation.value ),
						$( '<div>' )
							.addClass( 'three columns language text-right' )
							.prop( mw.translate.getLanguageProps( translation.language ) )
							.text( $.uls.data.getAutonym( translation.language ) )
					);

				translateEditor.suggestionAdder( $otherLanguage, translation.value );

				translateEditor.$editor.find( '.in-other-languages-title' )
					.removeClass( 'hide' )
					.after( $otherLanguage );
			} );
		},

		/**
		 * Shows the translation suggestions from Translation Memory
		 *
		 * @param {Array} suggestions A ttmserver array as returned by API.
		 */
		showTranslationMemory: function ( suggestions, targetLanguage ) {
			var $heading, $tmSuggestions,
				translateEditor = this;

			if ( !suggestions.length ) {
				return;
			}

			// Container for the suggestions
			$tmSuggestions = $( '<div>' ).addClass( 'tm-suggestions' );

			$heading = this.$editor.find( '.tm-suggestions-title' );
			$heading.after( $tmSuggestions );

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
							.prop( mw.translate.getLanguageProps( targetLanguage ) )
							.text( translation.target ),
						$( '<div>' )
							.addClass( 'three columns quality text-right' )
							.text( mw.msg( 'tux-editor-tm-match',
								mw.language.convertNumber( Math.floor( translation.quality * 100 ) ) ) ),
						$( '<div>' )
							.addClass( 'row text-right' )
							.append(
								$( '<a>' )
									.addClass( 'n-uses' )
									.data( 'n', 1 )
							)
					);

				translateEditor.suggestionAdder( $translation, translation.target );

				$tmSuggestions.append( $translation );
			} );

			// Show the heading only if we actually have suggestions
			if ( $tmSuggestions.length ) {
				$heading.removeClass( 'hide' );
			}
		},

		/**
		 * Shows the translation from machine translation systems
		 *
		 * @param {Array} suggestions
		 */
		showMachineTranslations: function ( suggestions, targetLanguage ) {
			var $mtSuggestions,
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

			$.each( suggestions, function ( index, translation ) {
				var $translation;

				$translation = $( '<div>' )
					.addClass( 'row tm-suggestion' )
					.append(
						$( '<div>' )
							.addClass( 'nine columns suggestiontext' )
							.prop( mw.translate.getProps( targetLanguage ) )
							.text( translation.target ),
						$( '<div>' )
							.addClass( 'three columns text-right service' )
							.text( translation.service )
					);

				translateEditor.suggestionAdder( $translation, translation.target );

				$mtSuggestions.append( $translation );
			} );
		},

		/**
		 * Makes the $source element clickable and clicking it will replace the
		 * translation textarea with the given suggestion.
		 *
		 * @param {jQuery} $source
		 * @param {string} suggestion Text to add
		 */
		suggestionAdder: function ( $source, suggestion ) {
			var inserter,
				$target = this.$editor.find( '.tux-textarea-translation' );

			inserter = function () {
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

			$source.on( 'click', inserter );
			$source.addClass( 'shortcut-activated' );
		},

		/**
		 * Shows the support options for the translator.
		 *
		 * @param {Object} support A support object as returned by API.
		 */
		showSupportOptions: function ( support ) {
			// Support URL
			if ( support.url ) {
				this.$editor.find( '.help a' ).attr( 'href', support.url );
				this.$editor.find( '.help' ).removeClass( 'hide' );
			}
		},

		/**
		 * Adds buttons for quickly inserting insertables.
		 *
		 * @param {Object} insertables A insertables object as returned by API.
		 */
		addInsertables: function ( insertables ) {
			var i,
				count = insertables.length,
				$sourceMessage = this.$editor.find( '.sourcemessage' ),
				$buttonArea = this.$editor.find( '.tux-editor-insert-buttons' ),
				$textarea = this.$editor.find( '.tux-textarea-translation' );

			for ( i = 0; i < count; i++ ) {
				// The dir and lang attributes must be set here,
				// because the language of the insertables is the language
				// of the source message and not of the translation.
				// The direction may appear confusing, for example,
				// in tvar strings, which would appear with the dollar sign
				// on the wrong end.
				$( '<button>' )
					.prop( {
						lang: $sourceMessage.prop( 'lang' ),
						dir: $sourceMessage.prop( 'dir' )
					} )
					.addClass( 'insertable shortcut-activated' )
					.text( insertables[ i ].display )
					.data( 'iid', i )
					.appendTo( $buttonArea );
			}

			$buttonArea.on( 'click', '.insertable', function () {
				var data = insertables[ $( this ).data( 'iid' ) ];
				$textarea.textSelection( 'encapsulateSelection', {
					pre: data.pre,
					post: data.post
				} );
				$textarea.focus().trigger( 'input' );
			} );

			this.resizeInsertables( $textarea );
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
				title: this.message.title
			} ).done( function ( result ) {
				var targetLanguage;

				translateEditor.$editor.find( '.infocolumn .loading' ).remove();

				if ( !result.helpers ) {
					mw.log( 'API did not return any translation helpers.' );
					return false;
				}

				targetLanguage = result.helpers.translation.language;

				translateEditor.showMessageDocumentation( result.helpers.documentation );
				translateEditor.showUneditableDocumentation( result.helpers.gettext );
				translateEditor.showAssistantLanguages( result.helpers.inotherlanguages );
				translateEditor.showTranslationMemory( result.helpers.ttmserver, targetLanguage );
				translateEditor.showMachineTranslations( result.helpers.mt );
				translateEditor.showSupportOptions( result.helpers.support );
				translateEditor.addDefinitionDiff( result.helpers.definitiondiff );
				translateEditor.addInsertables( result.helpers.insertables );

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
		 * @param {string} title Message title with namespace
		 * @return {string} URL for editing the documentation
		 */
		getDocumentationEditURL: function ( title ) {
			return mw.util.getUrl(
				title + '/' + mw.config.get( 'wgTranslateDocumentationLanguageCode' ),
				{ action: 'edit' }
			);
		}
	} );

	// Extend the translate editor
	mw.translate.editor = mw.translate.editor || {};
	$.extend( mw.translate.editor, translateEditorHelpers );

}( jQuery, mediaWiki ) );
