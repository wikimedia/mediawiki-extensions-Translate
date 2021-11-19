( function () {
	'use strict';
	var noOfSourceUnits, noOfTranslationUnits,
		pageName = '',
		langCode = '',
		sourceUnits = [];

	/**
	 * Create translation pages using content of right hand side blocks
	 * and identifiers from left hand side blocks. Create pages only if
	 * content is not empty.
	 *
	 * @param {number} i Array index to sourceUnits.
	 * @param {string} content
	 * @return {Function} Returns a function which returns a jQuery.Promise
	 */
	function createTranslationPage( i, content ) {

		return function () {
			var api = new mw.Api();

			var identifier = sourceUnits[ i ].identifier;
			var title = 'Translations:' + pageName + '/' + identifier + '/' + langCode;
			var summary = $( '#pm-summary' ).val();

			return api.postWithToken( 'csrf', {
				action: 'edit',
				watchlist: 'nochange',
				title: title,
				text: content,
				summary: summary
			} );
		};
	}

	/**
	 * Get the old translations of a given page at given time.
	 *
	 * @param {string} fuzzyTimestamp Timestamp in MediaWiki format
	 * @param {string} pageTitle
	 * @return {jQuery.Promise}
	 * @return {Function} return.done
	 * @return {Array} return.done.data Array of old translations
	 */
	function splitTranslationPage( fuzzyTimestamp, pageTitle ) {
		var api = new mw.Api();

		return api.get( {
			action: 'query',
			prop: 'revisions',
			rvprop: 'content',
			rvstart: fuzzyTimestamp,
			titles: pageTitle
		} ).then( function ( data ) {
			var $errorBox = $( '.mw-tpm-sp-error__message' );
			var obj;
			for ( var page in data.query.pages ) {
				obj = data.query.pages[ page ];
			}
			if ( obj === undefined ) {
				// obj was not initialized
				$errorBox.text( mw.msg( 'pm-page-does-not-exist', pageTitle ) ).removeClass( 'hide' );
				return $.Deferred().reject();
			}
			if ( obj.revisions === undefined ) {
				// the case of /en subpage where first edit is by FuzzyBot
				$errorBox.text( mw.msg( 'pm-old-translations-missing', pageTitle ) ).removeClass( 'hide' );
				return $.Deferred().reject();
			}
			var pageContent = obj.revisions[ 0 ][ '*' ];
			return pageContent.split( '\n\n' );
		} );
	}

	/**
	 * Get the timestamp before FuzzyBot's first edit on page.
	 *
	 * @param {string} pageTitle
	 * @return {jQuery.Promise}
	 * @return {Function} return.done
	 * @return {string} return.done.data
	 */
	function getFuzzyTimestamp( pageTitle ) {
		var api = new mw.Api();

		// This api call returns the timestamp of FuzzyBot's edit
		return api.get( {
			action: 'query',
			prop: 'revisions',
			rvprop: 'timestamp',
			rvuser: 'FuzzyBot',
			rvdir: 'newer',
			titles: pageTitle
		} ).then( function ( data ) {
			var $errorBox = $( '.mw-tpm-sp-error__message' );
			var obj;
			for ( var page in data.query.pages ) {
				obj = data.query.pages[ page ];
			}
			// Page does not exist if missing field is present
			if ( obj === undefined || obj.missing === '' ) {
				$errorBox.text( mw.msg( 'pm-page-does-not-exist', pageTitle ) ).removeClass( 'hide' );
				return $.Deferred().reject();
			}

			// Page exists, but no edit by FuzzyBot
			if ( obj.revisions === undefined ) {
				$errorBox.text( mw.msg( 'pm-old-translations-missing', pageTitle ) ).removeClass( 'hide' );
				return $.Deferred().reject();
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
	 * Get the translation units created by Translate extension.
	 *
	 * @param {string} page Page name
	 * @return {jQuery.Promise}
	 * @return {Function} return.done
	 * @return {Object[]} return.done.data Array of sUnit Objects
	 */
	function getSourceUnits( page ) {
		var api = new mw.Api();

		return api.get( {
			action: 'query',
			list: 'messagecollection',
			mcgroup: 'page-' + page,
			mclanguage: 'en',
			mcprop: 'definition'
		} ).then( function ( data ) {
			sourceUnits = [];
			var result = data.query.messagecollection;
			for ( var i = 0; i < result.length; i++ ) {
				var sUnit = {};
				var key = result[ i ].key;
				sUnit.identifier = key.slice( key.lastIndexOf( '/' ) + 1 );
				sUnit.definition = result[ i ].definition;
				sourceUnits.push( sUnit );
			}
			return sourceUnits;
		} ).fail( function ( code, result ) {
			// Incase the group does not exist, just return an empty array.
			var $errorContainer = $( '.mw-tpm-sp-error__message' );
			var errorMessage = mw.msg( 'pm-translation-unit-fetch-failed' );
			if (
				code === 'badparameter' &&
				result.error && result.error.info.indexOf( 'mcgroup' ) !== -1
			) {
				errorMessage = mw.msg( 'pm-pagetitle-not-translatable', page );
			}

			$errorContainer
				.text( errorMessage )
				.removeClass( 'hide' );
			$.Deferred().reject();
		} );
	}

	/**
	 * Shift rows up by one unit. This is called after a unit is deleted.
	 *
	 * @param {jQuery} $start The starting node
	 */
	function shiftRowsUp( $start ) {
		var $current = $start,
			$next = $start.next();

		while ( $next.length ) {
			var nextVal = $next.find( '.mw-tpm-sp-unit__target' ).val();
			$current.find( '.mw-tpm-sp-unit__target' ).val( nextVal );
			$current = $next;
			$next = $current.next();
		}
		if ( $current.find( '.mw-tpm-sp-unit__source' ).val() ) {
			$current.find( '.mw-tpm-sp-unit__target' ).val( '' );
		} else {
			$current.remove();
		}
	}

	/**
	 * Shift rows down by one unit. This is called after a new empty unit is
	 * added.
	 *
	 * @param {jQuery} $nextRow The next row to start with
	 * @param {string} text The text of the next row
	 * @return {string} text The text of the last row
	 */
	function shiftRowsDown( $nextRow, text ) {
		while ( $nextRow.length ) {
			var oldText = $nextRow.find( '.mw-tpm-sp-unit__target' ).val();
			$nextRow.find( '.mw-tpm-sp-unit__target' ).val( text );
			$nextRow = $nextRow.next();
			text = oldText;
		}
		return text;
	}

	/**
	 * Create a new row of source text and target text with action icons.
	 *
	 * @param {string} sourceText
	 * @param {string} targetText
	 * @return {jQuery} newUnit The new row unit object
	 */

	function createNewUnit( sourceText, targetText ) {
		var $newUnit = $( '<div>' ).addClass( 'mw-tpm-sp-unit row' );
		var $sourceUnit = $( '<textarea>' ).addClass( 'mw-tpm-sp-unit__source five columns' )
			.prop( 'readonly', true ).attr( 'tabindex', '-1' ).val( sourceText );
		var $targetUnit = $( '<textarea>' ).addClass( 'mw-tpm-sp-unit__target five columns' )
			.val( targetText ).prop( 'dir', $.uls.data.getDir( langCode ) );
		var $actionUnit = $( '<div>' ).addClass( 'mw-tpm-sp-unit__actions two columns' );
		$actionUnit.append(
			$( '<span>' ).addClass( 'mw-tpm-sp-action mw-tpm-sp-action--add' )
				.attr( 'title', mw.msg( 'pm-add-icon-hover-text' ) ),
			$( '<span>' ).addClass( 'mw-tpm-sp-action mw-tpm-sp-action--swap' )
				.attr( 'title', mw.msg( 'pm-swap-icon-hover-text' ) ),
			$( '<span>' ).addClass( 'mw-tpm-sp-action mw-tpm-sp-action--delete' )
				.attr( 'title', mw.msg( 'pm-delete-icon-hover-text' ) )
		);
		$newUnit.append( $sourceUnit, $targetUnit, $actionUnit );
		return $newUnit;
	}

	/**
	 * Display the source and target units alongwith the action icons.
	 *
	 * @param {Array} units
	 * @param {Array} translations
	 */
	function displayUnits( units, translations ) {
		noOfSourceUnits = units.length;
		noOfTranslationUnits = translations.length;
		var totalUnits = noOfSourceUnits > noOfTranslationUnits ? noOfSourceUnits : noOfTranslationUnits;
		var $unitListing = $( '.mw-tpm-sp-unit-listing' );
		$unitListing.html( '' );
		for ( var i = 0; i < totalUnits; i++ ) {
			var sourceText = '', targetText = '';
			if ( units[ i ] !== undefined ) {
				sourceText = units[ i ].definition;
			}
			if ( translations[ i ] !== undefined ) {
				targetText = translations[ i ];
			}
			var $newUnit = createNewUnit( sourceText, targetText );
			$unitListing.append( $newUnit );
		}
	}

	/**
	 * Split headers from remaining text in each translation unit if present.
	 *
	 * @param {Array} translations Array of initial units obtained on splitting
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
	 * @param {Object[]} units
	 * @param {string[]} translationUnits
	 * @return {string[]}
	 */
	function alignHeaders( units, translationUnits ) {
		var tIndex = 0;
		var regex = new RegExp( /^==[^=]+==$/m );
		for ( var i = 0; i < units.length; i++ ) {
			if ( regex.test( units[ i ].definition ) ) {
				tIndex = getHeaderUnit( tIndex, translationUnits );
				var mergeText = '';
				// search is over
				if ( tIndex === -1 ) {
					break;
				}
				// remove the unit
				var matchText = translationUnits.splice( tIndex, 1 ).toString();
				var emptyCount = i - tIndex;
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
		return translationUnits;
	}

	/**
	 * Handler for 'Save' button click event.
	 */
	function saveHandler() {
		var list = [];

		$( '.mw-tpm-sp-error__message' ).addClass( 'hide' );
		if ( noOfSourceUnits < noOfTranslationUnits ) {
			$( '.mw-tpm-sp-error__message' ).text( mw.msg( 'pm-extra-units-warning' ) )
				.removeClass( 'hide' );
			return;
		} else {
			$( 'input' ).prop( 'disabled', true );
			$( '.mw-tpm-sp-instructions' ).addClass( 'hide' );
			for ( var i = 0; i < noOfSourceUnits; i++ ) {
				var content = $( '.mw-tpm-sp-unit__target' ).eq( i ).val();
				content = content.trim();
				if ( content !== '' ) {
					list.push( createTranslationPage( i, content ) );
				}
			}

			$.ajaxDispatcher( list, 1 ).done( function () {
				$( '#action-import' ).removeClass( 'hide' );
				$( 'input' ).prop( 'disabled', false );
				$( '.mw-tpm-sp-instructions' )
					.text( mw.msg( 'pm-on-save-message-text' ) )
					.removeClass( 'hide' );
			} ).fail( function ( errmsg ) {
				$( 'input' ).prop( 'disabled', false );
				// eslint-disable-next-line mediawiki/msg-doc
				$( '.mw-tpm-sp-error__message' ).text( mw.msg( errmsg ) ).removeClass( 'hide' );
			} );
		}
	}

	/**
	 * Handler for 'Cancel' button click event.
	 */
	function cancelHandler() {
		$( '.mw-tpm-sp-error__message' ).addClass( 'hide' );
		$( '.mw-tpm-sp-instructions' ).addClass( 'hide' );
		$( '#action-save, #action-cancel' ).addClass( 'hide' );
		$( '#action-import' ).removeClass( 'hide' );
		$( '.mw-tpm-sp-unit-listing' ).html( '' );
	}

	/**
	 * Handler for add new unit icon ('+') click event. Adds a translation unit
	 * below the current unit.
	 *
	 * @param {jQuery.Event} event
	 */
	function addHandler( event ) {
		var $nextRow = $( event.target ).closest( '.mw-tpm-sp-unit' ).next();
		var $targetUnit = $nextRow.find( '.mw-tpm-sp-unit__target' );
		var text = $targetUnit.val();
		$targetUnit.val( '' );
		$nextRow = $nextRow.next();
		text = shiftRowsDown( $nextRow, text );
		if ( text ) {
			var $newUnit = createNewUnit( '', text );
			$( '.mw-tpm-sp-unit-listing' ).append( $newUnit );
		}
		noOfTranslationUnits += 1;
	}

	/**
	 * Handler for delete icon ('-') click event. Deletes the unit and shifts
	 * the units up by one.
	 *
	 * @param {jQuery.Event} event
	 */
	function deleteHandler( event ) {
		var $rowUnit = $( event.target ).closest( '.mw-tpm-sp-unit' );
		var sourceText = $rowUnit.find( '.mw-tpm-sp-unit__source' ).val();
		if ( !sourceText ) {
			$rowUnit.remove();
		} else {
			$rowUnit.find( '.mw-tpm-sp-unit__target' ).val( '' );
			shiftRowsUp( $rowUnit );
		}
		noOfTranslationUnits -= 1;
	}

	/**
	 * Handler for swap icon click event. Swaps the text in the current unit
	 * with the text in the unit below.
	 *
	 * @param {jQuery.Event} event
	 */
	function swapHandler( event ) {
		var $rowUnit = $( event.target ).closest( '.mw-tpm-sp-unit' );
		var tempText = $rowUnit.find( '.mw-tpm-sp-unit__target' ).val();
		var nextVal = $rowUnit.next().find( '.mw-tpm-sp-unit__target' ).val();
		$rowUnit.find( '.mw-tpm-sp-unit__target' ).val( nextVal );
		$rowUnit.next().find( '.mw-tpm-sp-unit__target' ).val( tempText );
	}

	/**
	 * Handler for 'Import' button click event. Imports source and translation
	 * units and displays them.
	 *
	 * @param {jQuery.Event} e
	 */
	function importHandler( e ) {
		var $errorBox = $( '.mw-tpm-sp-error__message' ),
			$messageBox = $( '.mw-tpm-sp-instructions' );

		e.preventDefault();

		var pageTitle = $( '#title' ).val().trim();
		if ( pageTitle === '' ) {
			$errorBox.text( mw.msg( 'pm-pagetitle-missing' ) ).removeClass( 'hide' );
			return;
		}

		var titleObj = mw.Title.newFromText( pageTitle );
		$messageBox.addClass( 'hide' );
		if ( titleObj === null ) {
			$errorBox.text( mw.msg( 'pm-pagetitle-invalid' ) ).removeClass( 'hide' );
			return;
		}

		pageTitle = titleObj.getPrefixedDb();
		var slashPos = pageTitle.lastIndexOf( '/' );

		if ( slashPos === -1 ) {
			$errorBox.text( mw.msg( 'pm-langcode-missing' ) ).removeClass( 'hide' );
			return;
		}

		pageName = pageTitle.substring( 0, slashPos );
		langCode = pageTitle.substring( slashPos + 1 );

		if ( pageName === '' ) {
			$errorBox.text( mw.msg( 'pm-pagetitle-invalid' ) ).removeClass( 'hide' );
			return;
		}

		$errorBox.addClass( 'hide' );

		var fuzzyTimestamp = null;
		var units = null;
		getFuzzyTimestamp( pageTitle )
			.then( function ( response ) {
				fuzzyTimestamp = response;
				return getSourceUnits( pageName );
			} )
			.then( function ( response ) {
				units = response;
				return splitTranslationPage( fuzzyTimestamp, pageTitle );
			} )
			.then( function ( translations ) {
				noOfSourceUnits = units.length;
				var translationUnits = splitHeaders( translations );
				translationUnits = alignHeaders( units, translationUnits );
				noOfTranslationUnits = translationUnits.length;
				displayUnits( units, translationUnits );
				$( '#action-save, #action-cancel' ).removeClass( 'hide' );
				$( '#action-import' ).addClass( 'hide' );
				$messageBox.text( mw.msg( 'pm-on-import-message-text' ) ).removeClass( 'hide' );
			} );
	}

	/**
	 * Listens to various click events
	 */
	function listen() {
		var $listing = $( '.mw-tpm-sp-unit-listing' );

		$( '#mw-tpm-sp-primary-form' ).on( 'submit', importHandler );
		$( '#action-import' ).on( 'click', importHandler );
		$( '#action-save' ).on( 'click', saveHandler );
		$( '#action-cancel' ).on( 'click', cancelHandler );
		$listing.on( 'click', '.mw-tpm-sp-action--swap', swapHandler );
		$listing.on( 'click', '.mw-tpm-sp-action--delete', deleteHandler );
		$listing.on( 'click', '.mw-tpm-sp-action--add', addHandler );
	}

	$( listen );

	mw.translate = mw.translate || {};
	mw.translate = $.extend( mw.translate, {
		getSourceUnits: getSourceUnits,
		getFuzzyTimestamp: getFuzzyTimestamp,
		splitTranslationPage: splitTranslationPage,
		splitHeaders: splitHeaders,
		alignHeaders: alignHeaders
	} );

}() );
