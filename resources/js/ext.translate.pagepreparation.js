( function ( $, mw ) {
	'use strict';

	var nsString = '';

	function addLanguageBar( pageContent ) {
		pageContent = '<languages/>\n' + pageContent.replace( /({{languages)+.*(}})+/gi, '' );
		return pageContent;
	}

	function fixInternalLinks ( pageContent ) {
		var normalizeRegex, linkPrefixRegex;
		normalizeRegex = new RegExp( /\[\[([^|]*?)\]\]/gi );
		// First convert all links into two-party form. If a link is not having a pipe,
		// add a pipe and duplicate the link text
		// Regex : http://regex101.com/r/pO9nN2
		pageContent = pageContent.replace( normalizeRegex, '[[$1|$1]]' );

		linkPrefixRegex = new RegExp( '\\[\\[((?:(?:' + nsString + '):)?[^:]*?)\\]\\]', 'gi' );
		// Add the 'Special:MyLanguage/' prefix for all internal links of valid namespaces and
		// mainspace.
		// Regex : http://regex101.com/r/jZ9wX7
		pageContent = pageContent.replace( linkPrefixRegex, '[[Special:MyLanguage/$1]]' );

		return pageContent;
	}

	function fixFiles( pageContent ) {
		var captionFilesRegex, fileRegex;

		captionFilesRegex = new RegExp( /\[\[([Ff]ile.*\|)(.*?)\]\]/gi );
		// Add translate tags for files with captions
		// Regex: http://regex101.com/r/zM0cI7
		pageContent = pageContent.replace( captionFilesRegex,
			'</translate>[[$1<translate>$2</translate>]]<translate>' );

		fileRegex = new RegExp( /\[\[([Ff]ile[^|]*?)\]\]/gi );
		// Add translate tags for files without captions
		// Regex : http://regex101.com/r/cB3xJ9
		pageContent = pageContent.replace( fileRegex,
			'</translate>[[$1]]<translate>');

		return pageContent;
	}

	function prepareForTranslation( pageName ) {
		var api = new mw.Api(), obj;
		//This api call returns the raw source text of the page
		api.get( {
			action:'query',
			prop: 'revisions',
			format: 'json',
			rvprop: 'content',
			rvlimit: '1',
			titles: pageName
		} ).done( function ( data ) {
			var pageContent;

			for ( var page in data.query.pages ) {
				obj = data.query.pages[page];
			}
			pageContent = obj.revisions[0]['*'];
			pageContent = addLanguageBar( pageContent );
			pageContent = fixInternalLinks( pageContent );
			pageContent = fixFiles( pageContent );
			console.log( pageContent );
		});
	}

	function getNameSpaces() {
		var namespacesObject, namespaces = [],
			index, i;
		namespacesObject = mw.config.get( 'wgNamespaceIds' );
		for ( var key in namespacesObject ) {
			namespaces.push( key );
		}
		index = namespaces.indexOf( '' );
		namespaces.splice( index, 1 );
		index = namespaces.indexOf( 'category' );
		namespaces.splice( index, 1 );
		index = namespaces.indexOf( 'category_talk' );
		namespaces.splice( index, 1 );

		for ( i = 0; i < namespaces.length; i++ ) {
			nsString += namespaces[i] + '|';
		}
		nsString = nsString.slice( 0, -1 );
	}

	$( document ).ready( function () {
		var pageName;
		getNameSpaces();
		pageName = mw.config.get( 'wgPageName' );
		prepareForTranslation( pageName );
	} );
} ( jQuery, mediaWiki ) );
