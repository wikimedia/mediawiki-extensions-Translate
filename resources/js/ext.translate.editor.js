( function ( $, mw ) {
	'use strict';

	function TranslateEditor( element ) {
		this.$editTrigger = $( element );
		this.$editor = null;
		this.$messageItem = this.$editTrigger.find( '.tux-message-item' );
		this.shown = false;
		this.expanded = false;
		this.translateDocumentationLanguageCode =
			mw.config.get( 'wgTranslateDocumentationLanguageCode' );
		this.listen();
	}

	TranslateEditor.prototype = {

		/**
		 * TODO: Looong method- refactor!
		 * Initialize the plugin
		 */
		init: function () {
			var $editorColumn,
				$infoColumn;

			$editorColumn = this.prepareEditorColumn();
			$infoColumn = this.prepareInfoColumn();

			this.$editor = $( '<div>' )
				.addClass( 'row tux-message-editor' )
				.append( $editorColumn, $infoColumn );

			this.expanded = false;
			this.$editTrigger.append( this.$editor );
			this.$editor.hide();

			this.getTranslationSuggestions();
			this.getTranslationMemorySuggestions();
		},

		prepareEditorColumn: function () {
			var translateEditor = this,
				$editorColumn,
				$messageKeyLabel,
				$textArea,
				$saveButton,
				$skipButton,
				$sourceString,
				$closeIcon,
				$infoToggleIcon;

			$editorColumn = $( '<div>' )
				.addClass( 'seven columns editcolumn' );

			$messageKeyLabel = $( '<div>' )
				.addClass( 'ten columns text-left messagekey' )
				.text( this.$editTrigger.data( 'title' ) );

			$closeIcon = $( '<span>' )
				.addClass( 'one column close' )
				.on( 'click', function () {
					translateEditor.hide();
				} ); // TODO: refactor event handler

			$infoToggleIcon = $( '<span>' )
				// Initially the editor column is contracted,
				// so show the expand button first
				.addClass( 'one column editor-info-toggle editor-expand' )
				.on( 'click', function () {
					translateEditor.infoToggle( $( this ) );
				} ); // TODO: refactor event handler

			$editorColumn.append( $( '<div>' )
				.addClass( 'row' )
				.append( $messageKeyLabel, $infoToggleIcon, $closeIcon )
			);

			$sourceString = $( '<span>' )
				.addClass( 'eleven column sourcemessage' )
				.text( this.$editTrigger.data( 'source' ) );

			$editorColumn.append( $( '<div>' )
				.addClass( 'row' )
				.append( $sourceString )
			);

			$textArea = $( '<textarea>' )
				.attr( {
					'placeholder': mw.msg( 'tux-editor-placeholder' )
				} )
				.addClass( 'eleven columns' );

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
					'title': mw.util.tooltipAccessKeyPrefix + 's'
				} )
				.addClass( 'three columns offset-by-one blue button' );
			// TODO: add click handler for this button

			$skipButton = $( '<button>' )
				.text( mw.msg( 'tux-editor-skip-button-label' ) )
				.attr( {
					'accesskey': 'd',
					'title': mw.util.tooltipAccessKeyPrefix + 'd'
				} )
				.addClass( 'three columns offset-by-one button' );
			// TODO: add click handler for this button

			$editorColumn.append( $( '<div>' )
				.addClass( 'row' )
				.append( $saveButton, $skipButton )
			);

			$editorColumn.append( $( '<span>' )
				.addClass( 'row text-left shortcutinfo' )
				.text( mw.msg( 'tux-editor-shortcut-info',
					$saveButton.attr( 'title' ).toUpperCase(),
					$skipButton.attr( 'title' ).toUpperCase() )
				)
			);

			return $editorColumn;
		},

		prepareInfoColumn: function () {
			var $infoColumn;

			$infoColumn = $( '<div>' )
			.addClass( 'five columns infocolumn' );

			$infoColumn.append( $( '<div>' )
				.addClass( 'row text-left message-desc' )
				.text( mw.msg( 'tux-editor-no-message-doc' ) )
			);

			// By default translateDocumentationLanguageCode is false.
			// It's defined as the MediaWiki global $wgTranslateDocumentationLanguageCode.
			if ( this.translateDocumentationLanguageCode ) {
				$infoColumn.append( $( '<div>' )
					.addClass( 'row text-left message-desc-edit' )
					.append( $( '<a>' )
						.attr( {
							href: ( new mw.Uri( window.location.href ) ).extend( {
									language: this.translateDocumentationLanguageCode
								} ).toString(), // FIXME: this link is not correct
							target: '_blank'
						} )
						.text( mw.msg( 'tux-editor-edit-desc' ) ) )
				);
			}

			$infoColumn.append( $( '<div>' )
				.addClass( 'row text-left tm-suggestions-title' )
				.text( mw.msg( 'tux-editor-suggestions-title' ) )
			);


			$infoColumn.append( $( '<div>' )
				.addClass( 'row text-left in-other-languages-title' )
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

			return $infoColumn;
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
				apiURL;

			queryParams = {
				action: 'query',
				meta: 'messagetranslations',
				mttitle: this.$editTrigger.data( 'title' ),
				format: 'json'
			};

			apiURL = mw.util.wikiScript( 'api' );

			$.get( apiURL, queryParams ).done( function ( result ) {
				var translations;

				if ( result.query ) {
					translations = result.query.messagetranslations;
					$.each( translations, function ( index ) {
						var translation,
							$otherLanguage;

						translation = translations[index];

						if ( translation.language === this.translateDocumentationLanguageCode ) {
							translateEditor.$editor.find( '.message-desc' )
								.text( translation['*'] );
						} else if ( translation.language !== translateEditor.$editTrigger.attr( 'lang' ) ) {
							$otherLanguage = $( '<div>' )
								.addClass( 'row in-other-language' )
								.append(
									$( '<div>' )
										.addClass( 'nine columns' )
										.text( translation['*'] ),
									$( '<div>' )
										.addClass( 'three columns language text-right' )
										.text( $.uls.data.getAutonym( translation.language ) )
								);

							translateEditor.$editor.find( '.in-other-languages-title' )
								.after( $otherLanguage );
						}
					} );
				}
			} ).fail( function ( ) {
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
				sourcelanguage: 'en', //FIXME: dont hardcode it
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
									.text( mw.msg( 'tux-editor-tm-match', suggestion.quality*100 ) )
								);

						translateEditor.$editor.find( '.tm-suggestions-title' )
							.after( $suggestion );
					} );
				}
			} ).fail( function ( ) {
				// what to do?
			} );
		},

		/**
		 * Attach event listeners
		 */
		listen: function () {
			var translateEditor = this;

			this.$editTrigger.dblclick( function () {
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
