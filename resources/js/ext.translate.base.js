( function () {
	'use strict';

	mw.translate = mw.translate || {};

	mw.translate = $.extend( mw.translate, {
		dirty: false,
		// A cache for language stats loaded from API,
		// indexed by language code
		languagestats: {},

		// Storage for language stats loader functions from API,
		// indexed by language code
		languageStatsLoader: {},

		/**
		 * Get language stats for a language from the API.
		 *
		 * @param {string} language Language code.
		 * @return {jQuery.Deferred}
		 */
		loadLanguageStats: function ( language ) {
			if ( !mw.translate.languageStatsLoader[ language ] ) {
				mw.translate.languageStatsLoader[ language ] = new mw.Api().get( {
					action: 'query',
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
			if ( Array.isArray( props ) ) {
				props = props.join( '|' );
			} else if ( props === undefined ) {
				props = 'id|label|description|icon|priority|prioritylangs|priorityforce|workflowstates';
			}

			var params = {
				meta: 'messagegroups',
				mgformat: 'flat',
				mgprop: props,
				mgroot: id,
				formatversion: 2
			};

			var api = new mw.Api();

			return api.get( params ).then( function ( result ) {
				return result.query.messagegroups[ 0 ];
			} );
		},

		/**
		 * Find a group from an array of message groups as returned by web api
		 * and recurse it through sub groups.
		 *
		 * @param {string} id Group id to search for.
		 * @param {Array} groups Array of message groups
		 * @return {Object} Message group object
		 */
		findGroup: function ( id, groups ) {
			if ( !id ) {
				return groups;
			}

			var result;
			groups.some( function ( group ) {
				if ( group.id === id ) {
					result = group;
					return true;
				}

				if ( group.groups ) {
					group = mw.translate.findGroup( id, group.groups );

					if ( group ) {
						result = group;
						return true;
					}
				}

				return false;
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

		/**
		 * Check if the current user can update and manage message groups.
		 *
		 * @return {boolean}
		 */
		canManage: function () {
			return mw.config.get( 'TranslateManageRight' );
		},

		/**
		 * Adds missing languages to the language database so that they can be used in ULS.
		 *
		 * @param {Object} languages Language tags mapped to language names
		 * @param {Array} regions Which regions to add the languages.
		 */
		addExtraLanguagesToLanguageData: function ( languages, regions ) {
			for ( var code in languages ) {
				if ( code in $.uls.data.languages ) {
					continue;
				}

				$.uls.data.addLanguage( code, {
					script: 'Zyyy',
					regions: regions,
					autonym: languages[ code ]
				} );
			}
		},

		isDirty: function () {
			// Something being typed in the current editor.
			return mw.translate.dirty ||
				// Previous editors has some unsaved edits
				$( '.tux-status-unsaved' ).length;
		},

		/**
		 * Return the language details for usage in HTML attributes
		 *
		 * @param {string} languageCode
		 * @return {Object}
		 */
		getLanguageDetailsForHtml: function ( languageCode ) {
			var languageCodeForHtml = languageCode;
			if ( languageCode === mw.config.get( 'wgTranslateDocumentationLanguageCode' ) ) {
				languageCodeForHtml = mw.config.get( 'wgContentLanguage' );
			}

			return {
				code: languageCodeForHtml,
				direction: $.uls.data.getDir( languageCodeForHtml ),
				autonym: $.uls.data.getAutonym( languageCode )
			};
		}
	} );

	/**
	 * A warning to be shown if a user tries to close the page or navigate away
	 * from it without saving the written translation.
	 */
	function translateOnBeforeUnloadRegister() {
		$( window ).on( 'beforeunload', function () {
			if ( mw.translate.isDirty() ) {
				// Return our message
				return mw.msg( 'translate-js-support-unsaved-warning' );
			}
		} );
	}

	$( function () {
		translateOnBeforeUnloadRegister();
	} );
}() );
