( function ( $, mw ) {
	'use strict';

	mw.translate = mw.translate || {};

	mw.translate = $.extend( mw.translate, {
		// A cache for language stats loaded from API,
		// indexed by language code
		languagestats: {},

		// Storage for language stats loader functions from API,
		// indexed by language code
		languageStatsLoader: {},

		messageGroups: {},
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

		loadMessageGroups: function () {
			var loader,
				queryParams = {
					action: 'query',
					format: 'json',
					meta: 'messagegroups',
					mgformat: 'tree',
					mgprop: 'id|label|description|icon|priority|prioritylangs|priorityforce|workflowstates',
					mgiconsize: '32'
				};
			loader = new mw.Api().get( queryParams );
			loader.done( function ( result ) {
				mw.translate.messageGroups = result.query.messagegroups;
			} );
			return loader;
		},

		/**
		 * Check if the current user is allowed to translate on this wiki.
		 * @return {boolean}
		 */
		canTranslate: function () {
			return mw.config.get( 'TranslateRight' );
		},

		/**
		 * Check if the current user is allowed to proofread on this wiki.
		 * @return {boolean}
		 */
		canProofread: function () {
			return mw.config.get( 'TranslateMessageReview' );
		}
	} );
}( jQuery, mediaWiki ) );
