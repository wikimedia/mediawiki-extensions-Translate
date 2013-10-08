( function ( mw ) {
	'use strict';

	mw.translate = mw.translate || {};

	/**
	 * This class can save translation into MediaWiki pages using the
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
			var deferred = new mw.Api().postWithEditToken( {
				action: 'edit',
				title: title,
				text: translation
			} );

			return deferred.promise();
		}
	};

	mw.translate.TranslationApiStorage = TranslationStorage;

}( mediaWiki ) );
