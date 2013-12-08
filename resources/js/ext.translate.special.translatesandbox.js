/**
 * JS for special page.
 * @author Niklas Laxström
 * @author Sucheta Ghoshal
 * @author Amir E. Aharoni
 * @author Pau Giner
 * @license GPL-2.0+
 */

( function ( $, mw ) {
	'use strict';

	var delay;

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
		var req,
			api = new mw.Api(),
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
		var $nextRequest,
			$selectedRequests = $( '.request-selector:checked' );

		$nextRequest = $selectedRequests.first().closest( '.request' ).prev();
		$selectedRequests.closest( '.request' ).remove();

		updateRequestCount();

		if ( !$nextRequest.length ) {
			// If there's no request above the first checked request,
			// try to get the first request in the column
			$nextRequest = $( '.requests .request' ).first();
		}

		if ( $nextRequest.length ) {
			$nextRequest.click();
			updateSelectedIndicator( 1 );
		} else {
			$( '.details.pane' ).empty();
			updateSelectedIndicator( 0 );
		}
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
				.addClass( 'tsb-header row' )
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

			// Display a message if the user didn't make any translations
			if ( !translations.translationstash.translations.length ) {
				$target.append(
					$( '<div>' )
						.addClass( 'tsb-details-no-translations' )
						.text( mw.msg( 'tsb-didnt-make-any-translations' ) )
				);

				return;
			}

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
											$.uls.data.getAutonym( translation.title.split( /[\\/ ]+/ ).pop() )
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
				.addClass( 'tsb-header row' ),
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

	/**
	 * Updates the counter of the selected users.
	 * @param {number} count The number of selected users
	 */
	function updateSelectedIndicator( count ) {
		var text = mw.msg( 'tsb-selected-count', mw.language.convertNumber( count ) );

		$( '.pane.requests .request-footer' ).text( text );
		if ( count > 1 ) {
			$( '.pane.details .tsb-header' ).text( text );
		}
	}

	/**
	 * Updates the number of requests.
	 */
	function updateRequestCount() {
		$( '.request-count' )
			.text( mw.msg( 'tsb-request-count', $( '.request:not(.hide)' ).length ) );
	}

	/**
	 * Sets the height of the panes to the window height.
	 */
	function setPanesHeight() {
		var $detailsPane = $( '.pane.details' ),
			$requestsPane = $( '.pane.requests' ),
			detailsHeight = $( window ).height() - $detailsPane.offset().top,
			requestsHeight = detailsHeight -
				$requestsPane.find( '.request-footer' ).height() -
				$requestsPane.find( '.request-header' ).height();

		$detailsPane.css( 'max-height', detailsHeight );
		$requestsPane.find( '.requests-list' ).css( 'max-height', requestsHeight );
	}

	$( document ).ready( function () {
		var $requestCheckboxes = $( '.request-selector' ),
			$languageSelector = $( '.language-selector' ),
			$selectAll = $( '.request-selector-all' ),
			$requestRows = $( '.requests .request' ),
			$detailsPane = $( '.pane.details' );

		// Delay so we get the correct height on page load
		window.setTimeout( setPanesHeight, 0 );
		$( window ).on( 'resize', setPanesHeight );

		$( '.request-filter-box' ).translatorSearch();

		// Handle clicks for the 'Select all' checkbox
		$selectAll.on( 'click', function () {
			var selectedCount,
				selectAllChecked = this.checked,
				$visibleRows = $requestRows.not( '.hide' );

			$visibleRows.each( function ( index, row ) {
				$( row ).find( '.request-selector' ).prop( {
					checked: selectAllChecked,
					disabled: false
				} );
			} );

			if ( selectAllChecked ) {
				displayOnMultipleSelection();
				$visibleRows.addClass( 'selected' );
				selectedCount = $requestCheckboxes.filter( ':checked' ).length;
			} else {
				$detailsPane.empty();
				$requestRows.removeClass( 'selected' );
				selectedCount = 0;
			}

			updateSelectedIndicator( selectedCount );
		} );

		$requestCheckboxes.on( 'click', function ( e ) {
			var checkedCount, $checkedBoxes,
				$thisRequestRow = $( this ).parents( 'div.request' );

			// Uncheck the rows that were selected by clicking the row
			$requestCheckboxes.filter( ':disabled' ).prop( 'disabled', false );

			if ( this.checked ) {
				$thisRequestRow.addClass( 'selected' );
			} else {
				$thisRequestRow.removeClass( 'selected' );
			}

			$checkedBoxes = $requestCheckboxes.filter( ':checked' );
			checkedCount = $checkedBoxes.length;

			if ( checkedCount === $requestCheckboxes.length ) {
				// All boxes are selected
				$selectAll.prop( {
					checked: true,
					indeterminate: false
				} );

				displayOnMultipleSelection();
			} else if ( checkedCount === 0 ) {
				// No boxes are selected
				$selectAll.prop( {
					checked: false,
					indeterminate: false
				} );

				$detailsPane.empty();
			} else if ( checkedCount === 1 ) {
				$selectAll.prop( {
					checked: false,
					indeterminate: true
				} );

				$checkedBoxes.prop( 'disabled', true );

				// Here we know that only one checkbox is selected,
				// so it's OK to query the data from it
				displayRequestDetails( $checkedBoxes.parents( 'div.request' ).data( 'data' ) );
			} else {
				$selectAll.prop( {
					checked: false,
					indeterminate: true
				} );

				displayOnMultipleSelection();
			}

			updateSelectedIndicator( checkedCount );

			e.stopPropagation();
		} );

		// Handle clicks on request rows.
		$requestRows.on( 'click', function () {
			var requestRow = this;

			displayRequestDetails( $( requestRow ).data( 'data' ) );

			// Clicking a row makes only that row selected and unselects all other rows
			$requestRows.each( function ( i, row ) {
				var $row = $( row );

				if ( row === requestRow ) {
					$row.addClass( 'selected' )
						.find( '.request-selector' ).prop( {
							checked: true,
							disabled: true
						} );
				} else {
					$row.removeClass( 'selected' )
						.find( '.request-selector' ).prop( {
							checked: false,
							disabled: false
						} );
				}
			} );

			$selectAll.prop( 'indeterminate', true );

			updateSelectedIndicator( 1 );
		} );

		if ( $requestRows.length ) {
			$requestRows.first().click();
		} else {
			$detailsPane.text( mw.msg( 'tsb-no-requests-from-new-users' ) );
		}

		// Activate language selector
		$languageSelector.uls( {
			onSelect: function ( language ) {
				$languageSelector
					.removeClass( 'unselected' )
					.addClass( 'selected' )
					.text( $.uls.data.getAutonym( language ) )
					.append( $( '<span>' ) // A '×' icon to clear the selector
						.addClass( 'clear' )
						.text( '×' )
						.click( function() {
							$languageSelector
								.removeClass( 'selected' )
								.addClass( 'unselected' )
								.text( mw.msg( 'tsb-all-languages-button-label' ) );

							filterRequestsByLanguage();

							return false;
						} )
					);

				filterRequestsByLanguage( language );
			}
		} );
	} );

	/**
	 * Filter the requests by language.
	 * @param {string} [language] Language code
	 */
	function filterRequestsByLanguage ( language ) {
		var $requests = $( '.request' );

		$requests.each( function ( index, request ) {
			var $request = $( request ),
				requestData = $request.data( 'data' );

			if ( !language ||
				( requestData.languagepreferences &&
					requestData.languagepreferences.languages &&
					requestData.languagepreferences.languages.indexOf( language ) > -1 )
			) {
				// Found language
				$request.removeClass( 'hide' );
			} else {
				$request.addClass( 'hide' );
			}
		} );

		updateAfterFiltering();
	}

	function TranslatorSearch( element ) {
		this.$search = $( element );
		this.init();
	}

	TranslatorSearch.prototype.init = function () {
		this.$search.on( 'search keyup', $.proxy( this.keyup, this ) );
	};

	TranslatorSearch.prototype.keyup = function() {
		var query,
			translatorSearch = this;

		// Respond to the keypress events after a small timeout to avoid freeze when typed fast
		delay( function () {
			query = $.trim( translatorSearch.$search.val() ).toLowerCase().trim();
			translatorSearch.filter( query );
		}, 300 );
	};

	TranslatorSearch.prototype.filter = function( query ) {
		var $requests = $( '.request' );

		$requests.each( function ( index, request ) {
			var $request = $( request ),
				requestData = $request.data( 'data' );

			if ( query.length === 0 ||
				requestData.username.toLowerCase().indexOf( query ) === 0 ||
				requestData.email.toLowerCase().indexOf( query ) === 0
			) {
				$request.removeClass( 'hide' );
			} else {
				$request.addClass( 'hide' );
			}
		} );

		updateAfterFiltering();
	};

	function updateAfterFiltering() {
		var $selectedRequests,
			$firstVisibleUser = $( '.request:not(.hide)' ).first();

		if ( $firstVisibleUser.length ) {
			$firstVisibleUser.click();
		} else {
			$( '.details' ).empty();
			$selectedRequests = $( '.request-selector:checked' );
			$selectedRequests.closest( '.request' ).removeClass( 'selected' );
			$selectedRequests.prop( {
				checked: false,
				disabled: false
			} );

			updateSelectedIndicator( 0 );
		}

		updateRequestCount();
	}

	$.fn.translatorSearch = function () {
		return this.each( function () {
			if ( !$.data( this, 'TranslatorSearch' ) )  {
				$.data( this, 'TranslatorSearch', new TranslatorSearch( this ) );
			}
		} );
	};

	delay = ( function () {
		var timer = 0;

		return function ( callback, milliseconds ) {
			clearTimeout( timer );
			timer = setTimeout( callback, milliseconds );
		};
	} () );
}( jQuery, mediaWiki ) );
