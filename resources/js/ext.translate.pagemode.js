( function ( $, mw ) {
	'use strict';
	/**
	 * Page mode plugin
	 *
	 * Prepare the page mode UI with all the required actions
	 * for a translation unit (message).
	 * This is mainly used with the messagetable plugin in page mode,
	 * but it is independent of messagetable.
	 * Example usage:
	 *
	 * $( 'div.pagemode' ).pagemode( {
	 *	message: messageObject, // Mandatory message object
	 *	sourcelangcode: 'en', // Mandatory source language code
	 *	targetlangcode: 'hi' // Mandatory target language code
	 * } );
	 */
	function PageMode( element, options ) {
		this.$message = $( element );
		this.options = options;
		this.message = this.options.message;
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
						.html( mw.translate.formatMessageGently( translation || '', pagemode.message.key ) )
						.addClass( 'highlight' );
				},
				onSave: function ( translation ) {
					pagemode.$message.find( '.tux-pagemode-translation' )
						.removeClass( 'highlight' );
					pagemode.message.translation = translation;
				}
			} );

		},

		render: function () {
			var targetLanguage, targetLanguageDir, sourceLanguage, sourceLanguageDir;

			sourceLanguage = this.options.sourcelangcode;
			sourceLanguageDir = $.uls.data.getDir( sourceLanguage );
			targetLanguage = this.options.targetlangcode;
			targetLanguageDir = $.uls.data.getDir( targetLanguage );

			this.$message.append(
				$( '<div>' )
					.addClass( 'row tux-message-item-compact message' )
					.append(
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
							.attr( 'title', mw.msg( 'translate-edit-title', this.message.key ) )
							.addClass( 'tux-pagemode-edit' )
					)
			)

			.addClass( this.message.properties.status );
		},

		/**
		 * Attach event listeners
		 */
		listen: function () {
			var pagemode = this;

			this.$message.children( '.message' ).on( 'click', function ( e ) {
				pagemode.$message.data( 'translateeditor' ).show();
				e.preventDefault();
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

