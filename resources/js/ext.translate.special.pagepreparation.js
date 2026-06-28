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
	 * Remove all the <translate> tags and {{#translation:}} templates before
	 * preparing the page. The tool will add them back wherever needed.
	 *
	 * @param {string} pageContent
	 * @return {string}
	 */
	function cleanupTags( pageContent ) {
		return pageContent.replace( /<\/?translate>\n?/gi, '' ).replace( /\{\{#translation:\}\}/, '' );
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
	 * and tag them with the {{#translation:}} parser function.
	 *
	 * @param {string} pageContent
	 * @return {string}
	 */
	function doCategories( pageContent ) {
		var aliasList = getNamespaceRegex( 14 );
		// Regex: https://regex101.com/r/sJ3gZ4/2
		var categoryRegex = new RegExp( '\\[\\[((' + aliasList + ')' +
			':[^\\|\\]]+)(\\|[^\\|]*?)?\\]\\]', 'gi' );
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
	 * Convert all the links into the two-party form and add the 'Special:MyLanguage/' prefix
	 * to links in valid namespaces for the wiki. For example, [[Example]] would be converted
	 * to [[Special:MyLanguage/Example|Example]].
	 *
	 * @param {string} pageContent
	 * @return {string}
	 */
	function fixInternalLinks( pageContent ) {

		var categoryNsString = getNamespaceRegex( 14 );
		var normalizeRegex = new RegExp( '\\[\\[(?!' + categoryNsString + ')([^|]*?)\\]\\]', 'gi' );
		// First, convert all links into the two-party form.
		// If a link is not having a pipe,
		// add a pipe and duplicate the link text
		// Regex: https://regex101.com/r/pO9nN2
		pageContent = pageContent.replace( normalizeRegex, '[[$1|$1]]' );

		var nsString = getNamespaceRegex( null );

		var linkPrefixRegex = new RegExp( '\\[\\[((?:(?:' + nsString +
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
			summary: summary,
			errorformat: 'html'
		} ).promise();
	}

	/**
	 * Display error message to the user
	 *
	 * @param {jQuery} $errorMessage Error message to display to the user
	 */
	function displayError( $errorMessage ) {
		if ( $errorMessage === undefined ) {
			$errorMessage = mw.message( 'pp-unexpected-error' ).parseDom();
		}
		$( '.messageDiv' )
			.empty()
			.removeClass( 'hide' )
			.append( $errorMessage )
			.addClass( 'mw-message-box-error' );
	}
	function displayErrorsFromData( data ) {
		var errors = data.errors;
		if ( errors.length === 1 ) {
			displayError( $.parseHTML( errors[ 0 ][ '*' ] ) );
		} else {
			var $errorList = $( '<ul>' );
			for ( var i = 0; i < errors.length; i++ ) {
				$errorList.append( $( '<li>' ).html( errors[ i ][ '*' ] ) );
			}
			displayError( $errorList );
		}
	}

	/**
	 * Failure callback method for the prepare step
	 *
	 * @param {jQuery} $errorMessage Error message to display to the user
	 */
	function onPrepareFailure( $errorMessage ) {
		displayError( $errorMessage );
	}

	$( function () {
		var $input = $( '#page' );
		var $messageDiv = $( '.messageDiv' );

		$( '#action-cancel' ).on( 'click', function () {
			document.location.reload( true );
		} );

		var pageContent;
		function handlePublish() {
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
				$( '#action-save, #action-cancel' ).addClass( 'hide' );
			} ).fail( function ( _code, data ) {
				displayErrorsFromData( data );
			} );
		}
		$( '#action-save' ).on( 'click', handlePublish );

		var isReadyToSave = false;

		const $prepareTranslateTaggerBtn = $( '#action-prepare-translate-tagger-api' );
		const $prepareBtn = $( '#action-prepare' );
		const enableButton = false;

		function togglePrepareButtons( status ) {
			$prepareTranslateTaggerBtn.prop( 'disabled', status );
			$prepareBtn.prop( 'disabled', status );
		}

		function doPrepare() {
			var pageName = $input.val().trim();
			$messageDiv.addClass( 'hide' ).removeClass( 'mw-message-box-error mw-message-box-success' );
			if ( pageName === '' ) {
				displayError( mw.message( 'pp-pagename-missing' ).parseDom() );
				return;
			}
			togglePrepareButtons( !enableButton );

			getPageContent( pageName ).done( function ( contentData ) {
				// Check if the page actually exists
				if ( contentData.query.pages[ 0 ].revisions === undefined ) {
					onPrepareFailure( mw.message( 'pp-page-does-not-exist', pageName ).parseDom() );
					togglePrepareButtons( enableButton );
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
					togglePrepareButtons( enableButton );
					$messageDiv.removeClass( 'hide' );
					if ( diff === undefined ) {
						onPrepareFailure( mw.message( 'pp-diff-error' ).parseDom() );
						return $.Deferred().reject();
					}

					if ( diff !== '' ) {
						isReadyToSave = true;
						$( '.diff tbody' ).html( diff );
						$( '.divDiff' ).removeClass( 'hide' );
						$messageDiv.text( mw.msg( 'pp-prepare-message' ) );
						$prepareBtn.addClass( 'hide' );
						$( '#action-save, #action-cancel' ).removeClass( 'hide' );
					} else {
						displayError( mw.message( 'pp-already-prepared-message' ).parseDom() );
					}
				} ).fail( function ( _code, errorData ) {
					displayErrorsFromData( errorData );
					togglePrepareButtons( enableButton );
				} );
			} ).fail( function ( _code, errorData ) {
				displayErrorsFromData( errorData );
				togglePrepareButtons( enableButton );
			} );
		}

		/**
		 * Call the Translate Tagger API with the raw page content and handle the
		 * response: validate it, fetch the diff, and update the UI accordingly.
		 *
		 * @param {string} translateTaggerUrl URL of the Translate Tagger API endpoint
		 * @param {string} pageName Page title being prepared
		 * @param {string} rawContent Raw wikitext of the page
		 */
		function callTranslateTaggerAPI( translateTaggerUrl, pageName, rawContent ) {
			$.ajax( {
				url: translateTaggerUrl,
				type: 'POST',
				dataType: 'json',
				contentType: 'application/json; charset=UTF-8',
				data: JSON.stringify( {
					wikitext: rawContent
				} )
			} ).done( function ( apiResponse ) {
				if ( !apiResponse || !apiResponse.converted ) {
					onPrepareFailure( mw.message( 'pp-translate-tagger-api-error', 'Invalid response from API' ).parseDom() );
					togglePrepareButtons( enableButton );
					return;
				}

				pageContent = apiResponse.converted.trim();

				getDiff( pageName, pageContent ).done( function ( diffData ) {
					const diff = diffData.compare.body;
					togglePrepareButtons( enableButton );
					$messageDiv.removeClass( 'hide' );

					if ( diff === undefined ) {
						onPrepareFailure( mw.message( 'pp-diff-error' ).parseDom() );
						return;
					}

					if ( diff !== '' ) {
						isReadyToSave = true;
						$( '.diff tbody' ).html( diff );
						$( '.divDiff' ).removeClass( 'hide' );
						$messageDiv.text( mw.msg( 'pp-prepare-message' ) );
						$prepareBtn.add( $prepareTranslateTaggerBtn ).addClass( 'hide' );
						$( '#action-save, #action-cancel' ).removeClass( 'hide' );
					} else {
						displayError( mw.message( 'pp-already-prepared-message' ).parseDom() );
					}
				} ).fail( function ( _code, errorData ) {
					displayErrorsFromData( errorData );
					togglePrepareButtons( enableButton );
				} );

			} ).fail( function ( _xhr, status, error ) {
				onPrepareFailure( mw.message( 'pp-translate-tagger-api-error', ( error || status ) ).parseDom() );
				togglePrepareButtons( enableButton );
			} );
		}

		function doPrepareTranslateTaggerAPI( event ) {
			const translateTaggerUrl = event.target.dataset.apiUrl;
			if ( !translateTaggerUrl ) {
				throw new Error( 'Unexpected condition: Translate tagger API URL not found' );
			}
			const pageName = $input.val().trim();
			$messageDiv.addClass( 'hide' ).removeClass( 'mw-message-box-error mw-message-box-success' );
			if ( pageName === '' ) {
				displayError( mw.message( 'pp-pagename-missing' ).parseDom() );
				return;
			}

			togglePrepareButtons( !enableButton );

			getPageContent( pageName ).done( function ( contentData ) {
				if ( contentData.query.pages[ 0 ].revisions === undefined ) {
					onPrepareFailure( mw.message( 'pp-page-does-not-exist', pageName ).parseDom() );
					togglePrepareButtons( enableButton );
					return $.Deferred().reject();
				}

				const rawContent = contentData.query.pages[ 0 ].revisions[ 0 ].content.trim();
				callTranslateTaggerAPI( translateTaggerUrl, pageName, rawContent );
			} ).fail( function ( _code, errorData ) {
				displayErrorsFromData( errorData );
				togglePrepareButtons( enableButton );
			} );
		}

		$prepareBtn.on( 'click', doPrepare );
		$prepareTranslateTaggerBtn.on( 'click', doPrepareTranslateTaggerAPI );

		$( '.mw-tpp-sp-form' ).on( 'submit', function () {
			if ( isReadyToSave ) {
				handlePublish();
			} else {
				doPrepare();
			}
			return false;
		} );
	} );

}() );
