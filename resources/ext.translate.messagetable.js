(function ( $, mw ) {
	'use strict';

	$( 'document' ).ready( function () {
		$( '.mw-translate-messagereviewbutton' ).click( function() {
			var $b = $(this);

			// @todo Avoid creating functions inside a loop
			var successFunction = function( data, textStatus ) {
				if ( data.error ) {
					var reason = mw.msg( 'api-error-' + data.error.code );
					$b.val( mw.msg( 'translate-messagereview-failure', reason ) );
				} else {
					$b.val( mw.msg( 'translate-messagereview-done' ) );
				}
			};

			var failFunction = function( jqXHR, textStatus ) {
				$b.val( mw.msg( 'translate-messagereview-failure', jqXHR.statusText ) );
			};

			var params = {
				action: 'translationreview',
				token: $b.data( 'token' ),
				revision: $b.data( 'revision' ),
				format: 'json'
			};
			$b.val( mw.msg( 'translate-messagereview-progress' ) );
			$b.prop( 'disabled', true );

			$.post( mw.util.wikiScript( 'api' ), params, successFunction ).fail( failFunction );
		} );
	} );
} )( jQuery, mediaWiki );
