/*!
 * TranslationStash front-end logic.
 *
 * @author Santhosh Thottingal
 * @license GPL-2.0+
 * @since 2013.10
 */

( function ( $, mw ) {
	'use strict';

	var userTranslations = {},
		translationStashStorage = new mw.translate.TranslationStashStorage();

	mw.translate.canTranslate = function () {
		// At this page, the new translator can translate
		return true;
	};

	function getMessages( messageGroup, language, offset, limit ) {
		var deferred = new mw.Api().get( {
			action: 'query',
			list: 'messagecollection',
			mcgroup: messageGroup,
			format: 'json',
			mclanguage: language,
			mcoffset: offset,
			mclimit: limit,
			mcprop: 'definition'
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
			statusClass = 'tux-status-' + status,
			statusMsg;

		if ( status === 'translated' ) {
			// tux-status-translated
			statusMsg = 'tux-status-' + status;
		}

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
							.text( statusMsg ? mw.msg( statusMsg ) : '' )
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
			storage: translationStashStorage,
			onSave: updateStats,
			onSkip: function () {
				var $next = this.$editTrigger.next( '.tux-message' );

				// If there is text in the skipped message, avoid showing the
				// regular "you have unsaved messages" when navigating away,
				// because there is no way to get back to these messages.
				this.markUnunsaved();

				// This can happen when it's
				// the last message in the translation stash
				if ( !$next.length ) {
					// Reload the page to get more messages
					// when we get to the last one
					window.location.reload();
				}
			},
			onReady: function () {
				this.$editor.find( '.tux-editor-skip-button' )
					.text( mw.msg( 'translate-translationstash-skip-button-label' ) );
			}
		} );
	}

	/**
	 * Updates the translation count at the top of the message list and
	 * displays warning when translation limit has been reached.
	 * Relies on classes stash-stats and tux-status-translated.
	 */
	function updateStats() {
		var count,
			$target = $( '.stash-stats' );

		count = $( '.tux-status-translated' ).length;
		if ( count === 0 ) {
			return;
		}

		$target.text( mw.msg(
			'translate-translationstash-translations',
			mw.language.convertNumber( count )
		) );

		if ( count >= mw.config.get( 'wgTranslateSandboxLimit' ) ) {
			// Remove the untranslated message to disallow translation beyond the limit
			$( '.tux-message' ).has( '.untranslated' ).remove();

			// Show a message telling that the limit was reached
			$( '.limit-reached' )
				.empty()
				.append( $( '<h1>' ).text( mw.msg( 'tsb-limit-reached-title' ) ) )
				.append( $( '<p>' ).text( mw.msg( 'tsb-limit-reached-body' ) ) )
				.removeClass( 'hide' );
		}
	}

	function loadMessages() {
		var $messageTable = $( '.tux-messagelist' ),
			messagegroup = '!sandbox';

		$( '<div>' )
			.addClass( 'tux-loading-indicator' )
			.appendTo( $messageTable );

		getMessages( messagegroup, $messageTable.data( 'targetlangcode' ) )
			.done( function ( result ) {
				var untranslated, messages = result.query.messagecollection;

				$messageTable.empty();
				$.each( messages, function ( index, message ) {
					message.properties = {};
					message.properties.status = 'untranslated';

					message.group = messagegroup;
					if ( userTranslations[ message.title ] ) {
						message.translation = userTranslations[ message.title ].translation;
						message.properties.status = 'translated';
					}

					addMessage( message );
				} );

				// Show the editor for the first untranslated message.
				untranslated = $( '.tux-message' )
					.has( '.tux-message-item.untranslated' )
					.first();
				if ( untranslated.length ) {
					untranslated.data( 'translateeditor' ).show();
				}

				updateStats();
			} ).fail( function ( errorCode, response ) {
				$messageTable.empty().addClass( 'error' )
					.text( 'Error: ' + errorCode + ' - ' +
						( response.error && response.error.info || 'Unknown error' )
					);
			} );
	}

	$( 'document' ).ready( function () {
		var $messageTable = $( '.tux-messagelist' ),
			$ulsTrigger = $( '.ext-translate-language-selector > .uls' );

		// Some links in helpers will navigate away by default. But since the messages
		// will change on this page on every load, we want to avoid that. Force the
		// links to open on new window/tab.
		mw.translateHooks.add( 'showTranslationHelpers', function ( helpers, $editor ) {
			$editor.find( 'a' ).prop( 'target', '_blank' );
		} );

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
			.done( function ( translations ) {
				if ( translations.translationstash.translations ) {
					$.each( translations.translationstash.translations,
						function ( index, translation ) {
							userTranslations[ translation.title ] = translation;
						} );
				}
				loadMessages();
			} );
	} );
}( jQuery, mediaWiki ) );
