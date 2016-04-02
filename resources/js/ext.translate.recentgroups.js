( function ( $, mw ) {
	'use strict';

	mw.translate = mw.translate || {};

	/**
	 * Simple wrapper for storing recent groups for an user.
	 *
	 * @class mw.translate.recentGroups
	 * @singleton
	 * @since 2016.03
	 */

	mw.translate.recentGroups = {
		// TODO: Use mw.storage when MW >= 1.26
		get: function () {
			try {
				return JSON.parse( localStorage.getItem( 'translate-recentgroups' ) ) || [];
			} catch ( e ) {}
			return [];
		},

		append: function ( value ) {
			var items = this.get() || [];

			items.unshift( value );
			items = items.filter( function ( item, index, array ) {
				return array.indexOf( item ) === index;
			} );
			items = items.slice( 0, 5 );

			try {
				localStorage.setItem( 'translate-recentgroups', JSON.stringify( items ) );
				return true;
			} catch ( e ) {}
			return false;
		}
	};
}( jQuery, mediaWiki ) );
