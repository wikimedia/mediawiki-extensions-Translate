( function ( $, mw ) {
	'use strict';
	$( document ).ready( function () {
		//alert("Hello from ext.translate.special.pagemigration.js");

		var api = new mw.Api(),
			translationUnits = [],
			messageToBeShown = "";

		function splitTranslationPage( timestampOld, pageTitle ){
			console.log("pageTitle is" + pageTitle);
			var deferred = new $.Deferred();
			var api_2 = new mw.Api();
			//This api call returns the content before FuzzyBot's edit
		    deferred = api_2.get ({
		        action: 'query',
		        prop: 'revisions',
		        format: 'json',
		        rvprop: 'content',
		        rvstart: timestampOld,
		        titles: pageTitle
		    }).done ( function( data ) {
		    	for (var page in data['query']['pages']) {
						var obj = data['query']['pages'][page];
		    	}
		    	if( typeof obj === 'undefined'){
		    		//obj was not initialized. Handle this case
		    		//deferred.reject()?
		    	}
		    	if (typeof obj['revisions'] === 'undefined') {
		    		//the case of /en subpage
		    		alert("Nothing to import");
		    		return;	//can be replaced with deferred.reject();
		    	};
		    	console.log(obj['revisions'][0]['*'].split('\n\n'));
		    	//console.log(data['query']['pages']['17']['revisions'][0]['*'].split('\n\n'));
		    	var pageContent = obj['revisions'][0]['*'];
		    	var oldTranslationUnits = pageContent.split('\n\n');
		    	for(var i=0; i < oldTranslationUnits.length; i++){
		    		$('#translationunits').append("<div id='t" + (i + 1) + "'>" + 
						oldTranslationUnits[i] + "</div><br/>");
		    	}
		    	//renderContentDialog(data['query']['pages'][wgArticleId]['revisions'][0]['*']);
		    });
			return deferred.promise();
		}

		function getFuzzyTimestamp( pageTitle ){
		    var api = new mw.Api();
		    var deferred = new $.Deferred();
		    // alert(articleId);
		    //This api call returns the timestamp of FuzzyBot's edit
		    deferred = api.get ({
		        action:'query',
		        prop: 'revisions',
		        format: 'json',
		        rvprop: 'timestamp',
		        rvuser: 'FuzzyBot',
		        rvdir: 'newer',
		        titles: pageTitle
		    }).done ( function( data ) {
		    	//FB = FuzzyBot
	    		for (var page in data['query']['pages']) {
						var obj = data['query']['pages'][page];
		    	}
		    	if( typeof obj === 'undefined'){
		    		//obj was not initialized. Handle this case
		    		//deferred.reject()?
		    	}
		    	console.log(data);
		    	if ( typeof obj['revisions'] === 'undefined' ) {	// variable is undefined //replace 17 by wgArticleId
	    			alert("No edit by FuzzyBot on this page");
					deferred.reject();
				}
				else {
					/*FB over here refers to FuzzyBot*/
					var timestampFB = obj['revisions'][0]['timestamp'];
			    	//var timestampFB = data['query']['pages']['17']['revisions'][0]['timestamp']; //replace 17 by wgArticleId
			        console.log( "Timestamp for FuzzyBot's revision: " + timestampFB );
			        var dateFB = new Date( timestampFB );
			        dateFB.setSeconds( dateFB.getSeconds() - 1 );
			        var timestampOld = dateFB.toISOString();
			        console.log( "New Timestamp: " + timestampOld );
			        //deferred.resolve();
	 				splitTranslationPage(timestampOld, pageTitle);
	 				
				}
		    });
		    return deferred.promise();
    	}

    	function getTranslationUnits( pageName ){

    		var deferred = new $.Deferred();
			deferred = api.get ({
				action:'query',
				list: 'messagecollection',
				format: 'json',
				mcgroup: 'page-' + pageName,
				mclanguage: 'en',
				mcprop: 'definition'
			}).done ( function( data ) {
				var result = data['query']['messagecollection'];
				//console.log(result.length);
				for (var i = 1; i < result.length; i++){
					var tUnit = new Object();
					var key = result[i].key;
					tUnit.identifier = key.slice(key.lastIndexOf('/') + 1);
					messageToBeShown += tUnit.identifier;
					tUnit.definition = result[i].definition;
					translationUnits.push(tUnit);
					$('#sourceunits').append("<div id='s" + tUnit.identifier + "'>" + 
						tUnit.definition + "</div><br/>");							
				}
				console.log("messageToBeShown = " + messageToBeShown);
				//splitTranslationPage();
			});
			return deferred.promise();
		}

		$('#buttonImport').click(function(){
			var pageName = $('#pagename').val();
			var langCode = $('#langcode').val();
			var pageTitle = pageName + "/" + langCode;
			$.when( getTranslationUnits( pageName ), getFuzzyTimestamp( pageTitle ) ).then( function(){
			 	console.log("All done now!");
			} );
		});
	} );
}( jQuery, mediaWiki ) );