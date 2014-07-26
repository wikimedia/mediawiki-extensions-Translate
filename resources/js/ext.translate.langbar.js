( function ( $, mw ) {
	'use strict';

	$( document ).ready( function () {
		var getUrl = mw.util.getUrl || mw.util.wikiGetlink,
			common = mw.config.get( 'wgCommonLanguages' ),
			key = "languagebar",
			defaultVal = { hide: false },
			value = $.jStorage.get( key, defaultVal ),
			$langbarMin = $( '.mw-translate-langbar-min' ),
			$langbarContainer = $( '.mw-translate-langbar-container' );

		// Keep the language bar collapsed across pages if hidden by user
		if ( $.jStorage.storageAvailable() ) {
			if ( value.hide === true ) {
				$langbarContainer.hide();
			}

			$langbarMin.click( function() {
				value.hide = $langbarContainer.is( ':visible' );
				$.jStorage.set( key, value );
			} );
		}

		$langbarMin.click( function() {
			$langbarContainer.toggle();
		} );

		$( '.mw-translate-viewmore' ).uls( {
			compact: true,
			quickList: common,
			onSelect: function( lang ) {
				var page, uri;

				if ( common.indexOf( lang ) !== -1 ) {
						page =  mw.config.get( 'wgPageBaseTitle' );
						uri = new mw.Uri( getUrl( page + '/'+ lang ) );
				} else {
					page =  mw.config.get( 'wgMessageGroupId' );
					uri = new mw.Uri( getUrl( 'Special:Translate' ) );
					uri.extend( {
						group: page,
						language: lang,
						action: 'page',
						filter: ''
					} );
				}
				window.location.href = uri.toString();
			}
		} );

//		mw.uls.eventlogger = new LanguageBarLogger();
	} );
	/**
	 * ULS Event logger
	 *
	 * @since 2013.08
	 * @see https://meta.wikimedia.org/wiki/Schema:UniversalLanguageSelector
	 */
	function LanguageBarLogger() {
		this.logEventQueue = $.Callbacks( 'memory once' );
		this.init();
		this.listen();
	}

	LanguageBarLogger.prototype = {
		init: function () {
			var eventLogger = this;

			mw.eventLog.setDefaults( 'LanguageBar', {
				enableToolbar: true
			} );

			eventLogger.logEventQueue.fire();
		},

		/**
		 * Local wrapper for 'mw.eventLog.logEvent'
		 *
		 * @param {Object} event Event action and optional fields
		 * @param {String} schema The schema; 'UniversalLanguageSelector' is the default
		 * @return {jQuery.Promise} jQuery Promise object for the logging call
		 */
		log: function ( event, schema ) {
			// We need to create our own deferred for two reasons:
			//  - logEvent might not be executed immediately
			//  - we cannot reject a promise returned by it
			// So we proxy the original promises status updates.
			var deferred = $.Deferred();

			schema = schema || 'LanguageBar';

			this.logEventQueue.add( function () {
				mw.eventLog.logEvent( schema, event )
					.done( deferred.resolve )
					.fail( deferred.reject );
			} );

			return deferred.promise();
		},

		/**
		 * Listen for event logging
		 */
		listen: function () {
			// Register handlers for event logging triggers
			mw.hook( 'mw.translate.langbar' ).add( $.proxy( this.langBarSave, this ) );
		},

		/**
		 * Log webfonts disabling
		 * @param {string} context Where the setting was changed.
		 */
		langBarSave: function ( context ) {
			console.log( "done" );
		}
	};
} ( jQuery, mediaWiki ) );
