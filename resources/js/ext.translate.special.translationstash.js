/**
 * TranslationStash front-end logic
 * @author Santhosh Thottingal
 * @license GPL-2.0+
 * @since 2013.10
 */

( function ( $, mw ) {
	'use strict';

	mw.translate.canTranslate = function () {
		// At this page, the new translator can translate
		return true;
	};

	var userTranslations = {},
		translationStashStorage = new mw.translate.TranslationStashStorage();

	function getMessages( messageGroup, language, offset, limit ) {
		var deferred = new mw.Api().get( {
			action: 'query',
			list: 'messagecollection',
			mcgroup: messageGroup,
			format: 'json',
			mclanguage: language,
			mcoffset: offset,
			mclimit: limit,
			mcprop: 'definition|properties'
		} );

		return deferred.promise();
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
			storage: translationStashStorage
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
					if ( userTranslations[message.title] ) {
						message.translation = userTranslations[message.title].value;
					}

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
		// Get the user translations if any(possibly from an early attempt)
		// and new messages to try.
		translationStashStorage.getUserTranslations()
			.done( function( translations ) {
				if ( translations.translationstash.translations ) {
					$.each( translations.translationstash.translations,
						function ( index, translation ) {
							userTranslations[translation.title] = translation;
					} );
				}
				loadMessages();
			} );
	} );
}( jQuery, mediaWiki ) );
