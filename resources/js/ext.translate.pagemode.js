( function () {
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
	 *     message: messageObject, // Mandatory message object
	 *     sourcelangcode: 'en', // Mandatory source language code
	 *     targetlangcode: 'hi' // Mandatory target language code
	 * } );
	 *
	 * @param {Element} element
	 * @param {Object} options
	 * @param {Object} options.message
	 * @param {string} options.sourcelangcode Language code.
	 * @param {string} options.targetlangcode Language code.
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
			var that = this;

			this.message.proofreadable = false;

			this.render();

			this.$message.translateeditor( {
				message: this.message,
				beforeSave: function ( translation ) {
					that.$message.find( '.tux-pagemode-translation' )
						.html( mw.translate.formatMessageGently( translation || '', that.message.key ) )
						.addClass( 'highlight' );
				},
				onSave: function ( translation ) {
					that.$message.find( '.tux-pagemode-translation' )
						.removeClass( 'highlight' );
					that.message.translation = translation;

					// `status` class is documented elsewhere
					// eslint-disable-next-line mediawiki/class-doc
					that.$message.find( '.tux-pagemode-status' )
						.removeClass( 'translated fuzzy proofread untranslated' )
						.addClass( that.message.properties.status );
				}
			} );

		},

		render: function () {
			var sourceLangDir = $.uls.data.getDir( this.options.sourcelangcode );

			var targetLangAttrib;
			if ( this.options.targetlangcode === mw.config.get( 'wgTranslateDocumentationLanguageCode' ) ) {
				targetLangAttrib = mw.config.get( 'wgContentLanguage' );
			} else {
				targetLangAttrib = this.options.targetlangcode;
			}

			var targetLangDir = $.uls.data.getDir( targetLangAttrib );

			// `status` class is documented elsewhere
			// eslint-disable-next-line mediawiki/class-doc
			this.$message.append(
				// `status` class is documented elsewhere
				// eslint-disable-next-line mediawiki/class-doc
				$( '<div>' )
					.addClass( 'row tux-message-item-compact message ' + this.message.properties.status )
					.append(
						// `status` class is documented elsewhere
						// eslint-disable-next-line mediawiki/class-doc
						$( '<div>' )
							.addClass( 'one column tux-pagemode-status ' + this.message.properties.status ),
						$( '<div>' )
							.addClass( 'five columns tux-pagemode-source' )
							.attr( {
								lang: this.options.sourcelangcode,
								dir: sourceLangDir
							} )
							.html( mw.translate.formatMessageGently( this.message.definition, this.message.key ) ),
						$( '<div>' )
							.addClass( 'five columns tux-pagemode-translation' )
							.attr( {
								lang: targetLangAttrib,
								dir: targetLangDir
							} )
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
			var that = this;

			this.$message.children( '.message' ).on( 'click', function ( e ) {
				that.$message.data( 'translateeditor' ).show();
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
				$this.data( 'pagemode', new PageMode( this, options ) );
			}
		} );
	};

	$.fn.pagemode.Constructor = PageMode;
}() );
