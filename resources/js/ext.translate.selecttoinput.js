$( function () {
	var appendFromSelect = function ( selectid, targetid ) {
		'use strict';

		var select = document.getElementById( selectid ),
			target = document.getElementById( targetid );

		if ( !target || !select ) {
			return;
		}

		var atxt = select.options[ select.selectedIndex ].value;

		if ( !atxt ) {
			return;
		}

		if ( target.value.replace( /\s+/g, '' ) !== '' ) {
			atxt = ', ' + atxt;
		}

		atxt = target.value + atxt;

		atxt = atxt.replace( /\bdefault\b[,\s]*/i, '' );

		target.value = atxt;
	};

	$( '.mw-translate-jssti' ).on( 'click', function () {
		var sourceId = $( this ).data( 'translate-jssti-sourceid' ),
			targetId = $( this ).data( 'translate-jssti-targetid' );

		appendFromSelect( sourceId, targetId );
	} );

} );
