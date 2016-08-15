( function ( $, mw ) {
	'use strict';

	/**
	 * This class can save translation to translation stash.
	 *
	 * @since 2013.10
	 */
	var TranslationStashStorage = function () {
		// No-op for now. Could take api module as param for example.
	};

	TranslationStashStorage.prototype = {
		/**
		 * Save the translation.
		 *
		 * @param {string} title The title of the page including language code
		 *   to store the translation.
		 * @param {string} translation The translation of the message
		 * @return {jQuery.Promise}
		 */
		save: function ( title, translation ) {
			var api = new mw.Api();

			// Change to csrf when support for MW 1.25 is dropped
			return api.postWithToken( 'edit', {
				action: 'translationstash',
				subaction: 'add',
				title: title,
				translation: translation
			} ).then( function () {
				// Fake normal save API
				return { edit: { result: 'Success' } };
			} );
		},

		/**
		 * Get the current users translations.
		 *
		 * @return {jQuery.Promise}
		 */
		getUserTranslations: function ( user ) {
			var api = new mw.Api();

			// Change to csrf when support for MW 1.25 is dropped
			return api.postWithToken( 'edit', {
				action: 'translationstash',
				subaction: 'query',
				username: user
			} ).promise();
		}

	};

	mw.translate = mw.translate || {};
	mw.translate.TranslationStashStorage = TranslationStashStorage;

}( jQuery, mediaWiki ) );
