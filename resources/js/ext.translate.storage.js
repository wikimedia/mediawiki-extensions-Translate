( function ( mw ) {
	'use strict';

	/**
	 * This class can save a translation into MediaWiki pages using the
	 * MediaWiki edit WebApi.
	 *
	 * @since 2013.10
	 */
	var TranslationApiStorage = function () {
		// No-op for now. Could take api module as param for example.
	};

	TranslationApiStorage.prototype = {
		/**
		 * Save the translation.
		 *
		 * @param {string} title The title of the page including language code
		 *   to store the translation.
		 * @param {string} translation The translation of the message
		 * @param {string} editSummary The edit summary
		 * @return {jQuery.Promise}
		 */
		save: function ( title, translation, editSummary ) {
			var api = new mw.Api();

			// Change to csrf when support for MW 1.25 is dropped
			return api.postWithToken( 'edit', {
				action: 'edit',
				title: title,
				text: translation,
				summary: editSummary,
				// If the session expires, fail the saving instead of saving it
				// as an anonymous user (if anonymous can save).
				// When undefined, the parameter is not included in the request
				assert: mw.user.isAnon() ? undefined : 'user'
			} );
		}
	};

	mw.translate = mw.translate || {};
	mw.translate.TranslationApiStorage = TranslationApiStorage;
}( mediaWiki ) );
