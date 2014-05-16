( function ( $, mw ) {
	'use strict';
	$( document ).ready( function () {
		////alert("Hello from ext.translate.special.pagemigration.js");

		var api = new mw.Api(),
			translationUnits = [],
			messageToBeShown = '';

		function splitTranslationPage( timestampOld, pageTitle ) {
			mw.log('pageTitle is' + pageTitle);
			var deferred = new $.Deferred(),
				api = new mw.Api();
				//delete
			//This api call returns the content before FuzzyBot's edit
		    deferred = api.get( {
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
					mw.log('No page');
					//obj was not initialized. Handle this case
					//deferred.reject()?
				}
				if ( typeof obj.revisions === 'undefined' ) {
					//the case of /en subpage
					//alert( 'Nothing to import' );
					return;	//can be replaced with deferred.reject();
				}
				mw.log( obj.revisions[0]['*'].split( '\n\n' ) );
				pageContent = obj.revisions[0]['*'];
				oldTranslationUnits = pageContent.split( '\n\n' );
				for ( i = 0; i < oldTranslationUnits.length; i++ ) {
					$( '#translationunits' ).append( '<div id="t"' + (i + 1) + '">' +
						oldTranslationUnits[i] + '</div><br/>' );
				}
		    } );
			return deferred.promise();
		}

		function getFuzzyTimestamp( pageTitle ) {
		    var api = new mw.Api(),
				deferred = new $.Deferred();
		    //This api call returns the timestamp of FuzzyBot's edit
			deferred = api.get( {
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
				if( typeof obj === 'undefined' ) {
					mw.log( 'No page' );
					//obj was not initialized. Handle this case
					//deferred.reject()?
				}
				mw.log( data );
				if ( typeof obj.revisions === 'undefined' ) {	// variable is undefined //replace 17 by wgArticleId
					//alert( 'No edit by FuzzyBot on this page' );
					deferred.reject();
				}
				else {
					/*FB over here refers to FuzzyBot*/
					timestampFB = obj.revisions[0].timestamp;
					//var timestampFB = data['query']['pages']['17']['revisions'][0]['timestamp']; //replace 17 by wgArticleId
				    mw.log( 'Timestamp for FuzzyBot\'s revision: ' + timestampFB );
				    dateFB = new Date( timestampFB );
				    dateFB.setSeconds( dateFB.getSeconds() - 1 );
				    timestampOld = dateFB.toISOString();
				    mw.log( 'New Timestamp: ' + timestampOld );
				    //deferred.resolve();
					splitTranslationPage( timestampOld, pageTitle );
				}
			});
			return deferred.promise();
		}

		function getTranslationUnits( pageName ) {

			var deferred = new $.Deferred();
			deferred = api.get( {
				action: 'query',
				list: 'messagecollection',
				format: 'json',
				mcgroup: 'page-' + pageName,
				mclanguage: 'en',
				mcprop: 'definition'
			} ).done ( function( data ) {
				
				var result, i, tUnit, key;

				result = data.query.messagecollection;
				//mw.log(result.length);
				for ( i = 1; i < result.length; i++ ) {
					tUnit = {};
					key = result[i].key;
					tUnit.identifier = key.slice( key.lastIndexOf('/') + 1 );
					messageToBeShown += tUnit.identifier;
					tUnit.definition = result[i].definition;
					translationUnits.push( tUnit );
					$( '#sourceunits' ).append( '<div id="s' + tUnit.identifier + '">' +
						tUnit.definition + '</div><br/>' );
				}
				mw.log( 'messageToBeShown = ' + messageToBeShown );
				//splitTranslationPage();
			});
			return deferred.promise();
		}

		$( '#buttonImport' ).click( function() {
			var pageName, langCode, pageTitle;
			pageName = $( '#pagename' ).val();
			langCode = $( '#langcode' ).val();
			pageTitle = pageName + '/' + langCode;
			$.when( getTranslationUnits( pageName ), getFuzzyTimestamp( pageTitle ) ).then( function() {
				mw.log( 'All done now!' );
			} );
		} );
	} );
}( jQuery, mediaWiki ) );