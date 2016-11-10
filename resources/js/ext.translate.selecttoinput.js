window.appendFromSelect = function ( selectid, targetid ) {
	'use strict';

	var select = document.getElementById( selectid ),
		target = document.getElementById( targetid ),
		atxt;

	if ( !target || !select ) {
		return;
	}

	atxt = select.options[ select.selectedIndex ].value;

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
