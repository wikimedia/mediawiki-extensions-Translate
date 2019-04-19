/*!
 * JavaScript hook framework for Translate (since MediaWiki code doesn't
 * yet have one. See hooks.txt in Translate directory for how to use hooks.
 *
 * @author Harry Burt
 * @license GPL-2.0-or-later
 * @since 2012-08-22
 */

( function () {
	'use strict';

	var registry = {};

	mw.translateHooks = {
		add: function ( name, func ) {
			showDeprecationWarning();

			if ( !registry[ name ] ) {
				registry[ name ] = [];
			}
			registry[ name ].push( func );
		},

		run: function ( /* infinite list of parameters */ ) {
			var args, name, length, i;

			showDeprecationWarning();

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

	function showDeprecationWarning() {
		mw.log.warn( '`mw.translateHooks` has been deprecated and will be removed in the ' +
			'future. Use `mw.hook` instead. See - ' +
			'https://doc.wikimedia.org/mediawiki-core/master/js/#!/api/mw.hook' );
	}
}() );
