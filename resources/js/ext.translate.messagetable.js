( function ( $, mw ) {
	'use strict';

	mw.translate = mw.translate || {};

	mw.translate = $.extend( mw.translate, {
		getMessages: function ( messageGroup, language, offset, limit, filter ) {
			var api = new mw.Api();

			return api.get( {
				action: 'query',
				list: 'messagecollection',
				mcgroup: messageGroup,
				format: 'json',
				mclanguage: language,
				mcoffset: offset,
				mclimit: limit,
				mcfilter: filter,
				mcprop: 'definition|translation|tags|properties'
			} );
		},

		loadMessages: function ( changes ) {
			var $loader = $( '.tux-messagetable-loader' );

			changes = changes || {};

			// Clear current messages
			$( '.tux-messagelist' ).trigger( 'clear' );

			// Change the properties that are provided
			if ( changes.filter !== undefined ) {
				$loader.data( 'filter', changes.filter );
			}
			if ( changes.group !== undefined ) {
				$loader.data( 'messagegroup', changes.group );
			}

			// Reset the number of messages remaining
			$loader.find( '.tux-messagetable-loader-count' ).text( '' );

			// Reset other info and make visible
			$loader
				.removeData( 'offset' )
				.removeAttr( 'data-offset' )
				.removeClass( 'hide' );

			// And start loading
			$loader.trigger( 'appear' );
		}
	} );

	function MessageTable( container, options ) {
		this.$container = $( container );
		this.options = options;
		this.options = $.extend( {}, $.fn.messagetable.defaults, options );
		// mode can be proofread, page or translate
		this.mode = this.options.mode;
		this.firstProofreadTipShown = false;
		this.$loader = this.$container.siblings( '.tux-messagetable-loader' );
		this.$actionBar = this.$container.siblings( '.tux-action-bar' );
		this.messages = [];
		this.loading = false;
		this.init();
		this.listen();
	}

	MessageTable.prototype = {
		init: function () {
			this.switchMode( this.mode );
		},

		listen: function () {
			var messageTable = this,
				$filterInput = this.$container.parent().find( '.tux-message-filter-box' );

			$( window ).scroll( function () {
				delay( function () {
					messageTable.scroll();
				}, 200 );
			} ).resize( function () {
				messageTable.resize();
				$( '.tux-messagetable-header' ).width( $( '.tux-messagelist' ).width() );
				$( '.tux-action-bar' ).width( $( '.tux-messagelist' ).width() );
			} );

			if ( mw.translate.isPlaceholderSupported( $filterInput ) ) {
				$filterInput.prop( 'placeholder', mw.msg( 'tux-message-filter-placeholder' ) );
			}

			$filterInput.on( 'textchange', function () {
				delay( function () {
					messageTable.search( $filterInput.val() );
				}, 300 );
			} );

			$( '.tux-message-filter-box-clear' ).on( 'click', function () {
				$filterInput.focus().val( '' ).trigger( 'input' );
			} );

			this.$container.on( 'clear', $.proxy( messageTable.clear, messageTable ) );

			this.$loader.appear( function () {
				messageTable.load();
			}, {
				// Appear callback need to be called more than once.
				one: false
			} );

			this.$actionBar.find( 'button.tux-proofread-button' ).on( 'click', function () {
				messageTable.switchMode( 'proofread' );
			} );

			this.$actionBar.find( 'button.translate-mode-button' ).on( 'click', function () {
				messageTable.switchMode( 'translate' );
			} );

			this.$actionBar.find( 'button.page-mode-button' ).on( 'click', function () {
				messageTable.switchMode( 'page' );
			} );
		},

		/**
		 * Clear the message table
		 */
		clear: function () {
			$( '.tux-messagelist' ).empty();
			this.messages = [];
			// Any ongoing loading process will notice this and will reject results.
			this.loading = false;
		},

		add: function ( message ) {
			var $message;

			// Prepare the message for display
			mw.translateHooks.run( 'formatMessageBeforeTable', message );

			if ( this.mode === 'translate' ) {
				this.addTranslate( message );

				return;
			}

			if ( this.mode === 'proofread' ) {
				$message = this.addProofread( message );

				if ( !this.firstProofreadTipShown ) {
					if ( $message.find( '.tux-proofread-action' ).length ) {
						$message.find( '.tux-proofread-action' ).tipsy( 'show' );
						this.firstProofreadTipShown = true;
					}
				}

				return;
			}

			if ( this.mode === 'page' ) {
				this.addPageModeMessage( message );
			}
		},

		/**
		 * Add a message to the message table for translation.
		 */
		addTranslate: function ( message ) {
			var $message, targetLanguage, targetLanguageDir, sourceLanguage, sourceLanguageDir,
				status,
				statusMsg = '',
				statusClass = '',
				$messageWrapper;

			sourceLanguage = this.$container.data( 'sourcelangcode' );
			sourceLanguageDir = $.uls.data.getDir( sourceLanguage );
			targetLanguage = this.$container.data( 'targetlangcode' );
			targetLanguageDir = $.uls.data.getDir( targetLanguage );

			status = message.properties.status;
			statusClass = 'tux-status-' + status;

			if ( message.tags.length &&
				$.inArray( 'optional', message.tags ) >= 0 &&
				status === 'untranslated'
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
				// Give grep a chance to find the usages:
				// tux-status-optional, tux-status-fuzzy, tux-status-proofread,
				// tux-status-translated, tux-status-saving, tux-status-unsaved
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
									'title': mw.msg( 'translate-edit-title', message.key )
								} )
								.text( mw.msg( 'tux-edit' ) )
						)
				);

			$messageWrapper.append( $message );
			this.$container.append( $messageWrapper );

			// Attach translate editor to the message
			$messageWrapper.translateeditor( {
				message: message
			} );
		},

		/**
		 * Add a message to the message table for proofreading.
		 */
		addProofread: function ( message ) {
			var $message = $( '<div>' )
				.addClass( 'row tux-message-proofread' );

			this.$container.append( $message );
			$message.proofread( {
				message: message,
				sourcelangcode: this.$container.data( 'sourcelangcode' ),
				targetlangcode: this.$container.data( 'targetlangcode' )
			} );

			return $message;
		},

		addPageModeMessage: function ( message ) {
			var $message;

			$message = $( '<div>' )
				.addClass( 'row tux-message tux-message-pagemode' );

			this.$container.append( $message );
			$message.pagemode( {
				message: message,
				sourcelangcode: this.$container.data( 'sourcelangcode' ),
				targetlangcode: this.$container.data( 'targetlangcode' )
			} );
		},

		/**
		 * Search the message filter
		 *
		 * @param {String} query
		 */
		search: function ( query ) {
			var resultCount = 0,
				$result,
				matcher = new RegExp( '(^|\\s|\\b)' + escapeRegex( query ), 'gi' ),
				itemsClass = {
					proofread: '.tux-message-proofread',
					page: '.tux-message-pagemode',
					translate: '.tux-message'
				};

			this.$container.find( itemsClass[ this.mode ] ).each( function () {
				var $message = $( this ),
					message = ( $message.data( 'translateeditor' ) ||
						$message.data( 'pagemode' ) ||
						$message.data( 'proofread' ) ).message;

				if ( matcher.test( message.definition ) || matcher.test( message.translation ) ) {
					$message.removeClass( 'hide' );
					resultCount++;
				} else {
					$message.addClass( 'hide' );
				}
			} );

			$result = this.$container.find( '.tux-message-filter-result' );
			if ( !$result.length ) {
				$result = $( '<div>' ).addClass( 'row highlight tux-message-filter-result' )
					.append(
						$( '<div>' )
							.addClass( 'ten columns advanced-search' ),
						$( '<button>' )
							.addClass( 'two columns button advanced-search' )
							.text( mw.msg( 'tux-message-filter-advanced-button' ) )
					);
				this.$container.prepend( $result );
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
			}

			this.$loader.trigger( 'appear' );

			// Trigger a scroll event for the window to make sure all floating toolbars
			// are in their position.
			$( window ).trigger( 'scroll' );
		},

		resize: function () {
			var actualWidth = 0;

			// Calculate the total width required for the filters
			$( '.row.tux-message-selector > li' ).each( function () {
				actualWidth += $( this ).outerWidth( true );
			} );

			// Grid row has a min width. After that scrollbars will appear.
			// We are checking whether the message table is wider than the current grid row width.
			if ( actualWidth >= parseInt( $( '.nine.columns' ).width(), 10 ) ) {
				$( '.tux-message-selector .more ul' ) // Overflow menu
					.prepend( $( '.row.tux-message-selector > li.column:last' ).prev() );

				// See if more items to be pushed to the overflow menu
				this.resize();
			}
		},

		load: function () {
			var messagegroup,
				pageSize,
				remaining,
				targetLanguage,
				query,
				messageTable = this,
				$messageList = $( '.tux-messagelist' ),
				offset = this.$loader.data( 'offset' ),
				filter = messageTable.$loader.data( 'filter' );

			messagegroup = messageTable.$loader.data( 'messagegroup' );
			pageSize = messageTable.$loader.data( 'pagesize' );
			targetLanguage = $messageList.data( 'targetlangcode' );

			if ( offset === -1 ) {
				return;
			}

			if ( messageTable.loading ) {
				// Avoid duplicate loading - the offset will be wrong and it will result
				// in duplicate messages shown in the page
				return;
			}

			messageTable.loading = true;

			mw.translate.getMessages( messagegroup, targetLanguage, offset, pageSize, filter )
				.done( function ( result ) {
					var messages = result.query.messagecollection,
						state;

					if ( !messageTable.loading ) {
						// reject. This was cancelled.
						return;
					}

					messageTable.loading = false;

					if ( messages.length === 0 ) {
						// And this is the first load for the filter...
						if ( messageTable.$container.children().length === 0 ) {
							messageTable.displayEmptyListHelp();
						}
					}

					$.each( messages, function ( index, message ) {
						message.group = messagegroup;
						messageTable.add( message );
						messageTable.messages.push( message );

						if ( index === 0 && messageTable.mode === 'translate' ) {
							$( '.tux-message:first' ).data( 'translateeditor' ).init();
						}
					} );

					state = result.query.metadata && result.query.metadata.state;
					$( '.tux-workflow' ).workflowselector( messagegroup, targetLanguage, state );

					// Dynamically loaded messages should pass the search filter if present.
					query = $( '.tux-message-filter-box' ).val();

					if ( query ) {
						messageTable.search( query );
					}

					if ( result['query-continue'] === undefined ) {
						// End of messages
						messageTable.$loader.data( 'offset', -1 ).addClass( 'hide' );
						return;
					}

					messageTable.$loader.data( 'offset', result['query-continue'].messagecollection.mcoffset );

					remaining = result.query.metadata.remaining;

					$( '.tux-messagetable-loader-count' ).text(
						mw.msg( 'tux-messagetable-more-messages', remaining )
					);

					$( '.tux-messagetable-loader-more' ).text(
						mw.msg( 'tux-messagetable-loading-messages', Math.min( remaining, pageSize ) )
					);
					// Make sure the floating toolbars are visible without the need for scroll
					$( window ).trigger( 'scroll' );
				} )
				.fail( function ( errorCode, response ) {
					if ( response.error.code === 'mctranslate-language-disabled' ) {
						$( '.tux-editor-header .group-warning' )
							.text( mw.msg( 'translate-language-disabled' ) )
							.show();
					}
					messageTable.$loader.data( 'offset', -1 ).addClass( 'hide' );
					messageTable.loading = false;
				} );
		},

		/**
		 * If the user selection results nothing to show, give some pointers
		 * what to do.
		 */
		displayEmptyListHelp: function () {
			var tab, $actionButton, $filterLink,
				messageTable = this,
				$wrap = $( '<div>' ).addClass( 'tux-empty-list' ),
				$empty = $( '<div>' ).addClass( 'tux-empty-list-header' ),
				$guide = $( '<div>' ).addClass( 'tux-empty-list-guide' ),
				$actions = $( '<div>' ).addClass( 'tux-empty-list-actions' );

			// Ugly! This should be provided somehow
			tab = $( '.tux-message-selector .selected' ).data( 'filter' );

			if ( tab === '' ) {
				$empty.text( mw.msg( 'tux-empty-list-all' ) );
				$guide.text( mw.msg( 'tux-empty-list-all-guide' ) );

			} else if ( tab === 'translated' ) {
				$empty.text( mw.msg( 'tux-empty-list-translated' ) );
				$guide.text( mw.msg( 'tux-empty-list-translated-guide' ) );
				$actionButton = $( '<button>' )
					.text( mw.msg( 'tux-empty-list-translated-action' ) )
					.addClass( 'green button' )
					.click( function () {
						mw.translate.changeFilter( $( '.tux-tab-untranslated' ).click() );
					} );
				$actions.append( $actionButton );

			} else {
				$empty.text( mw.msg( 'tux-empty-list-other' ) );
				if ( mw.translate.canProofread() ) {
					$guide.text( mw.msg( 'tux-empty-list-other-guide' ) );
					$actionButton = $( '<button>' )
						.text( mw.msg( 'tux-empty-list-other-action' ) )
						.addClass( 'green button' )
						.click( function () {
							messageTable.switchMode( 'proofread' );
						} );
					$actions.append( $actionButton );
				}
				$filterLink = $( '<a>' )
					.text( mw.msg( 'tux-empty-list-other-link' ) )
					.click( function () {
						$( '.tux-tab-all' ).click();
					} );
				$actions.append( $filterLink );

			}

			$wrap.append( $empty, $guide, $actions );
			this.$container.append( $wrap );
		},

		/**
		 * Switch the message table mode
		 *
		 * @param {string} mode The message table mode - proofread or translate
		 */
		switchMode: function ( mode ) {
			var messageTable = this,
				filter = messageTable.$loader.data( 'filter' ),
				userId = mw.config.get( 'wgUserId' ),
				$proofreadAction,
				$tuxTabUntranslated,
				$tuxTabUnproofread,
				$controlOwnButton,
				$hideTranslatedButton;

			messageTable.$actionBar.find( '.down' ).removeClass( 'down' );
			if ( mode === 'translate' ) {
				messageTable.$actionBar.find( '.translate-mode-button' ).addClass( 'down' );
			}
			if ( mode === 'proofread' ) {
				messageTable.$actionBar.find( '.tux-proofread-button' ).addClass( 'down' );
			}
			if ( mode === 'page' ) {
				messageTable.$actionBar.find( '.page-mode-button' ).addClass( 'down' );
			}

			this.firstProofreadTipShown = false;

			// "Accept message" tipsies may still be shown
			if ( messageTable.mode === 'proofread' ) {
				$proofreadAction = messageTable.$container.find( '.tux-proofread-action' );

				if ( $proofreadAction.length ) {
					$proofreadAction.tipsy( 'hide' );
				}
			}

			messageTable.mode = mode;
			mw.translate.changeUrl( { action: this.mode } );

			messageTable.$container.empty();

			$tuxTabUntranslated = $( '.tux-message-selector > .tux-tab-untranslated' );
			$tuxTabUnproofread = $( '.tux-message-selector > .tux-tab-unproofread' );
			$controlOwnButton = messageTable.$actionBar.find( '.tux-proofread-own-translations-button' );
			$hideTranslatedButton = messageTable.$actionBar.find( '.tux-editor-clear-translated' );

			if ( messageTable.mode === 'proofread' ) {
				$tuxTabUntranslated.addClass( 'hide' );
				$tuxTabUnproofread.removeClass( 'hide' );

				// Fix the filter if it is untranslated. Untranslated does not make sense
				// for proofread mode. Keep the filter if it is not 'untranslated'
				if ( !filter || filter.indexOf( '!translated' ) >= 0 ) {
					messageTable.messages = [];
					// default filter for proofread mode
					mw.translate.changeFilter( 'translated|!reviewer:' + userId +
						'|!last-translator:' + userId );
					$tuxTabUnproofread.addClass( 'selected' );
				}

				$controlOwnButton.removeClass( 'hide' );
				$hideTranslatedButton.addClass( 'hide' );
			} else {
				$tuxTabUntranslated.removeClass( 'hide' );
				$tuxTabUnproofread.addClass( 'hide' );
				$controlOwnButton.addClass( 'hide' );

				if ( messageTable.$loader.data( 'filter' ).indexOf( '!translated' ) > -1 ) {
					$hideTranslatedButton.removeClass( 'hide' );
				}

				if ( filter && filter.indexOf( '!last-translator' ) >= 0 ) {
					messageTable.messages = [];
					// default filter for translate mode
					mw.translate.changeFilter( '!translated' );
					$tuxTabUntranslated.addClass( 'selected' );
				}
			}

			$.each( messageTable.messages, function ( index, message ) {
				messageTable.add( message );
			} );
		},

		/**
		 * The scroll handler
		 */
		scroll: function () {
			var $window,
				$tuxTableHeader,
				$tuxActionBar,
				isActionBarFloating,
				needsTableHeaderFloat, needsTableHeaderStick,
				needsActionBarFloat, needsActionBarStick,
				windowScrollTop, windowScrollBottom,
				tuxTableHeaderHeight,
				messageListOffset,
				messageListHeight, messageListWidth,
				messageListTop, messageListBottom;

			$window = $( window );

			windowScrollTop = $window.scrollTop();
			windowScrollBottom = windowScrollTop + $window.height();
			messageListOffset = this.$container.offset();
			messageListHeight = this.$container.height();
			messageListBottom = messageListOffset.top + messageListHeight;
			messageListWidth = this.$container.width();
			messageListTop = messageListOffset.top;

			// Header:
			$tuxTableHeader = $( '.tux-messagetable-header' );
			tuxTableHeaderHeight = $tuxTableHeader.height();
			needsTableHeaderFloat = messageListTop - tuxTableHeaderHeight - windowScrollTop + 10 < 0;
			needsTableHeaderStick = messageListTop - tuxTableHeaderHeight - windowScrollTop - 10 >= 0;
			if ( needsTableHeaderFloat ) {
				$tuxTableHeader.addClass( 'floating' ).width( messageListWidth );
			} else if ( needsTableHeaderStick ) {
				$tuxTableHeader.removeClass( 'floating' );
			}

			// Action bar:
			$tuxActionBar = $( '.tux-action-bar' );
			isActionBarFloating = $tuxActionBar.hasClass( 'floating' );
			needsActionBarFloat = windowScrollBottom < messageListBottom;
			needsActionBarStick = windowScrollBottom > ( messageListBottom + $tuxActionBar.height() );

			if ( !isActionBarFloating && needsActionBarFloat ) {
				$tuxActionBar.addClass( 'floating' ).width( messageListWidth );
			} else if ( isActionBarFloating && needsActionBarStick ) {
				$tuxActionBar.removeClass( 'floating' );
			} else if ( isActionBarFloating && needsActionBarFloat ) {
				$tuxActionBar.css( 'left', messageListOffset.left - $window.scrollLeft() );
			}
		}
	};

	/*
	 * messagetable PLUGIN DEFINITION
	 */

	$.fn.messagetable = function ( options ) {
		return this.each( function () {
			var $this = $( this ),
				data = $this.data( 'messagetable' );

			if ( !data ) {
				$this.data( 'messagetable', ( data = new MessageTable( this, options ) ) );
			}

			if ( typeof options === 'string' ) {
				data[options].call( $this );
			}
		} );
	};

	$.fn.messagetable.Constructor = MessageTable;

	$.fn.messagetable.defaults = {
		mode: new mw.Uri().query.action || 'translate'
	};

	$( 'document' ).ready( function () {
		// Currently used only in the pre-TUX editor
		$( '.mw-translate-messagereviewbutton' ).click( function () {
			var $b, successFunction, failFunction, params;
			$b = $( this );

			successFunction = function ( data ) {
				if ( data.error ) {
					// Give grep a chance to find the usages:
					// api-error-invalidrevision, api-error-unknownmessage,
					// api-error-fuzzymessage, api-error-owntranslation
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
	} );

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

