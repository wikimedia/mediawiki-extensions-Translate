/*!
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

	/**
	 * A callback for sorting translations.
	 *
	 * @param {Object} translationA Object loaded from translation stash
	 * @param {Object} translationB Object loaded from translation stash
	 * @return {number} String comparison of language codes
	 */
	function sortTranslationsByLanguage( translationA, translationB ) {
		var a = translationA.title.split( '/' ).pop(),
			b = translationB.title.split( '/' ).pop();

		return a.localeCompare( b );
	}

	function doApiAction( options ) {
		var api = new mw.Api();

		options = $.extend( {}, { action: 'translatesandbox' }, options );

		// Change to csrf when support for MW 1.25 is dropped
		return api.postWithToken( 'edit', options )
			.promise();
	}

	function removeSelectedRequests() {
		var $nextRequest,
			$selectedRequests = $( '.request-selector:checked' );

		$nextRequest = $selectedRequests
			.first() // First selected request
			.closest( '.request' ) // The request corresponds that checkbox
			.prevAll( ':not(.hide)' ) // Go back till a non-hidden request
			.first(); // The above selecter gives list from bottom to top. Select the bottom one.

		$selectedRequests.closest( '.request' ).remove();

		updateRequestCount();

		if ( !$nextRequest.length ) {
			// If there's no request above the first checked request,
			// try to get the first request in the column
			$nextRequest = $( '.requests .request:not(.hide)' ).first();
		}

		if ( $nextRequest.length ) {
			$nextRequest.click();
			updateSelectedIndicator( 1 );
		} else {
			updateSelectedIndicator( 0 );
		}
	}

	/**
	 * Display the request details when user clicks on a request item
	 *
	 * @param {Object} request The request data set from backend on request items
	 */
	function displayRequestDetails( request ) {
		var storage, reminders,
			$reminderStatus = $( '<span>' ).addClass( 'reminder-status' ),
			$detailsPane = $( '.details.pane' );

		reminders = request.reminders ? request.reminders.split( '|' ) : [];

		if ( request.reminderscount ) {
			$reminderStatus.text( mw.msg(
				'tsb-reminder-sent',
				request.reminderscount,
				request.lastreminder
			) );
		}

		$detailsPane.empty().append(
			$( '<div>' )
				.addClass( 'tsb-header row' )
				.text( request.username ),
			$( '<div>' )
				.addClass( 'reminder-email row' )
				.append(
					$( '<span>' ).text( request.email ),
					$( '<a>' )
						.prop( 'href', '#' )
						.addClass( 'send-reminder link' )
						.text( mw.msg( 'tsb-reminder-link-text' ) )
						.on( 'click', function ( e ) {
							e.preventDefault();

							$reminderStatus
								.text( mw.msg( 'tsb-reminder-sending' ) );

							doApiAction( {
								'do': 'remind',
								userid: request.userid
							} ).done( function () {
								$reminderStatus.text( mw.msg( 'tsb-reminder-sent-new' ) );
							} ).fail( function () {
								$reminderStatus.text( mw.msg( 'tsb-reminder-failed' ) );
							} );
						} ),
					$reminderStatus
				),
			$( '<div>' )
				.addClass( 'languages row autonym' ),
			$( '<div>' )
				.addClass( 'signup-comment row' ),
			$( '<div>' )
				.addClass( 'actions row' )
				.append(
					$( '<button>' )
						.addClass( 'accept mw-ui-button mw-ui-progressive' )
						.text( mw.msg( 'tsb-accept-button-label' ) )
						.on( 'click', function () {
							window.tsbUpdatingUsers = true;

							doApiAction( {
								userid: request.userid,
								'do': 'promote'
							} ).done( function () {
								removeSelectedRequests();

								window.tsbUpdatingUsers = false;
							} );
						} ),
					$( '<button>' )
						.addClass( 'reject mw-ui-button mw-ui-destructive' )
						.text( mw.msg( 'tsb-reject-button-label' ) )
						.on( 'click', function () {
							window.tsbUpdatingUsers = true;

							doApiAction( {
								userid: request.userid,
								'do': 'delete'
							} ).done( function () {
								removeSelectedRequests();

								window.tsbUpdatingUsers = false;
							} );
						} )
				),
			$( '<div>' )
				.addClass( 'translations row' )
		);

		if ( request.languagepreferences ) {
			if ( request.languagepreferences.languages ) {
				$.each( request.languagepreferences.languages, function ( index, language ) {
					$detailsPane.find( '.languages' ).append(
						$( '<span>' )
							.prop( {
								dir: $.uls.data.getDir( language ),
								lang: language
							} )
							.text( $.uls.data.getAutonym( language ) )
					);
				} );
			}

			if ( request.languagepreferences.comment ) {
				$detailsPane.find( '.signup-comment' ).append(
					$( '<div>' )
						.addClass( 'signup-comment-label' )
						.text( mw.msg( 'tsb-user-posted-a-comment' ) ),
					$( '<div>' )
						.addClass( 'signup-comment-text' )
						.text( request.languagepreferences.comment )
				);
			}
		}

		// @todo: move higher in the tree
		storage = new mw.translate.TranslationStashStorage();
		storage.getUserTranslations( request.username ).done( showTranslations );
	}

	function showTranslations( translations ) {
		var gender,
			$target = $( '.translations' );

		$target.empty();

		// Display a message if the user didn't make any translations
		if ( !translations.translationstash.translations.length ) {
			$target.append(
				$( '<div>' )
					.addClass( 'tsb-details-no-translations' )
					.text( mw.msg( 'tsb-didnt-make-any-translations' ) )
			);

			return;
		}

		gender = $( '.requests-list .request.selected' ).data( 'data' ).gender;
		$target.append(
			$( '<div>' )
				.addClass( 'row title' )
				.append(
					$( '<div>' )
						.text( mw.msg( 'tsb-translations-source' ) )
						.addClass( 'four columns' ),
					$( '<div>' )
						.text( mw.msg( 'tsb-translations-user', gender ) )
						.addClass( 'four columns' ),
					$( '<div>' )
						.text( mw.msg( 'tsb-translations-current' ) )
						.addClass( 'four columns' )
				)
			);

		translations.translationstash.translations.sort( sortTranslationsByLanguage );
		$.each( translations.translationstash.translations, function ( index, translation ) {
			showTranslation( translation );
		} );
	}

	function showTranslation( translation ) {
		var $target = $( '.translations' ),
			translationLang = translation.title.split( '/' ).pop();

		$target.append( $( '<div>' )
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
							.addClass( 'info autonym' )
							.prop( {
								dir: $.uls.data.getDir( translationLang ),
								lang: translationLang
							} )
							.text(
								$.uls.data.getAutonym( translationLang )
							)
					),
				$( '<div>' )
					.addClass( 'four columns comparison' )
					.append(
						$( '<div>' ).text( translation.comparison || '' ),
						$( '<div>' )
							.addClass( 'info' )
							.text( translation.title )
					)
			)
		);
	}

	/**
	 * Display when multiple requests are checked.
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
						.addClass( 'accept-all mw-ui-button mw-ui-progressive' )
						.text( mw.msg( 'tsb-accept-all-button-label' ) )
						.on( 'click', function () {
							window.tsbUpdatingUsers = true;

							doApiAction( {
								userid: selectedUserIDs,
								'do': 'promote'
							} ).done( function () {
								removeSelectedRequests();

								window.tsbUpdatingUsers = false;
							} );
						} ),
					$( '<button>' )
						.addClass( 'reject-all mw-ui-button mw-ui-destructive' )
						.text( mw.msg( 'tsb-reject-all-button-label' ) )
						.on( 'click', function () {
							window.tsbUpdatingUsers = true;

							doApiAction( {
								userid: selectedUserIDs,
								'do': 'delete'
							} ).done( function () {
								removeSelectedRequests();

								window.tsbUpdatingUsers = false;
							} );
						} )
				)
		);
	}

	/**
	 * Updates the counter of the selected users.
	 *
	 * @param {number} count The number of selected users
	 */
	function updateSelectedIndicator( count ) {
		var text = mw.msg( 'tsb-selected-count', mw.language.convertNumber( count ) );

		$( '.requests.pane .request-footer .selected-counter' ).text( text );
		if ( count > 1 ) {
			$( '.details.pane .tsb-header' ).text( text );
		}
	}

	/**
	 * Returns older requests with the same number of translations.
	 *
	 * @return {jQuery} Older requests
	 */
	function getOlderRequests() {
		var $lastSelectedRequest = $( '.row.request.selected' ).last(),
			currentTranslationCount = $lastSelectedRequest.data( 'data' ).translations;

		return $lastSelectedRequest.nextAll( ':not(.hide)' ).filter( function () {
			return ( $( this ).data( 'data' ).translations === currentTranslationCount );
		} );
	}

	/**
	 * Updates the number of older requests with the same number
	 * of translations at the link in the bottom of the requests row
	 * or hides that link if there are no such requests.
	 */
	function indicateOlderRequests() {
		var oldRequestsCount, oldRequestsCountString,
			$olderRequests = getOlderRequests(),
			$olderRequestsIndicator = $( '.older-requests-indicator' );

		oldRequestsCount = $olderRequests.length;
		oldRequestsCountString = mw.language.convertNumber( oldRequestsCount );

		if ( oldRequestsCount ) {
			$olderRequestsIndicator
				.text( mw.msg( 'tsb-older-requests', oldRequestsCountString ) )
				.removeClass( 'hide' );
		} else {
			$olderRequestsIndicator
				.addClass( 'hide' );
		}
	}

	/**
	 * Updates the number of requests.
	 */
	function updateRequestCount() {
		var $requests = $( '.requests-list .request' ),
			visibleRequestsCount = $requests.filter( ':not(.hide)' ).length;

		$( '.request-count' ).text(
			mw.msg( 'tsb-request-count', mw.language.convertNumber( visibleRequestsCount ) )
		);

		if ( $requests.length === 0 ) {
			$( '.details.pane' )
				.empty()
				.append(
					$( '<div>' )
						.addClass( 'tsb-header row' )
						.text( mw.msg( 'tsb-no-requests-from-new-users' ) )
				);
		}
	}

	/**
	 * Sets the height of the panes to the window height.
	 */
	function setPanesHeight() {
		var $detailsPane = $( '.details.pane' ),
			$requestsPane = $( '.requests.pane' ),
			detailsHeight = $( window ).height() - $detailsPane.offset().top,
			requestsHeight = detailsHeight -
				$requestsPane.find( '.request-footer' ).height() -
				$requestsPane.find( '.request-header' ).height();

		$detailsPane.css( 'max-height', detailsHeight );
		$requestsPane.find( '.requests-list' ).css( 'max-height', requestsHeight );
	}

	function selectAllRequests() {
		var selectedCount,
			$requestCheckboxes = $( '.request-selector' ),
			$detailsPane = $( '.details.pane' ),
			$selectAll = $( '.request-selector-all' ),
			$requestRows = $( '.requests .request' ),
			selectAllChecked = $selectAll.prop( 'checked' ),
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
		indicateOlderRequests();
	}

	/**
	 * Handle click on request row
	 *
	 * @param {jQuery.Event} e
	 */
	function onSelectRequest( e ) {
		var $requestRow = $( e.target ).closest( '.request' ),
			$requestRows = $( '.requests .request' ),
			$selectAll = $( '.request-selector-all' );

		displayRequestDetails( $requestRow.data( 'data' ) );

		// Clicking a row makes only that row selected and unselects all other rows
		$requestRows.each( function ( i, row ) {
			var $row = $( row );

			if ( row === $requestRow[ 0 ] ) {
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
		indicateOlderRequests();
	}

	/**
	 * Event handler for request checkbox selection.
	 *
	 * @param {jQuery.Event} e
	 */
	function requestSelectHandler( e ) {
		var checkedCount, $checkedBoxes,
			request = e.target,
			$detailsPane = $( '.details.pane' ),
			$requestCheckboxes = $( '.request-selector' ),
			$selectAll = $( '.request-selector-all' ),
			$thisRequestRow = $( request ).parents( 'div.request' );

		// Uncheck the rows that were selected by clicking the row
		$requestCheckboxes.filter( ':disabled' ).prop( 'disabled', false );

		if ( request.checked ) {
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
		indicateOlderRequests();

		e.stopPropagation();
	}

	/**
	 * Old request click handler.
	 *
	 * @param {jQuery.Event} e
	 */
	function oldRequestSelector( e ) {
		e.preventDefault();

		getOlderRequests().each( function ( index, request ) {
			$( request ).find( '.request-selector' )
				.prop( 'checked', true ) // Otherwise the state doesn't actually change
				.change();
		} );
	}

	// ======================================
	// LanguageFilter plugin
	// ======================================
	function LanguageFilter( element ) {
		this.$selector = $( element );
		this.init();
	}

	LanguageFilter.prototype.init = function () {
		var languageFilter = this,
			$clearButton;

		$clearButton = $( '<button>' )
			.addClass( 'clear-language-selector hide' )
			.text( '×' );

		languageFilter.$selector.after( $clearButton );
		// Activate language selector
		languageFilter.$selector.uls( {
			onSelect: function ( language ) {
				languageFilter.$selector
					.removeClass( 'unselected' )
					.addClass( 'selected autonym' )
					.prop( {
						dir: $.uls.data.getDir( language ),
						lang: language
					} )
					.text( $.uls.data.getAutonym( language ) );

				languageFilter.filter( language );
				$clearButton.removeClass( 'hide' );
				indicateOlderRequests();
			},
			quickList: mw.uls.getFrequentLanguageList
		} );

		$clearButton.on( 'click', function () {
			var userLang = mw.config.get( 'wgUserLanguage' );

			languageFilter.$selector
				.removeClass( 'selected autonym' )
				.prop( {
					dir: $.uls.data.getDir( userLang ),
					lang: userLang
				} )
				.addClass( 'unselected' )
				.text( mw.msg( 'tsb-all-languages-button-label' ) );

			languageFilter.filter();
			$clearButton.addClass( 'hide' );
		} );
	};

	/**
	 * Filter the requests by language.
	 *
	 * @param {string} [language] Language code
	 */
	LanguageFilter.prototype.filter = function ( language ) {
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
	};

	$.fn.languageFilter = function () {
		return this.each( function () {
			if ( !$.data( this, 'LanguageFilter' ) )  {
				$.data( this, 'LanguageFilter', new LanguageFilter( this ) );
			}
		} );
	};

	// ======================================
	// TranslatorSearch plugin
	// ======================================
	function TranslatorSearch( element ) {
		this.$search = $( element );
		this.init();
	}

	TranslatorSearch.prototype.init = function () {
		this.$search.on( 'search keyup', $.proxy( this.keyup, this ) );
	};

	TranslatorSearch.prototype.keyup = function () {
		var query,
			translatorSearch = this;

		// Respond to the keypress events after a small timeout to avoid freeze when typed fast
		delay( function () {
			query = $.trim( translatorSearch.$search.val() ).toLowerCase().trim();
			translatorSearch.filter( query );
		}, 300 );
	};

	TranslatorSearch.prototype.filter = function ( query ) {
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
			$( '.details.pane' ).empty();
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
	}() );

	$( document ).ready( function () {
		var $requestCheckboxes = $( '.request-selector' ),
			$selectAll = $( '.request-selector-all' ),
			$requestRows = $( '.requests .request' );

		// Delay so we get the correct height on page load
		window.setTimeout( setPanesHeight, 0 );
		$( window ).on( 'resize', setPanesHeight );

		$( '.request-filter-box' ).translatorSearch();
		$( '.language-selector' ).languageFilter();

		// Handle clicks for the 'Select all' checkbox
		$selectAll.on( 'click', selectAllRequests );

		// Handle clicks on request checkboxes.
		$requestCheckboxes.on( 'click change', requestSelectHandler );

		// Handle clicks on request rows.
		$requestRows.on( 'click',  onSelectRequest );

		$( '.older-requests-indicator' ).on( 'click',  oldRequestSelector );

		if ( $requestRows.length ) {
			$requestRows.first().click();
		}

		updateRequestCount();
	} );
}( jQuery, mediaWiki ) );
