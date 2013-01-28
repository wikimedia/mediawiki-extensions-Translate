( function ( $ ) {
	'use strict';

	$( document ).ready( function () {
		$( '.tux-message' ).each( function () {
			var $this = $( this );

			$this.translateeditor( {
				message: {
					title: $this.data( 'title' ),
					definition: $this.data( 'definition' ),
					translation: $this.data( 'translation' )
				}
			} );
		} );
	} );
}( jQuery ) );
