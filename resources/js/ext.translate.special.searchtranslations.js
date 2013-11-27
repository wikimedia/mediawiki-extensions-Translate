( function ( $, mw ) {
	'use strict';

	var resultGroups;

	$( document ).ready( function () {
		var $messages = $( '.tux-message' );

		resultGroups = $( '.facet.groups' ).data( 'facets' );

		// Make the whole rows clickable
		$( '.facet-item' ).click( function () {
			window.location = $( this ).find( 'a' ).attr( 'href' );
		} );

		$messages.each( function () {
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

		$messages.last().addClass( 'last-message' );

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
				compact: true,
				languages: ulslanguages,
				top: $languages.offset().top,
				showRegions: regions
			} );
		}
	}

	function showMessageGroups() {
		var currentGroup,
			groupList,
			$groups;

		$groups = $( '.facet.groups' );
		currentGroup = $groups.data( 'group' );

		if ( !resultGroups ) {
			// No search results
			return;
		}

		groupList = Object.keys( resultGroups );
		listGroups( groupList, currentGroup, $groups );
	}

	function listGroups( groupList, parentGroup, $parent, level ) {
		var i,
			$grouSelectorTrigger,
			selectedClass = '',
			group,
			groupId,
			$groupRow,
			uri,
			maxListSize = 10,
			currentGroup = $( '.facet.groups' ).data( 'group' ),
			resultCount = groupList.length;

		level = level || 0;
		groupList = groupList.splice( 0, maxListSize );
		if ( currentGroup && resultGroups[currentGroup] &&
			$.inArray( currentGroup, groupList ) < 0
		) {
			// Make sure current selected group is displayed always.
			groupList = groupList.concat( currentGroup );
			groupList.sort( sortGroups );
		}
		groupList.sort( sortGroups );
		for ( i = 0; i <= groupList.length; i++ ) {
			groupId = groupList[i];
			group = mw.translate.findGroup( groupId, resultGroups );
			if ( !group ) {
				continue;
			}
			if ( currentGroup === groupId ) {
				selectedClass = 'selected';
			} else {
				selectedClass = '';
			}

			uri = new mw.Uri( window.location.href );
			uri.extend( { 'group': groupId } );

			$groupRow = $( '<div>' )
				.addClass( 'row facet-item ' + ' facet-level-' + level )
				.append( $( '<span>' )
					.addClass( 'facet-name ' + selectedClass)
					.append( $( '<a>' )
						.attr( 'href', uri.toString() )
						.text( group.label )
					),
					$( '<span>' )
						.addClass( 'facet-count ' + selectedClass )
						.text( group.count )
				);
			$parent.append( $groupRow );
			if ( group.groups && level < 2 ) {
				listGroups( Object.keys( group.groups ), group, $groupRow, level + 1 );
			}
		}

		if ( resultCount > maxListSize ) {
			$grouSelectorTrigger = $( '<div>')
				.addClass( 'rowfacet-item ' )
				.append( $( '<a>' )
					.text( '...' )
					.addClass( 'translate-search-more-groups' ),
					$( '<span>' )
						.addClass( 'translate-search-more-groups-info' )
						.text( mw.msg( 'translate-search-more-groups-info',
							resultCount - groupList.length ) )
				);
			$parent.append( $grouSelectorTrigger );

			$grouSelectorTrigger.msggroupselector( {
				language: mw.config.get( 'wgUserLanguage' ),
				position: {
					my: 'left top',
					at: 'left top'
				},
				onSelect: function ( group ) {
					var uri = new mw.Uri( window.location.href );
					uri.extend( { 'group': group.id } );
					window.location.href = uri.toString();
				}
			} );
		}
	}

	function sortGroups( groupIdA, groupIdB ) {
		var groupAName = mw.translate.findGroup( groupIdA, resultGroups ).label,
			groupBName = mw.translate.findGroup( groupIdB, resultGroups ).label;

		return groupAName.localeCompare( groupBName );
	}

	function sortLanguages( languageA, languageB ) {
		var languageNameA = mw.config.get( 'wgULSLanguages' )[languageA] || languageA,
			languageNameB = mw.config.get( 'wgULSLanguages' )[languageB] || languageB;

		return languageNameA.localeCompare( languageNameB );
	}
}( jQuery, mediaWiki ) );
