( function ( $, mw ) {
	'use strict';

	var api = new mw.Api(),
			translationUnits = [];

	function splitTranslationPage( timestampOld, pageTitle ) {

		var deferred = new $.Deferred(),
			api = new mw.Api();

		//This api call returns the content before FuzzyBot's edit
		api.get( {
			action: 'query',
			prop: 'revisions',
			format: 'json',
			rvprop: 'content',
			rvstart: timestampOld,
			titles: pageTitle
		} ).done( function( data ) {

				var pageContent, oldTranslationUnits, i, obj, page;

				for ( page in data.query.pages ) {
						obj = data.query.pages[page];
				}
				if ( typeof obj === 'undefined' ) {
					console.log('No page');
					//obj was not initialized. Handle this case
					//deferred.reject()?
				}
				if ( typeof obj.revisions === 'undefined' ) {
					//the case of /en subpage
					console.log( 'Nothing to import' );
					return;	//can be replaced with deferred.reject();
				}
				console.log( obj.revisions[0]['*'].split( '\n\n' ) );
				pageContent = obj.revisions[0]['*'];
				oldTranslationUnits = pageContent.split( '\n\n' );
				deferred.resolve(oldTranslationUnits);
		} );
		return deferred.promise();
	}

	function getFuzzyTimestamp( pageTitle ) {
		var api = new mw.Api(),
		deferred = new $.Deferred();
	    //This api call returns the timestamp of FuzzyBot's edit
		api.get( {
			action: 'query',
			prop: 'revisions',
			format: 'json',
			rvprop: 'timestamp',
			rvuser: 'FuzzyBot',
			rvdir: 'newer',
			titles: pageTitle
		} ).done ( function( data ) {

				var timestampFB, dateFB, timestampOld,
					page, obj;
				//FB = FuzzyBot
				for ( page in data.query.pages ) {
						obj = data.query.pages[page];
				}
				if ( typeof obj === 'undefined' ) {
					console.log( 'No page' );
					//obj was not initialized. Handle this case
					//deferred.reject()?
				}
				console.log( data );
				if ( typeof obj.revisions === 'undefined' ) {	// variable is undefined //replace 17 by wgArticleId
					console.log( 'No edit by FuzzyBot on this page' );
					deferred.reject();
				}
				else {
					/*FB over here refers to FuzzyBot*/
					timestampFB = obj.revisions[0].timestamp;
					console.log( 'Timestamp for FuzzyBot\'s revision: ' + timestampFB );
					dateFB = new Date( timestampFB );
					dateFB.setSeconds( dateFB.getSeconds() - 1 );
					timestampOld = dateFB.toISOString();
					console.log( 'New Timestamp: ' + timestampOld );
					splitTranslationPage( timestampOld, pageTitle ).done( function( translations ){
						deferred.resolve( translations );
					} );
				}
		} );
		return deferred.promise();
	}

	function getTranslationUnits( pageName ) {

		var deferred = new $.Deferred();
		api.get( {
			action: 'query',
			list: 'messagecollection',
			format: 'json',
			mcgroup: 'page-' + pageName,
			mclanguage: 'en',
			mcprop: 'definition'
		} ).done ( function( data ) {

				var result, i, tUnit, key;
				translationUnits = [],

				result = data.query.messagecollection;

				for ( i = 1; i < result.length; i++ ) {
					tUnit = {};
					key = result[i].key;
					tUnit.identifier = key.slice( key.lastIndexOf('/') + 1 );
					tUnit.definition = result[i].definition;
					translationUnits.push( tUnit );
				}
				deferred.resolve(translationUnits);
		} );
		return deferred.promise();
	}

	$( document ).ready( function () {

		function showTranslationUnits( translationUnits ) {
			var i;

			$( '#translationunits' ).html("");

			for ( i = 0; i < translationUnits.length; i++ ) {
				$( '#translationunits' ).append( '<div id="t"' + (i + 1) + '">' +
					translationUnits[i] + '</div><br/>' );	
			}	
		}

		function showSourceUnits( sourceUnits ) {
			var i;

			$( '#sourceunits' ).html("");

			for ( i = 0; i < sourceUnits.length; i++ ) {
				$( '#sourceunits' ).append( '<div id="s' + sourceUnits[i].identifier + '">' +
							sourceUnits[i].definition + '</div><br/>' );
			}
		}

		$( '#buttonImport' ).click( function() {
			var pageName, langCode, pageTitle;
			pageName = $( '#pagename' ).val();
			langCode = $( '#langcode' ).val();
			pageTitle = pageName + '/' + langCode;
			$.when( getTranslationUnits( pageName ), getFuzzyTimestamp( pageTitle ) ).then( function(sourceUnits, translationUnits) {
				console.log( 'All done now!' );
				showSourceUnits( sourceUnits );
				showTranslationUnits( translationUnits );
			} );
		} );
	} );
} ( jQuery, mediaWiki ) );