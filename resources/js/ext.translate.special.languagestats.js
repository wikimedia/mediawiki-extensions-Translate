/*!
 * Collapsing script for Special:LanguageStats in MediaWiki Extension:Translate
 * @author Krinkle <krinklemail (at) gmail (dot) com>
 * @author Niklas Laxström
 * @license GPL-2.0-or-later, CC-BY-SA-3.0
 */

( function () {
	'use strict';

	var $columns;

	/**
	 * Add css class to every other visible row.
	 * It's not possible to do zebra colors with CSS only if there are hidden rows.
	 */
	function doZebra( $table ) {
		$table.find( 'tr:visible:odd' ).toggleClass( 'tux-statstable-even', false );
		$table.find( 'tr:visible:even' ).toggleClass( 'tux-statstable-even', true );
	}

	function addExpanders( $table ) {
		var $allChildRows, $allTogglesCache, $toggleAllButton,
			$metaRows = $( 'tr.AggregateMessageGroup', $table );

		// Quick return
		if ( !$metaRows.length ) {
			return;
		}

		$metaRows.each( function () {
			var $toggler,
				$parent = $( this ),
				thisGroupId = $parent.attr( 'data-groupid' ),
				$children = $( 'tr[data-parentgroup="' + thisGroupId + '"]', $table );

			// Only do the collapse stuff if this Meta-group actually has children on this page
			if ( !$children.length ) {
				return;
			}

			// Build toggle link
			$toggler = $( '<span>' ).addClass( 'groupexpander collapsed' )
				.append(
					'[',
					$( '<a>' )
						.attr( 'href', '#' )
						.text( mw.msg( 'translate-langstats-expand' ) ),
					']'
				)
				.on( 'click', function ( e ) {
					var $el = $( this );
					// Switch the state and toggle the rows
					if ( $el.hasClass( 'collapsed' ) ) {
						$children.removeClass( 'statstable-hide' ).trigger( 'show' );
						doZebra( $table );
						$el.removeClass( 'collapsed' ).addClass( 'expanded' );
						$el.find( '> a' ).text( mw.msg( 'translate-langstats-collapse' ) );
					} else {
						$children.addClass( 'statstable-hide' ).trigger( 'hide' );
						doZebra( $table );
						$el.addClass( 'collapsed' ).removeClass( 'expanded' );
						$el.find( '> a' ).text( mw.msg( 'translate-langstats-expand' ) );
					}

					e.preventDefault();
				} );

			// Add the toggle link to the first cell of the meta group table-row
			$parent.find( ' > td' ).first().append( $toggler );

			// Handle hide/show recursively, so that collapsing parent group
			// hides all sub groups regardless of nesting level
			$parent.on( 'hide show', function ( event ) {
				// Reuse $toggle, $parent and $children from parent scope
				if ( $toggler.hasClass( 'expanded' ) ) {
					$children.trigger( event.type )[ event.type ]();
				}
			} );
		} );

		// Create, bind and append the toggle-all button
		$allChildRows = $( 'tr[data-parentgroup]', $table );
		$allTogglesCache = null;
		$toggleAllButton = $( '<span>' ).addClass( 'collapsed' )
			.append(
				'[',
				$( '<a>' )
					.attr( 'href', '#' )
					.text( mw.msg( 'translate-langstats-expandall' ) ),
				']'
			)
			.on( 'click', function ( e ) {
				var $el = $( this ),
					$allToggles = $allTogglesCache || $( '.groupexpander', $table );

				// Switch the state and toggle the rows
				// and update the local toggles too
				if ( $el.hasClass( 'collapsed' ) ) {
					$allChildRows.removeClass( 'statstable-hide' );
					$el.add( $allToggles ).removeClass( 'collapsed' ).addClass( 'expanded' );
					$el.find( '> a' ).text( mw.msg( 'translate-langstats-collapseall' ) );
					$allToggles.find( '> a' ).text( mw.msg( 'translate-langstats-collapse' ) );
				} else {
					$allChildRows.addClass( 'statstable-hide' );
					$el.add( $allToggles ).addClass( 'collapsed' ).removeClass( 'expanded' );
					$el.find( '> a' ).text( mw.msg( 'translate-langstats-expandall' ) );
					$allToggles.find( '> a' ).text( mw.msg( 'translate-langstats-expand' ) );
				}

				doZebra( $table );
				e.preventDefault();
			} );

		// Initially hide them
		$allChildRows.addClass( 'statstable-hide' );
		doZebra( $table );

		// Add the toggle-all button above the table
		$( '<p>' ).addClass( 'groupexpander-all' ).append( $toggleAllButton ).insertBefore( $table );
	}

	function applySorting( $table ) {
		var index,
			sort = {},
			re = /#sortable:(\d+)=(asc|desc)/,
			match = re.exec( location.hash );

		if ( match ) {
			index = parseInt( match[ 1 ], 10 );
			sort[ index ] = match[ 2 ];
		}
		$table.tablesorter( { sortList: [ sort ] } );

		$table.on( 'sortEnd.tablesorter', function () {
			$table.find( '.headerSortDown, .headerSortUp' ).each( function () {
				var headerIndex = $table.find( 'th' ).index( $( this ) ),
					dir = $( this ).hasClass( 'headerSortUp' ) ? 'asc' : 'desc';
				location.hash = 'sortable:' + headerIndex + '=' + dir;

				doZebra( $table );
			} );
		} );
	}

	function narrowTable( $table, enable ) {
		var $select,
			labelColumnCount = 1,
			// 0-indexed
			defaultValueColumn = 2;

		if ( $columns === undefined ) {
			$columns = $table.find( 'thead > tr > th ' ).map( function ( index, value ) {
				return value.textContent;
			} );
		}

		$select = makeValueColumnSelector( $columns, labelColumnCount, defaultValueColumn );
		// Prevent table sorter from making the select inaccessible
		$select.on( 'mousedown click', function ( e ) {
			e.stopPropagation();
		} ).on( 'change', function () {
			showValueColumn( $table, $select, labelColumnCount );
		} );

		if ( enable ) {
			showValueColumn( $table, $select, labelColumnCount );
		} else {
			// Restore original headings
			$table.find( 'thead > tr > th' ).map( function ( index ) {
				return $( this ).text( $columns[ index ] );
			} );
			$table.find( 'tr > *' ).removeClass( 'statstable-hide' );
		}

	}

	function makeValueColumnSelector( headings, skip, def ) {
		var i, $select = $( '<select>' );

		for ( i = skip; i < headings.length; i++ ) {
			$( '<option>' )
				.text( headings[ i ] )
				.val( i )
				.prop( 'selected', i === def )
				.appendTo( $select );
		}

		return $select;
	}

	function showValueColumn( $table, $select, skip ) {
		var i, index, cssQuery;

		index = parseInt( $select.val(), 10 );
		cssQuery = 'th:nth-child(_)'.replace( '_', index + 1 );
		$table.find( cssQuery ).html( $select );

		for ( i = 0; i < $select.children().length; i++ ) {
			cssQuery = 'tr > *:nth-child(_)'.replace( '_', i + skip + 1 );
			$table.find( cssQuery ).toggleClass( 'statstable-hide', i + skip !== index );
		}
	}

	$( function () {
		var isNarrowMode, minimumTableWidth,
			$table = $( '.statstable' );

		// Sometimes the table is not present on the page
		if ( !$table.length ) {
			return;
		}

		// Calculate absolute minimum table width
		if ( window.ResizeObserver ) {
			$table.css( 'max-width', '1px' );
		}

		applySorting( $table );
		addExpanders( $table );

		if ( !window.ResizeObserver ) {
			return;
		}

		// Hopefully previous stuff have time to render by now to have accurate picture of the width
		( window.requestAnimationFrame || setTimeout )( function () {
			minimumTableWidth = $table.outerWidth();
			$table.css( 'max-width', '' );
		} );

		new ResizeObserver( function ( entries ) {
			var newMode, shouldCollapse, shouldExpand;

			shouldCollapse = entries[ 0 ].contentRect.width < minimumTableWidth;
			// Some fudge to avoid flapping
			shouldExpand = entries[ 0 ].contentRect.width - 20 > minimumTableWidth;

			if ( isNarrowMode && shouldExpand ) {
				newMode = false;
			} else if ( !isNarrowMode && shouldCollapse ) {
				newMode = true;
			} else {
				newMode = isNarrowMode;
			}

			if ( newMode !== isNarrowMode ) {
				isNarrowMode = newMode;
				narrowTable( $table, isNarrowMode );
			}
		} ).observe( $table.parent().get( 0 ) );
	} );
}() );
