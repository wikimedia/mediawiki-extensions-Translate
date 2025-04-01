( function () {
	'use strict';

	var state, hideOptionalMessages = '!optional';

	state = {
		group: null,
		language: null,
		messageList: null
	};

	mw.translate = mw.translate || {};

	var logger = require( 'ext.translate.eventlogginghelpers' );
	if ( logger.isEventLoggingEnabled() ) {
		trackUserExit();
	}

	mw.translate = $.extend( mw.translate, {

		/**
		 * Change the group that is currently displayed
		 * in the TUX translation editor.
		 *
		 * @private
		 * @param {Object} group a message group object.
		 */
		changeGroup: function ( group ) {
			if ( !checkDirty() ) {
				return;
			}

			var sourceType;
			if ( group.id.indexOf( 'agg-' ) === 0 ) {
				sourceType = 'aggregate-group';
			} else if ( group.id.indexOf( 'page-' ) === 0 ) {
				sourceType = 'translatable-page';
			} else if ( group.id.indexOf( 'messagebundle-' ) === 0 ) {
				sourceType = 'message-bundle';
			} else {
				sourceType = 'normal-message-group';
			}

			showAggregateSubgroupCount();

			state.group = group.id;

			var changes = {
				group: group.id,
				showMessage: null

			};

			mw.translate.changeUrl( changes );
			mw.translate.updateTabLinks( changes );
			removeGroupWarnings();
			state.messageList.changeSettings( changes );
			updateGroupInformation( state );
			getTranslationStats( state.language ).done( function ( stats ) {
				logger.logClickEvent(
					'switch_translate_content',
					'message_group_menu',
					{
						// eslint-disable-next-line camelcase
						source_title: group.id,
						// eslint-disable-next-line camelcase
						source_type: sourceType,
						// eslint-disable-next-line camelcase
						translatable_count: stats.total,
						// eslint-disable-next-line camelcase
						translated_count: stats.translated
					}
				);
			} );
		},

		/**
		 * @private
		 * @param {string} language
		 */
		changeLanguage: function ( language ) {
			var changes = {
				language: language,
				showMessage: null
			};

			state.language = language;

			mw.translate.changeUrl( changes );
			mw.translate.updateTabLinks( changes );
			removeGroupWarnings();
			state.messageList.changeSettings( changes );
			state.groupSelector.updateTargetLanguage( language );
			updateGroupInformation( state );

			getTranslationStats( language ).done( function ( stats ) {
				logger.logClickEvent(
					'change_target_lang',
					'language_selector',
					{
						// eslint-disable-next-line camelcase
						target_language: language,
						// eslint-disable-next-line camelcase
						translatable_count: stats.total,
						// eslint-disable-next-line camelcase
						translated_count: stats.translated
					}
				);
			} );
		},

		/**
		 * @internal
		 * @param {string} filter
		 */
		changeFilter: function ( filter ) {
			if ( !checkDirty() ) {
				return;
			}

			mw.translate.changeUrl( { filter: filter, showMessage: null } );
			state.messageList.changeSettings( { filter: getActualFilter( filter ) } );
		},

		/**
		 * @internal
		 * @param {Object} params
		 */
		changeUrl: function ( params ) {
			var uri = new mw.Uri( window.location.href );

			uri.extend( params );

			// Support removing keys from the query
			Object.keys( params ).forEach( function ( key ) {
				if ( params[ key ] === null || params[ key ] === undefined ) {
					delete uri.query[ key ];
				}
			} );

			mw.hook( 'mw.translate.translationView.stateChange' ).fire( state );

			if ( uri.toString() === window.location.href ) {
				return;
			}

			if ( $( '.tux-messagelist' ).length ) {
				history.replaceState( uri, null, uri.toString() );
			} else {
				window.location.href = uri.toString();
			}
		},

		/**
		 * Updates the navigation tabs.
		 *
		 * @private
		 * @param {Object} params Url parameters to update.
		 * @since 2013.05
		 */
		updateTabLinks: function ( params ) {
			$( '.tux-tab a' ).each( function () {
				var $a = $( this );
				var uri = new mw.Uri( $a.prop( 'href' ) );
				uri.extend( params );
				$a.prop( 'href', uri.toString() );
			} );
		}
	} );

	function getTranslationStats( language ) {
		return mw.translate.loadMessageGroupStatsForItem( state.language, state.group ).then(
			function () {
				var statsData = mw.translate.languagestats[ language ] || [];
				for ( var i = 0; i < statsData.length; i++ ) {
					if ( statsData[ i ].group === state.group ) {
						return statsData[ i ];
					}
				}

				return {
					total: 0,
					translated: 0
				};
			}
		);
	}

	function getActionSource() {
		var uri = new mw.Uri( window.location.href );
		return uri.query.action_source || 'direct_open';
	}

	function getActualFilter( filter ) {
		var realFilters = [ '!ignored' ];
		var uri = new mw.Uri( window.location.href );
		if ( uri.query.optional !== '1' ) {
			realFilters.push( hideOptionalMessages );
		}
		if ( filter ) {
			realFilters.push( filter );
		}

		return realFilters.join( '|' );
	}

	function checkDirty() {
		if ( mw.translate.isDirty() ) {
			// eslint-disable-next-line no-alert
			return confirm( mw.msg( 'translate-js-support-unsaved-warning' ) );
		}
		return true;
	}

	// Returns an array of jQuery objects of rows of translated
	// and proofread messages in the TUX editors.
	// Used several times.
	function getTranslatedMessages( $translateContainer ) {
		$translateContainer = $translateContainer || $( '.ext-translate-container' );
		return $translateContainer.find( '.tux-message-item' )
			.filter( '.translated, .proofread' );
	}

	/**
	 * Updates all group specific stuff on the page.
	 *
	 * @param {Object} stateInfo Information about current group and language.
	 * @param {string} stateInfo.group Message group id.
	 * @param {string} stateInfo.language Language.
	 */
	function updateGroupInformation( stateInfo ) {
		var props = 'id|priority|prioritylangs|priorityforce|description|label|sourcelanguage|class|subscription';

		mw.translate.recentGroups.append( stateInfo.group );

		mw.translate.getMessageGroup( stateInfo.group, props ).done( function ( group ) {
			updateDescription( group );
			updateGroupPriorityWarnings( group, stateInfo.language );
			updateGroupSubscription( group );
		} );
	}

	function updateDescription( group ) {
		var
			api = new mw.Api(),
			$description = $( '.tux-editor-header .description' );

		if ( group.description === null ) {
			$description.empty();
			return;
		}
		var description = group.description;
		if (
			group.class === 'WikiPageMessageGroup' &&
			group.sourcelanguage !== state.language &&
			// Message documentation does not have a translation page
			state.language !== mw.config.get( 'wgTranslateDocumentationLanguageCode' )
		) {
			description = mw.msg(
				'translate-tag-page-wikipage-desc',
				':' + group.label + '/' + state.language,
				':' + group.label,
				$.uls.data.getAutonym( group.sourcelanguage ),
				group.sourcelanguage,
				$.uls.data.getAutonym( state.language ),
				state.language );
		}

		api.parse( description ).done( function ( parsedDescription ) {
			// The parsed text is returned in a <p> tag,
			// so it's removed here.
			$description.html( parsedDescription );
		} ).fail( function () {
			$description.empty();
			mw.log( 'Error parsing description for group ' + group.id );
		} );
	}

	function updateGroupPriorityWarnings( group, language ) {
		var $groupWarning = $( '.tux-editor-header .tux-group-warning' );

		if ( group.priority === 'discouraged' ) {
			$groupWarning.append(
				$( '<p>' ).append( $( '<strong>' )
					.text( mw.message( 'tpt-discouraged-translation-header' ).text() )
				),
				$( '<p>' ).append( mw.message( 'tpt-discouraged-translation-content' ).parseDom() )
			);
		}

		var headerMessage, languagesMessage;
		if ( !group.prioritylangs && group.priorityforce ) {
			headerMessage = mw.message(
				'tpt-discouraged-language-force-header',
				$.uls.data.getAutonym( language )
			);
			languagesMessage = mw.message( 'tpt-translation-restricted-no-priority-languages-no-reason' );
			$groupWarning.append(
				$( '<p>' ).append( $( '<strong>' ).text( headerMessage.text() ) ),
				$( '<p>' ).text( languagesMessage.text() )
			);
			return;
		}

		if ( !group.prioritylangs || isPriorityLanguage( language, group.prioritylangs ) ) {
			return;
		}

		// Make a comma-separated list of preferred languages
		var $preferredLanguages = $( '<span>' );
		group.prioritylangs.forEach( function ( languageCode, index ) {
			// bidi isolation for language names
			$preferredLanguages.append(
				$( '<bdi>' ).text( $.uls.data.getAutonym( languageCode ) )
			);

			// Add comma between languages
			if ( index + 1 !== group.prioritylangs.length ) {
				$preferredLanguages.append( ', ' );
			}
		} );

		if ( group.priorityforce ) {
			headerMessage = mw.message(
				'tpt-discouraged-language-force-header',
				$.uls.data.getAutonym( language )
			);
			languagesMessage = mw.message(
				'tpt-discouraged-language-force-content',
				$preferredLanguages
			);
		} else {
			headerMessage = mw.message(
				'tpt-discouraged-language-header',
				$.uls.data.getAutonym( language )
			);
			languagesMessage = mw.message(
				'tpt-discouraged-language-content',
				$preferredLanguages
			);
		}

		$groupWarning.append(
			$( '<p>' ).append( $( '<strong>' ).text( headerMessage.text() ) ),
			$( '<p>' ).append( languagesMessage.parseDom() )
		);
	}

	function updateGroupSubscription( group ) {
		if ( mw.config.get( 'wgTranslateEnableMessageGroupSubscription' ) !== true ) {
			return;
		}

		var $tuxBreadcrumb = $( '.tux-breadcrumb' );
		if ( group.subscription === undefined ) {
			$tuxBreadcrumb.find( '.tux-watch-button' ).remove();
			return;
		}

		var buttonMessage = group.subscription ? 'tux-unwatch-group' : 'tux-watch-group';
		var watchClass = group.subscription ? 'tux-watch-group--watch' : 'tux-watch-group--unwatch';
		var $subscribeButton = $( '<button>' )
			// CSS Classes:
			// * tux-watch-group--watch
			// * tux-watch-group--unwatch
			.addClass( 'mw-ui-button tux-watch-button ' + watchClass )
			.data( 'subscribed', group.subscription )
			.on( 'click', toggleSubscription );

		$subscribeButton.append(
			$( '<span>' ).addClass( 'tux-watch-icon' ),
			$( '<span>' )
				.addClass( 'tux-watch-label' )
				// * tux-watch-group
				// * tux-unwatch-group
				.text( mw.msg( buttonMessage ) )
		);

		$tuxBreadcrumb.find( '.tux-watch-button' ).remove();
		$tuxBreadcrumb.append( $subscribeButton );
	}

	function removeGroupWarnings() {
		var $tuxHeader = $( '.tux-editor-header' );
		$tuxHeader.find( '.tux-group-warning' ).empty();
	}

	function isPriorityLanguage( language, priorityLanguages ) {
		// Don't show priority notice if the language is message documentation.
		if ( language === mw.config.get( 'wgTranslateDocumentationLanguageCode' ) ) {
			return true;
		}

		// If no priority language is set, return early.
		if ( !priorityLanguages ) {
			return true;
		}

		return priorityLanguages.includes( language );
	}

	function setupLanguageSelector( $element ) {
		var ulsOptions = {
			languages: mw.config.get( 'wgTranslateLanguages' ),
			showRegions: [ 'SP' ].concat( $.fn.lcd.defaults.showRegions ),
			onSelect: function ( languageCode ) {
				var languageDetails = mw.translate.getLanguageDetailsForHtml( languageCode );
				mw.translate.changeLanguage( languageCode );
				$element
					.find( '.ext-translate-target-language' )
					.text( languageDetails.autonym )
					.prop( {
						lang: languageDetails.code,
						dir: languageDetails.direction
					} );
			},
			ulsPurpose: 'translate-special-translate',
			quickList: function () {
				return mw.uls.getFrequentLanguageList();
			}
		};

		mw.translate.addExtraLanguagesToLanguageData( ulsOptions.languages, [ 'SP' ] );
		$element.uls( ulsOptions );
	}

	function addTuxGroupWarningContainer() {
		var $tuxEditorHeader = $( '.tux-editor-header' );
		var $tuxWarning = $tuxEditorHeader.find( 'tux-group-warning' );
		if ( !$tuxWarning.length ) {
			$tuxWarning = $( '<div>' )
				.addClass( 'mw-message-box-warning mw-message-box tux-group-warning twelve column' );
			$tuxEditorHeader.append( $tuxWarning );
		}
	}

	function toggleSubscription() {
		var api = new mw.Api();
		var $button = $( this );
		$button.prop( 'disabled', true );

		var subscriptionStatus = $button.data( 'subscribed' );

		var params = {
			action: 'messagegroupsubscription',
			groupId: state.group,
			operation: subscriptionStatus ? 'unsubscribe' : 'subscribe',
			assert: 'user',
			formatversion: 2
		};

		return api.postWithToken( 'csrf', params ).then(
			function ( response ) {
				var oldSubscriptionStatus = subscriptionStatus;
				if ( response.messagegroupsubscription && response.messagegroupsubscription.success === 1 ) {
					var buttonMessage = oldSubscriptionStatus ? 'tux-watch-group' : 'tux-unwatch-group';
					var watchClass = oldSubscriptionStatus ?
						'tux-watch-group--unwatch' : 'tux-watch-group--watch';
					$button
						.removeClass( [ 'tux-watch-group--watch', 'tux-watch-group--unwatch' ] )
						// CSS Classes:
						// * tux-watch-group--watch
						// * tux-watch-group--unwatch
						.addClass( watchClass )
						.data( 'subscribed', !oldSubscriptionStatus );

					$button.find( '.tux-watch-label' )
						// * tux-watch-group
						// * tux-unwatch-group
						.text( mw.msg( buttonMessage ) );

					loadWatchedMessageGroups();
				} else {
					mw.notify( mw.msg( 'tux-subscription-error' ) );
				}
			},
			function ( error ) {
				mw.notify( mw.msg( 'tux-subscription-error' ) );
				mw.log.error( 'messagegroupsubscription: Failed', error, params );
			}
		).always( function () {
			$button.prop( 'disabled', false );
		} );
	}

	function loadWatchedMessageGroups() {
		if ( mw.config.get( 'wgTranslateEnableMessageGroupSubscription' ) !== true ) {
			return;
		}

		var api = new mw.Api();
		var params = {
			action: 'query',
			list: 'messagegroupsubscription',
			formatversion: 2
		};

		return api.get( params ).then(
			function ( response ) {
				state.groupSelector.setWatchedGroups( response.query.messagegroupsubscription );
			},
			function ( error ) {
				mw.log.error( 'messagegroupsubscription: Failed to fetch user subscriptions', error, params );
			}
		);
	}

	function showAggregateSubgroupCount() {
		var $groupBreadcrumbs = $( '.tux-breadcrumb .grouptitle' );
		var $selectedGroup = $groupBreadcrumbs.last();
		var groupCounts = $selectedGroup.data( 'msggroup-subgroup-count' );
		if ( groupCounts === undefined ) {
			$groupBreadcrumbs
				.find( '.tux-breadcrumb__item--aggregate-count' )
				.remove();
		} else {
			var subGroups = mw.msg( 'translate-msggroupselector-view-subprojects', groupCounts );
			$selectedGroup.append(
				$( '<span>' ).append( mw.message( 'parentheses', $( '<span>' ).text( subGroups ) ).parse() )
					.addClass( 'tux-breadcrumb__item--aggregate-count' )
			);
		}
	}

	function trackUserExit() {
		var timeout;
		var timeoutDuration = 30 * 60 * 1000; // 30 minutes in milliseconds

		function resetTimer() {
			// Clear the existing timeout
			clearTimeout( timeout );
			timeout = setTimeout( logInactivity, timeoutDuration );
		}

		function logInactivity() {
			logger.logEvent( 'exit', 'inactivity' );
		}

		function throttle( func, delay ) {
			var lastCall = 0;

			return function () {
				var now = Date.now();
				if ( now - lastCall >= delay ) {
					lastCall = now;
					func();
				}
			};
		}

		// Create a throttled version of resetTimer
		var throttledResetTimer = throttle( resetTimer, 3000 ); // 3 seconds between resets
		// Add event listeners with throttled reset
		var events = [
			'mousemove',
			'mousedown',
			'touchstart',
			'touchmove',
			'click',
			'keydown',
			'scroll',
			'wheel'
		];

		events.forEach( function ( eventName ) {
			window.addEventListener( eventName, throttledResetTimer, true );
		} );

		resetTimer(); // Initial timer setup

		window.addEventListener( 'unload', function () {
			logger.logEvent( 'exit', 'browser_tab_close' );
		} );
	}

	$( function () {
		var $messageList = $( '.tux-messagelist' );
		state.group = $( '.tux-messagetable-loader' ).data( 'messagegroup' );
		state.language = $messageList.data( 'targetlangcode' );
		var uri = new mw.Uri( window.location.href );

		if ( $messageList.length ) {
			$messageList.messagetable();
			state.messageList = $messageList.data( 'messagetable' );

			var filter = uri.query.filter;
			var offset = uri.query.showMessage;
			var limit;
			if ( offset ) {
				limit = uri.query.limit || 1;
				// Default to no filters
				filter = filter || '';
			}

			if ( filter === undefined ) {
				filter = '!translated';
			}

			$( '.tux-message-selector li' ).each( function () {
				var $this = $( this );

				if ( $this.data( 'filter' ) === filter ) {
					$this.addClass( 'selected' );
				}
			} );

			var newUrlParams = {
				group: state.group,
				language: state.language,
				filter: filter,
				showMessage: offset
			};
			if ( offset ) {
				newUrlParams.optional = 1;
			}
			mw.translate.changeUrl( newUrlParams );

			// Start loading messages
			var actualFilter = getActualFilter( filter );
			state.messageList.changeSettings( {
				group: state.group,
				language: state.language,
				offset: offset,
				limit: limit,
				filter: actualFilter
			} );

			if ( !actualFilter.includes( hideOptionalMessages ) ) {
				$( '#tux-option-optional' ).prop( 'checked', true );
			}
		}

		addTuxGroupWarningContainer();

		var position = {
			my: 'left top',
			at: 'left-10 bottom+5'
		};
		if ( $( document.body ).hasClass( 'rtl' ) ) {
			position = {
				my: 'right top',
				at: 'right+10 bottom+5'
			};
		}

		$( '.tux-breadcrumb__item--aggregate' ).msggroupselector( {
			onSelect: mw.translate.changeGroup,
			language: state.language,
			position: position,
			recent: mw.translate.recentGroups.get(),
			showWatched: mw.config.get( 'wgTranslateEnableMessageGroupSubscription' ) || false,
			menuClass: 'tux-groupselector-tpt'
		} );

		state.groupSelector = $( '.tux-breadcrumb__item--aggregate' ).data( 'msggroupselector' );
		loadWatchedMessageGroups();

		updateGroupInformation( state );

		showAggregateSubgroupCount();

		$( '.ext-translate-language-selector .uls' ).one( 'click', function () {
			var $target = $( this );
			mw.loader.using( 'ext.uls.mediawiki' ).done( function () {
				setupLanguageSelector( $target );
				$target.trigger( 'click' );
			} );
		} ).on( 'keypress', function () {
			$( this ).trigger( 'click' );
		} );

		if ( $.fn.translateeditor ) {
			// New translation editor
			$( '.tux-message' ).translateeditor();
		}

		var $translateContainer = $( '.ext-translate-container' );

		if ( mw.translate.canProofread() ) {
			$translateContainer.find( '.proofread-mode-button' ).removeClass( 'hide' );
		}

		var $hideTranslatedButton = $translateContainer.find( '.tux-editor-clear-translated' );
		$hideTranslatedButton
			.prop( 'disabled', !getTranslatedMessages( $translateContainer ).length )
			.on( 'click', function () {
				getTranslatedMessages( $translateContainer ).remove();
				$( this ).prop( 'disabled', true );
			} );

		// Message filter click handler
		$translateContainer.find( '.row.tux-message-selector > li' ).on( 'click', function () {
			var $this = $( this );

			if ( $this.hasClass( 'more' ) ) {
				return false;
			}

			var newFilter = $this.data( 'filter' );

			// Remove the 'selected' class from all the items.
			// Some of them could have been moved to under the "more" menu,
			// so everything under .row.tux-message-selector is searched.
			$translateContainer.find( '.row.tux-message-selector .selected' )
				.removeClass( 'selected' );
			mw.translate.changeFilter( newFilter );
			$this.addClass( 'selected' );

			var translated = newFilter !== '!translated';
			// TODO: this could should be in messagetable
			$hideTranslatedButton.toggleClass( 'hide', translated )
				.prop( 'disabled', !translated && !getTranslatedMessages( $translateContainer ).length );

			return false;
		} );

		// TODO: this could should be in messagetable
		$hideTranslatedButton
			.toggleClass( 'hide', $( '.tux-messagetable-loader' ).data( 'filter' ) !== '!translated' );

		// Don't let clicking the items in the "more" menu
		// affect the rest of it.
		$( '.row.tux-message-selector .more ul' )
			.on( 'click', function ( e ) {
				e.stopPropagation();
			} );

		$( '#tux-option-optional' ).on( 'change', function () {
			var currentUri = new mw.Uri( window.location.href ),
				checked = $( this ).prop( 'checked' );

			mw.translate.changeUrl( { optional: checked ? 1 : 0 } );
			mw.translate.changeFilter( currentUri.query.filter );
		} );

		getTranslationStats( state.language ).done( function ( stats ) {
			logger.logEvent(
				'interface_open',
				null,
				getActionSource(),
				{
					// eslint-disable-next-line camelcase
					source_language: $messageList.data( 'sourcelangcode' ),
					// eslint-disable-next-line camelcase
					target_language: state.language,
					// eslint-disable-next-line camelcase
					source_title: uri.query.group,
					// eslint-disable-next-line camelcase
					source_type: 'page',
					// eslint-disable-next-line camelcase
					translatable_count: stats.total,
					// eslint-disable-next-line camelcase
					translated_count: stats.translated
				}
			);
		} );
	} );

}() );
