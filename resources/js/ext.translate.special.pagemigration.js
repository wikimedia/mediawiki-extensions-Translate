( function ( $, mw ) {
	'use strict';
	var noOfSourceUnits, noOfTranslationUnits,
		pageName, langCode;

	/**
	 * Create translation pages using content of right hand side blocks
	 * and identifiers from left hand side blocks. Create pages only if
	 * content is not empty.
	 * @return {jQuery.Promise[]} deferreds
	 */
	function createTranslationPages() {
		var api = new mw.Api(), deferreds = [],
			i, sUnit, tUnit, identifier,
			title, content, summary, promise;

		for ( i = 0; i < noOfSourceUnits; i++ ) {
			sUnit = $( '#sourceunits div' ).eq( i );
			tUnit = $( '#translationunits div' ).eq( i );
			identifier = sUnit.attr( 'id' ).replace( 's', '' );
			title = 'Translations:' + pageName + '/' + identifier + '/' + langCode;
			content = tUnit.text();
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
		} ).then( function( data ) {
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
	 * Update the IDs of translation divs and action divs. This function is called
	 * when a unit is deleted or a new unit is added for manual splitting
	 */
	function updateIDs() {
		var divNumber = 1;
		$( '#translationunits div' ).each( function() {
			$( this ).attr( 'id', 't' + divNumber );
			divNumber += 1;
		} );
		divNumber = 1;
		$( '#actions div' ).each( function() {
			$( this ).attr( 'id', 'a' + divNumber );
			divNumber += 1;
		} );
	}

	/**
	 * Add empty RHS blocks to always match with the number of source units
	 */
	function addEmptyUnits() {
		var difference, i, divActions,
			divTranslations;

		divActions = $( '#actions' );
		divTranslations = $( '#translationunits' );
		if ( noOfSourceUnits <= noOfTranslationUnits ) {
			return;
		} else {
			difference = noOfSourceUnits - noOfTranslationUnits;
			for ( i = 1; i <= difference; i++ ) {
				$( '<div>' ).appendTo( divTranslations );
				$( '<div>' ).append( $( '<span>' ).attr( 'class', 'edit' ),
					$( '<span>' ).attr( 'class', 'delete' ),
					$( '<span>' ).attr( 'class', 'swap' ),
					$( '<span>' ).attr( 'class', 'add' ) )
				.appendTo( divActions );
			}
			noOfTranslationUnits = noOfSourceUnits;
		}
	}

	/**
	 * Display the action icons for each imported translation
	 * @param {Integer} numberOfTranslationUnits
	 */
	function showActionIcons( numberOfTranslationUnits ) {
		var i, divActions;

		divActions = $( '#actions' );
		divActions.html( '' );

		for ( i = 0; i < numberOfTranslationUnits; i++ ) {
			$( '<div>' ).attr( 'id', 'a' + ( i + 1 ) )
				.append( $( '<span>' ).attr( 'class', 'edit' ),
					$( '<span>' ).attr( 'class', 'delete' ),
					$( '<span>' ).attr( 'class', 'swap' ),
					$( '<span>' ).attr( 'class', 'add' ) )
				.appendTo( divActions );
		}
	}

	/**
	 * Display the imported translations
	 * @param {Array} translationUnits Array of translations
	 */
	function showTranslationUnits( translationUnits ) {
		var i, divTranslations;

		divTranslations = $( '#translationunits' );
		divTranslations.html( '' );

		for ( i = 0; i < translationUnits.length; i++ ) {
			$( '<div>' ).attr( 'id', 't' + ( i + 1 ) )
				.text( translationUnits[i] )
				.appendTo( divTranslations );
		}
	}

	/**
	 * Display the translation units for source page
	 * @param {Array} sourceUnits Array of Objects
	 */
	function showSourceUnits( sourceUnits ) {
		var i, divSource;

		divSource = $( '#sourceunits' );
		divSource.html( '' );

		for ( i = 0; i < sourceUnits.length; i++ ) {
			$( '<div>' ).attr( 'id', 's' + sourceUnits[i].identifier )
				.text( sourceUnits[i].definition )
				.appendTo( divSource );
		}
	}

	/**
	 * Disable edit, delete and swap icons when a translation unit is
	 * opened for editing.
	 */
	function disableOptions() {
		$( '.edit, .delete, .swap' ).addClass( 'disable' );
	}

	/**
	 * Enable the edit, delete and swap icons again when a translation unit
	 * is saved after having been opened for editing.
	 */
	function enableOptions() {
		$( '.edit, .delete, .swap' ).removeClass( 'disable' );
	}

	$( '#buttonSavePages' ).click( function() {
		var deferreds;

		if ( noOfSourceUnits !== noOfTranslationUnits ) {
			window.alert( 'Extra units might be present. Please match the source and translation units properly' );
			return;
		} else {
			deferreds = createTranslationPages();
			$( 'input' ).attr( 'disabled', 'disabled' );
			$.when.apply( null, deferreds ).done(function() {
				$( '#buttonImport' ).show();
				$( 'input' ).removeAttr( 'disabled' );
			});
		}
	} );

	$( '#buttonCancel' ).click( function() {
		$( '#buttonSavePages, #buttonCancel').hide();
		$( '#buttonImport' ).show();
		$( '#sourceunits, #translationunits, #actions' ).html( '' );
	} );

	$( document ).on( 'click', '.add', function() {
		var parentID, translationID;
		parentID = $( this ).parent().attr( 'id' );
		translationID = 't' + parentID.replace( 'a' , '' );
		$( '<div>' ).insertAfter( '#' + translationID );
		$( '<div>' ).append( $( '<span>' ).attr( 'class', 'edit' ),
			$( '<span>' ).attr( 'class', 'delete' ),
			$( '<span>' ).attr( 'class', 'swap' ),
			$( '<span>' ).attr( 'class', 'add' ) )
		.insertAfter( '#' + parentID );
		noOfTranslationUnits += 1;
		updateIDs();
	} );

	$( document ).on( 'click', '.delete', function() {
		var parentID, translationID;
		parentID = $( this ).parent().attr( 'id' );
		translationID = 't' + parentID.replace( 'a' , '' );
		$( '#' + translationID ).remove();
		$( this ).parent().remove();
		noOfTranslationUnits -= 1;
		addEmptyUnits();
		updateIDs();
	} );

	$( document ).on( 'click', '.save-edit', function() {
		var parentID, translationID;
		parentID = $( this ).parent().attr( 'id' );
		translationID = 't' + parentID.replace( 'a' , '' );
		$( '#' + translationID ).attr( 'contenteditable', 'false' );
		$( '#' + translationID ).css( 'background-color', '#FFFFFF' );
		$( this ).addClass( 'edit' ).removeClass( 'save-edit' );
		enableOptions();
	} );

	$( document ).on( 'click', '.edit', function() {
		var parentID, translationID;
		parentID = $( this ).parent().attr( 'id' );
		translationID = 't' + parentID.replace( 'a' , '' );
		$( '#' + translationID ).attr( 'contenteditable', 'true' );
		$( '#' + translationID ).css( 'background-color', '#FFF5F0' );
		$( this ).addClass( 'save-edit' ).removeClass( 'edit' );
		disableOptions();
	} );

	$ ( document ).on( 'click', '.swap', function() {
		var parentID, oldID, newID, tempData;
		parentID = $( this ).parent().attr( 'id' );
		oldID = Number( parentID.replace( 'a' , '' ) );
		newID = $ ( '#' + parentID ).next().attr( 'id' ).replace( 'a', '' );
		tempData = $( '#t' + oldID ).text();
		$( '#t' + oldID ).text( $( '#t' + newID ).text() );
		$( '#t' + newID ).text( tempData );
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
					updateIDs();
					$( '#buttonSavePages, #buttonCancel').show();
					$( '#buttonImport' ).hide();
				} );
			} );
		} );
	} );
} ( jQuery, mediaWiki ) );