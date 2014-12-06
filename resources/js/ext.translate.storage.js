( function ( mw ) {
	'use strict';

	mw.translate = mw.translate || {};

	/**
	 * This class can save a translation into MediaWiki pages using the
	 * MediaWiki edit WebApi.
	 * @since 2013.10
	 */
	var TranslationApiStorage = function () {
		// No-op for now. Could take api module as param for example.
	};

	TranslationApiStorage.prototype = {
		/**
		 * Save the translation
		 * @param {string} title The title of the page including language code
		 *   to store the translation.
		 * @param {string} translation The translation of the message
		 * @return {jQuery.Promise}
		 */
		save: function ( title, translation ) {
			return (new mw.Api()).postWithToken( 'edit', {
				action: 'edit',
				title: title,
				text: translation,
				// If the session expires, fail the saving instead of saving it
				// as an anonymous user (if anonymous can save).
				// When undefined, the parameter is not included in the request
				assert: mw.user.isAnon() ? undefined : 'user'
			} );
		}
	};

	mw.translate.TranslationApiStorage = TranslationApiStorage;
}( mediaWiki ) );
