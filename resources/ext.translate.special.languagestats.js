/**
 * Collapsing script for Special:LanguageStats in MediaWiki Extension:Translate
 * @author Krinkle <krinklemail (at) gmail (dot) com>
 * @author Niklas Laxström, 2012
 * @created January 3, 2011
 * @license GPL v2, CC-BY-SA-3.0
 */
/*global mw:false*/
jQuery( document ).ready( function( $ ) {
	"use strict";

	var
		$translateTable = $( '.mw-sp-translate-table' ),
		$metaRows = $( 'tr.AggregateMessageGroup', $translateTable );

	// Quick return
	if ( !$metaRows.size() ) {
		return;
	}

	$metaRows.each( function() {
		var
			$parent = $( this ),
			thisGroupId = $parent.attr( 'data-groupid' ),
			$children = $( 'tr[data-parentgroup="' + thisGroupId + '"]', $translateTable );

		// Only do the collapse stuff if this Meta-group actually has children on this page
		if ( !$children.size() ) {
			return;
		}

		// Build toggle link
		var $toggler = $( '<span class="groupexpander collapsed">[</span>' )
			.append( $( '<a href="#"></a>' )
			.text( mw.msg( 'translate-langstats-expand' ) ) )
			.append( ']' )
			.click( function( e ) {
				var $el = $( this );
				// Switch the state and toggle the rows
				if ( $el.hasClass( 'collapsed' ) ) {
					$children.fadeIn().trigger( 'show' );
					$el.removeClass( 'collapsed' ).addClass( 'expanded' );
					$el.find( '> a' ).text( mw.msg( 'translate-langstats-collapse' ) );
				} else {
					$children.fadeOut().trigger( 'hide' );
					$el.addClass( 'collapsed' ).removeClass( 'expanded' );
					$el.find( '> a' ).text( mw.msg( 'translate-langstats-expand' ) );
				}
				
				e.preventDefault();
			} );

		// Add the toggle link to the first cell of the meta group table-row
		$parent.find( ' > td:first' ).append( $toggler );
		
		// Handle hide/show recursively, so that collapsing parent group
		// hides all sub groups regardless of nesting level
		$parent.on( 'hide show', function( event ) {
			// Reuse $toggle, $parent and $children from parent scope
			if ( $toggler.hasClass( 'expanded' ) ) {
				$children.trigger( event.type )[event.type]();
			}
		} );
	} );

	// Create, bind and append the toggle-all button
	var
		$allChildRows = $( 'tr[data-parentgroup]', $translateTable ),
		$allToggles_cache = null,
		$toggleAllButton = $( '<span class="collapsed">[</span>' )
			.append( $( '<a href="#"></a>' )
			.text( mw.msg( 'translate-langstats-expandall' ) ) )
			.append( ']' )
			.click( function( e ) {
				var
					$el = $( this ),
					$allToggles = !!$allToggles_cache ? $allToggles_cache : $( '.groupexpander', $translateTable );
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
				
				e.preventDefault();
			} );

	// Initially hide them
	$allChildRows.hide();

	// Add the toggle-all button above the table
	$( '<p class="groupexpander-all"></p>' ).append( $toggleAllButton ).insertBefore( $translateTable );
} );

// When hovering a row, adjust brightness of the last two custom-colored cells as well
// See also translate.langstats.css for the highlighting for the other normal rows
mw.loader.using( 'jquery.colorUtil' , function() {
	"use strict";
	jQuery( document ).ready( function( $ ) {
		$( '.mw-sp-translate-table.wikitable tr' ).hover( function() {
			$( '> td.hover-color', this )
			// 30% more brightness
			.css( 'background-color', function( i, val ) {
				return $.colorUtil.getColorBrightness( val, +0.3 );
			} );
		}, function() {
			$( '> td.hover-color', this )
			// 30% less brightness
			.css( 'background-color', function( i, val ) {
				return $.colorUtil.getColorBrightness( val, -0.3 );
			} );
		} );
	} );
} );
