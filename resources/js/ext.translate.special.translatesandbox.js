/**
 * JS for special page.
 * @author Niklas Laxström
 * @license GPL-2.0+
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
			.fail( function () { window.alert( 'Failure' ); } );
	}

	/**
	 * Gets arbitrary messages in chosen language via the API.
	 * @param {Array} names Message keys.
	 * @param {String} [language] Language to use. Defaults to English.
	 * @return {jQuery.Deferred}
	 */
	function getMessages( names, language ) {
		var api, req, deferred;

		api = new mw.Api();
		deferred = new $.Deferred();

		req = api.post( {
			action: 'query',
			meta: 'allmessages',
			ammessages: names.join( '|' ),
			amlang: language || 'en'
		} );

		req.done( function ( data ) {
			var i,
				output = {};

			for ( i = 0; i < data.query.allmessages.length; i++ ) {
				output[data.query.allmessages[i].name] = data.query.allmessages[i]['*'];
			}

			deferred.resolve( output );
		} );

		req.fail( deferred.reject );

		return deferred;
	}

	/**
	 * Dialog where the user can tweak reminder email if wanted.
	 * @param {jQuery} $request
	 */
	function reminderDialog( $request ) {
		var $dialog, keys;

		keys = ['tsb-reminder-title-generic', 'tsb-reminder-content-generic' ];
		getMessages( keys ).done( function ( data ) {

			$dialog = $( '<div class="grid">' ).append(
				$( '<form>' ).append(
					$( '<div class="row">' ).append(
						$( '<div class="three columns text-left">' ).text( 'From:' ),
						$( '<div class="nine columns">' ).text( mw.config.get( 'wgUserName' ) + ' <your email>' )
					),
					$( '<div class="row">' ).append(
						$( '<div class="three columns">' ).text( 'To:' ),
						$( '<div class="nine columns">' ).text( $request.find( '.email' ).text() )
					),
					$( '<div class="row">' ).append(
						$( '<div class="three columns">' ).text( 'Subject:' ),
						$( '<input class="nine columns subject">' ).val( data['tsb-reminder-title-generic'] )
					),
					$( '<div class="row">' ).append(
						$( '<div class="three columns">' ).text( 'Body:' ),
						$( '<textarea class="nine columns body">' ).val( data['tsb-reminder-content-generic'] )
					)
				)
			);

			$dialog.dialog( {
				autoOpen: true,
				modal: true,
				width: '650px',
				buttons: {
					'Send': function () {
						doApiAction( {
							userid: $request.data( 'data' ).id,
							'do': 'remind',
							subject: $dialog.find( '.subject' ).val(),
							body: $dialog.find( '.body' ).val()
						} );
						$( this ).dialog( 'destroy' );
					},
					'Cancel': function () {
						$( this ).dialog( 'destroy' );
					}
				}
			} );
		} );
	}

	function loadRequestDetails ( request ) {

		var $detailsPane = $( '.details.pane' );

		$detailsPane.empty().append(
			$( '<div>' )
				.addClass( 'username row' )
				.text( request.username ),
			$( '<div>' )
				.addClass( 'email row' )
				.text(  request.email ),
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
						.addClass( 'accept primary green button' )
						.text( 'Accept' )
						.on( 'click', function () {
							doApiAction( {
								userid: request.userid,
								'do': 'promote'
							} );
						} ),
					/*$( '<button>' )
						.addClass( 'remind button' )
						.text( 'Send email reminder' )
						.on( 'click', function () {
							reminderDialog( request );
						} ),*/
					$( '<button>' )
						.addClass( 'delete destructive button' )
						.text( 'Reject' )
						.on( 'click', function () {
							doApiAction( {
								userid: request.userid,
								'do': 'delete'
							} );
						} )
				)
		);
	}


	$( document ).ready( function () {
		$( '.request-selector-all' ).click(function () {
			$( '.request-selector' ).attr( 'checked', this.checked );
		} );

		// if all checkbox are selected, check the selectall checkbox
		// and viceversa
		$( '.request-selector' ).on( 'click', function () {
			if ( $( '.request-selector' ).length === $( '.request-selector:checked' ).length ) {
				$( '.request-selector-all' ).attr( 'checked', 'checked' );
			} else {
				$( '.request-selector-all' ).removeAttr( 'checked' );
			}
		} );
		$( '.requests .request' ).on( 'click',  function () {
			loadRequestDetails( $( this ).data( 'data' ) );
		} );

		$( '.language-selector' ).uls();
	} );
}( jQuery, mediaWiki ) );
