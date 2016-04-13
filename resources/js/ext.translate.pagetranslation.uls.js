( function ( mw ) {
	'use strict';

	mw.uls.changeLanguage = function ( language ) {
		var page;

		page = 'Special:MyLanguage/' + mw.config.get( 'wgPageName' );

		if ( mw.config.get( 'wgTranslatePageTranslation' ) === 'translation' ) {
			page = page.replace( /\/[^\/]+$/, '' );
		}

		location.href = mw.util.getUrl( page, { setlang: language } );
	};
}( mediaWiki ) );
