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
			if( content === '' ) {
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
			if ( typeof obj === undefined ) {
				mw.log( 'No page' );
				return new $.Deferred().reject();
			}
			mw.log( data );
			if ( typeof obj.revisions === undefined ) {
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

	/**
	 * Shift rows up by one unit. This is called after a unit is deleted.
	 * @param {string} start The starting node
	 */
	function shiftRowsUp( start ) {
		var current = start, next = start.next();
    	while( next.length ) {
        	current.find( '.mw-tpm-sp-unit__target' ).val( next.find( '.mw-tpm-sp-unit__target' ).val() );
        	current = next;
        	next = next.next();
    	}
    	if ( current.find( '.mw-tpm-sp-unit__source' ).val() ) {
    		current.find( '.mw-tpm-sp-unit__target' ).val( '' );
    	} else {
    		current.remove();
    	}
	}

	/**
	 * Display the source and target units alongwith the action icons.
	 * @param {Array} sourceUnits
	 * @param {Array} translations
	 */
	function displayUnits( sourceUnits, translations ) {
		var i, totalUnits, newUnit,
			sourceUnit, targetUnit, actionUnit;

		noOfSourceUnits = sourceUnits.length;
		noOfTranslationUnits = translations.length;
		totalUnits = noOfSourceUnits > noOfTranslationUnits ? noOfSourceUnits : noOfTranslationUnits;
		for ( i = 0; i < totalUnits; i++ ) {
			newUnit = $( '<div>' ).attr( 'class', 'mw-tpm-sp-unit row' );
			sourceUnit = $( '<textarea>' ).attr( 'class', 'mw-tpm-sp-unit__source five columns' );
			targetUnit = $( '<textarea>' ).attr( 'class', 'mw-tpm-sp-unit__target five columns' );
			if ( sourceUnits[i] !== undefined ) {
				sourceUnit.val( sourceUnits[i].definition );
			}
			if ( translations[i] !== undefined ) {
				targetUnit.val( translations[i] );
			}
			actionUnit = $( '<div>' ).attr( 'class', 'mw-tpm-sp-unit__actions two columns' );
			actionUnit.append( $( '<span>' ).attr( 'class', 'mw-tpm-sp-action mw-tpm-sp-action--delete' ),
				$( '<span>' ).attr( 'class', 'mw-tpm-sp-action mw-tpm-sp-action--swap' ),
				$( '<span>' ).attr( 'class', 'mw-tpm-sp-action mw-tpm-sp-action--add' ) );
			newUnit.append( sourceUnit, targetUnit, actionUnit );
			$( '.mw-tpm-sp-unit-listing' ).append( newUnit );
		}
		$( '.mw-tpm-sp-unit__source' ).attr( 'readonly', 'readonly' );
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
				$( '#buttonImport' ).show();
				$( 'input' ).removeAttr( 'disabled' );
			});
		}
	} );

	$( '#action-cancel' ).click( function () {
		$( '#action-save, #action-cancel').hide();
		$( '#buttonImport' ).show();
		$( '.mw-tpm-sp-unit-listing' ).html( '' );
	} );

	$( document ).on( 'click', '.mw-tpm-sp-action--add', function () {
		var nextRow, text, oldText,
			sourceUnit, targetUnit, actionUnit,
			newUnit;
		nextRow = $( this ).parent().parent().next();
		text = nextRow.find( '.mw-tpm-sp-unit__target' ).val();
		nextRow.find( '.mw-tpm-sp-unit__target' ).val( '' );
		nextRow = nextRow.next();
		while( nextRow.length ) {
			oldText = nextRow.find( '.mw-tpm-sp-unit__target' ).val();
			nextRow.find( '.mw-tpm-sp-unit__target' ).val( text );
			nextRow = nextRow.next();
			text = oldText;
		}
		if( text ) {
			newUnit = $( '<div>' ).attr( 'class', 'mw-tpm-sp-unit row' );
			sourceUnit = $( '<textarea>' ).attr( 'class', 'mw-tpm-sp-unit__source five columns' );
			targetUnit = $( '<textarea>' ).attr( 'class', 'mw-tpm-sp-unit__target five columns' );
			targetUnit.val( text );
			actionUnit = $( '<div>' ).attr( 'class', 'mw-tpm-sp-unit__actions two columns' );
			actionUnit.append( $( '<span>' ).attr( 'class', 'mw-tpm-sp-action mw-tpm-sp-action--delete' ),
				$( '<span>' ).attr( 'class', 'mw-tpm-sp-action mw-tpm-sp-action--swap' ),
				$( '<span>' ).attr( 'class', 'mw-tpm-sp-action mw-tpm-sp-action--add' ) );
			newUnit.append( sourceUnit, targetUnit, actionUnit );
			$( '.mw-tpm-sp-unit-listing' ).append( newUnit );
		}
		noOfTranslationUnits += 1;
	} );

	$( document ).on( 'click', '.mw-tpm-sp-action--delete', function () {
		var sourceText, rowUnit;
		rowUnit = $( this ).parent().parent();
		sourceText = rowUnit.children( '.mw-tpm-sp-unit__source' ).val();
		if( !sourceText ) {
			$( this ).parent().parent().remove();
		} else {
			rowUnit.find( '.mw-tpm-sp-unit__target' ).val( '' );
			shiftRowsUp( rowUnit );
		}
		noOfTranslationUnits -= 1;
	} );

	$( document ).on( 'click', '.mw-tpm-sp-action--swap', function () {
		var rowUnit, tempText;
		rowUnit = $( this ).parent().parent();
		tempText = rowUnit.find( '.mw-tpm-sp-unit__target' ).val();
		rowUnit.find( '.mw-tpm-sp-unit__target' ).val( rowUnit.next().find( '.mw-tpm-sp-unit__target').val() );
		rowUnit.next().find( '.mw-tpm-sp-unit__target').val( tempText );
	} );

	$( document ).ready( function () {

		$( '#action-save, #action-cancel').hide();

		$( '#buttonImport' ).click( function () {
			var  pageTitle;
			pageName = $( '#title' ).val();
			langCode = $( '#language' ).val();
			pageTitle = pageName + '/' + langCode;

			$.when( getSourceUnits( pageName ), getFuzzyTimestamp( pageTitle ) )
				.then( function ( sourceUnits, fuzzyTimestamp ) {
				noOfSourceUnits = sourceUnits.length;
				splitTranslationPage( fuzzyTimestamp, pageTitle ).done( function ( translations ) {
					noOfTranslationUnits = translations.length;
					displayUnits( sourceUnits, translations );
					$( '#action-save, #action-cancel').show();
					$( '#buttonImport' ).hide();
				} );
			} );
		} );
	} );
} ( jQuery, mediaWiki ) );