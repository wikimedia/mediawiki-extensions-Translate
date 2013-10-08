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
	 * @param {Object} request
	 */
	function reminderDialog( request ) {
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
						$( '<div class="nine columns">' ).text( request.email )
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
							userid: request.userid,
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

	/**
	 * Display the request details when user clicks on a request item
	 *
	 * @param {Object} request The request data set from backend on request items
	 */
	function displayRequestDetails( request ) {
		var $detailsPane = $( '.details.pane' );

		$detailsPane.empty().append(
			$( '<div>' )
				.addClass( 'username row' )
				.text( request.username ),
			$( '<div>' )
				.addClass( 'email row' )
				.text( request.email ),
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
						.text( mw.msg( 'tsb-accept-button-label' ) )
						.on( 'click', function () {
							doApiAction( {
								userid: request.userid,
								'do': 'promote'
							} );
						} ),
					$( '<button>' )
						.addClass( 'delete destructive button' )
						.text( mw.msg( 'tsb-reject-button-label' ) )
						.on( 'click', function () {
							doApiAction( {
								userid: request.userid,
								'do': 'delete'
							} );
						} )
				),
			$( '<div>' )
				.addClass( 'reminder row' )
				.append(
					$( '<a href="#"></a>' )
						.addClass( 'remind link' )
						.text( mw.msg( 'tsb-reminder-link-text' ) )
						.on( 'click', function ( e ) {
							e.preventDefault();
							reminderDialog( request );
						} )
				)
		);
	}


	$( document ).ready( function () {
		var $selectAll = $( '.request-selector-all' );

		// Handle clicks for the select all checkbox
		$selectAll.click( function () {
			$( '.request-selector' ).prop( 'checked', this.checked );
		} );

		// And update the state of select-all checkbox
		$( '.request-selector' ).on( 'click', function ( e ) {
			var total, checked, $selects = $( '.request-selector' );

			total = $selects.length;
			checked = $selects.filter( ':checked' ).length;

			if ( checked === total ) {
				$selectAll.prop( 'checked', true ).prop( 'indeterminate', false );
			} else if ( checked === 0 ) {
				$selectAll.prop( 'checked', false ).prop( 'indeterminate', false );
			} else {
				$selectAll.prop( 'indeterminate', true );
			}

			e.stopPropagation();
		} );

		// Handle clicks on requests
		$( '.requests .request' ).on( 'click',  function () {
			displayRequestDetails( $( this ).data( 'data' ) );
		} );

		// Activate language selector
		$( '.language-selector' ).uls();
	} );
}( jQuery, mediaWiki ) );
