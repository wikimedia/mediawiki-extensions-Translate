/**
 * Collapsing script for Special:LanguageStats in MediaWiki Extension:Translate
 * @author Krinkle <krinklemail (at) gmail (dot) com>
 * @created January 3, 2011
 * @license GPL v2, CC-BY-SA-3.0
 */
var MSGexpand = 'expand', MSGcollapse = 'collapse', MSGexpandall = 'expand all', MSGcollapseall = 'collapse all';
jQuery( document ).ready( function() {
	if ( mw.config.get( 'wgPageName' ) == 'Special:LanguageStats' ) {

		var	$translateTable =  $( '.mw-sp-translate-table' ),
			$metaRows = $( 'tr[data-ismeta=1]', $translateTable );

		// Only do stuff if there are any meta group rows on this pages
		if ( $metaRows.size() ) {

			var $allChildRows = $( 'tr[data-parentgroups]', $translateTable ),
				$toggleAllButton = $( '<span class="mw-sp-langstats-expander">[<a href="#" onclick="return false;">' + MSGexpandall + '</a>]</span>' ).click( function() {
					var	$el = $( this ),
						$allToggles = $( '.mw-sp-langstats-toggle', $translateTable );
					// Switch the state and toggle the rows
					// and update the local toggles too
					if ( $el.hasClass( 'mw-sp-langstats-expander' ) ) {
						$allChildRows.fadeIn();
						$el.add( $allToggles ).removeClass( 'mw-sp-langstats-expander' ).addClass( 'mw-sp-langstats-collapser' )
						$el.find( '> a' ).text( MSGcollapseall );
						$allToggles.find( '> a' ).text( MSGcollapse );
					} else {
						$allChildRows.fadeOut();
						$el.add( $allToggles ).addClass( 'mw-sp-langstats-expander' ).removeClass( 'mw-sp-langstats-collapser' )
						$el.find( '> a' ).text( MSGexpandall );
						$allToggles.find( '> a' ).text( MSGexpand );
					}
				} );

			// Initially hide them
			$allChildRows.hide();

			// Add the toggle-all button above the table
			$( '<p class="mw-sp-langstats-toggleall"></p>' ).append( $toggleAllButton ).insertBefore( $translateTable );

			$metaRows.each( function() {
				// Get info and cache selectors
				var	$thisGroup = $(this),
					thisGroupId = $thisGroup.attr( 'data-groupid' ),
					$thisChildRows = $( 'tr[data-parentgroups~="' + thisGroupId + '"]', $translateTable );

				// Only do the collapse stuff if this Meta-group actually has children on this page
				if ( $thisChildRows.size() ) {

					// Build toggle link
					var $toggler = $( '<span class="mw-sp-langstats-toggle mw-sp-langstats-expander">[<a href="#" onclick="return false;">' + MSGexpand + '</a>]</span>' ).click( function() {
						var $el = $( this );
						// Switch the state and toggle the rows
						if ( $el.hasClass( 'mw-sp-langstats-expander' ) ) {
							$thisChildRows.fadeIn();
							$el.removeClass( 'mw-sp-langstats-expander' ).addClass( 'mw-sp-langstats-collapser' )
							   .find( '> a' ).text( MSGcollapse );
						} else {
							$thisChildRows.fadeOut();
							$el.addClass( 'mw-sp-langstats-expander' ).removeClass( 'mw-sp-langstats-collapser' )
							   .find( '> a' ).text( MSGexpand );
						}
					} );

					// Add the toggle link to the first cell of the meta group table-row
					$thisGroup.find( ' > td:first' ).append( $toggler );
				}
			} );
		}
	}
} );
// @TODO: Create the following messages "translate-expand-all" (new), "translate-collapse-all" (new), "translate-expand" ({{Identical|collapsible-expand}}) and "translate-collapse" ({{Identical|collapsible-collapse}})
// @TODO: Load this script via ResourceLoader and pass the those 4 messages
// @TODO: Replace hardcoded messages with mw.msg('');