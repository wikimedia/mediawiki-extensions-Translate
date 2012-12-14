( function ( $, mw ) {
	'use strict';

	function TranslateEditor( element ) {
		this.$editTrigger = $( element );
		this.$editor = null;
		this.$messageItem = this.$editTrigger.find( '.tux-message-item' );
		this.shown = false;
		this.expanded = false;
		this.listen();
	}

	TranslateEditor.prototype = {

		/**
		 * TODO: Looong method- refactor!
		 * Initialize the plugin
		 */
		init: function () {
			var translateEditor = this,
				$editorColumn,
				$infoColumn,
				$messageKeyLabel,
				$textArea,
				$saveButton,
				$skipButton,
				$sourceString,
				$editorControlIcons,
				$closeIcon,
				$infoToggleIcon;

			$editorColumn = $( '<div>' )
				.addClass( 'seven columns editcolumn' );

			$messageKeyLabel = $( '<div>' )
				.addClass( 'ten columns text-left messagekey' )
				.text( this.$editTrigger.data( 'title' ) );

			$closeIcon = $( '<span>' )
				.addClass( 'close' )
				.on( 'click', function () {
					translateEditor.hide();
				} ); // TODO: refactor event handler

			$infoToggleIcon = $( '<span>' )
				// Initially the editor column is contracted,
				// so show the expand button first
				.addClass( 'editor-info-toggle editor-expand' )
				.on( 'click', function () {
					translateEditor.infoToggle( $( this ) );
				} ); // TODO: refactor event handler

			$editorControlIcons = $( '<div>' )
				.addClass( 'two columns editor-control-icons text-right' )
				.append( $infoToggleIcon, $closeIcon );

			$editorColumn.append( $( '<div>' )
				.addClass( 'row' )
				.append( $messageKeyLabel, $editorControlIcons )
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
					'placeholder': 'Your translation' //FIXME i18n
				} )
				.addClass( 'eleven columns' );

			$editorColumn.append( $( '<div>' )
				.addClass( 'row' )
				.append( $textArea )
			);

			$saveButton = $( '<button>' )
				.text( 'Save translation' ) //FIXME i18n
				.attr( {
					'accesskey': 's',
					'title': mw.util.tooltipAccessKeyPrefix + 's'
				} )
				.addClass( 'three columns offset-by-one blue button' );
			// TODO: add click handler for this button

			$skipButton = $( '<button>' )
				.text( 'Skip to next' ) //FIXME i18n
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
				.text( 'Press "CTRL+S" to save or "CTRL+D" to skip to next message' ) //FIXME i18n
			);

			$infoColumn = $( '<div>' )
				.addClass( 'five columns infocolumn' );

			$infoColumn.append( $( '<div>' )
				.addClass( 'row text-left message-desc' )
				.text( 'Message documentation goes here' ) //FIXME i18n
			);

			$infoColumn.append( $( '<div>' )
				.addClass( 'row text-left tm-suggestions' )
				.text( 'Translation memory suggestions' ) //FIXME i18n
			);

			$infoColumn.append( $( '<div>' )
				.addClass( 'row text-left help' )
				.text( 'Need more help? Ask for more information' ) //FIXME i18n
			);

			this.$editor = $( '<div>' )
				.addClass( 'row tux-message-editor' );

			this.$editor.append( $editorColumn, $infoColumn );
			this.expanded = false;
			this.$editTrigger.append( this.$editor );
			this.$editor.hide();

			this.getTranslationSuggestions();
			this.getMessageDocumentation();
		},

		show: function () {
			if ( !this.$editor ) {
				this.init();
			}

			// Hide all other editors in the page
			$( '.tux-message' ).data( 'translateeditor' ).hide();
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

		infoToggle: function( toggleIcon ) {
			if ( this.expanded ) {
				this.contract( toggleIcon );
			} else {
				this.expand( toggleIcon );
			}
		},

		contract: function( toggleIcon ) {
			// Change the icon image
			toggleIcon.removeClass( 'editor-contract' );
			toggleIcon.addClass( 'editor-expand' );

			this.$editor.find( 'div.infocolumn' ).show();
			this.$editor.find( 'div.editcolumn' )
				.removeClass( 'twelve' )
				.addClass( 'seven' );

			this.expanded = false;
		},

		expand: function( toggleIcon ) {
			// Change the icon image
			toggleIcon.removeClass( 'editor-expand' );
			toggleIcon.addClass( 'editor-contract' );

			this.$editor.find( 'div.infocolumn' ).hide();
			this.$editor.find( 'div.editcolumn' )
				.removeClass( 'seven' )
				.addClass( 'twelve' );

			this.expanded = true;
		},

		getMessageDocumentation: function() {
			// API call to get message documentation.
			// callback should render message documentation to the editor's info column
		},

		getTranslationSuggestions: function() {
			// API call to get translation memory suggestions.
			// callback should render suggestions to the editor's info column
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
