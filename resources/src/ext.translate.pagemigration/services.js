const api = new mw.Api();

/**
 * Do a typeahead search for page titles.
 *
 * @internal
 * @param {string} search The user input
 * @return {jQuery.Promise<string[]>} Suggested titles for the given user input
 */
function typeaheadSearch( search ) {
	return api.get( {
		action: 'opensearch',
		formatversion: 2,
		limit: 10,
		search
	} ).then( ( data ) => data[ 1 ] );
}

/**
 * Get the old translations of a given page at given time.
 *
 * @param {string} fuzzyTimestamp Timestamp in MediaWiki format
 * @param {string} pageTitle
 * @return {jQuery.Promise<string[]>} Old translations
 */
function splitTranslationPage( fuzzyTimestamp, pageTitle ) {
	return api.get( {
		action: 'query',
		prop: 'revisions',
		rvprop: 'content',
		rvstart: fuzzyTimestamp,
		rvlimit: 1,
		formatversion: '2',
		titles: pageTitle
	} ).then( function ( data ) {
		var obj = data.query.pages[ 0 ];
		// TODO: Handle other cases such as invalid page titles ie: obj.invalid
		if ( obj === undefined || obj.missing ) {
			return $.Deferred().reject( mw.msg( 'pm-page-does-not-exist', pageTitle ) );
		}
		if ( obj.revisions === undefined ) {
			// the case of /en subpage where first edit is by FuzzyBot
			return $.Deferred().reject( mw.msg( 'pm-old-translations-missing', pageTitle ) );
		}
		return obj.revisions[ 0 ].content.split( '\n\n' );
	} );
}

/**
 * Get the timestamp before FuzzyBot's first edit on page.
 *
 * @param {string} pageTitle
 * @return {jQuery.Promise<string>} Timestamp
 */
function getFuzzyTimestamp( pageTitle ) {
	// This api call returns the timestamp of FuzzyBot's edit
	return api.get( {
		action: 'query',
		prop: 'revisions',
		rvprop: 'timestamp',
		rvuser: 'FuzzyBot',
		rvdir: 'newer',
		rvlimit: 1,
		formatversion: '2',
		titles: pageTitle
	} ).then( function ( data ) {
		var obj = data.query.pages[ 0 ];

		// Page does not exist if missing field is present
		if ( obj === undefined || obj.missing === '' ) {
			return $.Deferred().reject( mw.msg( 'pm-page-does-not-exist', pageTitle ) );
		}

		// Page exists, but no edit by FuzzyBot
		if ( obj.revisions === undefined ) {
			return $.Deferred().reject( mw.msg( 'pm-old-translations-missing', pageTitle ) );
		} else {
			// FB over here refers to FuzzyBot
			var timestampFB = obj.revisions[ 0 ].timestamp;
			var dateFB = new Date( timestampFB );
			dateFB.setSeconds( dateFB.getSeconds() - 1 );
			var timestampOld = dateFB.toISOString();
			mw.log( 'New Timestamp: ' + timestampOld );
			return timestampOld;
		}
	} );
}

/**
 * @typedef {Object} SourceUnit
 * @property {string} identifier
 * @property {string} definition
 */

/**
 * Get the translation units created by Translate extension.
 *
 * @param {string} page Page name
 * @return {jQuery.Promise<SourceUnit[]>}
 */
function getSourceUnits( page ) {
	return api.get( {
		action: 'query',
		list: 'messagecollection',
		mcgroup: 'page-' + page,
		mclanguage: 'en',
		mcprop: 'definition'
	} ).then(
		( data ) => data.query.messagecollection.map( ( { key, definition } ) => ( {
			identifier: key.slice( key.lastIndexOf( '/' ) + 1 ),
			definition
		} ) ),
		( code, result ) => {
			let errorMessage = mw.msg( 'pm-translation-unit-fetch-failed' );
			if (
				code === 'badparameter' &&
				result.error && result.error.info.includes( 'mcgroup' )
			) {
				errorMessage = mw.msg( 'pm-pagetitle-not-translatable', page );
			}

			return $.Deferred().reject( errorMessage );
		}
	);
}

/**
 * Split headers from remaining text in each translation unit if present.
 *
 * @internal
 * @param {string[]} translations Array of initial units obtained on splitting
 * @return {string[]} Array having the headers split into new unit
 */
function splitHeaders( translations ) {
	return translations.map( function ( elem ) {
		// Check https://regex101.com/r/oT7fZ2 for details
		return elem.match( /(^==.+$|(?:(?!^==).+\n?)+)/gm );
	} ).reduce( function ( acc, val ) {
		// This should be an Array.prototype.flatMap when ES2019 is supported
		return acc.concat( val );
	}, [] );
}

/**
 * Get the index of next translation unit containing h2 header.
 *
 * @param {number} startIndex Index to start the scan from
 * @param {string[]} translationUnits Segmented units.
 * @return {number} Index of the next unit found, -1 if not.
 */
function getHeaderUnit( startIndex, translationUnits ) {
	var regex = new RegExp( /^==[^=]+==$/m );
	for ( var i = startIndex; i < translationUnits.length; i++ ) {
		if ( regex.test( translationUnits[ i ] ) ) {
			return i;
		}
	}
	return -1;
}

/**
 * Align h2 headers in the order they appear.
 * Assumption: The source headers and translation headers appear in
 * the same order.
 *
 * @internal
 * @param {Object[]} units
 * @param {string[]} translationUnits Modified in-place
 */
