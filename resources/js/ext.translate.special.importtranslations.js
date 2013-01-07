( function ( $, mw ) {
	'use strict';
	$( function () {

		$( '.mw-translate-import-inputs' ).change( function () {
			var id = $( this ).attr( 'id' ).replace( /-input/, '' );

			$( 'input[name=upload-type]:checked' ).attr( 'checked', false );
			$( '#' + id ).attr( 'checked', 'checked' );
		} );

		$( '#mw-translate-up-wiki-input' ).autocomplete( {
			source: function ( request, response ) {
				var api, data, success;

				api = mw.util.wikiScript( 'api' );
				data = { action: 'opensearch', format: 'json', namespace: 6, search: request.term };

				success = function ( res ) {
					response( res[1] );
				};

				$.get( api, data, success );
			}
		} );
	} );
} ( jQuery, mediaWiki ) );
