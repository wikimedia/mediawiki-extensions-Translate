( function () {
	'use strict';

	$( function () {
		// Hide the dropdown menu when clicking outside of it
		$( 'html' ).on( 'click', function ( e ) {
			if ( !e.isDefaultPrevented() ) {
				$( '.tux-dropdown-menu' ).addClass( 'hide' );
			}
		} );
	} );
}() );
