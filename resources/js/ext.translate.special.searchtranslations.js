( function ( $ ) {
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
	} );
}( jQuery ) );
