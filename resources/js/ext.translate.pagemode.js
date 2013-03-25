( function ( $, mw ) {
	'use strict';

	function PageMode( element ) {
		this.$message = $( element );
		this.$container = $( '.tux-messagelist' );
		this.message = this.$message.data( 'message' );
		this.init();
		this.listen();
	}

	PageMode.prototype = {

		/**
		 * Initialize the plugin
		 */
		init: function () {
			var pagemode = this;

			this.render();

			pagemode.$message.translateeditor( {
				message: pagemode.message,
				beforeSave: function ( translation ) {
					pagemode.$message.find( '.tux-pagemode-translation' )
						.text( translation )
						.addClass( 'highlight' );
				},
				onSave: function ( translation ) {
					pagemode.$message.find( '.tux-pagemode-translation' )
						.removeClass( 'highlight' )
						.text( translation );
					pagemode.message.translation = translation;
				}
			} );

		},

		render: function () {
			var targetLanguage, targetLanguageDir, sourceLanguage, sourceLanguageDir;

			sourceLanguage = this.$container.data( 'sourcelangcode' );
			sourceLanguageDir = $.uls.data.getDir( sourceLanguage );
			targetLanguage = this.$container.data( 'targetlangcode' );
			targetLanguageDir = $.uls.data.getDir( targetLanguage );

			this.$message.append(
				$( '<div>' )
					.addClass( 'one column tux-pagemode-status ' + this.message.properties.status ),
				$( '<div>' )
					.addClass( 'five columns tux-pagemode-source' )
					.attr( {
						lang: sourceLanguage,
						dir: sourceLanguageDir
					} )
					.html( mw.translate.formatMessageGently( this.message.definition, this.message.key ) ),
				$( '<div>' )
					.addClass( 'five columns tux-pagemode-translation' )
					.attr( {
						lang: targetLanguage,
						dir: targetLanguageDir
					} )
					.html( mw.translate.formatMessageGently( this.message.translation || '', this.message.key ) ),
				$( '<div>' )
					.attr( 'title', mw.msg( 'tux-pagemode-edit-tooltip' ) )
					.addClass( 'tux-pagemode-edit' )
			)

			.addClass( this.message.properties.status );
		},

		/**
		 * Attach event listeners
		 */
		listen: function () {
			var pagemode = this;

			this.$message.find( '.tux-pagemode-edit' ).on( 'click', function () {
				pagemode.$message.data( 'translateeditor' ).show();
				return false;
			} );
		}
	};

	/*
	 * pagemode PLUGIN DEFINITION
	 */
	$.fn.pagemode = function ( options ) {
		return this.each( function () {
			var $this = $( this ),
				data = $this.data( 'pagemode' );

			if ( !data ) {
				$this.data( 'pagemode',
					( data = new PageMode( this, options ) )
				);
			}

		} );
	};

	$.fn.pagemode.Constructor = PageMode;

}( jQuery, mediaWiki ) );

