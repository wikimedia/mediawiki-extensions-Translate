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
		 * @param {jQuery} element
		 * @return {boolean}
		 */
		isPlaceholderSupported: function ( element ) {
			return ( 'placeholder' in element[0] );
		},

		// Storage for language stats loader functions from API,
		// indexed by language code
		languageStatsLoader: {},
		messageGroupsLoader: null,

		messageGroups: {},
		/**
		 * Get language stats for a language from the API.
		 * @param {string} language Language code.
		 * @return {deferred}
		 */
		loadLanguageStats: function ( language ) {
			if ( !mw.translate.languageStatsLoader[language] ) {
				mw.translate.languageStatsLoader[language] = new mw.Api().get( {
					action: 'query',
					format: 'json',
					meta: 'languagestats',
					lslanguage: language
				} );
			}

			mw.translate.languageStatsLoader[language].done( function ( result ) {
				mw.translate.languagestats[language] = result.query.languagestats;
			} );

			return mw.translate.languageStatsLoader[language];
		},

		/**
		 * Loads information about all message groups. Use getMessageGroup
		 * instead.
		 *
		 * @return {jQuery.Deferred}
		 */
		loadMessageGroups: function () {
			if ( mw.translate.messageGroupsLoader ) {
				return mw.translate.messageGroupsLoader;
			}

			var loader,
				queryParams = {
					action: 'query',
					format: 'json',
					meta: 'messagegroups',
					mgformat: 'tree',
					mgprop: 'id|label|description|icon|priority|prioritylangs|priorityforce|workflowstates',
					mgiconsize: '32'
				};
			loader = new mw.Api().get( queryParams );
			loader.done( function ( result ) {
				mw.translate.messageGroups = result.query.messagegroups;
			} );

			mw.translate.messageGroupsLoader = loader;
			return loader;
		},

		/**
		 * Load message group information asynchronously.
		 * @param {string} id Message group id
		 * @return {jQuery.Deferred}
		 */
		getMessageGroup: function ( id ) {
			var deferred = new $.Deferred();
			mw.translate.loadMessageGroups().done( function () {
				deferred.resolve( mw.translate.getGroup( id, mw.translate.messageGroups ) );
			} );
			return deferred;
		},

		/**
		 * Check if the current user is allowed to translate on this wiki.
		 * @return {boolean}
		 */
		canTranslate: function () {
			return mw.config.get( 'TranslateRight' );
		},

		/**
		 * Check if the current user is allowed to proofread on this wiki.
		 * @return {boolean}
		 */
		canProofread: function () {
			return mw.config.get( 'TranslateMessageReviewRight' );
		},

		addDocumentationLanguage: function () {
			var docLanguageCode = mw.config.get( 'wgTranslateDocumentationLanguageCode' );
			if ( $.uls.data.languages[docLanguageCode] ) {
				return;
			}
			$.uls.data.addLanguage( docLanguageCode, {
				script: $.uls.data.getScript( mw.config.get( 'wgContentLanguage' ) ),
				regions: ['SP'],
				autonym: mw.msg( 'translate-documentation-language' )
			} );
		},

		isDirty: function () {
			return  $( '.mw-ajax-dialog:visible' ).length // For old Translate
				// For new Translate, something being typed in the current editor.
				|| mw.translate.dirty
				// For new translate, previous editors has some unsaved edits
				|| $( '.tux-status-unsaved' ).length;
		}
	} );

	/**
	 * A warning to be shown if a user tries to close the page or navigate away
	 * from it without saving the written translation.
	 */
	function translateOnBeforeUnloadRegister() {
		pageShowHandler();
		$( window ).on( 'pageshow.translate', pageShowHandler );
	}

	function pageShowHandler() {
		$( window ).on( 'beforeunload.translate', function () {
			if ( mw.translate.isDirty() ) {
				// Return our message
				return mw.msg( 'translate-js-support-unsaved-warning' );
			}
		} );
	}

	$( document ).ready( function () {
		translateOnBeforeUnloadRegister();
	} );
}( jQuery, mediaWiki ) );
