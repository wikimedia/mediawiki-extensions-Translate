( function ( $, mw ) {
	'use strict';

	mw.translate = mw.translate || {};

	/**
	 * This class can save translation to translation stash
	 * @since 2013.10
	 */
	var TranslationStashStorage = function () {
		// No-op for now. Could take api module as param for example.
	};

	TranslationStashStorage.prototype = {
		/**
		 * Save the translation
		 * @param {string} title The title of the page including language code
		 *   to store the translation.
		 * @param {string} translation The translation of the message
		 * @return {jQuery.Promise}
		 */
		save: function ( title, translation ) {
			var token;

			token = mw.config.get( 'wgTranslateSupportsCsrfToken' ) ? 'csrf' : 'translationstash';
			options = {
				action: 'translationstash',
				subaction: 'add',
				title: title,
				translation: translation
			};

			return (new mw.Api()).postWithToken( token, options ).promise();
		},

		/**
		 * Get the current users translations
		 * @return {jQuery.Promise}
		 */
		getUserTranslations: function ( user ) {
			var token;

			token = mw.config.get( 'wgTranslateSupportsCsrfToken' ) ? 'csrf' : 'translationstash';
			options = {
				action: 'translationstash',
				subaction: 'query',
				username: user
			};

			return (new mw.Api()).postWithToken( token, options ).promise();
		}

	};

	mw.translate.TranslationStashStorage = TranslationStashStorage;

}( jQuery, mediaWiki ) );
