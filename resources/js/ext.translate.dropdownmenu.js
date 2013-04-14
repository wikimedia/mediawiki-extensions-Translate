( function () {
	$( document ).ready( function () {
		// Hide the workflow selector when clicking outside of it
		$( 'html' ).on( 'click', function ( e ) {
			if ( !e.isDefaultPrevented() ) {
				$( '.dropdown-menu' ).addClass( 'hide' );
			}
		} );
	} );
} )();
