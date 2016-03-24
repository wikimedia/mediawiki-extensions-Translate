( function ( $, mw ) {
	'use strict';

	mw.translate = mw.translate || {};

	mw.translate = $.extend( mw.translate, {
		dirty: false,
		// A cache for language stats loaded from API,
		// indexed by language code
		languagestats: {},

		/**
		 * Checks if the input placeholder attribute
		 * is supported on this element in this browser.
		 *
		 * @param {jQuery} element
		 * @return {boolean}
		 */
		isPlaceholderSupported: function ( element ) {
			return ( 'placeholder' in element[ 0 ] );
		},

		// Storage for language stats loader functions from API,
		// indexed by language code
		languageStatsLoader: {},

		/**
		 * Get language stats for a language from the API.
		 *
		 * @param {string} language Language code.
		 * @return {deferred}
		 */
		loadLanguageStats: function ( language ) {
			if ( !mw.translate.languageStatsLoader[ language ] ) {
				mw.translate.languageStatsLoader[ language ] = new mw.Api().get( {
					action: 'query',
					format: 'json',
					meta: 'languagestats',
					lslanguage: language
				} );
			}

			mw.translate.languageStatsLoader[ language ].done( function ( result ) {
				mw.translate.languagestats[ language ] = result.query.languagestats;
			} );

			return mw.translate.languageStatsLoader[ language ];
		},

		/**
		 * Load message group information asynchronously.
		 *
		 * @param {string} id Message group id
		 * @param {string|Array} [props] List of properties to load
		 * @return {jQuery.Promise} Object containing the requested properties on success.
		 */
		getMessageGroup: function ( id, props ) {
			var params, api;

			if ( $.isArray( props ) ) {
				props = props.join( '|' );
			} else if ( props === undefined ) {
				props = 'id|label|description|icon|priority|prioritylangs|priorityforce|workflowstates';
			}

			params = {
				meta: 'messagegroups',
				mgformat: 'flat',
				mgprop: props,
				mgroot: id
			};

			api = new mw.Api();

			return api.get( params ).then( function ( result ) {
				return result.query.messagegroups[ 0 ];
			} );
		},

		/**
		 * Find a group from an array of message groups as returned by web api
		 * and recurse it through sub groups.
		 *
		 * @param {string} id Group id to search for.
		 * @param {Array} groups Array of message grous
		 * @return {Object} Message group object
		 */
		findGroup: function ( id, groups ) {
			var result = null;

			if ( !id ) {
				return groups;
			}

			$.each( groups, function ( index, group ) {
				if ( group.id === id ) {
					result = group;
					return false;
				}

				if ( group.groups ) {
					group = mw.translate.findGroup( id, group.groups );

					if ( group ) {
						result = group;
						return false;
					}
				}
			} );

			return result;
		},

		/**
		 * Check if the current user is allowed to translate on this wiki.
		 *
		 * @return {boolean}
		 */
		canTranslate: function () {
			return mw.config.get( 'TranslateRight' );
		},

		/**
		 * Check if the current user is allowed to proofread on this wiki.
		 *
		 * @return {boolean}
		 */
		canProofread: function () {
			return mw.config.get( 'TranslateMessageReviewRight' );
		},

		/**
		 * Check if the current user can delete translations on this wiki.
		 *
		 * @return {boolean}
		 */
		canDelete: function () {
			return mw.config.get( 'DeleteRight' ) && mw.config.get( 'TranslateRight' );
		},

		addDocumentationLanguage: function () {
			var docLanguageCode = mw.config.get( 'wgTranslateDocumentationLanguageCode' );
			if ( $.uls.data.languages[ docLanguageCode ] ) {
				return;
			}
			$.uls.data.addLanguage( docLanguageCode, {
				script: $.uls.data.getScript( mw.config.get( 'wgContentLanguage' ) ),
				regions: [ 'SP' ],
				autonym: mw.msg( 'translate-documentation-language' )
			} );
		},

		isDirty: function () {
			return $( '.mw-ajax-dialog:visible' ).length || // For old Translate
				// For new Translate, something being typed in the current editor.
				mw.translate.dirty ||
				// For new translate, previous editors has some unsaved edits
				$( '.tux-status-unsaved' ).length;
		}
	} );

	function pageShowHandler() {
		$( window ).on( 'beforeunload.translate', function () {
			if ( mw.translate.isDirty() ) {
				// Return our message
				return mw.msg( 'translate-js-support-unsaved-warning' );
			}
		} );
	}

	/**
	 * A warning to be shown if a user tries to close the page or navigate away
	 * from it without saving the written translation.
	 */
	function translateOnBeforeUnloadRegister() {
		pageShowHandler();
		$( window ).on( 'pageshow.translate', pageShowHandler );
	}

	$( document ).ready( function () {
		translateOnBeforeUnloadRegister();
	} );
}( jQuery, mediaWiki ) );
