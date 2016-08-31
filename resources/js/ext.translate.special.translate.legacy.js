( function ( $, mw ) {
	'use strict';
	$( 'document' ).ready( function () {
		var api = new mw.Api();

		$( '.mw-translate-messagereviewbutton' ).click( function () {
			var $b, successFunction, failFunction, params;
			$b = $( this );

			successFunction = function ( data ) {
				var reason;

				if ( data.error ) {
					// Give grep a chance to find the usages:
					// api-error-invalidrevision, api-error-unknownmessage,
					// api-error-fuzzymessage, api-error-owntranslation
					reason = mw.msg( 'api-error-' + data.error.code );
					$b.val( mw.msg( 'translate-messagereview-failure', reason ) );
				} else {
					$b.val( mw.msg( 'translate-messagereview-done' ) );
				}
			};

			failFunction = function ( jqXHR ) {
				$b.val( mw.msg( 'translate-messagereview-failure', jqXHR.statusText ) );
			};

			params = {
				action: 'translationreview',
				revision: $b.data( 'revision' )
			};
			$b.val( mw.msg( 'translate-messagereview-progress' ) );
			$b.prop( 'disabled', true );

			// Change to csrf when support for MW 1.25 is dropped
			api.postWithToken( 'edit', params ).done( successFunction ).fail( failFunction );
		} );
	} );
}( jQuery, mediaWiki ) );
