(function ( $, mw ) {
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
} )( jQuery, mediaWiki );
