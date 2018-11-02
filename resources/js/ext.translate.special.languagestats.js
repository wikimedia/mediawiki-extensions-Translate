/*!
 * Collapsing script for Special:LanguageStats in MediaWiki Extension:Translate
 * @author Krinkle <krinklemail (at) gmail (dot) com>
 * @author Niklas Laxström
 * @license GPL-2.0-or-later, CC-BY-SA-3.0
 */

( function () {
	'use strict';

	/**
	 * Add css class to every other visible row.
	 * It's not possible to do zebra colors with CSS only if there are hidden rows.
	 */
	function doZebra() {
		$( '.statstable tr:visible:odd' ).toggleClass( 'tux-statstable-even', false );
		$( '.statstable tr:visible:even' ).toggleClass( 'tux-statstable-even', true );
	}

	$( function () {
		var $allChildRows, $allTogglesCache, $toggleAllButton,
			$translateTable = $( '.statstable' ),
			$metaRows = $( 'tr.AggregateMessageGroup', $translateTable );

		// Quick return
		if ( !$metaRows.length ) {
			return;
		}

		$metaRows.each( function () {
			var $toggler,
				$parent = $( this ),
				thisGroupId = $parent.attr( 'data-groupid' ),
				$children = $( 'tr[data-parentgroup="' + thisGroupId + '"]', $translateTable );

			// Only do the collapse stuff if this Meta-group actually has children on this page
			if ( !$children.length ) {
				return;
			}

			// Build toggle link
			$toggler = $( '<span class="groupexpander collapsed">[</span>' )
				.append( $( '<a href="#"></a>' )
					.text( mw.msg( 'translate-langstats-expand' ) ) )
				.append( ']' )
				.click( function ( e ) {
					var $el = $( this );
					// Switch the state and toggle the rows
					if ( $el.hasClass( 'collapsed' ) ) {
						$children.fadeIn( { start: doZebra } ).trigger( 'show' );
						$el.removeClass( 'collapsed' ).addClass( 'expanded' );
						$el.find( '> a' ).text( mw.msg( 'translate-langstats-collapse' ) );
					} else {
						$children.fadeOut( { done: doZebra } ).trigger( 'hide' );
						$el.addClass( 'collapsed' ).removeClass( 'expanded' );
						$el.find( '> a' ).text( mw.msg( 'translate-langstats-expand' ) );
					}

					e.preventDefault();
				} );

			// Add the toggle link to the first cell of the meta group table-row
			$parent.find( ' > td:first' ).append( $toggler );

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
		$allChildRows = $( 'tr[data-parentgroup]', $translateTable );
		$allTogglesCache = null;
		$toggleAllButton = $( '<span class="collapsed">[</span>' )
			.append( $( '<a href="#"></a>' )
				.text( mw.msg( 'translate-langstats-expandall' ) ) )
			.append( ']' )
			.click( function ( e ) {
				var $el = $( this ),
					$allToggles = $allTogglesCache || $( '.groupexpander', $translateTable );

				// Switch the state and toggle the rows
				// and update the local toggles too
				if ( $el.hasClass( 'collapsed' ) ) {
					$allChildRows.show();
					$el.add( $allToggles ).removeClass( 'collapsed' ).addClass( 'expanded' );
					$el.find( '> a' ).text( mw.msg( 'translate-langstats-collapseall' ) );
					$allToggles.find( '> a' ).text( mw.msg( 'translate-langstats-collapse' ) );
				} else {
					$allChildRows.hide();
					$el.add( $allToggles ).addClass( 'collapsed' ).removeClass( 'expanded' );
					$el.find( '> a' ).text( mw.msg( 'translate-langstats-expandall' ) );
					$allToggles.find( '> a' ).text( mw.msg( 'translate-langstats-expand' ) );
				}

				doZebra();
				e.preventDefault();
			} );

		// Initially hide them
		$allChildRows.hide();
		doZebra();

		// Add the toggle-all button above the table
		$( '<p class="groupexpander-all"></p>' ).append( $toggleAllButton ).insertBefore( $translateTable );
	} );

	$( function () {
		var index,
			sort = {},
			re = /#sortable:(\d+)=(asc|desc)/,
			match = re.exec( window.location.hash ),
			$tables = $( '.statstable' );

		if ( match ) {
			index = parseInt( match[ 1 ], 10 );
			sort[ index ] = match[ 2 ];
		}
		$tables.tablesorter( { sortList: [ sort ] } );

		$tables.on( 'sortEnd.tablesorter', function () {
			var $table = $( this );
			$table.find( '.headerSortDown, .headerSortUp' ).each( function () {
				var index = $table.find( 'th' ).index( $( this ) ),
					dir = $( this ).hasClass( 'headerSortUp' ) ? 'asc' : 'desc';
				window.location.hash = 'sortable:' + index + '=' + dir;

				doZebra();
			} );
		} );
	} );
}() );
