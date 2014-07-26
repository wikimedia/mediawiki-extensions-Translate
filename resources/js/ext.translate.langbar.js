( function ( $, mw ) {
	'use strict';

	$( document ).ready( function () {
		var getUrl = mw.util.getUrl || mw.util.wikiGetlink;
		var common = mw.config.get( 'wgCommonLanguages' );

		$( '.mw-translate-langbar-min' ).click( function() {
			$( '.mw-translate-langbar-container' ).toggle();
		} );

		$( '.mw-translate-viewmore' ).uls( {
			compact: true,
			quickList: common,
			onSelect: function( lang ) {
				var page, uri;

				if ( common.indexOf( lang ) !== -1 ) {
						page =  mw.config.get( 'wgPageBaseTitle' );
						uri = new mw.Uri( getUrl( page + '/'+ lang ) );
				} else {
					page =  mw.config.get( 'wgMessageGroupId' );
					uri = new mw.Uri( getUrl( 'Special:Translate' ) );
					uri.extend( {
						group: page,
						language: lang,
						action: 'page',
						filter: ''
					} );
				}
				window.location.href = uri.toString();
			}
		} );
	} );

} ( jQuery, mediaWiki ) );
