( function ( $, mw ) {
	'use strict';
	var noOfSourceUnits, noOfTranslationUnits,
		pageName, langCode;

	/**
	 * Create a translation page with the given title and content
	 * @param {string} title Title of the page
	 * @param {string} content Body of the page
	 * @param {string} summary Edit summary
	 * @param {identifier} Translation unit identifier
	 * @return {Function} return.done
	 * @return {jQuery.promise}
	 */
	function createTranslationPage( title, content, summary, identifier ) {
		var api = new mw.Api();

		return api.post( {
			action: 'edit',
			format: 'json',
			title: title,
			text: content,
			summary: summary,
			contentformat: 'text/x-wiki',
			contentmodel: 'wikitext',
			token: mw.user.tokens.get('editToken')
		} ).then( function() {
			return identifier;
		} ).promise();
	}

	/**
	 * Get the old translations of a given page at given time.
	 * @param {string} fuzzyTimestamp Timestamp in MediaWiki format
	 * @param {string} pageTitle
	 * @return {jQuery.Promise}
	 * @return {Function} return.done
	 * @return {Array} oldTranslationUnits Array of paragraphs
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
		} ).then( function( data ) {
			var pageContent, oldTranslationUnits, obj, page;

			for ( page in data.query.pages ) {
				obj = data.query.pages[page];
			}
			if ( typeof obj === undefined ) {
				// obj was not initialized. Handle this case
				mw.log('No page');
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
		} ).then ( function( data ) {
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
		} ).then ( function( data ) {
			var result, i, sUnit, key,
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
	 * Add empty RHS blocks to always match with the number of source units
	 */
	function addEmptyUnits() {
		var difference, i, previousID, newTUnit, newAUnit;

		if ( noOfSourceUnits <= noOfTranslationUnits ) {
			return;
		} else {
			difference = noOfSourceUnits - noOfTranslationUnits;
			previousID = Number( $( '#translationunits div:last' ).attr( 'id' ).replace( 't', '' ) );
			for ( i = 1; i <= difference; i++) {
				newTUnit = $( '<div>' )
					.attr( 'id', 't' + ( previousID + 1 ) );
				$( '#translationunits' ).append( newTUnit );
				newAUnit = $( '<div>' ).attr( 'id', 'a' + ( previousID + 1 ) );
				newAUnit.append( $( '<span>' ).attr( 'class', 'edit' ) );
				newAUnit.append( $( '<span>' ).attr( 'class', 'delete' ) );
				newAUnit.append( $( '<span>' ).attr( 'class', 'swap' ) );
				$( '#actions' ).append( newAUnit );
				previousID += 1;
			}
			noOfTranslationUnits = noOfSourceUnits;
		}
	}

	/**
	 * Display the action icons for each imported translation
	 * @param {Integer} numberOfTranslationUnits
	 */
	function showActionIcons( numberOfTranslationUnits ) {
		var i, newActionUnit;

		$( '#actions' ).html( '' );

		for ( i = 0; i < numberOfTranslationUnits; i++ ) {
			newActionUnit = $( '<div>' ).attr( 'id', 'a' + ( i + 1 ) );
			newActionUnit.append( $( '<span>' ).attr( 'class', 'edit' ) );
			newActionUnit.append( $( '<span>' ).attr( 'class', 'delete' ) );
			newActionUnit.append( $( '<span>' ).attr( 'class', 'swap' ) );
			$( '#actions' ).append( newActionUnit );
		}
	}

	/**
	 * Display the imported translations
	 * @param {Array} translationUnits Array of translations
	 */
	function showTranslationUnits( translationUnits ) {
		var i, newUnit;

		$( '#translationunits' ).html( '' );

		for ( i = 0; i < translationUnits.length; i++ ) {
			newUnit = $( '<div>' )
				.attr( 'id', 't' + ( i + 1 ) )
				.text( translationUnits[i] );
			$( '#translationunits' ).append( newUnit );
		}
	}

	/**
	 * Display the translation units for source page
	 * @param {Array} sourceUnits Array of Objects
	 */
	function showSourceUnits( sourceUnits ) {
		var i, newUnit;

		$( '#sourceunits' ).html( '' );

		for ( i = 0; i < sourceUnits.length; i++ ) {
			newUnit = $( '<div>' )
				.attr( 'id', 's' + sourceUnits[i].identifier )
				.text( sourceUnits[i].definition );
			$( '#sourceunits' ).append( newUnit );
		}
	}

	/**
	 * Hides the swap icon for the last translation unit
	 */
	function hideLastSwap() {
		$( '.swap' ).show();
		$('#actions div:last .swap').hide();
	}

	/**
	 * Disable edit, delete and swap icons when a translation unit is
	 * opened for editing.
	 */
	function disableOptions() {
		$( '.edit, .delete, .swap' ).each( function () {
			$( this).addClass( 'disable' );
		} );
	}

	/**
	 * Enable the edit, delete and swap icons again when a translation unit
	 * is saved after having been opened for editing.
	 */
	function enableOptions() {
		$( '.edit, .delete, .swap' ).each( function () {
			$( this).removeClass( 'disable' );
		} );
	}

	/**
	 * Fetch the translations one by one and pass them to a function to create
	 * the corresponding page.
	 */
	function createPages() {
		var i, sUnit, tUnit, identifier,
			title, content, summary;

		for ( i = 1; i <= noOfSourceUnits; i++) {
			sUnit = $( '#sourceunits div:nth-child(' + i + ')' );
			tUnit = $( '#translationunits div:nth-child(' + i + ')' );
			identifier = sUnit.attr('id').replace( 's', '' );
			title = 'Translations:' + pageName + '/' + identifier + '/' + langCode;
			content = tUnit.text();
			summary = 'imported translation using [[Special:PageMigration]]';
			createTranslationPage( title, content, summary, identifier );
		}
	}

	$( '#buttonSavePages' ).click( function() {
		if ( noOfSourceUnits !== noOfTranslationUnits ) {
			// alert ("Extra units might be present. Please match the source and translation units properly.");
			return;
		} else {
			createPages();
		}
	} );

	$( '#buttonCancel' ).click( function() {
		$( '#buttonSavePages, #buttonCancel').hide();
		$( '#buttonImport' ).show();
		$( '#sourceunits, #translationunits, #actions' ).html( '' );
	} );

	$( '.delete' ).live( 'click', function() {
		var parentID, translationID;
		parentID = $( this ).parent().attr( 'id' );
		translationID = 't' + parentID.replace( 'a' , '' );
		$( '#' + translationID ).remove();
		$( this ).parent().remove();
		noOfTranslationUnits -= 1;
		addEmptyUnits();
		hideLastSwap();
	} );

	$( '.save-edit' ).live( 'click', function() {
		var parentID, translationID;
		parentID = $( this ).parent().attr( 'id' );
		translationID = 't' + parentID.replace( 'a' , '' );
		$( '#' + translationID ).attr( 'contenteditable', 'false' );
		$( '#' + translationID ).css( 'background-color', '#FFFFFF' );
		$( this ).addClass( 'edit' ).removeClass( 'save-edit' );
		enableOptions();
	} );

	$( '.edit' ).live( 'click', function() {
		var parentID, translationID;
		parentID = $( this ).parent().attr( 'id' );
		translationID = 't' + parentID.replace( 'a' , '' );
		$( '#' + translationID ).attr( 'contenteditable', 'true' );
		$( '#' + translationID ).css( 'background-color', '#FFF5F0' );
		$( this ).addClass( 'save-edit' ).removeClass( 'edit' );
		disableOptions();
	} );

	$ ( '.swap' ).live( 'click', function() {
		var parentID, oldID, newID, tempData;
		parentID = $( this ).parent().attr( 'id' );
		oldID = Number( parentID.replace( 'a' , '' ) );
		newID = $ ( '#' + parentID ).next().attr( 'id' ).replace( 'a', '' );
		tempData = $( '#t' + oldID ).text();
		$( '#t' + oldID ).text( ( $( '#t' + newID ).text() ) );
		$( '#t' + newID ).text(tempData);
	} );

	$( document ).ready( function () {

		$( '#buttonSavePages, #buttonCancel').hide();

		$( '#buttonImport' ).click( function() {
			var  pageTitle;
			pageName = $( '#pagename' ).val();
			langCode = $( '#langcode' ).val();
			pageTitle = pageName + '/' + langCode;

			$.when( getSourceUnits( pageName ), getFuzzyTimestamp( pageTitle ) )
				.then( function( sourceUnits, fuzzyTimestamp ) {
				mw.log( 'All done now!' );
				noOfSourceUnits = sourceUnits.length;
				showSourceUnits( sourceUnits );
				splitTranslationPage( fuzzyTimestamp, pageTitle ).done( function( translations ) {
					noOfTranslationUnits = translations.length;
					showTranslationUnits( translations );
					showActionIcons( noOfTranslationUnits );
					addEmptyUnits();
					hideLastSwap();
					$( '#buttonSavePages, #buttonCancel').show();
					$( '#buttonImport' ).hide();
				} );
			} );
		} );
	} );
} ( jQuery, mediaWiki ) );