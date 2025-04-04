( function () {
	'use strict';
	var logger = require( 'ext.translate.eventlogginghelpers' );
	var itemsClass = {
		proofread: '.tux-message-proofread',
		page: '.tux-message-pagemode',
		translate: '.tux-message'
	};

	mw.translate = mw.translate || {};
	mw.translate = $.extend( mw.translate, {
		/** @ignore */
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
				errorformat: 'html',
				formatversion: 2,
				uselang: mw.config.get( 'wgUserLanguage' )
			} );
		}
	} );

	function MessageTable( container, options, settings ) {
		this.$container = $( container );
		this.options = options;
		this.options = Object.assign( {}, $.fn.messagetable.defaults, options );
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
		/** @private */
		init: function () {
			this.$actionBar.removeClass( 'hide' );
		},

		/** @private */
		listen: function () {
			var messageTable = this,
				$filterInput = this.$container.parent().find( '.tux-message-filter-box' );

			// Vector has transitions of 250ms which affect layout. Let those finish.
			$( window ).on( 'scroll', mw.util.debounce( function () {
				if ( isLoaderVisible( messageTable.$loader ) ) {
					messageTable.load();
				}
			}, 250 ) ).on( 'resize', mw.util.throttle( function () {
				messageTable.resize();
			}, 250 ) );

			$filterInput.on( 'input', mw.util.debounce( function () {
				messageTable.search( $filterInput.val() );
			}, 250 ) );

			this.$actionBar.find( 'button.proofread-mode-button' ).on( 'click', function () {
				messageTable.switchMode( 'proofread' );
				logger.logClickEvent( 'change_mode', 'review' );
			} );

			this.$actionBar.find( 'button.translate-mode-button' ).on( 'click', function () {
				messageTable.switchMode( 'translate' );
				logger.logClickEvent( 'change_mode', 'list' );
			} );

			this.$actionBar.find( 'button.page-mode-button' ).on( 'click', function () {
				messageTable.switchMode( 'page' );
				logger.logClickEvent( 'change_mode', 'page' );
			} );

			this.$proofreadOwnTranslations.on( 'click', function () {
				var $this = $( this ),
					enable = !$this.hasClass( 'down' );
				messageTable.$container.toggleClass( 'tux-hide-own', enable );
				$this.toggleClass( 'down', enable ).text( mw.msg( enable ?
					'tux-editor-proofreading-show-own-translations' :
					'tux-editor-proofreading-hide-own-translations' ) );
			} );
		},

		/**
		 * Clear the message table
		 *
		 * @private
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
		 * @private
		 * @param {Object} message
		 */
		add: function ( message ) {
			// Prepare the message for display
			mw.hook( 'mw.translate.messagetable.formatMessageBeforeTable' ).fire( message );

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
		 * @private
		 * @param {Object} message
		 */
		addTranslate: function ( message ) {
			var targetLangCode = this.$container.data( 'targetlangcode' ),
				sourceLangCode = this.$container.data( 'sourcelangcode' ),
				sourceLangDir = $.uls.data.getDir( sourceLangCode ),
				status = message.properties.status,
				statusClass = 'tux-status-' + status,
				$messageWrapper = $( '<div>' ).addClass( 'row tux-message' ),
				statusMsg = '';

			message.proofreadable = false;

			if ( message.tags.length &&
				message.tags.includes( 'optional' ) &&
				status === 'untranslated'
			) {
				status = 'optional';
				statusClass = 'tux-status-optional';
			}

			// Fuzzy translations need warning class
			if ( status === 'fuzzy' ) {
				statusClass = statusClass + ' tux-notice';
			}

			// Label the status if it is not untranslated
			if ( status !== 'untranslated' ) {
				// Give grep a chance to find the usages:
				// tux-status-optional, tux-status-fuzzy, tux-status-proofread,
				// tux-status-translated, tux-status-saving, tux-status-unsaved
				statusMsg = 'tux-status-' + status;
			}

			var targetLangDir, targetLangAttrib;
			if ( targetLangCode === mw.config.get( 'wgTranslateDocumentationLanguageCode' ) ) {
				targetLangAttrib = mw.config.get( 'wgContentLanguage' );
				targetLangDir = $.uls.data.getDir( targetLangAttrib );
			} else {
				targetLangAttrib = targetLangCode;
				targetLangDir = this.$container.data( 'targetlangdir' );
			}

			var $message = $( '<div>' )
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
								.html( $( document.body ).hasClass( 'rtl' ) ? '&rlm;' : '&lrm;' ),
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
								// The following messages are used here:
								// * tux-status-optional
								// * tux-status-fuzzy
								// * tux-status-proofread
								// * tux-status-translated
								// * tux-status-saving
								// * tux-status-unsaved
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
		 * @private
		 * @param {Object} message
		 */
		addProofread: function ( message ) {
			var $message = $( '<div>' )
				.addClass( 'row tux-message tux-message-proofread' );

			this.$container.append( $message );
			$message.proofread( {
				message: message,
				sourcelangcode: this.$container.data( 'sourcelangcode' ),
				targetlangcode: this.$container.data( 'targetlangcode' )
			} );

			var $icon = $message.find( '.tux-proofread-action' );
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
					var $visibleIcon = $( '.autotooltip:visible' );
					if ( !$visibleIcon.length ) {
						return;
					}

					var offset = $visibleIcon.offset();
					tooltip.$element.appendTo( document.body );
					tooltip
						.toggle( true )
						.toggleClipping( false )
						.togglePositioning( false )
						.setAnchorEdge( 'top' );
					tooltip.$element.css( {
						top: offset.top + $visibleIcon.outerHeight() + 5,
						left: offset.left + $visibleIcon.outerWidth() - tooltip.$element.width() / 2 - 15
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
		 * @private
		 * @param {Object} message
		 */
		addPageModeMessage: function ( message ) {
			var $message = $( '<div>' )
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
		 * @private
		 * @param {string} query
		 */
		search: function ( query ) {
			var resultCount = 0,
				matcher = new RegExp( '(^|\\s|\\b)' + escapeRegex( query ), 'i' );

			this.$container.find( itemsClass[ this.mode ] ).each( function () {
				var $message = $( this ),
					message = ( $message.data( 'translateeditor' ) ||
						$message.data( 'pagemode' ) ||
						$message.data( 'proofread' ) ).message;

				if (
					matcher.test( message.definition ) ||
					matcher.test( message.translation ) ||
					matcher.test( message.key )
				) {
					$message.removeClass( 'hide' );
					resultCount++;
				} else {
					$message.addClass( 'hide' );
				}
			} );

			var $result = this.$container.find( '.tux-message-filter-result' );
			if ( !$result.length ) {
				var $note = $( '<div>' )
					.addClass( 'advanced-search' );

				var $button = $( '<button>' )
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

			// Trigger a scroll event to make sure enough messages are loaded
			$( window ).trigger( 'scroll' );
		},

		/** @private */
		resize: function () {
			var $messageSelector = $( '.row.tux-message-selector' );

			if ( $messageSelector.is( ':hidden' ) ) {
				return;
			}

			var actualWidth = 0;
			// Calculate the total width required for the filters
			$messageSelector.children( 'li' ).each( function () {
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
		 * @internal
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

			// Update the target language
			var languageDetails = mw.translate.getLanguageDetailsForHtml( this.settings.language );
			this.$container.data( 'targetlangcode', languageDetails.code );
			this.$container.data( 'targetlangdir', languageDetails.direction );

			// Reset the statsbar
			this.$statsBar
				.empty()
				.removeData()
				.languagestatsbar( {
					language: this.settings.language,
					group: this.settings.group,
					onlyLoadCurrentGroupData: true
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
		 * @private
		 * @param {number} [limit] Only load this many messages and then stop even if there is more.
		 */
		load: function ( limit ) {
			var self = this,
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
				var messages = result.query.messagecollection;

				if ( !self.loading ) {
					// reject. This was cancelled.
					return;
				}

				self.clearLoadErrors();
				if ( result.warnings ) {
					for ( var i = 0; i !== result.warnings.length; i++ ) {
						var currentWarning = result.warnings[ i ];
						if ( currentWarning.code === 'translate-language-disabled-source' ) {
							self.handleLoadErrors( [ currentWarning ] );
							logger.logEvent(
								'message_prompt',
								'change_lang'
							);
							// Since translation to source language is disabled, do not display any messages
							return;
						}
						if ( currentWarning.code === 'translate-language-targetlang-variant-of-source' ) {
							self.displayLoadErrors( [ currentWarning ] );
							break;
						}
					}
				}

				if ( messages.length === 0 ) {
					// And this is the first load for the filter...
					if ( self.$container.children().length === 0 ) {
						self.displayEmptyListHelp();
					}
				}

				messages.forEach( function ( message, index ) {
					message.group = self.settings.group;
					self.add( message );
					self.messages.push( message );

					if ( index === 0 && self.mode === 'translate' ) {
						$( '.tux-message:first' ).data( 'translateeditor' ).init();
					}
				} );

				var state = result.query.metadata && result.query.metadata.state;
				$( '.tux-workflow' ).workflowselector(
					self.settings.group,
					self.settings.language,
					state
				).removeClass( 'hide' );

				// Dynamically loaded messages should pass the search filter if present.
				var query = $( '.tux-message-filter-box' ).val();

				if ( query ) {
					self.search( query );
				}

				if ( result[ 'query-continue' ] === undefined || limit ) {
					// End of messages
					self.$loader.data( 'offset', -1 )
						.addClass( 'hide' );
				} else {
					self.$loader.data( 'offset', result[ 'query-continue' ].messagecollection.mcoffset );

					var remaining = result.query.metadata.remaining;

					self.$loaderInfo.text(
						mw.msg( 'tux-messagetable-more-messages', remaining )
					);
				}

				// Helpfully open the first message in show mode on page load
				// But do not open it if we are at the bottom of the page waiting for more translation units
				if ( self.messages.length <= pageSize ) {
					// TODO: Refactor to avoid direct DOM access
					$( '.tux-message-item' ).first().trigger( 'click' );
				}

				self.updateHideOwnInProofreadingToggleVisibility();
				self.updateLastMessage();
			} ).fail( function ( errorCode, response ) {
				self.handleLoadErrors( response.errors, errorCode );
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
		 * @private
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

		updateHideOwnInProofreadingToggleVisibility: function () {
			var ownTranslations = this.$container.find( '.tux-message-proofread.own-translation' ).length;
			this.$proofreadOwnTranslations.toggleClass( 'hide', !ownTranslations );
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
								$( '#tux-option-optional' ).trigger( 'click' );
								e.preventDefault();
							} )
					);
				} else if ( selectedTab === 'outdated' ) {
					$emptyListHeader.text( mw.msg( 'tux-empty-no-outdated-messages' ) );
					$guide.text( mw.msg( 'tux-empty-list-other-guide' ) );
					$actions.append( messageTable.otherActionButton(
						'tux-empty-list-other-action',
						function () {
							$( '.tux-tab-unproofread' ).trigger( 'click' );
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
							$( '.tux-tab-translated' ).trigger( 'click' );
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
							$( '.tux-tab-untranslated' ).trigger( 'click' );
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
						.on( 'click', function () {
							$( '.tux-tab-all' ).trigger( 'click' );
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
		 * @private
		 * @param {string} mode The message table mode to switch to: translate, page or proofread
		 */
		switchMode: function ( mode ) {
			var messageTable = this,
				filter = this.settings.filter,
				userId = mw.config.get( 'wgUserId' );

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

			var $tuxTabUntranslated = $( '.tux-message-selector > .tux-tab-untranslated' );
			var $tuxTabUnproofread = $( '.tux-message-selector > .tux-tab-unproofread' );
			var $hideTranslatedButton = messageTable.$actionBar.find( '.tux-editor-clear-translated' );

			if ( messageTable.mode === 'proofread' ) {
				$tuxTabUntranslated.addClass( 'hide' );
				$tuxTabUnproofread.removeClass( 'hide' );

				// Fix the filter if it is untranslated. Untranslated does not make sense
				// for proofread mode. Keep the filter if it is not 'untranslated'
				if ( !filter || filter.includes( '!translated' ) ) {
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

				if ( filter.includes( '!translated' ) ) {
					$hideTranslatedButton.removeClass( 'hide' );
				}

				if ( filter && filter.includes( '!last-translator' ) ) {
					messageTable.messages = [];
					// default filter for translate mode
					mw.translate.changeFilter( '!translated' );
					$tuxTabUntranslated.addClass( 'selected' );
				}
			}

			if ( messageTable.messages.length ) {
				messageTable.messages.forEach( function ( message ) {
					messageTable.add( message );
				} );
			} else if ( messageTable.initialized && !messageTable.loading ) {
				messageTable.displayEmptyListHelp();
			}

			this.$loaderInfo.text(
				mw.msg( 'tux-messagetable-loading-messages', this.$loader.data( 'pagesize' ) )
			);

			messageTable.updateHideOwnInProofreadingToggleVisibility();
			messageTable.updateLastMessage();
		},

		/**
		 * Clear errors encountered during the loading state
		 *
		 * @private
		 */
		clearLoadErrors: function () {
			$( '.tux-editor-header .tux-group-warning .tux-api-load-error' ).remove();
		},

		/**
		 * Display errors encountered during the loading state.
		 *
		 * @private
		 * @param {Array} errors
		 * @param {string} errorCode
		 */
		displayLoadErrors: function ( errors, errorCode ) {
			var $warningContainer = $( '.tux-editor-header .tux-group-warning' );

			if ( errors ) {
				errors.forEach( function ( error ) {
					$warningContainer.append(
						$( '<p>' )
							.addClass( 'tux-api-load-error' )
							.html( error.html )
					);
				} );
			} else {
				$warningContainer.append(
					$( '<p>' )
						.addClass( 'tux-api-load-error' )
						.text( mw.msg( 'api-error-unknownerror', errorCode ) )
				);
			}
		},

		/**
		 * Displays the errors and updates the state of the table.
		 *
		 * @private
		 * @param {Array} errors
		 * @param {string} errorCode
		 */
		handleLoadErrors: function ( errors, errorCode ) {
			this.displayLoadErrors( errors, errorCode );

			$( '.tux-workflow' ).addClass( 'hide' );
			this.$loader.data( 'offset', -1 ).addClass( 'hide' );
			this.$actionBar.addClass( 'hide' );
			this.$header.addClass( 'hide' );
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
		var $window = $( window );

		var viewportBottom = ( window.innerHeight ? window.innerHeight : $window.height() ) +
			$window.scrollTop();

		var elementTop = $loader.offset().top;

		// Start already if user is reaching close to the bottom
		return elementTop - viewportBottom < 200;
	}

}() );
