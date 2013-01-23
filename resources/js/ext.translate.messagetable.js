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
				mcprop: [ 'definition', 'translation', 'tags', 'revision' ].join( '|' )
			};

			return $.get( apiURL, queryParams );
		}
	} );

	function messageFilterOverflowHandler () {
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
			messageFilterOverflowHandler() ;
		}
	}

	/**
	 * Add a message to the message table
	 */
	function addMessage( message ) {
		var $message,targetLanguage, targetLanguageDir, sourceLanguage, sourceLanguageDir,
			status = '',
			statusMsg = '',
			$messageWrapper,
			$messageList;

		$messageList = $( '.tux-messagelist' );

		sourceLanguage = $messageList.data( 'sourcelangcode' );
		sourceLanguageDir = $.uls.data.getDir( sourceLanguage );
		targetLanguage = $messageList.data( 'targetlangcode' );
		targetLanguageDir = $.uls.data.getDir( targetLanguage );

		if ( message.translation ) {
			status = 'translated';
		}

		//if ( message.tags.length ) {
			// FIXME: proofread is not coming in tags.
			//status += message.tags.join( ' ' );
		//}

		if ( status ) {
			statusMsg = 'tux-status-' + status;
		}

		$messageWrapper = $( '<div>' )
			.addClass( 'row tux-message' )
			.attr( {
				'data-translation': message.translation,
				'data-source': message.definition,
				'data-title': message.title,
				'data-group': message.group
			} );

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
							.addClass( 'tux-status-' + status )
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
		$messageWrapper.translateeditor();
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

		$( '.tux-messagetable-loader' ).appear( function () {
			var messagegroup, pageSize, remaining, targetLanguage,
				$this = $( this ),
				$messageList = $( '.tux-messagelist' ),
				offset = $this.data( 'offset' );

			if ( offset === '-1' ) {
				return false;
			}

			messagegroup = $this.data( 'messagegroup' );
			pageSize = $this.data( 'pagesize' );
			remaining = $this.data( 'remaining' );
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
					offset = result['query-continue'].messagecollection.mcoffset;
					$( '.tux-messagetable-loader' ).data( 'offset', offset )
						.data( 'remaining', remaining - pageSize );
					$( '.tux-messagetable-loader-count' )
						.text( mw.msg( 'tux-messagetable-more-messages', remaining - pageSize  ) );
				} else {
					// End of messages
					$( '.tux-messagetable-loader' ).data( 'offset', -1 )
						.addClass( 'hide' );
				}
			} );
		}, {
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

	function messageListScrollHandler () {
		var $window = $( window ),
			$messageList = $( '.tux-messagelist' ),
			$tuxActionBar = $( '.tux-action-bar' ),
			isFloating = $tuxActionBar.hasClass( 'floating' ),
			needFloat = $window.scrollTop() + $window.height() < (
				$messageList.offset().top + $messageList.height() ),
			needStick = $window.scrollTop() + $window.height() > (
				$messageList.offset().top + $messageList.height() + $tuxActionBar.height() );

		if ( !isFloating && needFloat ) {
			$tuxActionBar.addClass( 'floating' );
			$tuxActionBar.width( $messageList.width() );
		} else if ( isFloating && needStick ) {
			$tuxActionBar.removeClass( 'floating' );
		} else if ( isFloating && needFloat ) {
			$tuxActionBar.css( 'left', $messageList.offset().left - $window.scrollLeft() );
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
