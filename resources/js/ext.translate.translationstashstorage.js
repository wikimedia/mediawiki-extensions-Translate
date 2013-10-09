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
			var deferred = new mw.Api().post( {
				action: 'translationstash',
				subaction: 'add',
				title: title,
				value: translation,
				token: $( '#token' ).val()
			} );

			return deferred.promise();
		},

		/**
		 * Get the current users translations
		 * @return {jQuery.Promise}
		 */
		getUserTranslations: function () {
			var deferred = new mw.Api().get( {
				action: 'translationstash',
				subaction: 'query',
				// TODO: use postWithToken once it is ready in core.
				token: $( '#token' ).val()
			} );

			return deferred.promise();
		}

	};

	mw.translate.TranslationStashStorage = TranslationStashStorage;

}( jQuery, mediaWiki ) );
