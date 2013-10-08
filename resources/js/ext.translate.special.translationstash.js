/**
 * TranslationStash front-end logic
 * @author Santhosh Thottingal
 * @license GPL-2.0+
 * @since 2013.10
 */

( function ( $, mw ) {
	'use strict';

	function getMessages( messageGroup, language, offset, limit ) {
		var api = new mw.Api();

		return api.get( {
			action: 'query',
			list: 'messagecollection',
			mcgroup: messageGroup,
			format: 'json',
			mclanguage: language,
			mcoffset: offset,
			mclimit: limit,
			mcprop: 'definition|properties'
		} );

		// @todo: We need to get translations from the stash api
	}

	function addMessage( message ) {
		var $messageWrapper, $message,
			$messageTable = $( '.tux-messagelist' ),
			sourceLanguage = $messageTable.data( 'sourcelangcode' ),
			sourceLanguageDir = $.uls.data.getDir( sourceLanguage ),
			targetLanguage = $messageTable.data( 'targetlangcode' ),
			targetLanguageDir = $.uls.data.getDir( targetLanguage ),
			status = message.properties.status,
			statusClass = 'tux-status-' + status;

		$messageWrapper = $( '<div>' )
			.addClass( 'row tux-message' );

		$message = $( '<div>' )
			.addClass( 'row message tux-message-item ' + status )
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
						// Bidirectional isolation.
						// This should be removed some day when proper
						// unicode-bidi: isolate
						// is supported everywhere
						$( '<span>' )
							.html( $( 'body' ).hasClass( 'rtl' ) ? '&rlm;' : '&lrm;' ),
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
					),
				$( '<div>' )
					.addClass( 'two column tux-list-edit text-right' )
					.append(
						$( '<a>' )
							.attr( {
								title: mw.msg( 'translate-edit-title', message.key )
							} )
							.text( mw.msg( 'tux-edit' ) )
					)
			);

		$messageWrapper.append( $message );
		$messageTable.append( $messageWrapper );
		// Attach translate editor to the message
		$messageWrapper.translateeditor( {
			message: message,
			storage: new mw.translate.TranslationStashStorage()
		} );
	}

	function loadMessages() {
		var $messageTable = $( '.tux-messagelist' ),
			messagegroup = '!sandbox';

		getMessages( messagegroup, $messageTable.data( 'targetlangcode' ), 0, 20 )
			.done( function ( result ) {
				var messages = result.query.messagecollection;
				$.each( messages, function ( index, message ) {
					message.group = messagegroup;
					addMessage( message );
					if ( index === 0 ) {
						// Show the editor for the first message.
						$( '.tux-message:first' ).data( 'translateeditor' ).show();
					}
				} );
			} ).fail( function ( errorCode, response ) {
				$messageTable.empty().addClass( 'error' )
					.text( 'Error: ' + errorCode + ' - ' + response.error.info );
			} );
	}

	$( 'document' ).ready( function () {
		var $messageTable = $( '.tux-messagelist' ),
			$ulsTrigger = $( '.ext-translate-language-selector > .uls' );

		$ulsTrigger.uls( {
			onSelect: function ( language ) {
				var direction = $.uls.data.getDir( language ),
					autonym = $.uls.data.getAutonym( language );

				$ulsTrigger
					.text( autonym )
					.attr( {
						lang: language,
						dir: direction
					} );

				$messageTable
					.empty()
					.data( {
						targetlangcode: language,
						targetlangdir: direction
					} );

				loadMessages();
			}
		} );
		loadMessages();
	} );
}( jQuery, mediaWiki ) );
