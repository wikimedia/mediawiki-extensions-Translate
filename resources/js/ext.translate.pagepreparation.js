( function ( $, mw ) {
	'use strict';

	/**
	 * Save the page with a given page name and given content to the wiki.
	 * @param {string} pageName Page title
	 * @param {string} pageContent Content of the page to be saved
	 * @return {jQuery.promise}
	 */
	function savePage( pageName, pageContent ) {
		var api = new mw.Api();

		return api.postWithEditToken( {
			action: 'edit',
			format: 'json',
			title: pageName,
			text: pageContent,
			summary: mw.msg( 'pp-save-summary' ),
		} ).promise();
	}

	/**
	 * Remove all the <translate> tags before preparing the page. The
	 * tool will add them back wherever needed.
	 * @param {string} pageContent
	 * @return {string}
	 */
	function cleanupTags( pageContent ) {
		pageContent = pageContent.replace( /<\/?translate>\n?/gi, '' );
		return pageContent;
	}

	/**
	 * Add the <languages/> bar at the top of the page, if not present.
	 * Remove the old {{languages}} template, if present
	 * @param {string} pageContent
	 * @return {string}
	 */
	function addLanguageBar( pageContent ) {
		if ( !pageContent.match( /<languages\/>/gi ) ) {
			pageContent = '<languages/>\n' + pageContent;
		}
		pageContent = pageContent.replace( /\{\{languages.*?\}\}/gi, '' );
		return pageContent;
	}

	/**
	 * Add the <translate> and </translate> tags at the start and end of the page.
	 * The opening tag is added immediately after the <languages/> tag.
	 * @param {string} pageContent
	 * @return {string}
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
	 * @return {string}
	 */
	function addNewLines( pageContent ) {
		pageContent = pageContent.replace( /^(==.*==)\n*/gm, '\n$1\n\n' );
		return pageContent;
	}

	/**
	 * Add an anchor to a section header with the given headerText
	 * @param {string} headerText
	 * @param {string} pageContent
	 * @return {string}
	 */
	function addAnchor( headerText, pageContent ) {
		var headerSearchRegex, anchorID, replaceAnchorRegex,
			spanSearchRegex;

		anchorID = headerText.replace( ' ', '-' ).toLowerCase();

		headerText = $.escapeRE( headerText );
		// Search for the header having text as headerText
		// Regex: http://regex101.com/r/fD6iL1
		headerSearchRegex = new RegExp( '(==+[ ]*' + headerText + '[ ]*==+)', 'gi' );
		// This is to ensure the tags and the anchor are added only once
		if ( !pageContent.contains( '<span id="' + mw.html.escape( anchorID ) + '"' ) ) {
			pageContent = pageContent.replace( headerSearchRegex, '</translate>\n' +
				'<span id="' + mw.html.escape( anchorID ) + '"></span>\n<translate>\n$1' );
		}

		// This is to add back the tags which were removed in cleanupTags()
		if ( !pageContent.contains( '</translate>\n<span id="' + anchorID + '"' ) ) {
			spanSearchRegex = new RegExp( '(<span id="' + $.escapeRE( anchorID ) + '"></span>)', 'gi' );
			pageContent = pageContent.replace( spanSearchRegex, '\n</translate>\n$1\n</translate>\n' );
		}

		// Replace the link text with the anchorID defined above
		// Regex: http://regex101.com/r/kB5bK3
		replaceAnchorRegex = new RegExp( '(\\[\\[#)' + headerText + '(.*\\]\\])', 'gi' );
		pageContent = pageContent.replace( replaceAnchorRegex, '$1' +
			anchorID.replace( '$', '$$$' ) + '$2' );
		return pageContent;
	}

	/**
	 * Convert all the links into two-party form and add the 'Special:MyLanguage/' prefix
	 * to links in valid namespaces for the wiki. For example, [[Example]] would be converted
	 * to [[Special:MyLanguage/Example|Example]].
	 * @param {string} pageContent
	 * @return {string}
	 */
	function fixInternalLinks( pageContent ) {

		var normalizeRegex, linkPrefixRegex, sectionLinksRegex,
			match, searchText, namespaces, nsString;
		searchText = pageContent;

		normalizeRegex = new RegExp( /\[\[(?!Category)([^|]*?)\]\]/gi );
		// First convert all links into two-party form. If a link is not having a pipe,
		// add a pipe and duplicate the link text
		// Regex : http://regex101.com/r/pO9nN2
		pageContent = pageContent.replace( normalizeRegex, '[[$1|$1]]' );

		namespaces = getNamespaces();
		nsString = namespaces.join( '|' );
		// Finds all the links to sections on the same page.
		// Regex: http://regex101.com/r/cX6jT3
		sectionLinksRegex = new RegExp( /\[\[#(.*?)\|(.*?)\]\]/gi );
		match = sectionLinksRegex.exec( searchText );
		while ( match !== null ) {
			pageContent = addAnchor( match[1], pageContent );
			match = sectionLinksRegex.exec( searchText );
		}

		linkPrefixRegex = new RegExp( '\\[\\[((?:(?:special(?!:MyLanguage\\b)|' + nsString +
			'):)?[^:]*?)\\]\\]', 'gi' );
		// Add the 'Special:MyLanguage/' prefix for all internal links of valid namespaces and
		// mainspace.
		// Regex : http://regex101.com/r/zZ9jH9
		pageContent = pageContent.replace( linkPrefixRegex, '[[Special:MyLanguage/$1]]' );
		return pageContent;
	}

	/**
	 * Cleanup done after the page is prepared for translation by the tool.
	 * @param {string} pageContent
	 * @return {string}
	 */
	function postPreparationCleanup( pageContent ) {
		// Removes any extra newlines introduced by the tool
		pageContent = pageContent.replace( /\n\n+/gi, '\n\n' );
		// Removes the Special:MyLanguage/ prefix for section links
		pageContent = pageContent.replace( /Special:MyLanguage\/#/gi, '#' );
		return pageContent;
	}

	/**
	 * Get the current revision for the given page.
	 * @param {string} pageName
	 * @return {jQuery.Promise}
	 * @return {Function} return.done
	 * @return {string} return.done.value The current revision
	 */
	function getPageContent( pageName ) {
		var api = new mw.Api(), obj;
		return api.get( {
			action:'query',
			prop: 'revisions',
			format: 'json',
			rvprop: 'content',
			rvlimit: '1',
			titles: pageName
		} ).then( function ( data ) {

			for ( var page in data.query.pages ) {
				obj = data.query.pages[page];
			}
			return obj.revisions[0]['*'];
		} ).promise();
	}

	/**
	 * Get the list of valid namespaces for the wiki and remove unwanted
	 * ones from the list.
	 * @return {Array} Array of valid namespaces
	 */
	function getNamespaces() {
		var namespacesObject, namespaces = [], i;

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
			namespaces[i] = $.escapeRE( namespaces[i] );
		}
		return namespaces;
	}

	$( document ).ready( function () {
		var pageName;
		pageName = mw.config.get( 'wgPageName' );
		$.when( getPageContent( pageName ) ).done( function ( pageContent ) {
			pageContent = $.trim( pageContent );
			pageContent = cleanupTags( pageContent );
			pageContent = addLanguageBar( pageContent );
			pageContent = addTranslateTags( pageContent );
			pageContent = addNewLines( pageContent );
			pageContent = fixInternalLinks( pageContent );
			pageContent = postPreparationCleanup( pageContent );
			pageContent = $.trim( pageContent );
			savePage( pageName, pageContent ).then( function () {
				// This is just for the time being. So not doing i18n
				window.alert( 'The page was prepared for translation and has been saved.' );
			} );
		} );
	} );

} ( jQuery, mediaWiki ) );
