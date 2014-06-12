( function ( $, mw ) {
	'use strict';

	/**
	 * Save the page with a given page name and given content to the wiki.
	 * @param {string} pageName Page title
	 * @param {string} pageContent Content of the page to be saved
	 * @return {jQuery.promise}
	 */
	function savePage( pageName, pageContent ) {
		var api = new mw.Api(), summary;
		summary = 'prepared the page for translation';
		api.postWithEditToken( {
			action: 'edit',
			format: 'json',
			title: pageName,
			text: pageContent,
			summary: summary,
		} ).promise();
	}

	/**
	 * Remove all the <translate> tags before preparing the page. The
	 * tool will add them back wherever needed.
	 * @param {string} pageContent
	 * @return {string} pageContent
	 */
	function cleanupTags( pageContent ) {
		pageContent = pageContent.replace( /(<translate>\n|<translate>)/gi, '' );
		pageContent = pageContent.replace( /(<\/translate>\n|<\/translate>)/gi, '' );
		return pageContent;
	}

	/**
	 * Add the <languages/> bar at the top of the page, if not present.
	 * Remove the old {{languages}} template, if present
	 * @param {string} pageContent
	 * @return {string} pageContent
	 */
	function addLanguageBar( pageContent ) {
		if ( !pageContent.match( /<languages\/>\n/gi ) ) {
			pageContent = '<languages/>\n' + pageContent;
		}
		pageContent = pageContent.replace( /\{\{languages.*?\}\}/gi, '' );
		return pageContent;
	}

	/**
	 * Add the <translate> and </translate> tags at the start and end of the page.
	 * The opening tag is added immediately after the <languages/> bar.
	 * @param {string} pageContent
	 * @return {string} pageContent
	 */
	function addTranslateTags( pageContent ) {
		pageContent = pageContent.replace( /(<languages\/>\n)/gi, '$1<translate>\n' );
		pageContent = pageContent + '\n</translate>';
		return pageContent;
	}

	/**
	 * Add newlines before and after section headers. Extra newlines resulting after
	 * this operation are cleaned up in postPreparationCleanup() function.
	 * @param {string} pageContent
	 * @return {string} pageContent
	 */
	function addNewLines( pageContent ) {
		pageContent = pageContent.replace( /(==.*==)\n*/gi, '\n$1\n\n' );
		return pageContent;
	}

	/**
	 * Convert all the links into two-party form and add the 'Special:MyLanguage/' prefix
	 * to links in valid namespaces for the wiki.
	 * @param {string} pageContent
	 * @return {string} pageContent
	 */
	function fixInternalLinks ( pageContent ) {
		var normalizeRegex, linkPrefixRegex, nsString;

		normalizeRegex = new RegExp( /\[\[(?!Category)([^|]*?)\]\]/gi );
		// First convert all links into two-party form. If a link is not having a pipe,
		// add a pipe and duplicate the link text
		// Regex : http://regex101.com/r/pO9nN2
		pageContent = pageContent.replace( normalizeRegex, '[[$1|$1]]' );

		nsString = getNameSpaces();
		linkPrefixRegex = new RegExp( '\\[\\[((?:(?:special(?!:MyLanguage\\b)|' + nsString +
			'):)?[^:]*?)\\]\\]', 'gi' );
		// Add the 'Special:MyLanguage/' prefix for all internal links of valid namespaces and
		// mainspace.
		// Regex : http://regex101.com/r/zZ9jH9
		pageContent = pageContent.replace( linkPrefixRegex, '[[Special:MyLanguage/$1]]' );
		return pageContent;
	}

	/**
	 * Add <translate> tags around translatable content for Files. Deals with
	 * files with and without caption.
	 * @param {string} pageContent
	 * @return {string} pageContent
	 */
	function fixFiles( pageContent ) {
		var captionFilesRegex, fileRegex;

		captionFilesRegex = new RegExp( /\[\[([Ff]ile.*\|)(.*?)\]\]/gi );
		// Add translate tags for files with captions
		// Regex: http://regex101.com/r/zM0cI7
		pageContent = pageContent.replace( captionFilesRegex,
			'</translate>\n[[$1<translate>$2</translate>]]\n<translate>' );

		fileRegex = new RegExp( /\[\[([Ff]ile[^|]*?)\]\]/gi );
		// Add translate tags for files without captions
		// Regex : http://regex101.com/r/cB3xJ9
		pageContent = pageContent.replace( fileRegex,
			'\n</translate>[[$1]]\n<translate>' );
		return pageContent;
	}

	/**
	 * Cleanup done after the page is prepared for translation by the tool.
	 * @param {string} pageContent
	 * @return {string} pageContent
	 */
	function postPreparationCleanup( pageContent ) {
		// Removes any extra newlines introduced by the tool
		pageContent = pageContent.replace( /\n\n+/gi, '\n\n' );
		return pageContent;
	}

	/**
	 * Get the current revision for the given pageName.
	 * @param {string} pageName
	 * @return {jQuery.Promise}
	 * @return {Function} return.done
	 * @return {string} pageContent The current revision
	 */
	function getPageContent( pageName ) {
		var api = new mw.Api(), obj;
		//This api call returns the raw source text of the page
		return api.get( {
			action:'query',
			prop: 'revisions',
			format: 'json',
			rvprop: 'content',
			rvlimit: '1',
			titles: pageName
		} ).then( function ( data ) {
			var pageContent;

			for ( var page in data.query.pages ) {
				obj = data.query.pages[page];
			}
			pageContent = obj.revisions[0]['*'];
			return pageContent;
		}).promise();
	}

	/**
	 * Get the list of valid namespaces for the wiki and remove unwanted
	 * ones from the list. Sets the global variable nsString with the string
	 * required by regex linkPrefixRegex in fixInternalLinks() function.
	 * @return {string} String of valid namespaces separated by '|'
	 */
	function getNameSpaces() {
		var namespacesObject, namespaces = [],
			nsString = '', i;

		namespacesObject = mw.config.get( 'wgNamespaceIds' );
		for ( var key in namespacesObject ) {
			namespaces.push( key );
		}

		// Remove all what has been already handled somewhere else
		namespaces.splice( $.inArray( '', namespaces), 1 );
		namespaces.splice( $.inArray( 'category', namespaces), 1 );
		namespaces.splice( $.inArray( 'category_talk', namespaces), 1 );
		namespaces.splice( $.inArray( 'special', namespaces), 1 );
		namespaces.splice( $.inArray( 'file', namespaces), 1 );
		namespaces.splice( $.inArray( 'file_talk', namespaces), 1 );

		for ( i = 0; i < namespaces.length; i++ ) {
			nsString += namespaces[i] + '|';
		}
		nsString = nsString.slice( 0, -1 );
		return nsString;
	}

	$( document ).ready( function () {
		var pageName;
		pageName = mw.config.get( 'wgPageName' );
		getNameSpaces();
		$.when( getPageContent( pageName ) ).then( function ( pageContent ) {
			pageContent = $.trim( pageContent );
			pageContent = cleanupTags( pageContent );
			pageContent = addLanguageBar( pageContent );
			pageContent = addTranslateTags( pageContent );
			pageContent = addNewLines( pageContent );
			pageContent = fixFiles( pageContent );
			pageContent = fixInternalLinks( pageContent );
			pageContent = postPreparationCleanup( pageContent );
			pageContent = $.trim( pageContent );
			savePage( pageName, pageContent );
		} );
	} );

} ( jQuery, mediaWiki ) );