function alignHeaders( units, translationUnits ) {
	// The content does not have information about the page title. Add an empty string
	// at the beginning of the translationUnits array to match the length of units and
	// translationUnits.
	if ( units.length && units[ 0 ].identifier === 'Page_display_title' ) {
		translationUnits.unshift( '' );
	}
	let tIndex = 0;
	const regex = new RegExp( /^==[^=]+==$/m );
	for ( let i = 0; i < units.length; i++ ) {
		if ( regex.test( units[ i ].definition ) ) {
			tIndex = getHeaderUnit( tIndex, translationUnits );
			let mergeText = '';
			// search is over
			if ( tIndex === -1 ) {
				break;
			}
			// remove the unit
			let matchText = translationUnits.splice( tIndex, 1 ).toString();
			let emptyCount = i - tIndex;
			if ( emptyCount > 0 ) {
				// add empty units
				while ( emptyCount !== 0 ) {
					translationUnits.splice( tIndex, 0, '' );
					emptyCount -= 1;
				}
			} else if ( emptyCount < 0 ) {
				// merge units until there is room for tIndex translation unit to
				// align with ith source unit
				while ( emptyCount !== 0 ) {
					mergeText += translationUnits.splice( i, 1 ).toString() + '\n';
					emptyCount += 1;
				}
				if ( i !== 0 ) {
					translationUnits[ i - 1 ] += '\n' + mergeText;
				} else {
					matchText = mergeText + matchText;
				}
			}
			// add the unit back
			translationUnits.splice( i, 0, matchText );
			tIndex = i + 1;
		}
	}
}

/**
 * @typedef {Object} LoadedData
 * @property {SourceUnit[]} sourceUnits
 * @property {string[]} translationUnits
 * @property {string} translationLang
 * @property {'ltr'|'rtl'} translationDir
 * @property {Function} save
 */

/**
 * @internal
 * @param {string} translationPageName The string the user has input as the translation page name
 * @return {jQuery.Promise<LoadedData>} A `Promise` resolved with the translation units,
 *  or rejected with a translated error message
 */
function loadData( translationPageName ) {
	if ( translationPageName === '' ) {
		return $.Deferred().reject( mw.msg( 'pm-pagetitle-missing' ) );
	}

	const titleObj = mw.Title.newFromText( translationPageName );
	if ( titleObj === null ) {
		return $.Deferred().reject( mw.msg( 'pm-pagetitle-invalid' ) );
	}

	const pageTitle = titleObj.getPrefixedText();
	const slashPos = pageTitle.lastIndexOf( '/' );
	if ( slashPos === -1 ) {
		return $.Deferred().reject( mw.msg( 'pm-langcode-missing' ) );
	}

	const pageName = pageTitle.slice( 0, slashPos );
	const langCode = pageTitle.slice( slashPos + 1 );
	if ( !pageName || !langCode ) {
		return $.Deferred().reject( mw.msg( 'pm-pagetitle-invalid' ) );
	}

	return $.when(
		getFuzzyTimestamp( pageTitle ).then( ( fuzzyTimestamp ) => splitTranslationPage( fuzzyTimestamp, pageTitle ) ),
		getSourceUnits( pageName )
	).then( ( translations, sourceUnits ) => {
		const translationUnits = splitHeaders( translations );
		alignHeaders( sourceUnits, translationUnits );
		return {
			sourceUnits,
			translationUnits,
			translationLang: langCode,
			translationDir: $.uls.data.getDir( langCode ),
			save: ( summary ) => createUnits( { pageName, langCode, summary, sourceUnits, translationUnits } )
		};
	} );
}

/**
 * @param {Function[]} list List of callbacks returning promises.
 * @param {number} maxRetries Maximum number of times a failed promise is retried.
 * @return {jQuery.Promise}
 */
function ajaxDispatcherHelper( list, maxRetries ) {
	const deferred = $.Deferred();

	if ( list.length === 0 ) {
		deferred.resolve( [] );
		return deferred;
	}

	const first = list[ 0 ];
	const rest = list.slice( 1 );

	let retries = 0;
	const retrier = function ( result, promise ) {
		if ( !promise.state ) {
			return;
		}

		if ( promise.state() === 'rejected' ) {
			if ( retries < maxRetries ) {
				retries += 1;
				return first.call().always( retrier );
			}
		}

		if ( promise.state() !== 'pending' ) {
			ajaxDispatcherHelper( rest, maxRetries ).always( function ( promises ) {
				deferred.resolve( [].concat( promise, promises ) );
			} );
		}
	};

	first.call().always( retrier ).catch( function ( errmsg ) {
		return deferred.reject( errmsg );
	} );

	return deferred;
}

/**
 * @param {Object} params
 * @param {string} params.pageName
 * @param {string} params.langCode
 * @param {string} params.summary
 * @param {SourceUnit[]} params.sourceUnits
 * @param {string[]} params.translationUnits
 * @return {jQuery.Promise}
 */
function createUnits( { pageName, langCode, summary, sourceUnits, translationUnits } ) {
	if ( translationUnits.length > sourceUnits.length ) {
		return $.Deferred().reject( mw.msg( 'pm-extra-units-warning' ) );
	} else {
		const saveData = [];
		for ( let i = 0; i < translationUnits.length; ++i ) {
			if ( translationUnits[ i ] !== '' ) {
				const title = 'Translations:' + pageName + '/' + sourceUnits[ i ].identifier + '/' + langCode;
				const text = translationUnits[ i ];
				saveData.push( () => api.postWithToken( 'csrf', {
					action: 'edit',
					watchlist: 'nochange',
					title,
					text,
					summary
				} ) );
			}
		}
		return ajaxDispatcherHelper( saveData, 1 )
			.catch( ( errmsg ) => $.Deferred().reject( mw.msg( errmsg ) ) );
	}
}

module.exports = {
	typeaheadSearch,
	loadData,

	// For testing
	splitHeaders,
	alignHeaders
};
