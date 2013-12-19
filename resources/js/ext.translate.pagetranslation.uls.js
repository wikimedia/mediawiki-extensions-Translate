( function ( mw ) {
	'use strict';

	// BC for MW <= 1.21
	var getUrl = mw.util.getUrl || mw.util.wikiGetlink;

	mw.uls.changeLanguage = function ( language ) {
		var page, uri;
		page = 'Special:MyLanguage/' + mw.config.get( 'wgPageName' );
		uri = new mw.Uri( getUrl( page ) );

		uri.extend( {
			setlang: language
		} );
		window.location.href = uri.toString();
	};
} ( mediaWiki ) );
