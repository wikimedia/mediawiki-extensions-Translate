( function () {
	'use strict';

	var resultGroups;

	$( function () {
		resultGroups = $( '.facet.groups' ).data( 'facets' );

		$( '.tux-searchpage .mw-ui-button' ).on( 'click', function () {
			var query = $( '.tux-searchpage .searchinputbox' ).val(),
				result = lexOperators( query ),
				$form = $( '.tux-searchpage form[name=searchform]' );

			Object.keys( result ).forEach( function ( index ) {
				var $input = $( '<input>' ).prop( 'type', 'hidden' ),
					$elem = $form.find( 'input[name=' + index + ']' );

				if ( $elem.length ) {
					$elem.val( result[ index ] );
				} else {
					$form.append( $input
						.prop( {
							value: result[ index ],
							name: index
						} )
					);
				}
			} );
		} );

		buildSelectedBox();
		showLanguages();
		showMessageGroups();

		// Make the whole rows clickable
		$( '.tux-searchpage .row .facet-item' ).on( 'click', function ( event ) {
			window.location = $( this ).find( 'a' ).attr( 'href' );
			event.stopPropagation();
		} );
	} );

	// ES5-compatible Chrome, IE 9+, FF 4+, or Safari 5+ has Object.keys.
	// Make other old browsers happy
	if ( !Object.keys ) {
		Object.keys = function ( obj ) {
			var keys = [];
			for ( var k in obj ) {
				if ( Object.prototype.hasOwnProperty.call( obj, k ) ) {
					keys.push( k );
				}
			}
			return keys;
		};
	}

	function showLanguages() {
		var ulslanguages = [],
			selectedClasss = '',
			quickLanguageList = [],
			unique = [];

		var $languages = $( '.facet.languages' );
		var languages = $languages.data( 'facets' );
		var currentLanguage = $languages.data( 'language' );
		if ( !languages ) {
			return;
		}

		if ( currentLanguage !== '' ) {
			var uri = new mw.Uri( location.href );
			uri.extend( { language: '', filter: '' } );
			addToSelectedBox( getLanguageLabel( currentLanguage ), uri.toString() );
		}

		var resultCount = Object.keys( languages ).length;
		quickLanguageList = quickLanguageList.concat( mw.uls.getFrequentLanguageList() )
			.concat( Object.keys( languages ) );

		// Remove duplicates from the language list
		quickLanguageList.forEach( function ( lang ) {
			if ( languages[ lang ] && unique.indexOf( lang ) === -1 ) {
				unique.push( lang );
			}
		} );

		if ( currentLanguage && quickLanguageList.indexOf( currentLanguage ) >= 0 ) {
			quickLanguageList = unique.splice( 0, 5 );
			if ( quickLanguageList.indexOf( currentLanguage ) === -1 ) {
				quickLanguageList = quickLanguageList.concat( currentLanguage );
			}
		} else {
			quickLanguageList = unique.splice( 0, 6 );
		}

		quickLanguageList.sort( sortLanguages );

		for ( var i = 0; i <= quickLanguageList.length; i++ ) {
			var languageCode = quickLanguageList[ i ];
			var result = languages[ languageCode ];
			if ( !result ) {
				continue;
			}

			if ( currentLanguage === languageCode ) {
				selectedClasss = 'selected';
			} else {
				selectedClasss = '';
			}

			$languages.append( $( '<div>' )
				.addClass( 'row facet-item' )
				.append(
					$( '<span>' )
						// The following classes are used here:
						// * selected
						// * or no class
						.addClass( 'facet-name ' + selectedClasss )
						.append( $( '<a>' )
							.attr( 'href', result.url )
							.text( getLanguageLabel( languageCode ) )
						),
					$( '<span>' )
						.addClass( 'facet-count' )
						.text( result.count )
				)
			);
		}

		Object.keys( languages ).forEach( function ( lang ) {
			ulslanguages[ lang ] = mw.config.get( 'wgTranslateLanguages' )[ lang ];
		} );

		mw.translate.addExtraLanguagesToLanguageData( ulslanguages, [ 'SP' ] );

		if ( resultCount > 6 ) {
			var $ulsTrigger = $( '<a>' )
				.text( '...' )
				.addClass( 'translate-search-more-languages' );
			var $count = $( '<span>' )
				.addClass( 'translate-search-more-languages-info' )
				.text( mw.msg( 'translate-search-more-languages-info', resultCount - quickLanguageList.length ) );
			$languages.append( $ulsTrigger, $count );

			$ulsTrigger.uls( {
				onSelect: function ( language ) {
					window.location = languages[ language ].url;
				},
				compact: true,
				languages: ulslanguages,
				ulsPurpose: 'translate-special-searchtranslations',
				top: $languages.offset().top,
				showRegions: [ 'SP' ].concat( $.fn.lcd.defaults.showRegions )
			} );
		}
	}

	function showMessageGroups() {
		var $groups = $( '.facet.groups' );

		if ( !resultGroups ) {
			// No search results
			return;
		}

		var groupList = Object.keys( resultGroups );
		listGroups( groupList, undefined, $groups );
	}

	function listGroups( groupList, parentGrouppath, $parent, level ) {
		var selectedClass = '',
			maxListSize = 10,
			currentGroup = $( '.facet.groups' ).data( 'group' ),
			resultCount = groupList.length;

		level = level || 0;
		groupList.sort( sortGroups );
		if ( level === 0 ) {
			groupList = groupList.splice( 0, maxListSize );
		}
		var grouppath = getParameterByName( 'grouppath' ).split( '|' )[ 0 ];
		if ( currentGroup && resultGroups[ grouppath ] &&
			groupList.indexOf( grouppath ) < 0 &&
			level === 0
		) {
			// Make sure current selected group is displayed always.
			groupList = groupList.concat( grouppath );
		}
		groupList.sort( sortGroups );
		for ( var i = 0; i < groupList.length; i++ ) {
			var groupId = groupList[ i ];
			var group = findGroup( groupId, resultGroups );
			if ( !group ) {
				continue;
			}

			var uri = new mw.Uri( location.href );
			if ( parentGrouppath !== undefined ) {
				grouppath = parentGrouppath + '|' + groupId;
			} else {
				grouppath = groupId;
			}
			uri.extend( { group: groupId, grouppath: grouppath } );

			if ( currentGroup === groupId ) {
				selectedClass = 'selected';
				uri.extend( { group: '', grouppath: '' } );
				addToSelectedBox( group.label, uri.toString() );
			} else {
				selectedClass = '';
				uri.extend( { group: groupId, grouppath: grouppath } );
			}

			var $groupRow = $( '<div>' )
				// The following classes are used here:
				// * facet-level-0
				// * facet-level-1
				// * facet-level-2
				// * facet-level-3
				.addClass( 'row facet-item facet-level-' + level )
				.append(
					$( '<span>' )
						// Class name documented above
						.addClass( 'facet-name ' + selectedClass )
						.append( $( '<a>' )
							.attr( 'href', uri.toString() )
							.text( group.label )
						),
					$( '<span>' )
						.addClass( 'facet-count' )
						.text( mw.language.convertNumber( group.count ) )
				);
			$parent.append( $groupRow );
			if ( group.groups && level < 2 ) {
				listGroups( Object.keys( group.groups ), grouppath, $groupRow, level + 1 );
			}
		}

		if ( resultCount > maxListSize && resultCount - groupList.length > 0 && level === 0 ) {
			var $grouSelectorTrigger = $( '<div>' )
				.addClass( 'rowfacet-item ' )
				.append(
					$( '<a>' )
						.text( '...' )
						.addClass( 'translate-search-more-groups' ),
					$( '<span>' )
						.addClass( 'translate-search-more-groups-info' )
						.text( mw.msg( 'translate-search-more-groups-info',
							resultCount - groupList.length ) )
				);
			$parent.append( $grouSelectorTrigger );

			var position;
			if ( $( document.body ).hasClass( 'rtl' ) ) {
				position = {
					my: 'right top',
					at: 'right+90 top+40',
					collision: 'none'
				};
			} else {
				position = {
					my: 'left top',
					at: 'left-90 top+40',
					collision: 'none'
				};
			}
			var options = {
				language: mw.config.get( 'wgUserLanguage' ),
				position: position,
				onSelect: function ( selectedGroup ) {
					var currentUri = new mw.Uri( location.href );
					currentUri.extend( { group: selectedGroup.id, grouppath: selectedGroup.id } );
					location.href = currentUri.toString();
				},
				preventSelector: true
			};

			$grouSelectorTrigger.msggroupselector(
				options,
				Object.keys( resultGroups )
			);
		}
	}

	function lexOperators( str ) {
		var splitValues = str.split( ' ' ),
			result = {},
			query = '';

		splitValues.forEach( function ( string ) {
			matchOperators( string, function ( obj ) {
				if ( obj === false ) {
					query = query + ' ' + string;
				} else {
					result[ obj.operator ] = obj.value;
				}
			} );
		} );
		result.query = query.trim();

		return result;
	}

	function matchOperators( str, callback ) {
		var counter = false,
			// Add operators for different filters
			operatorRegex = [ 'language', 'group', 'filter' ];

		operatorRegex.forEach( function ( value ) {
			var regex = new RegExp( value + ':(\\S+)', 'i' );
			var matches;
			if ( ( matches = regex.exec( str ) ) !== null ) {
				counter = true;
				callback( {
					operator: value,
					value: matches[ 1 ]
				} );
			}
		} );
		if ( !counter ) {
			callback( false );
		}
	}

	function sortGroups( groupIdA, groupIdB ) {
		var groupAName = findGroup( groupIdA, resultGroups ).count,
			groupBName = findGroup( groupIdB, resultGroups ).count;

		if ( groupAName > groupBName ) {
			return -1;
		} else if ( groupAName < groupBName ) {
			return 1;
		}

		return 0;
	}

	/**
	 * Finds a specific group from a groups object containing nested groups.
	 *
	 * @param {string} targetGroupId
	 * @param {Object} groups
	 * @return {Object} Message group object, or null if group is not found
	 */
	function findGroup( targetGroupId, groups ) {
		var group = groups[ targetGroupId ], groupId;
		if ( group ) {
			return group;
		}

		for ( groupId in groups ) {
			if ( groups[ groupId ].groups ) {
				return findGroup( targetGroupId, groups[ groupId ].groups );
			}
		}

		return null;
	}

	function sortLanguages( languageA, languageB ) {
		var languageNameA = mw.config.get( 'wgULSLanguages' )[ languageA ] || languageA,
			languageNameB = mw.config.get( 'wgULSLanguages' )[ languageB ] || languageB;

		return languageNameA.localeCompare( languageNameB );
	}

	function getParameterByName( name ) {
		var uri = new mw.Uri();
		return uri.query[ name ] || '';
	}

	function getLanguageLabel( languageCode ) {
		return mw.config.get( 'wgULSLanguages' )[ languageCode ] || languageCode;
	}

	// Build a selected box to show the selected items
	function buildSelectedBox() {
		$( '.tux-search-inputs' )
			.removeClass( 'offset-by-three' )
			.before(
				$( '<div>' )
					.addClass( 'three columns tux-selectedbox' )
			);
	}

	function addToSelectedBox( label, url ) {
		$( '.tux-searchpage .tux-selectedbox' ).append( $( '<div>' )
			.addClass( 'row facet-item' )
			.append(
				$( '<span>' )
					.addClass( 'facet-name selected' )
					.append( $( '<a>' )
						.attr( 'href', url )
						.text( label )
					),
				$( '<span>' )
					.addClass( 'facet-count' )
					.text( 'X' )
			)
		);
	}
}() );
