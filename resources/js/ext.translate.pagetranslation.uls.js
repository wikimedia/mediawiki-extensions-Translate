( function () {
	'use strict';

	/**
	 * Overrides the mw.uls.changeLanguage present in ULS
	 *
	 * @internal
	 * @param {string} language
	 */
	mw.uls.changeLanguage = function ( language ) {
		var page = 'Special:MyLanguage/' + mw.config.get( 'wgPageName' );

		if ( mw.config.get( 'wgTranslatePageTranslation' ) === 'translation' ) {
			page = page.replace( /\/[^/]+$/, '' );
		}

		mw.uls.setLanguage( language ).then( function () {
			location.href = mw.util.getUrl( page );
		} );
	};
}() );
