( function ( $, mw ) {
	'use strict';

	var resultGroups;

	$( document ).ready( function () {
		var $messages = $( '.tux-message' );

		resultGroups = $( '.facet.groups' ).data( 'facets' );

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

		$( '.tux-searchpage .button' ).click( function () {
			var query = $( '.tux-searchpage .searchinputbox' ).val(),
				result = lexOperators( query ),
				$form = $( '.tux-searchpage form[name=searchform]' );

			$.each( result, function ( index, value ) {
				var $input = $( '<input>' )
					.prop( 'type', 'hidden' ),
					$elem = $form.find( 'input[name=' + index + ']' );

				if ( $elem.length ) {
					$elem.val( value );
				} else {
					$form.append( $input
						.prop( {
							value: value,
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
		$( '.tux-searchpage .row .facet-item' ).click( function ( event ) {
			window.location = $( this ).find( 'a' ).attr( 'href' );
			event.stopPropagation();
		} );
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
			$ulsTrigger,
			uri;

		$languages = $( '.facet.languages' );
		languages = $languages.data( 'facets' );
		currentLanguage = $languages.data( 'language' );
		if ( !languages ) {
			return;
		}

		if ( currentLanguage !== '' ) {
			uri = new mw.Uri( location.href );
			uri.extend( { language: '', filter: '' } );
			addToSelectedBox( getLanguageLabel( currentLanguage ), uri.toString() );
		}

		resultCount = Object.keys( languages ).length;

		// If a documentation pseudo-language is defined,
		// add it to the language selector
		docLanguageCode = mw.config.get( 'wgTranslateDocumentationLanguageCode' );
		if ( languages[ docLanguageCode ] ) {
			mw.translate.addDocumentationLanguage();
			mw.config.get( 'wgULSLanguages' )[ docLanguageCode ] = mw.msg( 'translate-documentation-language' );
			regions = [ 'WW', 'SP', 'AM', 'EU', 'ME', 'AF', 'AS', 'PA' ];
		}

		quickLanguageList = quickLanguageList.concat( mw.uls.getFrequentLanguageList() )
			.concat( Object.keys( languages ) );

		// Remove duplicates from the language list
		$.each( quickLanguageList, function ( i, v ) {
			result = languages[ v ];
			if ( result && $.inArray( v, unique ) === -1 ) {
				unique.push( v );
			}
		} );

		if ( currentLanguage && $.inArray( currentLanguage, quickLanguageList ) >= 0 ) {
			quickLanguageList = unique.splice( 0, 5 );
			if ( $.inArray( currentLanguage, quickLanguageList ) === -1 ) {
				quickLanguageList = quickLanguageList.concat( currentLanguage );
			}
		} else {
			quickLanguageList = unique.splice( 0, 6 );
		}

		quickLanguageList.sort( sortLanguages );

		for ( i = 0; i <= quickLanguageList.length; i++ ) {
			languageCode = quickLanguageList[ i ];
			result = languages[ languageCode ];
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
				.append( $( '<span>' )
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

		$.each( Object.keys( languages ), function ( index, languageCode ) {
			ulslanguages[ languageCode ] = mw.config.get( 'wgULSLanguages' )[ languageCode ];
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
					window.location = languages[ language ].url;
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

		if ( !resultGroups ) {
			// No search results
			return;
		}

		groupList = Object.keys( resultGroups );
		listGroups( groupList, currentGroup, $groups );
	}

	function listGroups( groupList, parentGrouppath, $parent, level ) {
		var i,
			$grouSelectorTrigger,
			selectedClass = '',
			group,
			groupId,
			$groupRow,
			uri,
			maxListSize = 10,
			currentGroup = $( '.facet.groups' ).data( 'group' ),
			resultCount = groupList.length,
			position,
			groups,
			options,
			grouppath;

		level = level || 0;
		groupList.sort( sortGroups );
		if ( level === 0 ) {
			groupList = groupList.splice( 0, maxListSize );
		}
		grouppath = getParameterByName( 'grouppath' ).split( '|' )[ 0 ];
		if ( currentGroup && resultGroups[ grouppath ] &&
			$.inArray( grouppath, groupList ) < 0 &&
			level === 0
		) {
			// Make sure current selected group is displayed always.
			groupList = groupList.concat( grouppath );
		}
		groupList.sort( sortGroups );
		for ( i = 0; i < groupList.length; i++ ) {
			groupId = groupList[ i ];
			group = mw.translate.findGroup( groupId, resultGroups );
			if ( !group ) {
				continue;
			}

			uri = new mw.Uri( location.href );
			if ( parentGrouppath !== undefined ) {
				grouppath = parentGrouppath + '|' + groupId;
			} else {
				grouppath =  groupId;
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

			$groupRow = $( '<div>' )
				.addClass( 'row facet-item ' + ' facet-level-' + level )
				.append( $( '<span>' )
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
			$grouSelectorTrigger = $( '<div>' )
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

			if ( $( 'body' ).hasClass( 'rtl' ) ) {
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
			options = {
				language: mw.config.get( 'wgUserLanguage' ),
				position: position,
				onSelect: function ( group ) {
					var uri = new mw.Uri( location.href );
					uri.extend( { group: group.id, grouppath: group.id } );
					location.href = uri.toString();
				},
				preventSelector: true
			};
			groups = $.map( resultGroups, function ( value, index ) {
				return index;
			} );
			$grouSelectorTrigger.msggroupselector(
				options,
				groups
			);
		}
	}

	function lexOperators( str ) {
		var string = str.split( ' ' ),
			result = {},
			query = '';

		$.each( string, function ( index, value ) {
			matchOperators( value, function ( obj ) {
				if ( obj === false ) {
					query = query + ' ' + value;
				} else {
					result[ obj.operator ] = obj.value;
				}
			} );
		} );
		result.query = query.trim();

		return result;
	}

	function matchOperators( str, callback ) {
		var matches,
			counter = false,
			// Add operators for different filters
			operatorRegex = [ 'language', 'group', 'filter' ];

		$.each( operatorRegex, function ( index, value ) {
			var regex = new RegExp( value + ':(\\S+)', 'i' );
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
		var groupAName = mw.translate.findGroup( groupIdA, resultGroups ).count,
			groupBName = mw.translate.findGroup( groupIdB, resultGroups ).count;

		if ( groupAName > groupBName ) {
			return -1;
		} else if ( groupAName < groupBName ) {
			return 1;
		}

		return 0;
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
			.append( $( '<span>' )
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
}( jQuery, mediaWiki ) );
