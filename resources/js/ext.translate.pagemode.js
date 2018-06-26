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
	 *	message: messageObject // Mandatory message object
	 * } );
	 *
	 * @param {Element} element
	 * @param {Object} options
	 * @cfg {Object} message
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

					pagemode.$message.find( '.tux-pagemode-status' )
						.removeClass( 'translated fuzzy proofread untranslated' )
						.addClass( pagemode.message.properties.status );
				}
			} );

		},

		render: function () {
			this.$message.append(
				$( '<div>' )
					.addClass( 'row tux-message-item-compact message ' + this.message.properties.status )
					.append(
						$( '<div>' )
							.addClass( 'one column tux-pagemode-status ' + this.message.properties.status ),
						$( '<div>' )
							.addClass( 'five columns tux-pagemode-source' )
							.prop( mw.translate.getLanguageProps( this.message.sourceLanguage ) )
							.html( mw.translate.formatMessageGently( this.message.definition, this.message.key ) ),
						$( '<div>' )
							.addClass( 'five columns tux-pagemode-translation' )
							.prop( mw.translate.getLanguageProps( this.message.targetLanguage ) )
							.html( mw.translate.formatMessageGently( this.message.translation || '', this.message.key ) ),
						$( '<div>' )
							.attr( 'title', mw.msg( 'translate-edit-title', this.message.key ) )
							.addClass( 'tux-pagemode-edit' )
					)
			).addClass( this.message.properties.status );
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
