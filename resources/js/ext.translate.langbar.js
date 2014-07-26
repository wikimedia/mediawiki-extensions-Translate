( function ( $, mw ) {
	'use strict';

	$( document ).ready( function () {
		var getUrl = mw.util.getUrl || mw.util.wikiGetlink,
			common = mw.config.get( 'wgCommonLanguages' ),
			key = 'languagebar',
			defaultVal = { hide: false },
			value = $.jStorage.get( key, defaultVal ),
			$langbarMin = $( '.mw-translate-langbar-min' ),
			$langbarContainer = $( '.mw-translate-langbar-container' );

		// Remove fallback selector
		$( '#mw-more-languages' ).remove();

		// Keep the language bar collapsed across pages if hidden by user
		if ( $.jStorage.storageAvailable() ) {
			if ( value.hide === true ) {
				$langbarContainer.hide();
			}

			$langbarMin.click( function() {
				value.hide = $langbarContainer.is( ':visible' );
				$.jStorage.set( key, value );
			} );
		}

		$langbarMin.click( function() {
			$langbarContainer.toggle();
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
