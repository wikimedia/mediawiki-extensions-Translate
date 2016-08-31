( function ( $, mw ) {
	'use strict';

	/**
	 * Save the page with a given page name and given content to the wiki.
	 *
	 * @param {string} pageName Page title
	 * @param {string} pageContent Content of the page to be saved
	 * @return {jQuery.Promise}
	 */
	function savePage( pageName, pageContent ) {
		var api = new mw.Api();

		// Change to csrf when support for MW 1.25 is dropped
		return api.postWithToken( 'edit', {
			action: 'edit',
			format: 'json',
			title: pageName,
			text: pageContent,
			summary: $( '#pp-summary' ).val()
		} ).promise();
	}

	/**
	 * Get the diff between the current revision and the prepared page content.
	 *
	 * @param {string} pageName Page title
	 * @param {string} pageContent Content of the page to be saved
	 * @return {jQuery.Promise}
	 * @return {Function} return.done
	 * @return {string} return.done.data
	 */
	function getDiff( pageName, pageContent ) {
		var api = new mw.Api();

		return api.post( {
			action: 'query',
			prop: 'revisions',
			format: 'json',
			rvprop: 'content',
			rvlimit: '1',
			titles: pageName,
			rvdifftotext: pageContent
		} ).then( function ( data ) {
			var page, obj, diff;

			for ( page in data.query.pages ) {
				obj = data.query.pages[ page ];
			}

			diff = obj.revisions[ 0 ].diff[ '*' ];

			return diff;
		} );
	}

	/**
	 * Remove all the <translate> tags and {{translation}} templates before
	 * preparing the page. The tool will add them back wherever needed.
	 *
	 * @param {string} pageContent
	 * @return {string}
	 */
	function cleanupTags( pageContent ) {
		pageContent = pageContent.replace( /<\/?translate>\n?/gi, '' );
		return pageContent;
	}

	/**
	 * Add the <languages/> bar at the top of the page, if not present.
	 * Remove the old {{languages}} template, if present.
	 *
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
	 * Add <translate> tags around Categories to make them a part of the page template
	 * and tag them with the {{translation}} template.
	 *
	 * @param {string} pageContent
	 * @return {jQuery.Promise}
	 */
	function doCategories( pageContent ) {
		return getNamespaceAliases( 14 ).then( function ( aliases ) {
			var i, aliasList, categoryRegex;

			aliases.push( 'category' );
			for ( i = 0; i < aliases.length; i++ ) {
				aliases[ i ] = mw.RegExp.escape( aliases[ i ] );
			}

			aliasList = aliases.join( '|' );
			// Regex: http://regex101.com/r/sJ3gZ4/2
			categoryRegex = new RegExp( '\\[\\[((' + aliasList + ')' +
				':[^\\|]+)(\\|[^\\|]*?)?\\]\\]', 'gi' );
			pageContent = pageContent.replace( categoryRegex, '\n</translate>\n' +
				'[[$1{{#translation:}}$3]]\n<translate>\n' );

			return pageContent;
		} );
	}

	/**
	 * Add the <translate> and </translate> tags at the start and end of the page.
	 * The opening tag is added immediately after the <languages/> tag.
	 *
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
	 *
	 * @param {string} pageContent
	 * @return {string}
	 */
	function addNewLines( pageContent ) {
		pageContent = pageContent.replace( /^(==.*==)\n*/gm, '\n$1\n\n' );
		return pageContent;
	}

	/**
	 * Add an anchor to a section header with the given headerText
	 *
	 * @param {string} headerText
	 * @param {string} pageContent
	 * @return {string}
	 */
	function addAnchor( headerText, pageContent ) {
		var headerSearchRegex, anchorID, replaceAnchorRegex,
			spanSearchRegex;

		anchorID = headerText.replace( ' ', '-' ).toLowerCase();

		headerText = mw.RegExp.escape( headerText );
		// Search for the header having text as headerText
		// Regex: http://regex101.com/r/fD6iL1
		headerSearchRegex = new RegExp( '(==+[ ]*' + headerText + '[ ]*==+)', 'gi' );
		// This is to ensure the tags and the anchor are added only once

		if ( pageContent.indexOf( '<span id="' + mw.html.escape( anchorID ) + '"' ) === -1 ) {
			pageContent = pageContent.replace( headerSearchRegex, '</translate>\n' +
				'<span id="' + mw.html.escape( anchorID ) + '"></span>\n<translate>\n$1' );
		}

		// This is to add back the tags which were removed in cleanupTags()
		if ( pageContent.indexOf( '</translate>\n<span id="' + anchorID + '"' ) === -1 ) {
			spanSearchRegex = new RegExp( '(<span id="' + mw.RegExp.escape( anchorID ) + '"></span>)', 'gi' );
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
	 *
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
		sectionLinksRegex = new RegExp( /\[\[#(.*?)(\|(.*?))?\]\]/gi );
		match = sectionLinksRegex.exec( searchText );
		while ( match !== null ) {
			pageContent = addAnchor( match[ 1 ], pageContent );
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
	 * Fetch all the aliases for a given namespace on the wiki.
	 *
	 * @param {number} namespaceID
	 * @return {jQuery.Promise}
	 * @return {Function} return.done
	 * @return {Array} return.done.data
	 */
	function getNamespaceAliases( namespaceID ) {
		var api = new mw.Api();

		return api.get( {
			action: 'query',
			meta: 'siteinfo',
			siprop: 'namespacealiases'
		} ).then( function ( data ) {
			var alias, aliases = [];

			for ( alias in data.query.namespacealiases ) {
				if ( data.query.namespacealiases[ alias ].id === namespaceID ) {
					aliases.push( data.query.namespacealiases[ alias ][ '*' ] );
				}
			}

			return aliases;
		} );
	}

	/**
	 * Add translate tags around only translatable content for files and keep everything else
	 * as a part of the page template.
	 *
	 * @param {string} pageContent
	 * @return {jQuery.Promise}
	 */
	function doFiles( pageContent ) {
		return getNamespaceAliases( 6 ).then( function ( aliases ) {
			var i, aliasList, captionFilesRegex, fileRegex;

			aliases.push( 'file' );

			for ( i = 0; i < aliases.length; i++ ) {
				aliases[ i ] = mw.RegExp.escape( aliases[ i ] );
			}

			aliasList = aliases.join( '|' );

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
	 * Keep templates outside <translate>....</translate> tags
	 * Does not deal with nested templates, needs manual changes.
	 *
	 * @param {string} pageContent
	 * @return {string} pageContent
	 */
	function doTemplates( pageContent ) {
		var templateRegex;
		// Regex: http://regex101.com/r/wA3iX0
		templateRegex = new RegExp( /^({{[\s\S]*?}})/gm );

		pageContent = pageContent.replace( templateRegex, '</translate>\n$1\n<translate>' );
		return pageContent;
	}

	/**
	 * Cleanup done after the page is prepared for translation by the tool.
	 *
	 * @param {string} pageContent
	 * @return {string}
	 */
	function postPreparationCleanup( pageContent ) {
		// Removes any extra newlines introduced by the tool
		pageContent = pageContent.replace( /\n\n+/gi, '\n\n' );
		// Removes redundant <translate> tags
		pageContent = pageContent.replace( /\n<translate>(\n*?)<\/translate>/gi, '' );
		// Removes the Special:MyLanguage/ prefix for section links
		pageContent = pageContent.replace( /Special:MyLanguage\/#/gi, '#' );
		return pageContent;
	}

	/**
	 * Get the current revision for the given page.
	 *
	 * @param {string} pageName
	 * @return {jQuery.Promise}
	 * @return {Function} return.done
	 * @return {string} return.done.value The current revision
	 */
	function getPageContent( pageName ) {
		var obj,
			api = new mw.Api();

		return api.get( {
			action: 'query',
			prop: 'revisions',
			format: 'json',
			rvprop: 'content',
			rvlimit: '1',
			titles: pageName
		} ).then( function ( data ) {
			var page;

			for ( page in data.query.pages ) {
				obj = data.query.pages[ page ];
			}

			return obj.revisions[ 0 ][ '*' ];
		} );
	}

	/**
	 * Get the list of valid namespaces for the wiki and remove unwanted
	 * ones from the list.
	 *
	 * @return {Array} Array of valid namespaces
	 */
	function getNamespaces() {
		var key, namespacesObject, i,
			namespaces = [];

		namespacesObject = mw.config.get( 'wgNamespaceIds' );
		for ( key in namespacesObject ) {
			namespaces.push( key );
		}

		// Remove all what has been already handled somewhere else
		namespaces.splice( $.inArray( '', namespaces ), 1 );
		namespaces.splice( $.inArray( 'category', namespaces ), 1 );
		namespaces.splice( $.inArray( 'category_talk', namespaces ), 1 );
		namespaces.splice( $.inArray( 'special', namespaces ), 1 );
		namespaces.splice( $.inArray( 'file', namespaces ), 1 );
		namespaces.splice( $.inArray( 'file_talk', namespaces ), 1 );

		for ( i = 0; i < namespaces.length; i++ ) {
			namespaces[ i ] = mw.RegExp.escape( namespaces[ i ] );
		}
		return namespaces;
	}

	$( document ).ready( function () {
		var pageContent,
			$input = $( '#page' );

		$( '#action-cancel' ).click( function () {
			document.location.reload( true );
		} );

		$( '#action-save' ).click( function () {
			var serverName, pageName,
				pageUrl = '';

			pageName = $.trim( $input.val() );
			serverName = mw.config.get( 'wgServerName' );
			savePage( pageName, pageContent ).done( function () {
				pageUrl = mw.Title.newFromText( pageName ).getUrl( { action: 'edit' } );
				$( '.messageDiv' ).html( mw.message( 'pp-save-message', pageUrl ).parse() ).show();
				$( '.divDiff' ).hide( 'fast' );
				$( '#action-prepare' ).show();
				$input.val( '' );
				$( '#action-save' ).hide();
				$( '#action-cancel' ).hide();
			} );
		} );

		$( '#action-prepare' ).click( function () {
			var pageName, messageDiv = $( '.messageDiv' );

			pageName = $.trim( $input.val() );
			messageDiv.hide();
			if ( pageName === '' ) {
				window.alert( mw.msg( 'pp-pagename-missing' ) );
				return;
			}

			$.when( getPageContent( pageName ) ).done( function ( content ) {
				pageContent = content;
				pageContent = $.trim( pageContent );
				pageContent = cleanupTags( pageContent );
				pageContent = addLanguageBar( pageContent );
				pageContent = addTranslateTags( pageContent );
				pageContent = addNewLines( pageContent );
				pageContent = fixInternalLinks( pageContent );
				pageContent = doTemplates( pageContent );
				doFiles( pageContent )
				.then( doCategories )
				.done( function ( pageContent ) {
					pageContent = postPreparationCleanup( pageContent );
					pageContent = $.trim( pageContent );
					getDiff( pageName, pageContent ).done( function ( diff ) {
						$( '.diff tbody' ).append( diff );
						$( '.divDiff' ).show( 'fast' );
						if ( diff !== '' ) {
							messageDiv.html( mw.msg( 'pp-prepare-message' ) ).show();
							$( '#action-prepare' ).hide();
							$( '#action-save' ).show();
							$( '#action-cancel' ).show();
						} else {
							messageDiv.html( mw.msg( 'pp-already-prepared-message' ) ).show();
						}
					} );
				} );
			} );
		} );
	} );

}( jQuery, mediaWiki ) );
