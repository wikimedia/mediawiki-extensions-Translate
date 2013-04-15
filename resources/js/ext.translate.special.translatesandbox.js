/**
 * JS for special page.
 * @author Niklas Laxström
 * @license GPL2+
 */

(function ( $, mw ) {
	'use strict';

	function doApiAction( options ) {
		var api = new mw.Api();

		options = $.extend( {}, {
			action: 'translatesandbox',
			token: $( '#token' ).val()
		}, options );

		api.post( options )
			.done( function () { window.alert( 'Success' ); } )
			.fail( function () { window.alert( 'Failure' ); } )
		;
	}

	$( document ).ready( function () {
		var $requests, $detailsPane;

		$detailsPane = $( '.details.pane' );
		$requests = $( '.requests .request' );
		$requests.on( 'click', function () {
			var $this = $( this );

			$detailsPane.empty().append(
				$( '<div>' )
					.addClass( 'username row' )
					.text( $this.find( '.username' ).text() ),
				$( '<div>' )
					.addClass( 'email row' )
					.text( $this.find( '.email' ).text() ),
				$( '<div>' )
					.addClass( 'languages row' )
					.append(
						$( '<span>' ).text( 'Afrikaans' ),
						$( '<span>' ).text( 'español' )
					),
				$( '<div>' )
					.addClass( 'actions row' )
					.append(
						$( '<button>' )
							.addClass( 'accept primary button' )
							.text( 'Accept' )
							.on( 'click', function () {
								doApiAction( {
									userid: $this.data( 'data' ).id,
									'do': 'promote'
								} );
							} ),
						$( '<button>' )
							.addClass( 'delete destructive button' )
							.text( 'Delete' )
							.on( 'click', function () {
								doApiAction( {
									userid: $this.data( 'data' ).id,
									'do': 'delete'
								} );
							} )
					)
			);
		} );
	} );
}( jQuery, mediaWiki ) );
