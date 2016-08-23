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
		get: function () {
			return JSON.parse( mw.storage.get( 'translate-recentgroups' ) ) || [];
		},

		append: function ( value ) {
			var items = this.get();

			items.unshift( value );
			items = items.filter( function ( item, index, array ) {
				return array.indexOf( item ) === index;
			} );
			items = items.slice( 0, 5 );

			mw.storage.set( 'translate-recentgroups', JSON.stringify( items ) );
		}
	};
}( jQuery, mediaWiki ) );
