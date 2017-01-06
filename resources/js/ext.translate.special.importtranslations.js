( function ( $ ) {
	'use strict';

	function buttoner( $input ) {
		if ( $input.val ) {
			$( 'input[type=submit]' ).prop( 'disabled', false );
		} else {
			$( 'input[type=submit]' ).prop( 'disabled', true );
		}
	}

	$( function () {
		var $input = $( '#mw-translate-up-local-input' );
		$input.on( 'change', function () {
			buttoner( $input );
		} );

		buttoner( $input );
	} );
}( jQuery ) );
