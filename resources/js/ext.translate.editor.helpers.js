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
			$messageDescEditor.removeClass( 'hide' );

			$messageDescEditor.find( 'textarea' ).focus();

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

			// XXX: Any validations to be done before proceeding?
			api.post( {
				action: 'edit',
				title: translateEditor.message.title
					.replace( /\/[a-z\-]+$/, '/' + mw.config.get( 'wgTranslateDocumentationLanguageCode' ) ),
				text: newDocumentation,
				token: mw.user.tokens.get( 'editToken' )
			} ).done(function ( response ) {
				var $messageDesc = translateEditor.$editor.find( '.infocolumn-block .message-desc' );

				if ( response.edit.result === 'Success' ) {
					api.parse( newDocumentation,
						function ( parsedDocumentation ) {
							$messageDesc.html( parsedDocumentation );
						},
						function ( errorCode, results ) {
							$messageDesc.html( newDocumentation );
							// TODO
							mw.log( 'Error parsing documentation ' + errorCode + ' ' + results );
						}
					);

					translateEditor.hideDocumentationEditor();
				} else {
					// TODO
					mw.log( 'Problem saving documentation' );
				}
			} ).fail( function ( errorCode, results ) {
				// TODO better handling is needed
				mw.log( 'Error saving documentation ' + errorCode + ' ' + results.error.info );
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
			if ( documentation.value ) {
				documentationDir = $.uls.data.getDir( documentation.language );
				// Show the documentation and set appropriate
				// lang and dir attributes.
				// The message documentation is assumed to be written
				// in the content language of the wiki.
				$messageDoc
					.attr( {
						lang: documentation.language,
						dir: documentationDir
					} )
					.addClass( documentationDir ) // hack
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
			} else {
				$messageDoc.text( mw.msg( 'tux-editor-no-message-doc' ) );
				$descEditLink.text( mw.msg( 'tux-editor-add-desc' ) );
			}

			$messageDescViewer.removeClass( 'hide' );
		},

		/**
		 * Shows the machine translations.
		 * @param {array} translations An inotherlanguages array as returned by the translation helpers API.
		 */
		showAssistantLanguages: function ( translations ) {
			var translateEditor = this;

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
		},

		/**
		 * Shows the message documentation.
		 * @param {array} suggestions A ttmserver array as returned by API.
		 */
		showTranslationMemory: function ( suggestions ) {
			var $tmSuggestions, $translationTextarea;

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

				// See if it is already listed, and increment use count
				$tmSuggestions.find( '.tm-suggestion' ).each( function () {
					var $sug = $( this ), $uses, count;
					if ( $sug.find( '.suggestiontext ' ).text() === translation.target ) {
						// Update the message and data value
						$uses = $sug.find( '.n-uses' );
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
							.addClass( 'row tm-suggestion-top' )
							.append(
								$( '<div>' )
									.addClass( 'nine columns suggestiontext' )
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
										$translationTextarea
											.val( translation.target )
											.trigger( 'input' );
									} ),
								$( '<a>' )
									.addClass( 'three columns n-uses text-right' )
									.data( 'n', 1 )
							)
					);

				$tmSuggestions.append( $translation );
			} );
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
			} ).done(function ( result ) {
					// TODO This may be an error that must be handled
					if ( !result.helpers ) {
						mw.log( 'API did not return any translation helpers.' );
						return false;
					}

					translateEditor.showMessageDocumentation( result.helpers.documentation );
					translateEditor.showAssistantLanguages( result.helpers.inotherlanguages );
					translateEditor.showTranslationMemory( result.helpers.ttmserver );
					translateEditor.showSupportOptions( result.helpers.support );
					translateEditor.addDefinitionDiff( result.helpers.definitiondiff );
			} ).fail( function ( errorCode, results ) {
					// TODO: proper handling is needed
					mw.log( 'Error loading translation aids' + errorCode + results.error.info );
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
