( function ( $ ) {
	'use strict';

	function TranslateEditor( element, options ) {
		this.$editTrigger = $( element );
		this.$editor = null;
		this.$messageItem = this.$editTrigger.find( '.tux-message-item' );
		this.shown = false;
		this.listen();
	}

	TranslateEditor.prototype = {
		constructor: TranslateEditor,

		/**
		 * TODO: Looong method- refactor!
		 * Initialize the plugin
		 */
		init: function () {
			var $editorColumn,
				$infoColumn,
				$messageKeyLabel,
				$textArea,
				$saveButton,
				$skipButton,
				$sourceString,
				$closeIcon;

			$editorColumn = $( '<div>' )
				.addClass( 'six columns editcolumn' );

			$messageKeyLabel= $( '<div>' )
				.addClass( 'eleven columns text-left messagekey' )
				.text( this.$editTrigger.data( 'title' ) );

			$closeIcon = $( '<div>' )
			.addClass( 'one column close text-right' );

			$editorColumn.append( $( '<div>' )
				.addClass( 'row' )
				.append( $messageKeyLabel, $closeIcon )
				);
			$sourceString =  $( '<span>' )
				.addClass( 'eleven column sourcemessage' )
				.text( this.$editTrigger.data( 'source' ) );

			$editorColumn.append( $( '<div>' )
					.addClass( 'row' )
					.append( $sourceString )
					);

			$textArea = $( '<textarea>' )
				.attr( {
					'placeholder' : 'Your translation' //FIXME i18n
				} )
				.addClass( 'eleven columns' );

			$editorColumn.append( $( '<div>' )
				.addClass( 'row' )
				.append( $textArea )
				);

			$saveButton = $( '<button>' )
				.text( 'Save translation' ) //FIXME i18n
				.addClass( 'three columns offset-by-one blue button' );

			$skipButton = $( '<button>' )
				.text( 'Skip to next' ) //FIXME i18n
				.addClass( 'three columns offset-by-one button' );

			$editorColumn.append( $( '<div>' )
				.addClass( 'row' )
				.append( $saveButton, $skipButton )
				);

			$editorColumn.append( $( '<span>' )
				.addClass( 'row text-left shortcutinfo' )
				.text( 'Press "CTRL+S" to save or "CTRL+D" to skip to next message' ) //FIXME i18n
			);

			$infoColumn = $( '<div>' )
			.addClass( 'six columns infocolumn' );

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

			this.$editor.append( $editorColumn , $infoColumn );
			this.$editTrigger.append( this.$editor );
			this.$editor.hide();
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
			this.$editor && this.$editor.hide();
			this.$messageItem.show();
			this.shown = false;
			return false;
		},

		/**
		 * Attach event listeners
		 */
		listen: function () {
			var translateEditor = this;

			this.$editTrigger.dblclick( function ( e ) {
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

	$.fn.translateeditor.Constructor = TranslateEditor;


}( jQuery ) );
