( function ( $, mw ) {
	'use strict';

	/**
	 * Get the old translations of a given page at given time.
	 * @param {string} fuzzyTimestamp Timestamp in MediaWiki format
	 * @param {string} pageTitle
	 * @return {jQuery.Promise}
	 * @return {Function} return.done
	 * @return {Array} oldTranslationUnits Array of paragraphs
	 */
	function splitTranslationPage( fuzzyTimestamp, pageTitle ) {
		var api = new mw.Api();

		return api.get( {
			action: 'query',
			prop: 'revisions',
			format: 'json',
			rvprop: 'content',
			rvstart: fuzzyTimestamp,
			titles: pageTitle
		} ).then( function( data ) {
			var pageContent, oldTranslationUnits, obj, page;

			for ( page in data.query.pages ) {
				obj = data.query.pages[page];
			}
			if ( typeof obj === undefined ) {
				// obj was not initialized. Handle this case
				mw.log('No page');
				return new $.Deferred().reject();
			}
			if ( typeof obj.revisions === undefined ) {
				// the case of /en subpage
				mw.log( 'Nothing to import' );
				return new $.Deferred().reject();
			}
			mw.log( obj.revisions[0]['*'].split( '\n\n' ) );
			pageContent = obj.revisions[0]['*'];
			oldTranslationUnits = pageContent.split( '\n\n' );
			return oldTranslationUnits;
		} ).promise();
	}

	/**
	 * Get the timestamp before FuzzyBot's first edit on page
	 * @param {string} pageTitle
	 * @return {jQuery.Promise}
	 * @return {Function} return.done
	 * @return {string} return.done.data
	 */
	function getFuzzyTimestamp( pageTitle ) {
		var api = new mw.Api();

		// This api call returns the timestamp of FuzzyBot's edit
		return api.get( {
			action: 'query',
			prop: 'revisions',
			format: 'json',
			rvprop: 'timestamp',
			rvuser: 'FuzzyBot',
			rvdir: 'newer',
			titles: pageTitle
		} ).then ( function( data ) {
			var timestampFB, dateFB, timestampOld,
				page, obj;
			// FB = FuzzyBot
			for ( page in data.query.pages ) {
				obj = data.query.pages[page];
			}
			if ( typeof obj === undefined ) {
				mw.log( 'No page' );
				return new $.Deferred().reject();
			}
			mw.log( data );
			if ( typeof obj.revisions === undefined ) {
				mw.log( 'No edit by FuzzyBot on this page' );
				return new $.Deferred().reject();
			} else {
				/*FB over here refers to FuzzyBot*/
				timestampFB = obj.revisions[0].timestamp;
				dateFB = new Date( timestampFB );
				dateFB.setSeconds( dateFB.getSeconds() - 1 );
				timestampOld = dateFB.toISOString();
				mw.log( 'New Timestamp: ' + timestampOld );
				return timestampOld;
			}
		} ).promise();
	}

	/**
	 * Get the translation units created by Translate extension
	 * @param {string} pageName
	 * return {jQuery.Promise}
	 * return {Function} return.done
	 * return {Array} return.done.data Array of sUnit Objects
	 */
	function getSourceUnits( pageName ) {
		var api = new mw.Api();

		return api.get( {
			action: 'query',
			list: 'messagecollection',
			format: 'json',
			mcgroup: 'page-' + pageName,
			mclanguage: 'en',
			mcprop: 'definition'
		} ).then ( function( data ) {
			var result, i, sUnit, key,
				sourceUnits = [];

			result = data.query.messagecollection;

			for ( i = 1; i < result.length; i++ ) {
				sUnit = {};
				key = result[i].key;
				sUnit.identifier = key.slice( key.lastIndexOf( '/' ) + 1 );
				sUnit.definition = result[i].definition;
				sourceUnits.push( sUnit );
			}
			return sourceUnits;
		} ).promise();
	}

	/**
	 * Display the imported translations
	 * @param {Array} translationUnits Array of translations
	 */
	function showTranslationUnits( translationUnits ) {
		var i, newUnit;

		$( '#translationunits' ).html( '' );

		for ( i = 0; i < translationUnits.length; i++ ) {
			newUnit = $( '<div>' )
				.attr( 'id', 't' + ( i + 1 ) )
				.text( translationUnits[i] );
			$( '#translationunits' ).append( newUnit );
		}
	}

	/**
	 * Display the translation units for source page
	 * @param {Array} sourceUnits Array of Objects
	 */
	function showSourceUnits( sourceUnits ) {
		var i, newUnit;

		$( '#sourceunits' ).html( '' );

		for ( i = 0; i < sourceUnits.length; i++ ) {
			newUnit = $( '<div>' )
				.attr( 'id', 's' + sourceUnits[i].identifier )
				.text( sourceUnits[i].definition );
			$( '#sourceunits' ).append( newUnit );
		}
	}

	$( document ).ready( function () {

		$( '#buttonImport' ).click( function() {
			var pageName, langCode, pageTitle;

			pageName = $( '#pagename' ).val();
			langCode = $( '#langcode' ).val();
			pageTitle = pageName + '/' + langCode;

			$.when( getSourceUnits( pageName ), getFuzzyTimestamp( pageTitle ) )
				.then( function( sourceUnits, fuzzyTimestamp ) {
				mw.log( 'All done now!' );
				showSourceUnits( sourceUnits );
				splitTranslationPage( fuzzyTimestamp, pageTitle ).done( function( translations ) {
					showTranslationUnits( translations );
				} );
			} );
		} );
	} );
} ( jQuery, mediaWiki ) );