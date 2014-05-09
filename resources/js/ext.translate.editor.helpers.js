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
				.find( '.tux-textarea-documentation' )
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
				deferred = new $.Deferred(),
				newDocumentation = translateEditor.$editor.find( '.tux-textarea-documentation' ).val();

			deferred = api.post( {
				action: 'edit',
				title: translateEditor.message.title
					.replace( /\/[a-z\-]+$/, '/' + mw.config.get( 'wgTranslateDocumentationLanguageCode' ) ),
				text: newDocumentation,
				token: mw.user.tokens.get( 'editToken' )
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
			return deferred.promise();
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

				this.$editor.find( '.tux-textarea-documentation' )
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

			$translationTextarea = this.$editor.find( '.tux-textarea-translation' );

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

				translateEditor.suggestionAdder( $otherLanguage, translation.value );

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
		 * @param {array} suggestions
		 */
		showMachineTranslations: function ( suggestions ) {
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
		 * transltion textarea with the given suggestion.
		 *
		 * @param {jQuery} $source
		 * @param {String} suggestion Text to add
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
		 * Adds buttons for quickly inserting insertables.
		 * @param {object} insertables A insertables object as returned by API.
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
						dir: $sourceMessage.prop( 'dir' ),
						lang: $sourceMessage.prop( 'lang' )
					} )
					.addClass( 'insertable shortcut-activated' )
					.text( insertables[i].display )
					.data( 'iid', i )
					.appendTo( $buttonArea );
			}

			$buttonArea.on( 'click', '.insertable', function () {
				var data = insertables[$( this ).data( 'iid' )];
				$textarea.textSelection( 'encapsulateSelection', {
					pre: data.pre,
					post: data.post
				} );
				$textarea.focus().trigger( 'input' );
			} );
		},

		/**
		 * XXX
		 * @param {object} XXX
		 */
		addTerms: function ( terms ) {
			var i, text, start, middle, end, replacement, offsetchange = 0,
				count = terms.length,
				$sourceMessage = this.$editor.find( '.sourcemessage' ),
				$buttonArea = this.$editor.find( '.tux-editor-insert-buttons' ),
				$textarea = this.$editor.find( '.tux-textarea-translation' );


			if ( terms.error ) {
				return;
			}

			text = $sourceMessage.html();
			for ( i = 0; i < count; i++ ) {
				start = text.substring( 0, offsetchange + terms[i].range[0] );
				middle = text.substring( offsetchange + terms[i].range[0], terms[i].range[1] );
				end = text.substr( offsetchange + terms[i].range[1] );
				replacement = $( '<span>' )
					.css( 'background-color', '#F0F0F0' )
					.html( middle )
					.click( function () {
						$textarea.textSelection( 'encapsulateSelection', {
							pre: 'translation goes here'
						} );
					} );
				offsetchange += replacement[0].outerHTML - middle;
				$sourceMessage.empty().append( start, replacement, end );
			}

			console.log( text );


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
				translateEditor.addInsertables( result.helpers.insertables );
				translateEditor.addTerms( result.helpers.terminology );

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
