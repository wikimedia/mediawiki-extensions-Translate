/*!
 * JavaScript hook framework for Translate (since MediaWiki code doesn't
 * yet have one. See hooks.txt in Translate directory for how to use hooks.
 *
 * @author Harry Burt
 * @license GPL-2.0+
 * @since 2012-08-22
 */

( function ( mw ) {
	'use strict';

	var registry = {};

	mw.translateHooks = {
		add: function ( name, func ) {
			if ( !registry[ name ] ) {
				registry[ name ] = [];
			}
			registry[ name ].push( func );
		},

		run: function ( /* infinite list of parameters */ ) {
			var args, name, length, i;

			args = Array.prototype.slice.call( arguments );
			name = args.shift();

			if ( registry[ name ] ) {
				length = registry[ name ].length;

				for ( i = 0; i < length; i++ ) {
					registry[ name ][ i ].apply( null, args );
				}
			}
		}
	};
}( mediaWiki ) );
