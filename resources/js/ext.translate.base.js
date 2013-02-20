( function ( $, mw ) {
	'use strict';

	mw.translate = mw.translate || {};

	mw.translate = $.extend( mw.translate, {
		// A cache for language stats loaded from API,
		// indexed by language code
		languagestats: {},

		// A cache for message groups stats loaded from API
		messageGroups: {},

		// Storage for language stats loader functions from API,
		// indexed by language code
		languageStatsLoader: {},

		/**
		 * Get language stats for a language from the API.
		 * @param {string} language Language code.
		 * @return {deferred}
		 */
		loadLanguageStats: function ( language ) {
			if ( !mw.translate.languageStatsLoader[language] ) {
				mw.translate.languageStatsLoader[language] = new mw.Api().get( {
					action: 'query',
					format: 'json',
					meta: 'languagestats',
					lslanguage: language
				} );
			}

			mw.translate.languageStatsLoader[language].done( function ( result ) {
				mw.translate.languagestats[language] = result.query.languagestats;
			} );

			return mw.translate.languageStatsLoader[language];
		},

		/**
		 * Get message groups info from the API.
		 * @return {deferred}
		 */
		loadMessageGroups: function () {
			return new mw.Api().get( {
				action: 'query',
				format: 'json',
				meta: 'messagegroups',
				mgformat: 'tree',
				mgprop: 'id|label|description|icon|priority|prioritylangs|priorityforce|workflowstates',
				// Keep this in sync with css!
				mgiconsize: '32'
			} ).done( function ( result ) {
				mw.translate.messageGroups = result.query.messagegroups;
			} ).fail( function ( errorCode, result ) {
				mw.log( 'Error loading message groups : ' + errorCode + ' ' + result );
			} );
		},

		/**
		 * Check if the current user is allowed to translate on this wiki.
		 * @return {boolean}
		 */
		canTranslate: function () {
			return mw.config.get( 'TranslateRight' );
		}
	} );
}( jQuery, mediaWiki ) );
