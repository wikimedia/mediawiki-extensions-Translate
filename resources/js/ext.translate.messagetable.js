( function ( $, mw ) {
	'use strict';

	mw.translate = mw.translate || {};

	mw.translate = $.extend( mw.translate, {
		getMessages: function ( messageGroup, language, offset, limit, filter ) {
			var queryParams,
				apiURL = mw.util.wikiScript( 'api' );

			queryParams = {
				action: 'query',
				list: 'messagecollection',
				mcgroup: messageGroup,
				format: 'json',
				mclanguage: language,
				mcoffset: offset,
				mclimit: limit,
				mcfilter: filter || mw.Uri().query.filter,
				mcprop: [ 'definition', 'translation', 'tags', 'properties' ].join( '|' )
			};

			return $.get( apiURL, queryParams );
		},

		loadMessages: function () {
			$( '.tux-messagetable-loader' ).trigger( 'appear' );
		}
	} );

	function messageFilterOverflowHandler() {
		var actualWidth = 0;

		// Calculate the total width required for the filters
		$( '.row.tux-message-selector >li' ).each( function () {
			actualWidth += $( this ).outerWidth( true );
		} );

		// Grid row has a min width. After that scrollbars will appear.
		// We are checking whether the filters is wider than the current grid row width
		if ( actualWidth >= parseInt( $( '.nine.columns' ).width(), 10 ) ) {
			$( '.tux-message-selector .more ul' ) // Overflow menu
				.prepend( $( '.row.tux-message-selector > li.column:last' ).prev() );

			// See if more items to be pushed to the overflow menu
			messageFilterOverflowHandler();
		}
	}

	/**
	 * Add a message to the message table
	 */
	function addMessage( message ) {
		var $message, targetLanguage, targetLanguageDir, sourceLanguage, sourceLanguageDir,
			status = '',
			statusMsg = '',
			statusClass = '',
			$messageWrapper,
			$messageList;

		$messageList = $( '.tux-messagelist' );

		sourceLanguage = $messageList.data( 'sourcelangcode' );
		sourceLanguageDir = $.uls.data.getDir( sourceLanguage );
		targetLanguage = $messageList.data( 'targetlangcode' );
		targetLanguageDir = $.uls.data.getDir( targetLanguage );

		status = message.properties.status;
		statusClass = 'tux-status-' + status;

		if ( message.tags.length
			&& $.inArray( 'optional', message.tags ) >= 0
			&& status === 'untranslated'
		) {
			status = 'optional';
			statusClass = 'tux-status-optional';
		}

		// Fuzzy translations need warning class
		if ( status === 'fuzzy' ) {
			statusClass = statusClass + ' tux-warning';
		}

		// Label the status if it is not untranslated
		if ( status !== 'untranslated' ) {
			statusMsg = 'tux-status-' + status;
		}

		$messageWrapper = $( '<div>' )
			.addClass( 'row tux-message' )
			.data( 'message', message );

		$message = $( '<div>' )
			.addClass( 'row tux-message-item ' + status )
			.append(
				$( '<div>' )
					.addClass( 'eight columns tux-list-message' )
					.append(
						$( '<span>' )
							.addClass( 'tux-list-source' )
							.attr( {
								lang: sourceLanguage,
								dir: sourceLanguageDir
							} )
							.text( message.definition ),
						$( '<span>' )
							.addClass( 'tux-list-translation' )
							.attr( {
								lang: targetLanguage,
								dir: targetLanguageDir
							} )
							.text( message.translation || '' )
						),
				$( '<div>' )
					.addClass( 'two columns tux-list-status text-center' )
					.append(
						$( '<span>' )
							.addClass( statusClass )
							.text( statusMsg ? mw.msg( statusMsg ) : '' )
					),
				$( '<div>' )
					.addClass( 'two column tux-list-edit text-right' )
					.append(
						$( '<a>' )
							.attr( {
								'title': mw.msg( 'translate-edit-title', message.key )
							} )
							.text( mw.msg( 'tux-edit' ) )
					)
			);

		$messageWrapper.append( $message );
		$( '.tux-messagetable-loader' ).before( $messageWrapper );

		// Attach translate editor to the message
		$messageWrapper.translateeditor( {
			message: message
		} );
	}

	function messageTableLoader() {
		var messagegroup,
			pageSize,
			remaining,
			targetLanguage,
			query,
			$loader = $( '.tux-messagetable-loader' ),
			$messageList = $( '.tux-messagelist' ),
			offset = $loader.data( 'offset' ),
			filter = $loader.data( 'filter' );

		messagegroup = $loader.data( 'messagegroup' );
		pageSize = $loader.data( 'pagesize' );
		targetLanguage = $messageList.data( 'targetlangcode' );

		if ( offset === -1 ) {
			return;
		}

		$.when(
			mw.translate.getMessages( messagegroup, targetLanguage, offset, pageSize, filter )
		).then( function ( result ) {
			var messages = result.query.messagecollection;

			$.each( messages, function ( index, message ) {
				message.group = messagegroup;
				addMessage( message );
			} );

			if ( result['query-continue'] === undefined ) {
				// End of messages
				$loader.data( 'offset', -1 ).addClass( 'hide' );
				return;
			}

			// Dynamically loaded messages should pass the search filter if present.
			query = $( '.tux-message-filter-box' ).val();
			if ( query ) {
				search( query );
			}

			$loader.data( 'offset', result['query-continue'].messagecollection.mcoffset );

			remaining = result.query.metadata.remaining;
			$( '.tux-messagetable-loader-count' ).text(
				mw.msg( 'tux-messagetable-more-messages', remaining )
			);
			$( '.tux-messagetable-loader-more' ).text(
				mw.msg( 'tux-messagetable-loading-messages', Math.min( remaining, pageSize ) )
			);
		} );
	}

	/**
	 * Search the message filter
	 *
	 * @param {String} query
	 */
	function search( query ) {
		var $messageTable,
			resultCount = 0,
			$result,
			matcher = new RegExp( '\\b' + escapeRegex( query ), 'i' );

		$messageTable = $( '.tux-messagelist' );
		$messageTable.find( '.tux-message' ).each( function () {
			var $message = $( this ),
				message = $message.data( 'message' );

			if ( matcher.test( message.definition ) || matcher.test( message.translation ) ) {
				$message.removeClass( 'hide' );
				resultCount++;
			} else {
				$message.addClass( 'hide' );
			}
		} );

		$result = $messageTable.find( '.tux-message-filter-result' );
		if ( !$result.length ) {
			$result = $( '<div>' ).addClass( 'row highlight tux-message-filter-result' )
				.append(
					$( '<div>' )
						.addClass( 'ten columns advanced-search' ),
					$( '<button>' )
						.addClass( 'two columns button advanced-search' )
						.text( mw.msg( 'tux-message-filter-advanced-button' ) )
				);
			$messageTable.prepend( $result );
		}

		if ( !query ) {
			$result.addClass( 'hide' );
			$( '.tux-message-filter-box-clear' ).addClass( 'hide' );
		} else {
			$result.removeClass( 'hide' )
				.find( 'div' )
				.text( mw.msg( 'tux-message-filter-result', resultCount, query ) );
			$result.find( 'button' ).on( 'click', function () {
				window.location.href = new mw.Uri( mw.util.wikiGetlink( 'Special:SearchTranslations' ) )
					.extend( { query: query } );
			} );
			$( '.tux-message-filter-box-clear' ).removeClass( 'hide' );
			mw.translate.loadMessages();
		}
	}

	$( 'document' ).ready( function () {
		// Currently used only in the pre-TUX editor
		$( '.mw-translate-messagereviewbutton' ).click( function () {
			var $b, successFunction, failFunction, params;
			$b = $( this );

			successFunction = function ( data ) {
				if ( data.error ) {
					var reason = mw.msg( 'api-error-' + data.error.code );
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
				token: $b.data( 'token' ),
				revision: $b.data( 'revision' ),
				format: 'json'
			};
			$b.val( mw.msg( 'translate-messagereview-progress' ) );
			$b.prop( 'disabled', true );

			$.post( mw.util.wikiScript( 'api' ), params, successFunction ).fail( failFunction );
		} );

		$( '.tux-messagetable-loader' ).appear( messageTableLoader, {
			// Appear callback need to be called more than once.
			one: false
		} );

		$( '.tux-message-filter-box' ).on( 'input propertychange', function () {
			delay( search( $( this ).val() ), 300 );
		} );

		$( '.tux-message-filter-box-clear' ).on( 'click', function () {
			$( '.tux-message-filter-box' ).focus().val( '' );
			$( this ).addClass( 'hide' );
		} );

		messageFilterOverflowHandler();
	} );

	$( window ).resize( function () {
		messageFilterOverflowHandler();
		$( '.tux-action-bar' ).width( $( '.tux-messagelist' ).width() );
	} );

	$( window ).scroll( function () {
		delay( messageListScrollHandler, 300 );
	} );

	function messageListScrollHandler() {
		var $window,
			$tuxActionBar,
			isFloating,
			needFloat, needStick,
			windowScrollBottom,
			messageListOffset, messageListHeight, messageListBottom,
			$messageList = $( '.tux-messagelist' );

		$window = $( window );
		$tuxActionBar = $( '.tux-action-bar' );
		isFloating = $tuxActionBar.hasClass( 'floating' );

		windowScrollBottom = $window.scrollTop() + $window.height();
		messageListOffset = $messageList.offset();
		messageListHeight = $messageList.height();
		messageListBottom = messageListOffset.top + messageListHeight;
		needFloat = windowScrollBottom < messageListBottom;
		needStick = windowScrollBottom > ( messageListBottom + $tuxActionBar.height() );

		if ( !isFloating && needFloat ) {
			$tuxActionBar
				.addClass( 'floating' )
				.width( $messageList.width() );
		} else if ( isFloating && needStick ) {
			$tuxActionBar.removeClass( 'floating' );
		} else if ( isFloating && needFloat ) {
			$tuxActionBar.css( 'left', messageListOffset.left - $window.scrollLeft() );
		}
	}

	var delay = ( function () {
		var timer = 0;

		return function ( callback, milliseconds ) {
			clearTimeout( timer );
			timer = setTimeout( callback, milliseconds );
		};
	} () );

	/**
	 * Escape the search query for regex match
	 * @param {string} value A search string to be escaped.
	 * @returns {string} Escaped string that is safe to use for a search.
	 */
	function escapeRegex( value ) {
		return value.replace( /[\-\[\]{}()*+?.,\\\^$\|#\s]/g, '\\$&' );
	}

}( jQuery, mediaWiki ) );
