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
		showMessageGroups();
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
			resultCount = 0,
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

		resultCount = Object.keys(languages).length;

		// If a documentation pseudo-language is defined,
		// add it to the language selector
		docLanguageCode = mw.config.get( 'wgTranslateDocumentationLanguageCode' );
		if ( languages[docLanguageCode] ) {
			mw.translate.addDocumentationLanguage();
			window.wgULSLanguages[docLanguageCode] = mw.msg( 'translate-documentation-language' );
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
						.text( window.wgULSLanguages[languageCode] || languageCode )
					),
					$( '<span>')
						.addClass('facet-count')
						.text( result.count )
				)
			);
		}

		$.each( Object.keys( languages ), function( index, languageCode) {
			ulslanguages[languageCode] = window.wgULSLanguages[languageCode];
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
				compact: true,
				languages: ulslanguages,
				top: $languages.offset().top,
				showRegions: regions
			} );
		}
	}

	function showMessageGroups() {
		var $grouSelectorTrigger,
			selectedClasss = '',
			currentGroup,
			resultCount,
			$count,
			i,
			group,
			groupId,
			groupList,
			$groups;

		$groups = $( '.facet.groups' );
		currentGroup = $groups.data( 'group' );

		mw.translate.messageGroups = $groups.data( 'facets' );

		groupList = Object.keys( mw.translate.messageGroups );
		resultCount = groupList.length;

		if ( currentGroup && $.inArray( currentGroup, groupList ) < 0 ) {
			groupList = groupList.splice( 0, 5 );
			groupList = groupList.concat( currentGroup );
			groupList.sort( sortGroups );
		} else {
			groupList = groupList.splice( 0, 6 );
		}
		groupList.sort( sortGroups );
		for ( i = 0; i <= groupList.length; i++ ) {
			groupId = groupList[i];
			group = mw.translate.messageGroups[groupId];
			if ( !group ) {
				continue;
			}
			if ( currentGroup === groupId ) {
				selectedClasss = 'selected';
			} else {
				selectedClasss = '';
			}

			$groups.append( $( '<div>')
				.addClass( 'row facet-item ' + selectedClasss )
				.append( $( '<span>')
					.addClass('facet-name')
					.append( $('<a>')
						.attr( {
							href: group.url,
							title: group.description
						} )
						.text( group.label )
					),
					$( '<span>')
						.addClass('facet-count')
						.text( group.count )
				)
			);
		}

		if ( resultCount > 6 ) {
			$grouSelectorTrigger = $( '<a>' )
				.text( '...' )
				.addClass( 'translate-search-more-groups' );

			$count = $( '<span>' )
				.addClass( 'translate-search-more-groups-info' )
				.text( mw.msg( 'translate-search-more-groups-info', resultCount - groupList.length ) );
			$groups.append( $grouSelectorTrigger, $count );

			$grouSelectorTrigger.msggroupselector( {
				onSelect: function ( group ) {
					window.location = group.url;
				}
			} );
		}
	}

	function sortGroups ( groupIdA, groupIdB ) {
		var groupAName = mw.translate.messageGroups[groupIdA].label,
			groupBName = mw.translate.messageGroups[groupIdB].label;

		return groupAName.localeCompare( groupBName );
	}

	function sortLanguages ( languageA, languageB ) {
		var languageNameA = window.wgULSLanguages[languageA] || languageA,
			languageNameB = window.wgULSLanguages[languageB] || languageB;

		return languageNameA.localeCompare( languageNameB );
	}
}( jQuery, mediaWiki ) );
