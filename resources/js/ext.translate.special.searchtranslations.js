( function ( $, mw ) {
	'use strict';

	$( document ).ready( function () {
		// Make the whole rows clickable
		$( '.facet-item' ).click( function () {
			window.location = $( this ).find( 'a' ).attr( 'href' );
		} );

		$( '.tux-message' ).each( function () {
			var $this = $( this );

			$this.translateeditor( {
				message: {
					title: $this.data( 'title' ),
					definition: $this.data( 'definition' ),
					translation: $this.data( 'translation' ),
					group: $this.data( 'group' )
				}
			} );
		} );

		showLanguages();

	} );

	// ES5-compatible Chrome, IE 9+, FF 4+, or Safari 5+ has Object.keys.
	// Make other old browsers happy
	if ( !Object.keys ) {
		Object.keys = function ( obj ) {
			var keys = [],
				k;
			for ( k in obj ) {
				if ( Object.prototype.hasOwnProperty.call( obj, k ) ) {
					keys.push( k );
				}
			}
			return keys;
		};
	}

	function showLanguages() {
		var $languages,
			languages,
			ulslanguages = [],
			currentLanguage,
			resultCount,
			$count,
			result,
			i,
			selectedClasss = '',
			docLanguageCode,
			languageCode,
			quickLanguageList = [],
			unique = [],
			regions,
			$ulsTrigger;

		$languages = $( '.facet.languages' );
		languages = $languages.data( 'facets' );
		currentLanguage = $languages.data( 'language' );
		if ( !languages ) {
			return;
		}

		resultCount = Object.keys( languages ).length;

		// If a documentation pseudo-language is defined,
		// add it to the language selector
		docLanguageCode = mw.config.get( 'wgTranslateDocumentationLanguageCode' );
		if ( languages[docLanguageCode] ) {
			mw.translate.addDocumentationLanguage();
			mw.config.get( 'wgULSLanguages' )[docLanguageCode] = mw.msg( 'translate-documentation-language' );
			regions = ['WW', 'SP', 'AM', 'EU', 'ME', 'AF', 'AS', 'PA'];
		}

		quickLanguageList = quickLanguageList.concat( mw.uls.getFrequentLanguageList() )
			.concat( Object.keys( languages ) );

		// Remove duplicates from the language list
		$.each( quickLanguageList, function ( i, v ) {
			result = languages[v];
			if ( result && $.inArray( v, unique ) === -1 ) {
				unique.push( v );
			}
		} );

		if ( currentLanguage && $.inArray( currentLanguage, quickLanguageList ) >= 0 ) {
			quickLanguageList = unique.splice( 0, 5 );
			quickLanguageList = quickLanguageList.concat( currentLanguage );
		} else {
			quickLanguageList = unique.splice( 0, 6 );
		}

		quickLanguageList.sort( sortLanguages );

		for ( i = 0; i <= quickLanguageList.length; i++ ) {
			languageCode = quickLanguageList[i];
			result = languages[languageCode];
			if ( !result ) {
				continue;
			}

			if ( currentLanguage === languageCode ) {
				selectedClasss = 'selected';
			} else {
				selectedClasss = '';
			}

			$languages.append( $( '<div>')
				.addClass( 'row facet-item ' + selectedClasss )
				.append( $( '<span>')
					.addClass('facet-name')
					.append( $('<a>')
						.attr( 'href', result.url )
						.text( mw.config.get( 'wgULSLanguages' )[languageCode] || languageCode )
					),
					$( '<span>')
						.addClass('facet-count')
						.text( result.count )
				)
			);
		}

		$.each( Object.keys( languages ), function ( index, languageCode ) {
			ulslanguages[languageCode] = mw.config.get( 'wgULSLanguages' )[languageCode];
		} );

		if ( resultCount > 6 ) {
			$ulsTrigger = $( '<a>' )
				.text( '...' )
				.addClass( 'translate-search-more-languages' );
			$count = $( '<span>' )
				.addClass( 'translate-search-more-languages-info' )
				.text( mw.msg( 'translate-search-more-languages-info', resultCount - quickLanguageList.length ) );
			$languages.append( $ulsTrigger, $count );

			$ulsTrigger.uls( {
				onSelect: function ( language ) {
					window.location = languages[language].url;
				},
				lazyload: false,
				languages: ulslanguages,
				top: $languages.offset().top,
				showRegions: regions
			} );
		}
	}

	function sortLanguages( languageA, languageB ) {
		var languageNameA = mw.config.get( 'wgULSLanguages' )[languageA] || languageA,
			languageNameB = mw.config.get( 'wgULSLanguages' )[languageB] || languageB;

		return languageNameA.localeCompare( languageNameB );
	}
}( jQuery, mediaWiki ) );
