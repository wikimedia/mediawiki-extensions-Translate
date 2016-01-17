( function ( mw, $ ) {
	'use strict';

	// Make sure the language selection dropdown doesn't trigger
	// Special:Preferences unsaved preferences check
	var $langSelector = $( '#mw-language-selector' );
	function updateDefaultSelected() {
		var $opt = $langSelector.find( 'option:selected' );
		$opt.prop( 'defaultSelected', $opt.prop( 'selected' ) );
	}

	$langSelector.on( 'change keydown mousedown', function () {
		updateDefaultSelected();
	} );
	updateDefaultSelected();
}( mediaWiki, jQuery ) );
