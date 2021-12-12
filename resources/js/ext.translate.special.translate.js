( function () {
	'use strict';

	var state, hideOptionalMessages = '!optional';

	state = {
		group: null,
		language: null,
		messageList: null
	};

	mw.translate = mw.translate || {};

	mw.translate = $.extend( mw.translate, {

		/**
		 * Change the group that is currently displayed
		 * in the TUX translation editor.
		 *
		 * @param {Object} group a message group object.
		 */
		changeGroup: function ( group ) {
			if ( !checkDirty() ) {
				return;
			}

			state.group = group.id;

			var changes = {
				group: group.id,
				showMessage: null

			};

			mw.translate.changeUrl( changes );
			mw.translate.updateTabLinks( changes );
			$( '.tux-editor-header .group-warning' ).empty();
			state.messageList.changeSettings( changes );
			updateGroupInformation( state );
		},

		changeLanguage: function ( language ) {
			var changes = {
				language: language,
				showMessage: null
			};

			state.language = language;

			mw.translate.changeUrl( changes );
			mw.translate.updateTabLinks( changes );
			$( '.tux-editor-header .group-warning' ).empty();
			state.messageList.changeSettings( changes );
			state.groupSelector.updateTargetLanguage( language );
			updateGroupInformation( state );
		},

		changeFilter: function ( filter ) {
			if ( !checkDirty() ) {
				return;
			}

			mw.translate.changeUrl( { filter: filter, showMessage: null } );
			state.messageList.changeSettings( { filter: getActualFilter( filter ) } );
		},

		changeUrl: function ( params, forceChange ) {
			var uri = new mw.Uri( window.location.href );

			uri.extend( params );

			// Support removing keys from the query
			Object.keys( params ).forEach( function ( key ) {
				if ( params[ key ] === null ) {
					delete uri.query[ key ];
				}
			} );

			mw.hook( 'mw.translate.translationView.stateChange' ).fire( state );

			if ( uri.toString() === window.location.href ) {
				return;
			}

			// If supported by the browser and requested, change the URL with
			// this URI but try not to leave the page.
			if ( !forceChange && history.pushState && $( '.tux-messagelist' ).length ) {
				history.pushState( uri, null, uri.toString() );
			} else {
				// For old browsers, just reload
				window.location.href = uri.toString();
			}
		},

		/**
		 * Updates the navigation tabs.
		 *
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
		var props = 'id|priority|prioritylangs|priorityforce|description';

		mw.translate.recentGroups.append( stateInfo.group );

		mw.translate.getMessageGroup( stateInfo.group, props ).done( function ( group ) {
			updateDescription( group );
			updateGroupWarning( group, stateInfo.language );
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

		api.parse( group.description ).done( function ( parsedDescription ) {
			// The parsed text is returned in a <p> tag,
			// so it's removed here.
			$description.html( parsedDescription );
		} ).fail( function () {
			$description.empty();
			mw.log( 'Error parsing description for group ' + group.id );
		} );
	}

	function updateGroupWarning( group, language ) {
		var $groupWarning = $( '.tux-editor-header .group-warning' );

		if ( group.priority === 'discouraged' ) {
			$groupWarning.append(
				$( '<p>' ).append( $( '<strong>' )
					.text( mw.message( 'tpt-discouraged-translation-header' ).text() )
				),
				$( '<p>' ).append( mw.message( 'tpt-discouraged-translation-content' ).parseDom() )
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

		var headerMessage, languagesMessage;
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

	function isPriorityLanguage( language, priorityLanguages ) {
		// Don't show priority notice if the language is message documentation.
		if ( language === mw.config.get( 'wgTranslateDocumentationLanguageCode' ) ) {
			return true;
		}

		// If no priority language is set, return early.
		if ( !priorityLanguages ) {
			return true;
		}

		if ( priorityLanguages.indexOf( language ) !== -1 ) {
			return true;
		}

		return false;
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

	$( function () {
		var $messageList = $( '.tux-messagelist' );
		state.group = $( '.tux-messagetable-loader' ).data( 'messagegroup' );
		state.language = $messageList.data( 'targetlangcode' );

		if ( $messageList.length ) {
			$messageList.messagetable();
			state.messageList = $messageList.data( 'messagetable' );

			var uri = new mw.Uri( window.location.href );
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

			mw.translate.changeUrl( {
				group: state.group,
				language: state.language,
				filter: filter,
				showMessage: offset,
				optional: offset ? 1 : undefined
			} );

			// Start loading messages
			var actualFilter = getActualFilter( filter );
			state.messageList.changeSettings( {
				group: state.group,
				language: state.language,
				offset: offset,
				limit: limit,
				filter: actualFilter
			} );

			if ( actualFilter.indexOf( hideOptionalMessages ) === -1 ) {
				$( '#tux-option-optional' ).prop( 'checked', true );
			}
		}

		var position;
		if ( $( document.body ).hasClass( 'rtl' ) ) {
			position = {
				my: 'right top',
				at: 'right+80 bottom+5'
			};
		}
		$( '.tux-breadcrumb__item--aggregate' ).msggroupselector( {
			onSelect: mw.translate.changeGroup,
			language: state.language,
			position: position,
			recent: mw.translate.recentGroups.get()
		} );
		state.groupSelector = $( '.tux-breadcrumb__item--aggregate' ).data( 'msggroupselector' );

		updateGroupInformation( state );

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

			// TODO: this could should be in messagetable
			if ( newFilter === '!translated' ) {
				$hideTranslatedButton
					.removeClass( 'hide' )
					.prop( 'disabled', !getTranslatedMessages( $translateContainer ).length );
			} else {
				$hideTranslatedButton.addClass( 'hide' );
			}

			return false;
		} );

		// TODO: this could should be in messagetable
		if ( $( '.tux-messagetable-loader' ).data( 'filter' ) === '!translated' ) {
			$hideTranslatedButton.removeClass( 'hide' );
		} else {
			$hideTranslatedButton.addClass( 'hide' );
		}

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
	} );

}() );
