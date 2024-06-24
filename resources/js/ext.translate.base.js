( function () {
	'use strict';

	mw.translate = mw.translate || {};

	mw.translate = $.extend( mw.translate, {
		/** @private */
		dirty: false,

		/**
		 * A cache for language stats loaded from API, indexed by language code
		 *
		 * @internal
		 */
		languagestats: {},

		/**
		 * Storage for language stats loader functions from API, indexed by language code
		 *
		 * @private
		 */
		languageStatsLoader: {},

		/**
		 * Get language stats for a language from the API.
		 *
		 * @internal
		 * @param {string} language Language code.
		 * @return {jQuery.Deferred}
		 */
		loadMessageGroupStatsForLanguage: function ( language ) {
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
		 * Get language stats for a language and group from the API
		 *
		 * @internal
		 * @param {string} language
		 * @param {string} group
		 * @return {jQuery.Deferred}
		 */
		loadMessageGroupStatsForItem: function ( language, group ) {
			var uniqueKey = group + '|' + language;
			if ( !mw.translate.languageStatsLoader[ uniqueKey ] ) {
				mw.translate.languageStatsLoader[ uniqueKey ] = new mw.Api().get( {
					action: 'query',
					meta: 'languagestats',
					lslanguage: language,
					lsgroup: group
				} );
			}

			mw.translate.languageStatsLoader[ uniqueKey ]
				.done( function ( result ) {
					if ( result.query.languagestats && result.query.languagestats.length ) {
						mw.translate.languagestats[ language ] = result.query.languagestats;
					} else {
						mw.translate.languagestats[ language ] = [];
					}

				} );

			return mw.translate.languageStatsLoader[ uniqueKey ];
		},

		/**
		 * Load message group information asynchronously.
		 *
		 * @internal
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
				formatversion: 2,
				uselang: mw.config.get( 'wgUserLanguage' )
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
		 * @internal
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
		 * @internal
		 * @return {boolean}
		 */
		canTranslate: function () {
			return mw.config.get( 'TranslateRight' );
		},

		/**
		 * Check if the current user is allowed to proofread on this wiki.
		 *
		 * @internal
		 * @return {boolean}
		 */
		canProofread: function () {
			return mw.config.get( 'TranslateMessageReviewRight' );
		},

		/**
		 * Check if the current user can delete translations on this wiki.
		 *
		 * @internal
		 * @return {boolean}
		 */
		canDelete: function () {
			return mw.config.get( 'DeleteRight' ) && mw.config.get( 'TranslateRight' );
		},

		/**
		 * Check if the current user can update and manage message groups.
		 *
		 * @internal
		 * @return {boolean}
		 */
		canManage: function () {
			return mw.config.get( 'TranslateManageRight' );
		},

		/**
		 * Adds missing languages to the language database so that they can be used in ULS.
		 *
		 * @internal
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

		/**
		 * Checks if there are any unsaved edits in Special:Translate
		 *
		 * @internal
		 * @return {boolean|number}
		 */
		isDirty: function () {
			// Something being typed in the current editor.
			return mw.translate.dirty ||
				// Previous editors has some unsaved edits
				$( '.tux-status-unsaved' ).length;
		},

		/**
		 * Return the language details for usage in HTML attributes
		 *
		 * @internal
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
