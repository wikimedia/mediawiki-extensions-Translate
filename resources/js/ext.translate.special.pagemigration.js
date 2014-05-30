( function ( $, mw ) {
	'use strict';
	var noOfSourceUnits, noOfTranslationUnits,
		pageName, langCode, sourceUnits = [],
		translationUnits;

	/**
	 * Create translation pages using content of right hand side blocks
	 * and identifiers from left hand side blocks. Create pages only if
	 * content is not empty.
	 * @return {Function} Returns a function which returns a jQuery.Promise
	 */
	function createTranslationPage( i, content ) {

		return function () {
			var api = new mw.Api(),
			identifier, title, summary,
			deferred = new $.Deferred();

			identifier = sourceUnits[i].identifier;
			title = 'Translations:' + pageName + '/' + identifier + '/' + langCode;
			summary = 'imported translation using [[Special:PageMigration]]';

			deferred = api.postWithEditToken( {
				action: 'edit',
				format: 'json',
				title: title,
				text: content,
				summary: summary,
			} );
			return deferred.promise();
		};
	}

	/**
	 * Get the old translations of a given page at given time.
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
				obj = data.query.pages[page];
			}
			if ( typeof obj === undefined ) {
				// obj was not initialized
				errorBox.text( mw.msg( 'pm-page-does-not-exist', pageTitle ) ).show( 'fast' );
				return new $.Deferred().reject();
			}
			if ( obj.revisions === undefined ) {
				// the case of /en subpage where first edit is by FuzzyBot
				errorBox.text( mw.msg( 'pm-old-translations-missing', pageTitle ) ).show( 'fast' );
				return new $.Deferred().reject();
			}
			pageContent = obj.revisions[0]['*'];
			oldTranslationUnits = pageContent.split( '\n\n' );
			translationUnits = oldTranslationUnits;
			return oldTranslationUnits;
		} ).promise();
	}

	/**
	 * Get the timestamp before FuzzyBot's first edit on page
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
		} ).then ( function ( data ) {
			var timestampFB, dateFB, timestampOld,
				page, obj,
				errorBox = $( '.mw-tpm-sp-error__message' );
			for ( page in data.query.pages ) {
				obj = data.query.pages[page];
			}
			// Page does not exist if missing field is present
			if ( obj.missing === '' ) {
				errorBox.text( mw.msg( 'pm-page-does-not-exist', pageTitle ) ).show( 'fast' );
				return new $.Deferred().reject();
			}
			// Page exists, but no edit by FuzzyBot
			if ( obj.revisions === undefined ) {
				errorBox.text( mw.msg( 'pm-old-translations-missing', pageTitle ) ).show( 'fast' );
				return new $.Deferred().reject();
			} else {
				// FB over here refers to FuzzyBot
				timestampFB = obj.revisions[0].timestamp;
				dateFB = new Date( timestampFB );
				dateFB.setSeconds( dateFB.getSeconds() - 1 );
				timestampOld = dateFB.toISOString();
				mw.log( 'New Timestamp: ' + timestampOld );
				return timestampOld;
			}
		} ).promise();
	}

	/**
	 * Get the translation units created by Translate extension
	 * @param {string} pageName
	 * return {jQuery.Promise}
	 * return {Function} return.done
	 * return {Array} return.done.data Array of sUnit Objects
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
		} ).then ( function ( data ) {
			var result, i, sUnit, key;
			sourceUnits = [];
			result = data.query.messagecollection;
			for ( i = 1; i < result.length; i++ ) {
				sUnit = {};
				key = result[i].key;
				sUnit.identifier = key.slice( key.lastIndexOf( '/' ) + 1 );
				sUnit.definition = result[i].definition;
				sourceUnits.push( sUnit );
			}
			return sourceUnits;
		} ).promise();
	}

	mw.translate = mw.translate || {};
	mw.translate = $.extend( mw.translate, {
		getSourceUnits: getSourceUnits,
		getFuzzyTimestamp: getFuzzyTimestamp,
		splitTranslationPage: splitTranslationPage
	} );

	/**
	 * Shift rows up by one unit. This is called after a unit is deleted.
	 * @param {jQuery} $start The starting node
	 */
	function shiftRowsUp( $start ) {
		var $current = $start, $next = $start.next(), nextVal;
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
	 * Create a new row of source text and target text with action icons
	 * @param {string} sourceText
	 * @param {string} targetText
	 * @return {jQuery} newUnit The new row unit object
	 */

	function createNewUnit( sourceText, targetText ) {
		var newUnit, sourceUnit, targetUnit, actionUnit;

		newUnit = $( '<div>' ).addClass( 'mw-tpm-sp-unit row' );
		sourceUnit = $( '<textarea>' ).addClass( 'mw-tpm-sp-unit__source five columns' )
			.prop( 'readonly', 'readonly' ).val( sourceText );
		targetUnit = $( '<textarea>' ).addClass( 'mw-tpm-sp-unit__target five columns' )
			.val( targetText );
		actionUnit = $( '<div>' ).addClass( 'mw-tpm-sp-unit__actions two columns' );
		actionUnit.append( $( '<span>' ).addClass( 'mw-tpm-sp-action mw-tpm-sp-action--delete' )
				.attr( 'title', mw.msg( 'pm-delete-icon-hover-text' ) ),
			$( '<span>' ).addClass( 'mw-tpm-sp-action mw-tpm-sp-action--swap' )
				.attr( 'title', mw.msg( 'pm-swap-icon-hover-text' ) ),
			$( '<span>' ).addClass( 'mw-tpm-sp-action mw-tpm-sp-action--add' )
				.attr( 'title', mw.msg( 'pm-add-icon-hover-text' ) ) );
		newUnit.append( sourceUnit, targetUnit, actionUnit );
		return newUnit;
	}

	/**
	 * Display the source and target units alongwith the action icons.
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
			if ( sourceUnits[i] !== undefined ) {
				sourceText = sourceUnits[i].definition;
			}
			if ( translations[i] !== undefined ) {
				targetText = translations[i];
			}
			newUnit = createNewUnit( sourceText, targetText );
			unitListing.append( newUnit );
		}
	}

	$( '.mw-tpm-sp-error__message' ).hide( 'fast' );
	/*
	 * Split headers from remaining text in each translation unit if present.
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
	 * Handler for 'Save' button click event.
	 */
	function saveHandler() {
		var i, list = [], content;
		

		if ( noOfSourceUnits < noOfTranslationUnits ) {
			$( '.mw-tpm-sp-error__message' ).text( mw.msg( 'pm-extra-units-warning' ) )
				.show( 'fast' );
			return;
		} else {
			$( 'input' ).attr( 'disabled', 'disabled' );
			for ( i = 0; i < noOfSourceUnits; i++ ) {
				content = $( '.mw-tpm-sp-unit__target' ).eq( i ).val();
				content = $.trim( content );
				if ( content !== '' ) {
					list.push( createTranslationPage( i, content ) );
				}
			}

			$.ajaxDispatcher( list, 1 ).done( function () {
				$( '#action-import' ).removeClass( 'hide' );
				$( 'input' ).removeAttr( 'disabled' );
			} );
		}
	}

	/**
	 * Handler for 'Cancel' button click event.
	 */
	function cancelHandler() {
		$( '.mw-tpm-sp-error__message' ).hide( 'fast' );
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
		nextVal = rowUnit.next().find( '.mw-tpm-sp-unit__target').val();
		rowUnit.find( '.mw-tpm-sp-unit__target' ).val( nextVal );
		rowUnit.next().find( '.mw-tpm-sp-unit__target' ).val( tempText );
	}

	/**
	 * Handler for 'Import' button click event. Imports source and translation
	 * units and displays them.
	 */
	function importHandler() {
		var pageTitle, errorBox = $( '.mw-tpm-sp-error__message' );
		pageName = $.trim( $( '#title' ).val() );
		langCode = $.trim( $( '#language' ).val() );
		pageTitle = pageName + '/' + langCode;
		errorBox.hide( 'fast' );
		if ( pageName === '' ) {
			errorBox.text( mw.msg( 'pm-pagename-missing' ) ).show( 'fast' );
			return;
		}
		if ( langCode === '' ) {
			errorBox.text( mw.msg( 'pm-langcode-missing' ) ).show( 'fast' );
			return;
		}
		$.when( getSourceUnits( pageName ), getFuzzyTimestamp( pageTitle ) )
			.then( function ( sourceUnits, fuzzyTimestamp ) {
			noOfSourceUnits = sourceUnits.length;
			splitTranslationPage( fuzzyTimestamp, pageTitle ).done( function ( translations ) {
				noOfTranslationUnits = translations.length;
				displayUnits( sourceUnits, translations );
				$( '#action-save, #action-cancel').removeClass( 'hide' );
				$( '#action-import' ).addClass( 'hide' );
			$.when( getSourceUnits( pageName ), getFuzzyTimestamp( pageTitle ) )
				.then( function ( sourceUnits, fuzzyTimestamp ) {
				noOfSourceUnits = sourceUnits.length;
				splitTranslationPage( fuzzyTimestamp, pageTitle ).done( function ( translations ) {
					var translationUnits = splitHeaders( translations );
					noOfTranslationUnits = translationUnits.length;
					displayUnits( sourceUnits, translationUnits );
					$( '#action-save, #action-cancel').removeClass( 'hide' );
					$( '#action-import' ).addClass( 'hide' );
				} );
			} );
		} );
	}

	/**
	 * Listens to various click events
	 */
	function listen() {
		var $listing = $( '.mw-tpm-sp-unit-listing' );
		$( '#action-import' ).click( importHandler );
		$( '#action-save' ).click( saveHandler );
		$( '#action-cancel' ).click( cancelHandler );
		$listing.on( 'click', '.mw-tpm-sp-action--swap', swapHandler );
		$listing.on( 'click', '.mw-tpm-sp-action--delete', deleteHandler );
		$listing.on( 'click', '.mw-tpm-sp-action--add', addHandler );
	}

	$( document ).ready( listen );
} ( jQuery, mediaWiki ) );
