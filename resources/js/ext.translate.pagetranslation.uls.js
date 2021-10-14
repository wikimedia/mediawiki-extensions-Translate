( function () {
	'use strict';

	mw.uls.changeLanguage = function ( language ) {
		var page = 'Special:MyLanguage/' + mw.config.get( 'wgPageName' );

		if ( mw.config.get( 'wgTranslatePageTranslation' ) === 'translation' ) {
			page = page.replace( /\/[^/]+$/, '' );
		}

		if ( mw.uls.setLanguage ) {
			mw.uls.setLanguage( language ).then( function () {
				location.href = mw.util.getUrl( page );
			} );
		} else {
			// Fallback if ULS is older than Translate (2021.03)
			location.href = mw.util.getUrl( page, { setlang: language } );
		}
	};
}() );
