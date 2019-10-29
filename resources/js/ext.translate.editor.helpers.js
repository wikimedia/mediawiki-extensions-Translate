/*!
 * Translate editor additional helper functionality
 */
( function () {
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
				langAttr,
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
				langAttr = {
					lang: documentation.language,
					dir: documentationDir
				};

				// Possible classes:
				// * mw-content-ltr
				// * mw-content-rtl
				// (The direction classes are needed, because the documentation
				// is likely to be MediaWiki-formatted text.)
				$messageDoc
					.attr( langAttr )
					.addClass( 'mw-content-' + documentationDir )
					.html( documentation.html );

				$messageDoc.find( 'a[href]' ).prop( 'target', '_blank' );

				this.$editor.find( '.tux-textarea-documentation' )
					.attr( langAttr )
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
				var $otherLanguage, langAttr,
					translation = translations[ index ];

				langAttr = {
					lang: translation.language,
					dir: $.uls.data.getDir( translation.language )
				};

				$otherLanguage = $( '<div>' )
					.addClass( 'row in-other-language' )
					.append(
						$( '<div>' )
							.addClass( 'nine columns suggestiontext' )
							.attr( langAttr )
							.text( translation.value ),
						$( '<div>' )
							.addClass( 'three columns language text-right' )
							.attr( langAttr )
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
		 * @param {Array} translations A ttmserver array as returned by API.
		 */
		showTranslationMemory: function ( translations ) {
			var $heading, $tmSuggestions, $messageList, lang, dir,
				suggestions = {};

			if ( !translations.length ) {
				return;
			}

			// Container for the suggestions
			$tmSuggestions = $( '<div>' ).addClass( 'tm-suggestions' );

			$heading = this.$editor.find( '.tm-suggestions-title' );
			$heading.after( $tmSuggestions );

			$messageList = $( '.tux-messagelist' );
			lang = $messageList.data( 'targetlangcode' );
			dir = $messageList.data( 'targetlangdir' );

			translations.forEach( function ( translation ) {
				var suggestion;

				// Remove once formatversion=2
				if ( translation.local === '' ) {
					translation.local = true;
				} else if ( translation.local === undefined ) {
					translation.local = false;
				}

				if ( translation.local && translation.location === this.message.title ) {
					// Do not add self-suggestions
					return;
				}

				// Check if suggestion with this value already exists
				suggestion = suggestions[ translation.target ];
				if ( suggestion ) {
					suggestion.count++;
					suggestion.sources.push( translation );
					suggestion.$showSourcesElement.children( 'a' ).text(
						mw.msg(
							'tux-editor-n-uses',
							mw.language.convertNumber( suggestion.count )
						) + '  〉'
					);

					return;
				}

				suggestion = {};

				suggestion.$showSourcesElement = $( '<div>' )
					.addClass( 'row text-right' )
					.append( $( '<a>' ).addClass( 'n-uses' ) );

				suggestion.$element = $( '<div>' )
					.addClass( 'row tm-suggestion' )
					.append(
						$( '<div>' )
							.addClass( 'nine columns suggestiontext' )
							.attr( {
								lang: lang,
								dir: dir
							} )
							.text( translation.target ),
						$( '<div>' )
							.addClass( 'three columns quality text-right' )
							.text(
								mw.msg(
									'tux-editor-tm-match',
									mw.language.convertNumber( Math.floor( translation.quality * 100 ) )
								)
							),
						suggestion.$showSourcesElement
					);

				suggestion.count = 1;
				suggestion.sources = [];
				suggestion.sources.push( translation );

				this.suggestionAdder( suggestion.$element, translation.target );

				suggestions[ translation.target ] = suggestion;
			}, this );

			if ( $.isEmptyObject( suggestions ) ) {
				return;
			}

			Object.keys( suggestions ).forEach( function ( key ) {
				var suggestion = suggestions[ key ];

				suggestion.$showSourcesElement.on( 'click', function ( e ) {
					this.onShowTranslationMemorySources( e, suggestion );
				}.bind( this ) );
				$tmSuggestions.append( suggestion.$element );
			}, this );

			$heading.removeClass( 'hide' );
		},

		onShowTranslationMemorySources: function ( e, suggestion ) {
			e.stopPropagation();

			if ( suggestion.$sourcesElement ) {
				suggestion.$sourcesElement.toggle();
				return;
			}

			// Build the sources list. Add class to show external icons :(
			suggestion.$sourcesElement = $( '<ul>' )
				.addClass( 'tux-tm-suggestion-source mw-parser-output' );

			// Sort local suggestions first, then alphabetically
			suggestion.sources.sort( function ( a, b ) {
				if ( a.local === b.local ) {
					return a.location.localeCompare( b.location );
				} else {
					return a.local ? -1 : 1;
				}
			} );

			suggestion.sources.forEach( function ( translation ) {
				suggestion.$sourcesElement.append(
					$( '<li>' )
						.append(
							$( '<a>' )
								.prop( 'target', '_blank' )
								.prop( 'href', translation.editorUrl || translation.uri )
								.text( translation.location )
								.toggleClass( 'external', !translation.local )
						)
				);
			} );
			suggestion.$element.after( suggestion.$sourcesElement );
		},

		/**
		 * Shows the translation from machine translation systems
		 *
		 * @param {Array} suggestions
		 */
		showMachineTranslations: function ( suggestions ) {
			var $mtSuggestions, $messageList, translationLang, translationDir,
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

			$messageList = $( '.tux-messagelist' );
			translationLang = $messageList.data( 'targetlangcode' );
			translationDir = $messageList.data( 'targetlangdir' );

			$.each( suggestions, function ( index, translation ) {
				var $translation;

				$translation = $( '<div>' )
					.addClass( 'row tm-suggestion' )
					.append(
						$( '<div>' )
							.addClass( 'nine columns suggestiontext' )
							.attr( {
								lang: translationLang,
								dir: translationDir
							} )
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
			var api = new mw.Api();

			api.get( {
				action: 'translationaids',
				title: this.message.title
			} ).done( function ( result ) {
				this.$editor.find( '.infocolumn .loading' ).remove();

				if ( !result.helpers ) {
					mw.log.warn( 'API did not return any translation helpers.' );
					return false;
				}

				this.showMessageDocumentation( result.helpers.documentation );
				this.showUneditableDocumentation( result.helpers.gettext );
				this.showAssistantLanguages( result.helpers.inotherlanguages );
				this.showTranslationMemory( result.helpers.ttmserver );
				this.showMachineTranslations( result.helpers.mt );
				this.showSupportOptions( result.helpers.support );
				this.addDefinitionDiff( result.helpers.definitiondiff );
				this.addInsertables( result.helpers.insertables );

				// Load the possible warnings as soon as possible, do not wait
				// for the user to make changes. Otherwise users might try confirming
				// translations which fail checks. Confirmation seems to work but
				// the message will continue to appear outdated.
				if ( this.message.properties &&
					this.message.properties.status === 'fuzzy'
				) {
					this.validateTranslation();
				}

				mw.hook( 'mw.translate.editor.showTranslationHelpers' ).fire(
					result.helpers, this.$editor
				);

			}.bind( this ) ).fail( function ( errorCode, results ) {
				this.$editor.find( '.infocolumn .loading' ).remove();
				this.$editor.find( '.infocolumn' ).append(
					$( '<div>' )
						.text( mw.msg( 'tux-editor-loading-failed', results.error.info ) )
						.addClass( 'warningbox tux-translation-aid-error' )
				);
				mw.log.error( 'Error loading translation aids:', errorCode, results );
			}.bind( this ) );
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

}() );
