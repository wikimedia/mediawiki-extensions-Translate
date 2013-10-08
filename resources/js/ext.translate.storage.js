( function ( mw ) {
	'use strict';

	mw.translate = mw.translate || {};
	/**
	 * TranslationStorage class
	 * @param {string} title The translation source, the title of the message.
	 * @param {string} translation The translation of the message
	 */
	var TranslationStorage = function ( title, translation ) {
		this.title = title;
		this.translation = translation;
	};

	TranslationStorage.prototype = {
		/**
		 * Save the translation
		 * @return {jQuery.Promise}
		 */
		save: function () {
			return new mw.Api().postWithEditToken( {
				action: 'edit',
				title: this.title,
				text: this.translation
			} );
		}
	};

	mw.translate.TranslationStorage = TranslationStorage;

}( mediaWiki ) );
