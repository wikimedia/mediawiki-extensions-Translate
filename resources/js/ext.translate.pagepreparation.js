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
	 * Convert all the links into two-party form and add the 'Special:MyLanguage/' prefix
	 * to links in valid namespaces for the wiki. For example, [[Example]] would be converted
	 * to [[Special:MyLanguage/Example|Example]].
	 * @param {string} pageContent
	 * @return {string}
	 */
	function fixInternalLinks( pageContent ) {
		var normalizeRegex, linkPrefixRegex,
			namespaces, nsString;

		normalizeRegex = new RegExp( /\[\[(?!Category)([^|]*?)\]\]/gi );
		// First convert all links into two-party form. If a link is not having a pipe,
		// add a pipe and duplicate the link text
		// Regex : http://regex101.com/r/pO9nN2
		pageContent = pageContent.replace( normalizeRegex, '[[$1|$1]]' );

		namespaces = getNamespaces();
		nsString = namespaces.join( '|' );
		linkPrefixRegex = new RegExp( '\\[\\[((?:(?:special(?!:MyLanguage\\b)|' + nsString +
			'):)?[^:]*?)\\]\\]', 'gi' );
		// Add the 'Special:MyLanguage/' prefix for all internal links of valid namespaces and
		// mainspace.
		// Regex : http://regex101.com/r/zZ9jH9
		pageContent = pageContent.replace( linkPrefixRegex, '[[Special:MyLanguage/$1]]' );
		return pageContent;
	}

	/**
	 * Fetch all the aliases for a given namespace on the wiki.
	 * @param {string} pageContent
	 * @return {Array} The list of aliases for the given namespace
	 */
	function getNamespaceAliases( namespace ) {
		var namespaceID, api = new mw.Api();

		namespaceID = mw.config.get( 'wgNamespaceIds' )[namespace];
		return api.get( {
			action:'query',
			meta: 'siteinfo',
			siprop: 'namespacealiases'
		} ).then( function ( data ) {
			var aliases = [];
			for ( var alias in data.query.namespacealiases ) {
				if( data.query.namespacealiases[alias].id === namespaceID ) {
					aliases.push( $.escapeRE( data.query.namespacealiases[alias]['*'] ) );
				}
			}
			return aliases;
		} ).promise();
	}

	/**
	 * Add translate tags around only translatable content for files and keep everything else
	 * as a part of the page template.
	 * @param {string} pageContent
	 * @return {string}
	 */
	function doFiles( pageContent ) {

		$.when( getNamespaceAliases( 'file' ) ).then( function ( aliases ) {
			var aliasList, captionFilesRegex, fileRegex;

			aliases.push( 'file' );
			aliasList = aliases.join('|');

			// Add translate tags for files with captions
			captionFilesRegex = new RegExp( '\\[\\[(' + aliasList + ')(.*\\|)(.*?)\\]\\]', 'gi' );
			pageContent = pageContent.replace( captionFilesRegex,
				'</translate>\n[[$1$2<translate>$3</translate>]]\n<translate>' );

			// Add translate tags for files without captions
			fileRegex = new RegExp( '/\\[\\[((' + aliasList + ')[^\\|]*?)\\]\\]', 'gi' );
			pageContent = pageContent.replace( fileRegex, '\n</translate>[[$1]]\n<translate>' );

			return pageContent;
		} );
	}

	/**
	 * Cleanup done after the page is prepared for translation by the tool.
	 * @param {string} pageContent
	 * @return {string}
	 */
	function postPreparationCleanup( pageContent ) {
		// Removes any extra newlines introduced by the tool
		pageContent = pageContent.replace( /\n\n+/gi, '\n\n' );
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
		$.when( getPageContent( pageName ) ).done( function ( content ) {
			var pageContent = content;
			pageContent = $.trim( pageContent );
			pageContent = cleanupTags( pageContent );
			pageContent = addLanguageBar( pageContent );
			pageContent = addTranslateTags( pageContent );
			pageContent = addNewLines( pageContent );
			pageContent = doFiles( pageContent );
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
