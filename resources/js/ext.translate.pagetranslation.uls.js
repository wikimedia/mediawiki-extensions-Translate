( function ( mw ) {
	'use strict';

	mw.uls.changeLanguage = function ( language ) {
		var page, uri;

		page = 'Special:MyLanguage/' + mw.config.get( 'wgPageName' );

		if ( mw.config.get( 'wgTranslatePageTranslation' ) === 'translation' ) {
			page = page.replace( /\/[^\/]+$/, '' );
		}

		uri = mw.util.getUrl( page, { setlang: language } );

		location.href = uri;
	};
}( mediaWiki ) );
