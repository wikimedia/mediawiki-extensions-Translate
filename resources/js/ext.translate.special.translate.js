( function ( $, mw ) {
	'use strict';

	var state = {
		group: null,
		language: null
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
			var changes;

			if ( !checkDirty() ) {
				return;
			}

			state.group = group.id;

			changes = {
				group: group.id
			};

			mw.translate.changeUrl( changes );
			mw.translate.updateTabLinks( changes );
			mw.translate.loadMessages( changes );
			updateGroupInformation( state );
		},

		changeLanguage: function ( language ) {
			var changes, targetDir, targetLangAttrib,
				userLanguageCode = mw.config.get( 'wgUserLanguage' );

			if ( !checkDirty() ) {
				return;
			}

			state.language = language;

			changes = {
				language: language
			};

			if ( language === mw.config.get( 'wgTranslateDocumentationLanguageCode' ) ) {
				targetLangAttrib = userLanguageCode;
				targetDir = $.uls.data.getDir( userLanguageCode );
			} else {
				targetLangAttrib = language;
				targetDir = $.uls.data.getDir( language );
			}

			// Changes to attributes must also be reflected
			// when the element is created on the server side
			$( '.ext-translate-language-selector > .uls' )
				.text( $.uls.data.getAutonym( language ) )
				.attr( {
					lang: targetLangAttrib,
					dir: targetDir
				} );
			$( '.tux-messagelist' ).data( {
				targetlangcode: language,
				targetlangdir: targetDir
			} );

			mw.translate.changeUrl( changes );
			mw.translate.updateTabLinks( changes );
			mw.translate.loadMessages();
			updateGroupInformation( state );
		},

		changeFilter: function ( filter ) {
			var realFilters, uri;

			if ( !checkDirty() ) {
				return;
			}

			realFilters = [ '!ignored' ];
			uri = new mw.Uri( window.location.href );
			if ( uri.query.optional !== '1' ) {
				realFilters.push( '!optional' );
			}
			if ( filter ) {
				realFilters.push( filter );
			}

			mw.translate.changeUrl( { filter: filter } );
			mw.translate.loadMessages( { filter: realFilters.join( '|' ) } );
		},

		changeUrl: function ( params ) {
			var uri = new mw.Uri( window.location.href );

			uri.extend( params );

			if ( uri.toString() === window.location.href ) {
				return;
			}

			// Change the URL with this URI, but don't leave the page.
			if ( history.pushState && $( '.tux-messagelist' ).length ) {
				// IE<10 does not support pushState. Never mind.
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
				var $a, uri;

				$a = $( this );
				uri = new mw.Uri( $a.prop( 'href' ) );
				uri.extend( params );
				$a.prop( 'href', uri.toString() );
			} );
		}
	} );

	function checkDirty() {
		if ( mw.translate.isDirty() ) {
			return window.confirm( mw.msg( 'translate-js-support-unsaved-warning' ) );
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
	 */
	function updateGroupInformation( state ) {
		var props = 'id|priority|prioritylangs|priorityforce|description';

		mw.translate.recentGroups.append( state.group );

		mw.translate.getMessageGroup( state.group, props ).done( function ( group ) {
			updateDescription( group );
			updateGroupWarning( group, state.language );
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
			$description.html( $( parsedDescription ).html() );
		} ).fail( function () {
			$description.empty();
			mw.log( 'Error parsing description for group ' + group.id );
		} );
	}

	function updateGroupWarning( group, language ) {
		var preferredLanguages, headerMessage, languagesMessage,
			$groupWarning = $( '.tux-editor-header .group-warning' );

		$groupWarning.empty();

		// Check whether the group has priority languages
		if ( !group.prioritylangs ) {
			return;
		}

		// And if the current language is among them, we can return early
		if ( $.inArray( language, group.prioritylangs ) !== -1 ) {
			return;
		}

		// Make a comma-separated list of preferred languages
		preferredLanguages = $.map( group.prioritylangs, function ( lang ) {
			// bidi isolation for language names
			return '<bdi>' + $.uls.data.getAutonym( lang ) + '</bdi>';
		} ).join( ', ' );

		headerMessage = mw.message( group.priorityforce ?
			'tpt-discouraged-language-force-header' :
			'tpt-discouraged-language-header',
			$.uls.data.getAutonym( language )
		).parse();

		languagesMessage = mw.message( group.priorityforce ?
			'tpt-discouraged-language-force-content' :
			'tpt-discouraged-language-content',
			preferredLanguages
		).parse();

		$groupWarning.append(
			$( '<p>' ).append( $( '<strong>' ).text( headerMessage ) ),
			// html because of the <bdi> and because it's parsed
			$( '<p>' ).html( languagesMessage )
		);
	}

	function setupLanguageSelector( $element ) {
		var docLanguageCode, docLanguageAutonym, ulsOptions;

		ulsOptions = {
			onSelect: function ( language ) {
				mw.translate.changeLanguage( language );
			},
			quickList: function () {
				return mw.uls.getFrequentLanguageList();
			}
		};

		// If a documentation pseudo-language is defined,
		// add it to the language selector
		docLanguageCode = mw.config.get( 'wgTranslateDocumentationLanguageCode' );
		if ( docLanguageCode ) {
			docLanguageAutonym = mw.msg( 'translate-documentation-language' );
			ulsOptions.languages = mw.config.get( 'wgULSLanguages' );
			ulsOptions.languages[ docLanguageCode ] = docLanguageAutonym;
			mw.translate.addDocumentationLanguage();
			ulsOptions.showRegions = [ 'WW', 'SP', 'AM', 'EU', 'ME', 'AF', 'AS', 'PA' ];
		}

		$element.uls( ulsOptions );
	}

	$( document ).ready( function () {
		var $translateContainer, $hideTranslatedButton, $controlOwnButton, $messageList,
			filter, uri, position;

		$messageList = $( '.tux-messagelist' );
		if ( $messageList.length ) {
			uri = new mw.Uri( window.location.href );
			filter = uri.query.filter;

			if ( filter === undefined ) {
				filter = '!translated';
			}

			mw.translate.changeFilter( filter );
			$( '.tux-message-selector li' ).each( function () {
				var $this = $( this );

				if ( $this.data( 'filter' ) === filter ) {
					$this.addClass( 'selected' );
				}
			} );
		}

		state.group = $( '.tux-messagetable-loader' ).data( 'messagegroup' );
		state.language = $messageList.data( 'targetlangcode' ) || // for tux=1
			mw.config.get( 'wgUserLanguage' ); // for tux=0

		if ( $( 'body' ).hasClass( 'rtl' ) ) {
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

		updateGroupInformation( state );

		$messageList.messagetable();

		$( '.ext-translate-language-selector .uls' ).one( 'click', function () {
			var $target = $( this );
			mw.loader.using( 'ext.uls.mediawiki' ).done( function () {
				setupLanguageSelector( $target );
				$target.click();
			} );
		} );

		if ( $.fn.translateeditor ) {
			// New translation editor
			$( '.tux-message' ).translateeditor();
		}

		$translateContainer = $( '.ext-translate-container' );

		if ( mw.translate.canProofread() ) {
			$translateContainer.find( '.proofread-mode-button' ).removeClass( 'hide' );
		}

		$hideTranslatedButton = $translateContainer.find( '.tux-editor-clear-translated' );
		$hideTranslatedButton
			.prop( 'disabled', !getTranslatedMessages( $translateContainer ).length )
			.click( function () {
				getTranslatedMessages( $translateContainer ).remove();
				$( this ).prop( 'disabled', true );
			} );

		$controlOwnButton = $translateContainer.find( '.tux-proofread-own-translations-button' );
		$controlOwnButton.click( function () {
			var $this = $( this ),
				ownTranslatedMessages = $translateContainer.find( '.own-translation' ),
				hideMessage = mw.msg( 'tux-editor-proofreading-hide-own-translations' ),
				showMessage = mw.msg( 'tux-editor-proofreading-show-own-translations' );

			if ( $this.hasClass( 'down' ) ) {
				ownTranslatedMessages.removeClass( 'hide' );
				$this.removeClass( 'down' ).text( hideMessage );
			} else {
				ownTranslatedMessages.addClass( 'hide' );
				$this.addClass( 'down' ).text( showMessage );
			}
		} );

		// Message filter click handler
		$translateContainer.find( '.row.tux-message-selector > li' ).on( 'click', function () {
			var newFilter,
				$this = $( this );

			if ( $this.hasClass( 'more' ) ) {
				return false;
			}

			newFilter = $this.data( 'filter' );

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
			var uri = new mw.Uri( window.location.href ),
				checked = $( this ).prop( 'checked' );

			mw.translate.changeUrl( { optional: checked ? 1 : 0 } );
			mw.translate.changeFilter( uri.query.filter );
		} );
	} );

}( jQuery, mediaWiki ) );
