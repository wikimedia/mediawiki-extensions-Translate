( function ( $, mw ) {
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
	 * @return {Function} Returns a function which returns a jQuery.Promise
	 */
	function createTranslationPage( i, content ) {

		return function () {
			var identifier, title, summary,
				api = new mw.Api();

			identifier = sourceUnits[ i ].identifier;
			title = 'Translations:' + pageName + '/' + identifier + '/' + langCode;
			summary = $( '#pm-summary' ).val();

			// Change to csrf when support for MW 1.25 is dropped
			return api.postWithToken( 'edit', {
				action: 'edit',
				format: 'json',
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
			format: 'json',
			rvprop: 'content',
			rvstart: fuzzyTimestamp,
			titles: pageTitle
		} ).then( function ( data ) {
			var pageContent, oldTranslationUnits, obj, page,
				errorBox = $( '.mw-tpm-sp-error__message' );
			for ( page in data.query.pages ) {
				obj = data.query.pages[ page ];
			}
			if ( typeof obj === undefined ) {
				// obj was not initialized
				errorBox.text( mw.msg( 'pm-page-does-not-exist', pageTitle ) ).show( 'fast' );
				return $.Deferred().reject();
			}
			if ( obj.revisions === undefined ) {
				// the case of /en subpage where first edit is by FuzzyBot
				errorBox.text( mw.msg( 'pm-old-translations-missing', pageTitle ) ).show( 'fast' );
				return $.Deferred().reject();
			}
			pageContent = obj.revisions[ 0 ][ '*' ];
			oldTranslationUnits = pageContent.split( '\n\n' );
			return oldTranslationUnits;
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
			format: 'json',
			rvprop: 'timestamp',
			rvuser: 'FuzzyBot',
			rvdir: 'newer',
			titles: pageTitle
		} ).then( function ( data ) {
			var timestampFB, dateFB, timestampOld,
				page, obj,
				errorBox = $( '.mw-tpm-sp-error__message' );
			for ( page in data.query.pages ) {
				obj = data.query.pages[ page ];
			}
			// Page does not exist if missing field is present
			if ( obj.missing === '' ) {
				errorBox.text( mw.msg( 'pm-page-does-not-exist', pageTitle ) ).show( 'fast' );
				return $.Deferred().reject();
			}
			// Page exists, but no edit by FuzzyBot
			if ( obj.revisions === undefined ) {
				errorBox.text( mw.msg( 'pm-old-translations-missing', pageTitle ) ).show( 'fast' );
				return $.Deferred().reject();
			} else {
				// FB over here refers to FuzzyBot
				timestampFB = obj.revisions[ 0 ].timestamp;
				dateFB = new Date( timestampFB );
				dateFB.setSeconds( dateFB.getSeconds() - 1 );
				timestampOld = dateFB.toISOString();
				mw.log( 'New Timestamp: ' + timestampOld );
				return timestampOld;
			}
		} );
	}

	/**
	 * Get the translation units created by Translate extension.
	 *
	 * @param {string} pageName
	 * @return {jQuery.Promise}
	 * @return {Function} return.done
	 * @return {Object[]} return.done.data Array of sUnit Objects
	 */
	function getSourceUnits( pageName ) {
		var api = new mw.Api();

		return api.get( {
			action: 'query',
			list: 'messagecollection',
			format: 'json',
			mcgroup: 'page-' + pageName,
			mclanguage: 'en',
			mcprop: 'definition'
		} ).then( function ( data ) {
			var result, i, sUnit, key;
			sourceUnits = [];
			result = data.query.messagecollection;
			for ( i = 1; i < result.length; i++ ) {
				sUnit = {};
				key = result[ i ].key;
				sUnit.identifier = key.slice( key.lastIndexOf( '/' ) + 1 );
				sUnit.definition = result[ i ].definition;
				sourceUnits.push( sUnit );
			}
			return sourceUnits;
		} );
	}

	/**
	 * Shift rows up by one unit. This is called after a unit is deleted.
	 *
	 * @param {jQuery} $start The starting node
	 */
	function shiftRowsUp( $start ) {
		var nextVal,
			$current = $start,
			$next = $start.next();

		while ( $next.length ) {
			nextVal = $next.find( '.mw-tpm-sp-unit__target' ).val();
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
		var oldText;

		while ( $nextRow.length ) {
			oldText = $nextRow.find( '.mw-tpm-sp-unit__target' ).val();
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
		var newUnit, sourceUnit, targetUnit, actionUnit;

		newUnit = $( '<div>' ).addClass( 'mw-tpm-sp-unit row' );
		sourceUnit = $( '<textarea>' ).addClass( 'mw-tpm-sp-unit__source five columns' )
			.prop( 'readonly', true ).attr( 'tabindex', '-1' ).val( sourceText );
		targetUnit = $( '<textarea>' ).addClass( 'mw-tpm-sp-unit__target five columns' )
			.val( targetText );
		actionUnit = $( '<div>' ).addClass( 'mw-tpm-sp-unit__actions two columns' );
		actionUnit.append( $( '<span>' ).addClass( 'mw-tpm-sp-action mw-tpm-sp-action--add' )
				.attr( 'title', mw.msg( 'pm-add-icon-hover-text' ) ),
			$( '<span>' ).addClass( 'mw-tpm-sp-action mw-tpm-sp-action--swap' )
				.attr( 'title', mw.msg( 'pm-swap-icon-hover-text' ) ),
			$( '<span>' ).addClass( 'mw-tpm-sp-action mw-tpm-sp-action--delete' )
				.attr( 'title', mw.msg( 'pm-delete-icon-hover-text' ) ) );
		newUnit.append( sourceUnit, targetUnit, actionUnit );
		return newUnit;
	}

	/**
	 * Display the source and target units alongwith the action icons.
	 *
	 * @param {Array} sourceUnits
	 * @param {Array} translations
	 */
	function displayUnits( sourceUnits, translations ) {
		var i, totalUnits, newUnit, unitListing,
			sourceText, targetText;

		noOfSourceUnits = sourceUnits.length;
		noOfTranslationUnits = translations.length;
		totalUnits = noOfSourceUnits > noOfTranslationUnits ? noOfSourceUnits : noOfTranslationUnits;
		unitListing = $( '.mw-tpm-sp-unit-listing' );
		unitListing.html( '' );
		for ( i = 0; i < totalUnits; i++ ) {
			sourceText = targetText = '';
			if ( sourceUnits[ i ] !== undefined ) {
				sourceText = sourceUnits[ i ].definition;
			}
			if ( translations[ i ] !== undefined ) {
				targetText = translations[ i ];
			}
			newUnit = createNewUnit( sourceText, targetText );
			unitListing.append( newUnit );
		}
	}

	/**
	 * Split headers from remaining text in each translation unit if present.
	 *
	 * @param {Array} translations Array of initial units obtained on splitting
	 * @return {string[]} translationUnits Array having the headers split into new unit
	 */
	function splitHeaders( translations ) {
		return $.map( translations, function ( elem ) {
			// Check http://regex101.com/r/oT7fZ2 for details
			return elem.match( /(^==.+$|(?:(?!^==).+\n?)+)/gm );
		} );
	}

	/**
	 * Get the index of next translation unit containing h2 header.
	 *
	 * @param {number} startIndex Index to start the scan from
	 * @return {number} i Index of the next unit found, -1 if not
	 */
	function getHeaderUnit( startIndex, translationUnits ) {
		var i, regex;
		regex = new RegExp( /^==[^=]+==$/m );
		for ( i = startIndex; i < translationUnits.length; i++ ) {
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
	 */
	function alignHeaders( sourceUnits, translationUnits ) {
		var i, regex, tIndex = 0,
			matchText, emptyCount, mergeText;

		regex = new RegExp( /^==[^=]+==$/m );
		for ( i = 0; i < sourceUnits.length; i++ ) {
			if ( regex.test( sourceUnits[ i ].definition ) ) {
				tIndex = getHeaderUnit( tIndex, translationUnits );
				mergeText = '';
				// search is over
				if ( tIndex === -1 ) {
					break;
				}
				// remove the unit
				matchText = translationUnits.splice( tIndex, 1 ).toString();
				emptyCount = i - tIndex;
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
		var i, content, list = [];

		$( '.mw-tpm-sp-error__message' ).hide( 'fast' );
		if ( noOfSourceUnits < noOfTranslationUnits ) {
			$( '.mw-tpm-sp-error__message' ).text( mw.msg( 'pm-extra-units-warning' ) )
				.show( 'fast' );
			return;
		} else {
			$( 'input' ).prop( 'disabled', true );
			$( '.mw-tpm-sp-instructions' ).hide( 'fast' );
			for ( i = 0; i < noOfSourceUnits; i++ ) {
				content = $( '.mw-tpm-sp-unit__target' ).eq( i ).val();
				content = $.trim( content );
				if ( content !== '' ) {
					list.push( createTranslationPage( i, content ) );
				}
			}

			$.ajaxDispatcher( list, 1 ).done( function () {
				$( '#action-import' ).removeClass( 'hide' );
				$( 'input' ).prop( 'disabled', false );
				$( '.mw-tpm-sp-instructions' ).text( mw.msg( 'pm-on-save-message-text' ) ).show( 'fast' );
			} );
		}
	}

	/**
	 * Handler for 'Cancel' button click event.
	 */
	function cancelHandler() {
		$( '.mw-tpm-sp-error__message' ).hide( 'fast' );
		$( '.mw-tpm-sp-instructions' ).hide( 'fast' );
		$( '#action-save, #action-cancel' ).addClass( 'hide' );
		$( '#action-import' ).removeClass( 'hide' );
		$( '.mw-tpm-sp-unit-listing' ).html( '' );
	}

	/**
	 * Handler for add new unit icon ('+') click event. Adds a translation unit
	 * below the current unit.
	 */
	function addHandler( event ) {
		var nextRow, text, newUnit, targetUnit;

		nextRow = $( event.target ).closest( '.mw-tpm-sp-unit' ).next();
		targetUnit = nextRow.find( '.mw-tpm-sp-unit__target' );
		text = targetUnit.val();
		targetUnit.val( '' );
		nextRow = nextRow.next();
		text = shiftRowsDown( nextRow, text );
		if ( text ) {
			newUnit = createNewUnit( '', text );
			$( '.mw-tpm-sp-unit-listing' ).append( newUnit );
		}
		noOfTranslationUnits += 1;
	}

	/**
	 * Handler for delete icon ('-') click event. Deletes the unit and shifts
	 * the units up by one.
	 */
	function deleteHandler( event ) {
		var sourceText, rowUnit;
		rowUnit = $( event.target ).closest( '.mw-tpm-sp-unit' );
		sourceText = rowUnit.find( '.mw-tpm-sp-unit__source' ).val();
		if ( !sourceText ) {
			rowUnit.remove();
		} else {
			rowUnit.find( '.mw-tpm-sp-unit__target' ).val( '' );
			shiftRowsUp( rowUnit );
		}
		noOfTranslationUnits -= 1;
	}

	/**
	 * Handler for swap icon click event. Swaps the text in the current unit
	 * with the text in the unit below.
	 */
	function swapHandler( event ) {
		var rowUnit, tempText, nextVal;
		rowUnit = $( event.target ).closest( '.mw-tpm-sp-unit' );
		tempText = rowUnit.find( '.mw-tpm-sp-unit__target' ).val();
		nextVal = rowUnit.next().find( '.mw-tpm-sp-unit__target' ).val();
		rowUnit.find( '.mw-tpm-sp-unit__target' ).val( nextVal );
		rowUnit.next().find( '.mw-tpm-sp-unit__target' ).val( tempText );
	}

	/**
	 * Handler for 'Import' button click event. Imports source and translation
	 * units and displays them.
	 *
	 * @param {jQuery.event} e
	 */
	function importHandler( e ) {
		var pageTitle, slashPos, titleObj,
			errorBox = $( '.mw-tpm-sp-error__message' ),
			messageBox = $( '.mw-tpm-sp-instructions' );

		e.preventDefault();

		pageTitle = $.trim( $( '#title' ).val() );
		if ( pageTitle === '' ) {
			errorBox.text( mw.msg( 'pm-pagetitle-missing' ) ).show( 'fast' );
			return;
		}

		titleObj = mw.Title.newFromText( pageTitle );
		messageBox.hide( 'fast' );
		if ( titleObj === null ) {
			errorBox.text( mw.msg( 'pm-pagetitle-invalid' ) ).show( 'fast' );
			return;
		}

		pageTitle = titleObj.getPrefixedDb();
		slashPos = pageTitle.lastIndexOf( '/' );

		if ( slashPos === -1 ) {
			errorBox.text( mw.msg( 'pm-langcode-missing' ) ).show( 'fast' );
			return;
		}

		pageName = pageTitle.substring( 0, slashPos );
		langCode = pageTitle.substring( slashPos + 1 );

		if ( pageName === '' ) {
			errorBox.text( mw.msg( 'pm-pagetitle-invalid' ) ).show( 'fast' );
			return;
		}

		errorBox.hide( 'fast' );

		$.when( getSourceUnits( pageName ), getFuzzyTimestamp( pageTitle ) )
			.then( function ( sourceUnits, fuzzyTimestamp ) {
			noOfSourceUnits = sourceUnits.length;
			splitTranslationPage( fuzzyTimestamp, pageTitle ).done( function ( translations ) {
				var translationUnits = splitHeaders( translations );
				translationUnits = alignHeaders( sourceUnits, translationUnits );
				noOfTranslationUnits = translationUnits.length;
				displayUnits( sourceUnits, translationUnits );
				$( '#action-save, #action-cancel' ).removeClass( 'hide' );
				$( '#action-import' ).addClass( 'hide' );
				messageBox.text( mw.msg( 'pm-on-import-message-text' ) ).show( 'fast' );
			} );
		} );
	}

	/**
	 * Listens to various click events
	 */
	function listen() {
		var $listing = $( '.mw-tpm-sp-unit-listing' );

		$( '#mw-tpm-sp-primary-form' ).submit( importHandler );
		$( '#action-import' ).click( importHandler );
		$( '#action-save' ).click( saveHandler );
		$( '#action-cancel' ).click( cancelHandler );
		$listing.on( 'click', '.mw-tpm-sp-action--swap', swapHandler );
		$listing.on( 'click', '.mw-tpm-sp-action--delete', deleteHandler );
		$listing.on( 'click', '.mw-tpm-sp-action--add', addHandler );
	}

	$( document ).ready( listen );

	mw.translate = mw.translate || {};
	mw.translate = $.extend( mw.translate, {
		getSourceUnits: getSourceUnits,
		getFuzzyTimestamp: getFuzzyTimestamp,
		splitTranslationPage: splitTranslationPage,
		alignHeaders: alignHeaders
	} );

}( jQuery, mediaWiki ) );
