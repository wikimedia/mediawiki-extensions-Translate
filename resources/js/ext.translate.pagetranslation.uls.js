( function ( mw ) {
	'use strict';

	mw.uls.changeLanguage = function ( language ) {
		var page, uri;
		page = 'Special:MyLanguage/' + mw.config.get( 'wgPageName' );
		uri = new mw.Uri( mw.util.getUrl( page ) );

		uri.extend( {
			setlang: language
		} );
		window.location.href = uri.toString();
	};
} ( mediaWiki ) );
