window.appendFromSelect = function ( selectid, targetid ) {
	'use strict';

	var select, target, atxt;

	select = document.getElementById( selectid );
	target = document.getElementById( targetid );

	if ( !target || !select ) {
		return;
	}

	atxt = select.options[ select.selectedIndex ].value;

	if ( !atxt ) {
		return;
	}

	/* Ugly hack */

	target.value = target.value.replace( /default/, '' );

	if ( target.value.replace( /[\s\t\n]/ig, '' ) !== '' ) {
		atxt = ', ' + atxt;
	}
	target.value += atxt;
};
