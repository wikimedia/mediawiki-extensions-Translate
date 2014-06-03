( function ( $, mw ) {
	'use strict';
	var noOfSourceUnits, noOfTranslationUnits,
		pageName, langCode, sourceUnits = [], translationUnits;

	/**
	 * Create translation pages using content of right hand side blocks
	 * and identifiers from left hand side blocks. Create pages only if
	 * content is not empty.
	 * @return {jQuery.Promise[]} deferreds
	 */
	function createTranslationPages() {
		var api = new mw.Api(), deferreds = [],
			i, tUnit, identifier, title,
			content, summary, promise;
		for ( i = 0; i < noOfSourceUnits; i++ ) {
			tUnit = $( '.mw-tpm-sp-unit__target' ).eq( i );
			identifier = sourceUnits[i].identifier;
			title = 'Translations:' + pageName + '/' + identifier + '/' + langCode;
			content = tUnit.val();
			summary = 'imported translation using [[Special:PageMigration]]';
			if ( content === '' ) {
				continue;
			}
			promise = api.postWithEditToken( {
				action: 'edit',
				format: 'json',
				title: title,
				text: content,
				summary: summary,
			} ).promise();
			deferreds.push( promise );
		}
		return deferreds;
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
			var pageContent, oldTranslationUnits, obj, page;
			for ( page in data.query.pages ) {
				obj = data.query.pages[page];
			}
			if ( typeof obj === undefined ) {
				// obj was not initialized. Handle this case
				mw.log( 'No page' );
				return new $.Deferred().reject();
			}
			if ( typeof obj.revisions === undefined ) {
				// the case of /en subpage
				mw.log( 'Nothing to import' );
				return new $.Deferred().reject();
			}
			mw.log( obj.revisions[0]['*'].split( '\n\n' ) );
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
				page, obj;
			// FB = FuzzyBot
			for ( page in data.query.pages ) {
				obj = data.query.pages[page];
			}
			if ( obj.missing === '' ) {
				mw.log( 'No page' );
				return new $.Deferred().reject();
			}
			mw.log( data );
			if ( obj.revisions === undefined ) {
				mw.log( 'No edit by FuzzyBot on this page' );
				return new $.Deferred().reject();
			} else {
				/*FB over here refers to FuzzyBot*/
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
		actionUnit.append( $( '<span>' ).addClass( 'mw-tpm-sp-action mw-tpm-sp-action--delete' ),
			$( '<span>' ).addClass( 'mw-tpm-sp-action mw-tpm-sp-action--swap' ),
			$( '<span>' ).addClass( 'mw-tpm-sp-action mw-tpm-sp-action--add' ) );
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

	$( '#action-save' ).click( function () {
		var deferreds;

		if ( noOfSourceUnits < noOfTranslationUnits ) {
			window.alert( 'Extra units might be present. Please match the source and translation units properly' );
			return;
		} else {
			deferreds = createTranslationPages();
			$( 'input' ).attr( 'disabled', 'disabled' );
			$.when.apply( null, deferreds ).done( function () {
				$( '#action-import' ).removeClass( 'hide' );
				$( 'input' ).removeAttr( 'disabled' );
			});
		}
	} );

	$( '#action-cancel' ).click( function () {
		$( '#action-save, #action-cancel' ).addClass( 'hide' );
		$( '#action-import' ).removeClass( 'hide' );
		$( '.mw-tpm-sp-unit-listing' ).html( '' );
	} );

	$( '.mw-tpm-sp-unit-listing' ).on( 'click', '.mw-tpm-sp-action--add', function () {
		var nextRow, text, newUnit;

		nextRow = $( this ).parents( '.mw-tpm-sp-unit' ).next();
		text = nextRow.find( '.mw-tpm-sp-unit__target' ).val();
		nextRow.find( '.mw-tpm-sp-unit__target' ).val( '' );
		nextRow = nextRow.next();
		text = shiftRowsDown( nextRow, text );
		if ( text ) {
			newUnit = createNewUnit( '', text );
			$( '.mw-tpm-sp-unit-listing' ).append( newUnit );
		}
		noOfTranslationUnits += 1;
	} );

	$( '.mw-tpm-sp-unit-listing' ).on( 'click', '.mw-tpm-sp-action--delete', function () {
		var sourceText, rowUnit;
		rowUnit = $( this ).parents( '.mw-tpm-sp-unit' );
		sourceText = rowUnit.children( '.mw-tpm-sp-unit__source' ).val();
		if ( !sourceText ) {
			$( this ).parent().parent().remove();
		} else {
			rowUnit.find( '.mw-tpm-sp-unit__target' ).val( '' );
			shiftRowsUp( rowUnit );
		}
		noOfTranslationUnits -= 1;
	} );

	$( '.mw-tpm-sp-unit-listing' ).on( 'click', '.mw-tpm-sp-action--swap', function () {
		var rowUnit, tempText, nextVal;
		rowUnit = $( this ).parents( '.mw-tpm-sp-unit' );
		tempText = rowUnit.find( '.mw-tpm-sp-unit__target' ).val();
		nextVal = rowUnit.next().find( '.mw-tpm-sp-unit__target').val();
		rowUnit.find( '.mw-tpm-sp-unit__target' ).val( nextVal );
		rowUnit.next().find( '.mw-tpm-sp-unit__target' ).val( tempText );
	} );

	$( '.mw-tpm-sp-unit-listing' ).ready( function () {

		$( '#action-save, #action-cancel').addClass( 'hide' );

		$( '#action-import' ).click( function () {
			var pageTitle;
			pageName = $( '#title' ).val();
			langCode = $( '#language' ).val();
			pageTitle = pageName + '/' + langCode;

			$.when( getSourceUnits( pageName ), getFuzzyTimestamp( pageTitle ) )
				.then( function ( sourceUnits, fuzzyTimestamp ) {
				noOfSourceUnits = sourceUnits.length;
				splitTranslationPage( fuzzyTimestamp, pageTitle ).done( function ( translations ) {
					noOfTranslationUnits = translations.length;
					displayUnits( sourceUnits, translations );
					$( '#action-save, #action-cancel').removeClass( 'hide' );
					$( '#action-import' ).addClass( 'hide' );
				} );
			} );
		} );
	} );
} ( jQuery, mediaWiki ) );