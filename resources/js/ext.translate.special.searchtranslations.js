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
			languageCount = 0,
			resultCount = 0,
			$count,
			result,
			i,
			languageCode,
			quickLanguageList = [],
			unique = [],
			$ulsTrigger;

		$languages = $( '.facet.languages' );
		languages = $languages.data( 'facets' );

		if ( !languages ) {
			return;
		}

		resultCount = Object.keys(languages).length;

		if ( $languages.data( 'language' ) ) {
			languageCount++;
			quickLanguageList = [ $languages.data( 'language' ) ];
		}

		quickLanguageList = quickLanguageList.concat( mw.uls.getFrequentLanguageList() )
			.concat( Object.keys( languages ) );

		// Remove duplicates from the language list
		$.each( quickLanguageList, function ( i, v ) {
			if ( $.inArray( v, unique ) === -1 ) {
				unique.push( v );
			}
		} );

		quickLanguageList = unique;

		for ( i = 0; i <= quickLanguageList.length; i++ ) {
			languageCode = quickLanguageList[i],
			result = languages[languageCode];

			if ( !result ) {
				continue;
			}

			$languages.append( $( '<div>')
				.addClass( 'row facet-item' )
				.append( $( '<span>')
					.addClass('facet-name')
					.append( $('<a>')
						.attr( 'href', result.url )
						.text( window.wgULSLanguages[languageCode] )
					),
					$( '<span>')
						.addClass('facet-count')
						.text( result.count )
				)
			);

			if ( languageCount === 6 ) {
				break;
			}

			languageCount++;
		}

		$.each( Object.keys( languages ), function( index, languageCode) {
			languages[languageCode] =  window.wgULSLanguages[languageCode];
		} );

		if ( languageCount !== resultCount ) {
			$ulsTrigger = $( '<a>' )
				.text( '...' )
				.addClass( 'translate-search-more-languages' );
			$count = $( '<span>' )
				.addClass( 'translate-search-more-languages-info' )
				.text( mw.msg( 'translate-search-more-languages-info', resultCount - languageCount ) );
			$languages.append( $ulsTrigger, $count );

			$ulsTrigger.uls( {
				onSelect: function ( language ) {
					window.location = languages[language].url;
				},
				lazyload: false,
				languages: languages,
				top: $languages.offset().top
			} );
		}
	}
}( jQuery, mediaWiki ) );
