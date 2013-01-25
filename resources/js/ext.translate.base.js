( function ( $, mw ) {
	'use strict';
	mw.translate = mw.translate || {};

	mw.translate = $.extend( mw.translate, {

		canTranslate: function () {
			return mw.config.get( 'TranslateRight' );
		},
	} );
}( jQuery, mediaWiki ) );
