( function ( mw ) {
	'use strict';

	mw.uls.changeLanguage = function ( language ) {
		var page, uri;

		page = 'Special:MyLanguage/' + mw.config.get( 'wgPageName' );

		if ( mw.config.get( 'wgTranslatePageTranslation' ) === 'translation' ) {
			page = page.replace( /\/[^\/]+$/, '' );
		}

		uri = new mw.Uri( mw.util.getUrl( page ) );

		uri.extend( {
			setlang: language
		} );

		location.href = uri.toString();
	};
} ( mediaWiki ) );
