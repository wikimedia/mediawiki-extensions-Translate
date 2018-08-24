( function ( $, mw ) {
	'use strict';

	var itemsClass = {
		proofread: '.tux-message-proofread',
		page: '.tux-message-pagemode',
		translate: '.tux-message'
	};

	mw.translate = mw.translate || {};
	mw.translate = $.extend( mw.translate, {
		getMessages: function ( messageGroup, language, offset, limit, filter ) {
			var api = new mw.Api();

			return api.get( {
				action: 'query',
				list: 'messagecollection',
				mcgroup: messageGroup,
				mclanguage: language,
				mcoffset: offset,
				mclimit: limit,
				mcfilter: filter,
				mcprop: 'definition|translation|tags|properties',
				rawcontinue: 1,
				errorformat: 'html'
			} );
		}
	} );

	function MessageTable( container, options, settings ) {
		this.$container = $( container );
		this.options = options;
		this.options = $.extend( {}, $.fn.messagetable.defaults, options );
		this.settings = settings;
		// mode can be proofread, page or translate
		this.mode = this.options.mode;
		this.firstProofreadTipShown = false;
		this.initialized = false;
		this.$header = this.$container.siblings( '.tux-messagetable-header' );
		// Container is between these in the dom.
		this.$loader = this.$container.siblings( '.tux-messagetable-loader' );
		this.$loaderIcon = this.$loader.find( '.tux-loading-indicator' );
		this.$loaderInfo = this.$loader.find( '.tux-messagetable-loader-info' );
		this.$actionBar = this.$container.siblings( '.tux-action-bar' );
		this.$statsBar = this.$actionBar.find( '.tux-message-list-statsbar' );
		this.$proofreadOwnTranslations = this.$actionBar.find( '.tux-proofread-own-translations-button' );
		this.messages = [];
		this.loading = false;
		this.init();
		this.listen();
	}

	MessageTable.prototype = {
		init: function () {
			this.$actionBar.removeClass( 'hide' );
		},

		listen: function () {
			var messageTable = this,
				$filterInput = this.$container.parent().find( '.tux-message-filter-box' );

			// Vector has transitions of 250ms which affect layout. Let those finish.
			$( window ).on( 'scroll', $.debounce( 250, function () {
				messageTable.scroll();

				if ( isLoaderVisible( messageTable.$loader ) ) {
					messageTable.load();
				}
			} ) ).on( 'resize', $.throttle( 250, function () {
				messageTable.resize();
				messageTable.scroll();
			} ) );

			if ( mw.translate.isPlaceholderSupported( $filterInput ) ) {
				$filterInput.prop( 'placeholder', mw.msg( 'tux-message-filter-placeholder' ) );
			}

			$filterInput.on( 'textchange', $.debounce( 250, function () {
				messageTable.search( $filterInput.val() );
			} ) );

			this.$actionBar.find( 'button.proofread-mode-button' ).on( 'click', function () {
				messageTable.switchMode( 'proofread' );
			} );

			this.$actionBar.find( 'button.translate-mode-button' ).on( 'click', function () {
				messageTable.switchMode( 'translate' );
			} );

			this.$actionBar.find( 'button.page-mode-button' ).on( 'click', function () {
				messageTable.switchMode( 'page' );
			} );

			this.$proofreadOwnTranslations.click( function () {
				var $this = $( this ),
					hideMessage = mw.msg( 'tux-editor-proofreading-hide-own-translations' ),
					showMessage = mw.msg( 'tux-editor-proofreading-show-own-translations' );

				if ( $this.hasClass( 'down' ) ) {
					messageTable.setHideOwnInProofreading( false );
					$this.removeClass( 'down' ).text( hideMessage );
				} else {
					messageTable.setHideOwnInProofreading( true );
					$this.addClass( 'down' ).text( showMessage );
				}
			} );
		},

		/**
		 * Clear the message table
		 */
		clear: function () {
			this.$container.empty();
			$( '.translate-tooltip' ).remove();
			this.messages = [];
			// Any ongoing loading process will notice this and will reject results.
			this.loading = false;
		},

		/**
		 * Adds a new message using current mode.
		 *
		 * @param {Object} message
		 */
		add: function ( message ) {
			// Prepare the message for display
			mw.translateHooks.run( 'formatMessageBeforeTable', message );

			if ( this.mode === 'translate' ) {
				this.addTranslate( message );
			} else if ( this.mode === 'proofread' ) {
				this.addProofread( message );
			} else if ( this.mode === 'page' ) {
				this.addPageModeMessage( message );
			}
		},

		/**
		 * Add a message to the message table for translation.
		 *
		 * @param {Object} message
		 */
		addTranslate: function ( message ) {
			var $message,
				targetLangDir, targetLangAttrib,
				targetLangCode = this.$container.data( 'targetlangcode' ),
				sourceLangCode = this.$container.data( 'sourcelangcode' ),
				sourceLangDir = $.uls.data.getDir( sourceLangCode ),
				status = message.properties.status,
				statusClass = 'tux-status-' + status,
				$messageWrapper = $( '<div>' ).addClass( 'row tux-message' ),
				statusMsg = '';

			if ( message.tags.length &&
				message.tags.indexOf( 'optional' ) >= 0 &&
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

			if ( targetLangCode === mw.config.get( 'wgTranslateDocumentationLanguageCode' ) ) {
				targetLangAttrib = mw.config.get( 'wgContentLanguage' );
				targetLangDir = $.uls.data.getDir( targetLangAttrib );
			} else {
				targetLangAttrib = targetLangCode;
				targetLangDir = this.$container.data( 'targetlangdir' );
			}

			$message = $( '<div>' )
				.addClass( 'row message tux-message-item ' + status )
				.append(
					$( '<div>' )
						.addClass( 'eight columns tux-list-message' )
						.append(
							$( '<span>' )
								.addClass( 'tux-list-source' )
								.attr( {
									lang: sourceLangCode,
									dir: sourceLangDir
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
									lang: targetLangAttrib,
									dir: targetLangDir
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
									title: mw.msg( 'translate-edit-title', message.key ),
									href: mw.util.getUrl( message.title, { action: 'edit' } )
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
		 *
		 * @param {Object} message
		 */
		addProofread: function ( message ) {
			var $message, $icon;

			$message = $( '<div>' ).addClass( 'row tux-message-proofread' );

			this.$container.append( $message );
			$message.proofread( {
				message: message,
				sourcelangcode: this.$container.data( 'sourcelangcode' ),
				targetlangcode: this.$container.data( 'targetlangcode' )
			} );

			$icon = $message.find( '.tux-proofread-action' );
			if ( $icon.length === 0 ) {
				return;
			}

			// Add autotooltip to first available proofread action icon
			if ( this.firstProofreadTipShown ) {
				return;
			}
			this.firstProofreadTipShown = true;
			$icon.addClass( 'autotooltip' );

			mw.loader.using( 'oojs-ui-core' ).done( function () {
				var tooltip = new OO.ui.PopupWidget( {
					padded: true,
					align: 'center',
					width: 250,
					classes: [ 'translate-tooltip' ],
					$content: $( '<p>' ).text( $icon.prop( 'title' ) )
				} );

				setTimeout( function () {
					var offset, $icon = $( '.autotooltip:visible' );
					if ( !$icon.length ) {
						return;
					}

					offset = $icon.offset();
					tooltip.$element.appendTo( 'body' );
					tooltip.toggle( true ).toggleClipping( false ).togglePositioning( false );
					tooltip.$element.css( {
						top: offset.top + $icon.outerHeight() + 5,
						left: offset.left + $icon.outerWidth() - tooltip.$element.width() / 2 - 15
					} );

					setTimeout( function () {
						tooltip.$element.remove();
					}, 4000 );
				}, 1000 );
			} );
		},

		/**
		 * Add a message to the message table for wiki page mode.
		 *
		 * @param {Object} message
		 */
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
		 * @param {string} query
		 */
		search: function ( query ) {
			var $note, $button, $result,
				resultCount = 0,
				matcher = new RegExp( '(^|\\s|\\b)' + escapeRegex( query ), 'gi' );

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
				$note = $( '<div>' )
					.addClass( 'advanced-search' );

				$button = $( '<button>' )
					.addClass( 'mw-ui-button' )
					.text( mw.msg( 'tux-message-filter-advanced-button' ) );

				$result = $( '<div>' )
					.addClass( 'tux-message-filter-result' )
					.append( $note, $button );

				this.$container.prepend( $result );
			}

			if ( !query ) {
				$result.addClass( 'hide' );
			} else {
				$result.removeClass( 'hide' )
					.find( '.advanced-search' )
					.text( mw.msg( 'tux-message-filter-result', resultCount, query ) );
				$result.find( 'button' ).on( 'click', function () {
					window.location.href = mw.util.getUrl( 'Special:SearchTranslations', { query: query } );
				} );
			}

			this.updateLastMessage();

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

		/**
		 * Start loading messages again with new settings.
		 *
		 * @param {Object} changes
		 */
		changeSettings: function ( changes ) {
			// Clear current messages
			this.clear();
			this.settings = $.extend( this.settings, changes );

			if ( this.initialized === false ) {
				this.switchMode( this.mode );
			}

			// Reset the number of messages remaining
			this.$loaderInfo.text(
				mw.msg( 'tux-messagetable-loading-messages', this.$loader.data( 'pagesize' ) )
			);

			// Reset the statsbar
			this.$statsBar
				.empty()
				.removeData()
				.languagestatsbar( {
					language: this.settings.language,
					group: this.settings.group
				} );

			this.initialized = true;
			// Reset other info and make visible
			this.$loader
				.removeData( 'offset' )
				.removeAttr( 'data-offset' )
				.removeClass( 'hide' );

			if ( changes.offset ) {
				this.$loader.data( 'offset', changes.offset );
			}

			this.$header.removeClass( 'hide' );
			this.$actionBar.removeClass( 'hide' );

			// Start loading messages
			this.load( changes.limit );
		},

		/**
		 * @param {number} [limit] Only load this many messages and then stop even if there is more.
		 */
		load: function ( limit ) {
			var remaining,
				query,
				self = this,
				offset = this.$loader.data( 'offset' ),
				pageSize = limit || this.$loader.data( 'pagesize' );

			if ( offset === -1 ) {
				return;
			}

			if ( this.loading ) {
				// Avoid duplicate loading - the offset will be wrong and it will result
				// in duplicate messages shown in the page
				return;
			}

			this.loading = true;
			this.$loaderIcon.removeClass( 'tux-loading-indicator--stopped' );

			mw.translate.getMessages(
				this.settings.group,
				this.settings.language,
				offset,
				pageSize,
				this.settings.filter
			).done( function ( result ) {
				var messages = result.query.messagecollection,
					state;

				if ( !self.loading ) {
					// reject. This was cancelled.
					return;
				}

				if ( messages.length === 0 ) {
					// And this is the first load for the filter...
					if ( self.$container.children().length === 0 ) {
						self.displayEmptyListHelp();
					}
				}

				$.each( messages, function ( index, message ) {
					message.group = self.settings.group;
					self.add( message );
					self.messages.push( message );

					if ( index === 0 && self.mode === 'translate' ) {
						$( '.tux-message:first' ).data( 'translateeditor' ).init();
					}
				} );

				state = result.query.metadata && result.query.metadata.state;
				$( '.tux-workflow' ).workflowselector(
					self.settings.group,
					self.settings.language,
					state
				);

				// Dynamically loaded messages should pass the search filter if present.
				query = $( '.tux-message-filter-box' ).val();

				if ( query ) {
					self.search( query );
				}

				if ( result[ 'query-continue' ] === undefined || limit ) {
					// End of messages
					self.$loader.data( 'offset', -1 )
						.addClass( 'hide' );

					// Helpfully open the first message in show mode
					// TODO: Refactor to avoid direct DOM access
					$( '.tux-message-item' ).first().click();
				} else {
					self.$loader.data( 'offset', result[ 'query-continue' ].messagecollection.mcoffset );

					remaining = result.query.metadata.remaining;

					self.$loaderInfo.text(
						mw.msg( 'tux-messagetable-more-messages', remaining )
					);

					// Make sure the floating toolbars are visible without the need for scroll
					$( window ).trigger( 'scroll' );
				}

				self.updateHideOwnInProofreadingToggleVisibility();
				self.updateLastMessage();
			} ).fail( function ( errorCode, response ) {
				var $warningContainer = $( '.tux-editor-header .group-warning' );

				if ( response.errors ) {
					$.map( response.errors, function ( error ) {
						$warningContainer.append( error[ '*' ] );
					} );
				} else {
					$warningContainer.text( mw.msg( 'api-error-unknownerror', errorCode ) );
				}
				self.$loader.data( 'offset', -1 ).addClass( 'hide' );
				self.$actionBar.addClass( 'hide' );
				self.$header.addClass( 'hide' );
			} ).always( function () {
				self.$loaderIcon.addClass( 'tux-loading-indicator--stopped' );
				self.loading = false;
			} );
		},

		updateLastMessage: function () {
			var $messages = this.$container.find( itemsClass[ this.mode ] );

			// If a message was previously marked as "last", restore it to normal state
			$messages.filter( '.last-message' ).removeClass( 'last-message' );

			// At the class to the current last shown message
			$messages
				.not( '.hide' )
				.last()
				.addClass( 'last-message' );
		},

		/**
		 * Creates a uniformly styled button for different actions,
		 * shown when there are no messages to display.
		 *
		 * @param {string} labelMsg A message key for the button label.
		 * @param {Function} callback A callback for clicking the button.
		 * @return {jQuery} A button element.
		 */
		otherActionButton: function ( labelMsg, callback ) {
			return $( '<button>' )
				.addClass( 'mw-ui-button mw-ui-progressive mw-ui-big' )
				.text( mw.msg( labelMsg ) )
				.on( 'click', callback );
		},

		/**
		 * Enables own message hiding in proofread mode.
		 *
		 * @param {boolean} enabled
		 */
		setHideOwnInProofreading: function ( enabled ) {
			if ( enabled ) {
				this.$container.addClass( 'tux-hide-own' );
			} else {
				this.$container.removeClass( 'tux-hide-own' );
			}
		},

		updateHideOwnInProofreadingToggleVisibility: function () {
			if ( this.$container.find( '.tux-message-proofread.own-translation' ).length ) {
				this.$proofreadOwnTranslations.removeClass( 'hide' );
			} else {
				this.$proofreadOwnTranslations.addClass( 'hide' );
			}
		},

		/**
		 * If the user selection doesn't show anything,
		 * give some pointers to other things to do.
		 */
		displayEmptyListHelp: function () {
			var messageTable = this,
				// @todo Ugly! This should be provided somehow
				selectedTab = $( '.tux-message-selector .selected' ).data( 'title' ),
				$wrap = $( '<div>' ).addClass( 'tux-empty-list' ),
				$emptyListHeader = $( '<div>' ).addClass( 'tux-empty-list-header' ),
				$guide = $( '<div>' ).addClass( 'tux-empty-list-guide' ),
				$actions = $( '<div>' ).addClass( 'tux-empty-list-actions' );

			if ( messageTable.mode === 'proofread' ) {
				if ( selectedTab === 'all' ) {
					$emptyListHeader.text( mw.msg( 'tux-empty-no-messages-to-display' ) );
					$guide.append(
						$( '<p>' )
							.text( mw.msg( 'tux-empty-there-are-optional' ) ),
						$( '<a>' )
							.attr( 'href', '#' )
							.text( mw.msg( 'tux-empty-show-optional-messages' ) )
							.on( 'click', function ( e ) {
								$( '#tux-option-optional' ).click();
								e.preventDefault();
							} )
					);
				} else if ( selectedTab === 'outdated' ) {
					$emptyListHeader.text( mw.msg( 'tux-empty-no-outdated-messages' ) );
					$guide.text( mw.msg( 'tux-empty-list-other-guide' ) );
					$actions.append( messageTable.otherActionButton(
						'tux-empty-list-other-action',
						function () {
							$( '.tux-tab-unproofread' ).click();
							// @todo untranslated
						} )
					);
					// @todo View all
				} else if ( selectedTab === 'translated' ) {
					$emptyListHeader.text( mw.msg( 'tux-empty-nothing-to-proofread' ) );
					$guide.text( mw.msg( 'tux-empty-you-can-help-providing' ) );
					$actions.append( messageTable.otherActionButton(
						'tux-empty-list-translated-action',
						function () {
							messageTable.switchMode( 'translate' );
						} )
					);
				} else if ( selectedTab === 'unproofread' ) {
					$emptyListHeader.text( mw.msg( 'tux-empty-nothing-new-to-proofread' ) );
					$guide.text( mw.msg( 'tux-empty-you-can-help-providing' ) );
					$actions.append( messageTable.otherActionButton(
						'tux-empty-you-can-review-already-proofread',
						function () {
							$( '.tux-tab-translated' ).click();
						} )
					);
				}
			} else {
				if ( selectedTab === 'all' ) {
					$emptyListHeader.text( mw.msg( 'tux-empty-list-all' ) );
					$guide.text( mw.msg( 'tux-empty-list-all-guide' ) );
				} else if ( selectedTab === 'translated' ) {
					$emptyListHeader.text( mw.msg( 'tux-empty-list-translated' ) );
					$guide.text( mw.msg( 'tux-empty-list-translated-guide' ) );
					$actions.append( messageTable.otherActionButton(
						'tux-empty-list-translated-action',
						function () {
							mw.translate.changeFilter( $( '.tux-tab-untranslated' ).click() );
						} )
					);
				} else {
					$emptyListHeader.text( mw.msg( 'tux-empty-list-other' ) );

					if ( mw.translate.canProofread() ) {
						$guide.text( mw.msg( 'tux-empty-list-other-guide' ) );
						$actions.append( messageTable.otherActionButton(
							'tux-empty-list-other-action',
							function () {
								messageTable.switchMode( 'proofread' );
							} )
						);
					}

					$actions.append( $( '<a>' )
						.text( mw.msg( 'tux-empty-list-other-link' ) )
						.click( function () {
							$( '.tux-tab-all' ).click();
						} )
					);
				}
			}

			$wrap.append( $emptyListHeader, $guide, $actions );
			this.$container.append( $wrap );
		},

		/**
		 * Switch the message table mode
		 *
		 * @param {string} mode The message table mode to switch to: translate, page or proofread
		 */
		switchMode: function ( mode ) {
			var messageTable = this,
				filter = this.settings.filter,
				userId = mw.config.get( 'wgUserId' ),
				$tuxTabUntranslated,
				$tuxTabUnproofread,
				$hideTranslatedButton;

			messageTable.$actionBar.find( '.tux-view-switcher .down' ).removeClass( 'down' );
			if ( mode === 'translate' ) {
				messageTable.$actionBar.find( '.translate-mode-button' ).addClass( 'down' );
			}
			if ( mode === 'proofread' ) {
				messageTable.$actionBar.find( '.proofread-mode-button' ).addClass( 'down' );
			}
			if ( mode === 'page' ) {
				messageTable.$actionBar.find( '.page-mode-button' ).addClass( 'down' );
			}

			messageTable.firstProofreadTipShown = false;

			messageTable.mode = mode;
			mw.translate.changeUrl( { action: messageTable.mode } );

			// Emulate clear without clearing loaded messages
			messageTable.$container.empty();
			$( '.translate-tooltip' ).remove();

			$tuxTabUntranslated = $( '.tux-message-selector > .tux-tab-untranslated' );
			$tuxTabUnproofread = $( '.tux-message-selector > .tux-tab-unproofread' );
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
					// Own translations are not present in proofread + unreviewed mode
				}

				$hideTranslatedButton.addClass( 'hide' );
			} else {
				$tuxTabUntranslated.removeClass( 'hide' );
				$tuxTabUnproofread.addClass( 'hide' );

				if ( filter.indexOf( '!translated' ) > -1 ) {
					$hideTranslatedButton.removeClass( 'hide' );
				}

				if ( filter && filter.indexOf( '!last-translator' ) >= 0 ) {
					messageTable.messages = [];
					// default filter for translate mode
					mw.translate.changeFilter( '!translated' );
					$tuxTabUntranslated.addClass( 'selected' );
				}
			}

			if ( messageTable.messages.length ) {
				$.each( messageTable.messages, function ( index, message ) {
					messageTable.add( message );
				} );
			} else if ( messageTable.initialized ) {
				messageTable.displayEmptyListHelp();
			}

			this.$loaderInfo.text(
				mw.msg( 'tux-messagetable-loading-messages', this.$loader.data( 'pagesize' ) )
			);

			messageTable.updateHideOwnInProofreadingToggleVisibility();
			messageTable.updateLastMessage();
		},

		/**
		 * The scroll handler
		 */
		scroll: function () {
			var $window,
				isActionBarFloating,
				needsTableHeaderFloat, needsTableHeaderStick,
				needsActionBarFloat, needsActionBarStick,
				windowScrollTop, windowScrollBottom,
				messageTableRelativePos,
				messageListOffset,
				messageListHeight, messageListWidth,
				messageListTop, messageListBottom;

			$window = $( window );

			windowScrollTop = $window.scrollTop();
			windowScrollBottom = windowScrollTop + $window.height();
			messageListOffset = this.$container.offset();
			messageListHeight = this.$container.height();
			messageListTop = messageListOffset.top;
			messageListBottom = messageListTop + messageListHeight;
			messageListWidth = this.$container.width();

			// Header:
			messageTableRelativePos = messageListTop - this.$header.height() - windowScrollTop;
			needsTableHeaderFloat = messageTableRelativePos + 10 < 0;
			needsTableHeaderStick = messageTableRelativePos - 10 >= 0;
			if ( needsTableHeaderFloat ) {
				this.$header.addClass( 'floating' ).width( messageListWidth );
			} else if ( needsTableHeaderStick ) {
				// Let the element change width automatically again
				this.$header.removeClass( 'floating' ).css( 'width', '' );
			}

			// Action bar:
			isActionBarFloating = this.$actionBar.hasClass( 'floating' );
			needsActionBarFloat = windowScrollBottom < messageListBottom;
			needsActionBarStick = windowScrollBottom > ( messageListBottom + this.$actionBar.height() );

			if ( !isActionBarFloating && needsActionBarFloat ) {
				this.$actionBar.addClass( 'floating' ).width( messageListWidth );
			} else if ( isActionBarFloating && needsActionBarStick ) {
				// Let the element change width automatically again
				this.$actionBar.removeClass( 'floating' ).css( 'width', '' );
			} else if ( isActionBarFloating && needsActionBarFloat ) {
				this.$actionBar.width( messageListWidth );
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
				data[ options ].call( $this );
			}
		} );
	};

	$.fn.messagetable.Constructor = MessageTable;

	$.fn.messagetable.defaults = {
		mode: new mw.Uri().query.action || 'translate'
	};

	/**
	 * Escape the search query for regex match.
	 *
	 * @param {string} value A search string to be escaped.
	 * @return {string} Escaped string that is safe to use for a search.
	 */
	function escapeRegex( value ) {
		return value.replace( /[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&' );
	}

	function isLoaderVisible( $loader ) {
		var viewportBottom, elementTop,
			$window = $( window );

		viewportBottom = ( window.innerHeight ? window.innerHeight : $window.height() ) +
			$window.scrollTop();

		elementTop = $loader.offset().top;

		// Start already if user is reaching close to the bottom
		return elementTop - viewportBottom < 200;
	}

}( jQuery, mediaWiki ) );
