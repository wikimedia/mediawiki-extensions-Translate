( function () {
	'use strict';

	/**
	 * Get a regex snippet matching any aliases (including the canonical
	 * name) of the given namespace, or of “other” namespaces if the
	 * namespace is not given.
	 *
	 * @param {number|null} filter The namespace ID to filter for, or
	 *  `null` to filter for “other” namespaces
	 * @return {string} The aliases of the specified namespace(s),
	 *  regex-escaped and joined with `|`
	 */
	function getNamespaceRegex( filter ) {
		var aliases = [];
		var namespacesObject = mw.config.get( 'wgNamespaceIds' );
		for ( var key in namespacesObject ) {
			if ( filter !== null ) {
				if ( namespacesObject[ key ] === filter ) {
					aliases.push( mw.util.escapeRegExp( key ) );
				}
			} else {
				switch ( namespacesObject[ key ] ) {
					case -1:
					case 0:
					case 6:
					case 7:
					case 14:
					case 15:
						// not needed or handled somewhere else
						break;
					default:
						aliases.push( mw.util.escapeRegExp( key ) );
				}
			}
		}

		return aliases.join( '|' );
	}

	/**
	 * Remove all the <translate> tags and {{translation}} templates before
	 * preparing the page. The tool will add them back wherever needed.
	 *
	 * @param {string} pageContent
	 * @return {string}
	 */
	function cleanupTags( pageContent ) {
		return pageContent.replace( /<\/?translate>\n?/gi, '' );
	}

	/**
	 * Add the <languages/> bar at the top of the page, if not present.
	 * Remove the old {{languages}} template if it is present.
	 *
	 * @param {string} pageContent
	 * @return {string}
	 */
	function addLanguageBar( pageContent ) {
		if ( !/<languages ?\/>/gi.test( pageContent ) ) {
			pageContent = '<languages/>\n' + pageContent;
		}
		return pageContent.replace( /\{\{languages.*?}}/gi, '' );
	}

	/**
	 * Add <translate> tags around Categories to make them a part of the page template
	 * and tag them with the {{translation}} template.
	 *
	 * @param {string} pageContent
	 * @return {string}
	 */
	function doCategories( pageContent ) {
		var aliasList = getNamespaceRegex( 14 );
		// Regex: https://regex101.com/r/sJ3gZ4/2
		var categoryRegex = new RegExp( '\\[\\[((' + aliasList + ')' +
			':[^\\|]+)(\\|[^\\|]*?)?\\]\\]', 'gi' );
		return pageContent.replace( categoryRegex, '\n</translate>\n' +
			'[[$1{{#translation:}}$3]]\n<translate>\n' );
	}

	/**
	 * Add the <translate> and </translate> tags at the start and end of the page.
	 * The opening tag is added immediately after the <languages/> tag.
	 *
	 * @param {string} pageContent
	 * @return {string}
	 */
	function addTranslateTags( pageContent ) {
		// Check for a <languages/> tag with a newline after.
		// If there is no newline, there is some text/syntax after, so we don't want to add any <translate> tags now.
		if ( /<languages ?\/>\n/gi.test( pageContent ) ) {
			pageContent = pageContent.replace( /(<languages ?\/>\n)/gi, '$1<translate>\n' ) +
				'\n</translate>';
		}

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
		return pageContent.replace( /^(==.*==)\n*/gm, '\n$1\n\n' );
	}

	/**
	 * Add an anchor to a section header with the given headerText.
	 *
	 * @param {string} headerText
	 * @param {string} pageContent
	 * @return {string}
	 */
	function addAnchor( headerText, pageContent ) {
		var anchorID = headerText.replace( ' ', '-' ).toLowerCase();

		headerText = mw.util.escapeRegExp( headerText );
		// Search for the header having text as headerText
		// Regex: https://regex101.com/r/fD6iL1
		var headerSearchRegex = new RegExp( '(==+[ ]*' + headerText + '[ ]*==+)', 'gi' );
		// This is to ensure that the tags and the anchor are added only once

		if ( !pageContent.includes( '<span id="' + mw.html.escape( anchorID ) + '"' ) ) {
			pageContent = pageContent.replace( headerSearchRegex, '</translate>\n' +
				'<span id="' + mw.html.escape( anchorID ) + '"></span>\n<translate>\n$1' );
		}

		// This is to add back the tags which were removed in cleanupTags()
		if ( !pageContent.includes( '</translate>\n<span id="' + anchorID + '"' ) ) {
			var spanSearchRegex = new RegExp( '(<span id="' + mw.util.escapeRegExp( anchorID ) + '"></span>)', 'gi' );
			pageContent = pageContent.replace( spanSearchRegex, '\n</translate>\n$1\n</translate>\n' );
		}

		// Replace the link text with the anchorID defined above
		// Regex: https://regex101.com/r/kB5bK3
		var replaceAnchorRegex = new RegExp( '(\\[\\[#)' + headerText + '(.*\\]\\])', 'gi' );
		return pageContent.replace( replaceAnchorRegex, '$1' +
			anchorID.replace( '$', '$$$' ) + '$2' );
	}

	/**
	 * Convert all the links into the two-party form and add the 'Special:MyLanguage/' prefix
	 * to links in valid namespaces for the wiki. For example, [[Example]] would be converted
	 * to [[Special:MyLanguage/Example|Example]].
	 *
	 * @param {string} pageContent
	 * @return {string}
	 */
	function fixInternalLinks( pageContent ) {
		var searchText = pageContent;

		var categoryNsString = getNamespaceRegex( 14 );
		var normalizeRegex = new RegExp( '\\[\\[(?!' + categoryNsString + ')([^|]*?)\\]\\]', 'gi' );
		// First, convert all links into the two-party form.
		// If a link is not having a pipe,
		// add a pipe and duplicate the link text
		// Regex: https://regex101.com/r/pO9nN2
		pageContent = pageContent.replace( normalizeRegex, '[[$1|$1]]' );

		var nsString = getNamespaceRegex( null );
		// Finds all the links to sections on the same page.
		// Regex: https://regex101.com/r/cX6jT3
		var sectionLinksRegex = new RegExp( /\[\[#(.*?)(\|(.*?))?]]/gi );
		var match = sectionLinksRegex.exec( searchText );
		while ( match !== null ) {
			pageContent = addAnchor( match[ 1 ], pageContent );
			match = sectionLinksRegex.exec( searchText );
		}

		var linkPrefixRegex = new RegExp( '\\[\\[((?:(?:special(?!:MyLanguage\\b)|' + nsString +
			'):)?[^:]*?)\\]\\]', 'gi' );
		// Add the 'Special:MyLanguage/' prefix for all internal links of valid namespaces and
		// main namespace.
		// Regex: https://regex101.com/r/zZ9jH9
		return pageContent.replace( linkPrefixRegex, '[[Special:MyLanguage/$1]]' );
	}

	/**
	 * Add translate tags around only translatable content for files and keep everything else
	 * as a part of the page template.
	 *
	 * @param {string} pageContent
	 * @return {string}
	 */
	function doFiles( pageContent ) {
		var aliasList = getNamespaceRegex( 6 );

		// Add translate tags for files with captions
		var captionFilesRegex = new RegExp( '\\[\\[(' + aliasList + ')(.*\\|)(.*?)\\]\\]', 'gi' );
		pageContent = pageContent.replace( captionFilesRegex,
			'</translate>\n[[$1$2<translate>$3</translate>]]\n<translate>' );

		// Add translate tags for files without captions
		var fileRegex = new RegExp( '/\\[\\[((' + aliasList + ')[^\\|]*?)\\]\\]', 'gi' );
		return pageContent.replace( fileRegex, '\n</translate>[[$1]]\n<translate>' );
	}

	/**
	 * Keep templates outside <translate>....</translate> tags.
	 * Does not deal with nested templates, needs manual changes.
	 *
	 * @param {string} pageContent
	 * @return {string} pageContent
	 */
	function doTemplates( pageContent ) {
		// Regex: https://regex101.com/r/wA3iX0
		var templateRegex = new RegExp( /^({{[\s\S]*?}})/gm );

		return pageContent.replace( templateRegex, '</translate>\n$1\n<translate>' );
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
		return pageContent.replace( /Special:MyLanguage\/#/gi, '#' );
	}

	/**
	 * Get the current revision for the given page.
	 *
	 * @param {string} pageName
	 * @return {jQuery.Promise}
	 */
	function getPageContent( pageName ) {
		var api = new mw.Api();

		return api.get( {
			action: 'query',
			prop: 'revisions',
			rvprop: 'content',
			rvlimit: '1',
			formatversion: '2',
			titles: pageName
		} ).promise();
	}

	/**
	 * Get the diff between the current revision and the prepared page content.
	 *
	 * @param {string} pageName Page title
	 * @param {string} pageContent New content of the page
	 * @return {jQuery.Promise}
	 */
	function getDiff( pageName, pageContent ) {
		var api = new mw.Api();

		return api.post( {
			action: 'compare',
			prop: 'diff',
			formatversion: '2',
			fromtitle: pageName,
			toslots: 'main',
			'totext-main': pageContent
		} ).promise();
	}

	/**
	 * Save the page with a given page name and given content to the wiki.
	 *
	 * @param {string} pageName Page title
	 * @param {string} pageContent Content of the page to be saved
	 * @param {string} summary Edit summary for the change
	 * @return {jQuery.Promise}
	 */
	function savePage( pageName, pageContent, summary ) {
		var api = new mw.Api();

		return api.postWithToken( 'csrf', {
			action: 'edit',
			title: pageName,
			text: pageContent,
			summary: summary
		} ).promise();
	}

	/**
	 * Display error message to the user
	 *
	 * @param {string} errorMessage Error message to display to the user
	 */
	function displayError( errorMessage ) {
		if ( errorMessage === undefined ) {
			errorMessage = mw.msg( 'pp-unexpected-error' );
		}

		$( '.messageDiv' )
			.text( errorMessage )
			.removeClass( 'hide' )
			.addClass( 'mw-message-box-error' );
	}

	/**
	 * Failure callback method for the prepare step
	 *
	 * @param {string} errorMessage Error message to display to the user
	 */
	function onPrepareFailure( errorMessage ) {
		displayError( errorMessage );
		$( '#action-prepare' ).prop( 'disabled', false );
	}

	$( function () {
		var $input = $( '#page' );
		var $messageDiv = $( '.messageDiv' );

		$( '#action-cancel' ).on( 'click', function () {
			document.location.reload( true );
		} );

		var pageContent;
		$( '#action-save' ).on( 'click', function () {
			var pageName = $input.val().trim();
			$messageDiv.removeClass( 'mw-message-box-error mw-message-box-success' );
			savePage( pageName, pageContent, $( '#pp-summary' ).val() ).done( function () {
				var pageUrl = mw.Title.newFromText( pageName ).getUrl( { action: 'edit' } );
				$messageDiv
					.empty()
					.append( mw.message( 'pp-save-message', pageUrl ).parseDom() )
					.addClass( 'mw-message-box-success' )
					.removeClass( 'hide' );
				$( '.divDiff' ).addClass( 'hide' );
				$( '#action-prepare' ).removeClass( 'hide' );
				$input.val( '' );
				$( '#action-save' ).addClass( 'hide' );
				$( '#action-cancel' ).addClass( 'hide' );
			} ).fail( function ( _code, data ) {
				displayError( data.error.info );
			} );
		} );

		$( '#action-prepare' ).on( 'click', function () {
			var pageName = $input.val().trim();
			$messageDiv.addClass( 'hide' ).removeClass( 'mw-message-box-error mw-message-box-success' );
			if ( pageName === '' ) {
				// eslint-disable-next-line no-alert
				alert( mw.msg( 'pp-pagename-missing' ) );
				return;
			}
			$( this ).prop( 'disabled', true );

			getPageContent( pageName ).done( function ( contentData ) {
				// Check if the page actually exists
				if ( contentData.query.pages[ 0 ].revisions === undefined ) {
					onPrepareFailure( mw.msg( 'pp-page-does-not-exist', pageName ) );
					return $.Deferred().reject();
				}

				pageContent = contentData.query.pages[ 0 ].revisions[ 0 ].content.trim();
				pageContent = cleanupTags( pageContent );
				pageContent = addLanguageBar( pageContent );
				pageContent = addTranslateTags( pageContent );
				pageContent = addNewLines( pageContent );
				pageContent = fixInternalLinks( pageContent );
				pageContent = doTemplates( pageContent );
				pageContent = doFiles( pageContent );
				pageContent = doCategories( pageContent );
				pageContent = postPreparationCleanup( pageContent );
				pageContent = pageContent.trim();
				getDiff( pageName, pageContent ).done( function ( diffData ) {
					var diff = diffData.compare.body;
					var $prepare = $( '#action-prepare' );
					// Enable prepare button whether the diff failed or not, so it can be clicked again...
					$prepare.prop( 'disabled', false );
					$messageDiv.removeClass( 'hide' );
					if ( diff === undefined ) {
						onPrepareFailure( mw.msg( 'pp-diff-error' ) );
						return $.Deferred().reject();
					}

					if ( diff !== '' ) {
						$( '.diff tbody' ).html( diff );
						$( '.divDiff' ).removeClass( 'hide' );
						$messageDiv.text( mw.msg( 'pp-prepare-message' ) );
						$prepare.addClass( 'hide' );
						$( '#action-save' ).removeClass( 'hide' );
						$( '#action-cancel' ).removeClass( 'hide' );
					} else {
						displayError( mw.msg( 'pp-already-prepared-message' ) );
					}
				} ).fail( function ( _code, errorData ) {
					onPrepareFailure( errorData.error && errorData.error.info );
				} );
			} ).fail( function ( _code, errorData ) {
				onPrepareFailure( errorData.error && errorData.error.info );
			} );
		} );
	} );

}() );
