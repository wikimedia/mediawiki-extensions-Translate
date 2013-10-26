/**
 * JS for special page.
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

(function ( $, mw ) {
	'use strict';

	function doApiAction( options ) {
		var api = new mw.Api();

		options = $.extend( {}, { action: 'translatesandbox' }, options );

		return api.postWithToken( 'translatesandbox', options )
			.promise();
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
		var $dialog,
			keys = [ 'tsb-reminder-title-generic', 'tsb-reminder-content-generic' ];

		getMessages( keys ).done( function ( data ) {
			// FIXME i18n
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

	function removeSelectedRequests() {
		$( '.request-selector:checked' )
			.closest( '.request' ).remove();
		$( '.request-count div' )
			.text( mw.msg( 'tsb-request-count', $( '.request' ).length ) );
	}

	/**
	 * Display the request details when user clicks on a request item
	 *
	 * @param {Object} request The request data set from backend on request items
	 */
	function displayRequestDetails( request ) {
		var storage,
			$detailsPane = $( '.details.pane' );

		$detailsPane.empty().append(
			$( '<div>' )
				.addClass( 'username row' )
				.text( request.username ),
			$( '<div>' )
				.addClass( 'email row' )
				.text( request.email ),
			$( '<div>' )
				.addClass( 'languages row autonym' ),
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
							} ).done( removeSelectedRequests );
						} ),
					$( '<button>' )
						.addClass( 'delete destructive button' )
						.text( mw.msg( 'tsb-reject-button-label' ) )
						.on( 'click', function () {
							doApiAction( {
								userid: request.userid,
								'do': 'delete'
							} ).done( removeSelectedRequests );
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
				),
			$( '<div>' )
				.addClass( 'translations row' )
		);

		if ( request.languagepreferences && request.languagepreferences.languages ) {
			$.each( request.languagepreferences.languages, function ( index, language ) {
				$detailsPane.find( '.languages' ).append(
					$( '<span>' )
						.text( $.uls.data.getAutonym( language ) )
				);
			} );
		}

		// @todo: move higher in the tree
		storage = new mw.translate.TranslationStashStorage();
		storage.getUserTranslations( request.username ).done( function ( translations ) {
			var $target = $( '.translations' );

			// TODO: Header for the translations. not i18ned, need UX review
			$target.append(
				$( '<div>' )
					.addClass( 'row title' )
					.append(
						$( '<div>' )
							.text( mw.msg( 'tsb-translations-source' ) )
							.addClass( 'four columns' ),
						$( '<div>' )
							.text( mw.msg( 'tsb-translations-user' ) )
							.addClass( 'four columns' ),
						$( '<div>' )
							.text( mw.msg( 'tsb-translations-current' ) )
							.addClass( 'four columns' )
					)
				);
			$.each( translations.translationstash.translations, function( index, translation ) {
				$target.append(
					$( '<div>' )
						.addClass( 'row' )
						.append(
							$( '<div>' )
								.addClass( 'four columns source' )
								.text( translation.definition ),
							$( '<div>' )
								.addClass( 'four columns translation' )
								.append(
									$( '<div>' ).text( translation.translation ),
									$( '<div>' )
										.addClass( 'info' )
										.text(
											$.uls.data.getAutonym( translation.title.split(/[\\/ ]+/).pop() )
										)
								),
							$( '<div>' )
								.addClass( 'four columns comparison' )
								.append(
									$( '<div>' ).text( translation.comparison ),
									$( '<div>' )
										.addClass( 'info' )
										.text( translation.title )
								)
						)
				);
			} );
		} );
	}

	/**
	 * Display when multiple requests are checked
	 */
	function displayOnMultipleSelection() {
		var selectedUserIDs = $( '.request-selector:checked' ).map( function ( i, checkedBox ) {
			return $( checkedBox ).parents( 'div.request' ).data( 'data' ).userid;
		} );

		selectedUserIDs = selectedUserIDs.toArray().join( '|' );

		$( '.details.pane' ).empty().append(
			$( '<div>' )
				.addClass( 'actions row' )
				.append(
					$( '<button>' )
						.addClass( 'accept primary green button' )
						.text( mw.msg( 'tsb-accept-all-button-label' ) )
						.on( 'click', function () {
							doApiAction( {
								userid: selectedUserIDs,
								'do': 'promote'
							} ).done( removeSelectedRequests );
						} ),
					$( '<button>' )
						.addClass( 'delete destructive button' )
						.text( mw.msg( 'tsb-reject-all-button-label' ) )
						.on( 'click', function () {
							doApiAction( {
								userid: selectedUserIDs,
								'do': 'delete'
							} ).done( removeSelectedRequests );
						} )
				)
		);
	}

	$( document ).ready( function () {
		var $selectAll = $( '.request-selector-all' ),
			$detailsPane = $( '.details.pane' );

		// Handle clicks for the select all checkbox
		$selectAll.on( 'click', function () {
			$( '.request-selector' ).prop( 'checked', this.checked );

			if ( this.checked ) {
				displayOnMultipleSelection();
			} else {
				$detailsPane.empty();
			}
		} );

		// And update the state of select-all checkbox
		$( '.request-selector' ).on( 'click', function ( e ) {
			var total, checked, $selects = $( '.request-selector' );

			total = $selects.length;
			checked = $selects.filter( ':checked' ).length;

			if ( checked === total ) {
				$selectAll.prop( 'checked', true ).prop( 'indeterminate', false );
				displayOnMultipleSelection();
			} else if ( checked === 0 ) {
				$detailsPane.empty();
				$selectAll.prop( 'checked', false ).prop( 'indeterminate', false );
			} else {
				$selectAll.prop( 'indeterminate', true );
				displayOnMultipleSelection();
			}

			e.stopPropagation();
		} );

		// Handle clicks on requests
		$( '.requests .request' ).on( 'click', function () {
			displayRequestDetails( $( this ).data( 'data' ) );
		} );

		// Activate language selector
		$( '.language-selector' ).uls();
	} );
}( jQuery, mediaWiki ) );
