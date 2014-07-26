( function ( $, mw ) {
	'use strict';

	$( document ).ready( function () {
		var getUrl = mw.util.getUrl || mw.util.wikiGetlink;

		console.log( mw.config.get( 'wgCommonLanguages' ));
		var p = mw.config.get( 'wgCommonLanguages' );

		$( '.langbar-min' ).click( function() {
			$( '.container' ).toggle();
		} );

		$( '.viewmore' ).uls( {
			compact: true,
			quickList: p,
			onSelect: function( lang ) {
				var page, uri;

				if ( p.indexOf( lang ) !== -1 ) {
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
