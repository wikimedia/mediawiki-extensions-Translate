/**
 * JavaScript hook framework for Translate (since MediaWiki code doesn't
 * yet have one. See hooks.txt in Translate directory for how to use hooks.
 *
 * @author Harry Burt
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @since 2012-08-22
 */

( function ( mw ) {
	"use strict";
	var registry = {};
	mw.translateHooks = {
		add: function ( name, func ) {
			if ( !registry[name] ) {
				registry[name] = [];
			}
			registry[name].push( func );
		},

		run: function ( /* infinite list of parameters */ ) {
			var args = Array.prototype.slice.call( arguments );
			var name = args.shift();
			if ( registry[name] ) {
				var length = registry[name].length;
				for ( var i = 0; i < length; i++ ) {
					registry[name][i].apply( null, args );
				}
			}
		}
	};
}( mediaWiki ) );
