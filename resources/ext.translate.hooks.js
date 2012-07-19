(function ( $, mw ) {
	mw.translateHooks = {
		add: function ( name, func ) {
			if ( !mw.translate[name] ) {
				mw.translateHooks[name] = [];
			}
			mw.translateHooks[name].push( func );
		},

		run: function ( /* infinite list of parameters */ ) {
			if ( mw.translateHooks[name] ) {
				var args = Array.prototype.slice.call( arguments );
				var name = args.shift();
				var length = mw.translateHooks[name].length;
				for ( var i = 0; i < length; i++ ) {
					mw.translateHooks[name][i].apply( this, args );
				}
			}
		}
	};
} )( jQuery, mediaWiki );