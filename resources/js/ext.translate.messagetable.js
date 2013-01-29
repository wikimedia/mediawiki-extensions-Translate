( function ( $, mw ) {
	'use strict';

	mw.translate = mw.translate || {};

	mw.translate = $.extend( mw.translate, {
		getMessages: function ( messageGroup, language, offset, limit ) {
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
				mcfilter: mw.Uri().query.filter,
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
		if ( actualWidth >= parseInt( $( '.row' ).width(), 10 ) ) {
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
					.addClass( 'nine columns tux-list-message' )
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
					.addClass( 'one column tux-list-edit text-center' )
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
			$loader = $( '.tux-messagetable-loader' ),
			$messageList = $( '.tux-messagelist' ),
			offset = $loader.data( 'offset' );

		messagegroup = $loader.data( 'messagegroup' );
		pageSize = $loader.data( 'pagesize' );
		remaining = $loader.data( 'remaining' );
		targetLanguage = $messageList.data( 'targetlangcode' );

		$.when(
			mw.translate.getMessages( messagegroup, targetLanguage, offset, pageSize )
		).then( function ( result ) {
			var messages = result.query.messagecollection;

			$.each( messages, function ( index, message ) {
				message.group = messagegroup;
				addMessage( message );
			} );

			if ( result['query-continue'] ) {
				remaining = remaining - pageSize;
				offset = result['query-continue'].messagecollection.mcoffset;
				$loader.data( 'offset', offset )
					.data( 'remaining', remaining );
				$( '.tux-messagetable-loader-count' )
					.text( mw.msg( 'tux-messagetable-more-messages', remaining ) );
			} else {
				// End of messages
				$loader.data( 'offset', -1 ).addClass( 'hide' );
			}
		} );
	}

	$( 'document' ).ready( function () {
		// Currently used only in the pre-TUX editor
		$( '.mw-translate-messagereviewbutton' ).click( function () {
			var $b, successFunction, failFunction, params;
			$b = $( this );

			// TODO Avoid creating functions inside a loop
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
}( jQuery, mediaWiki ) );
