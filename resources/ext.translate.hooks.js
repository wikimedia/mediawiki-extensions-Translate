(function ( $, mw ) {
	mw.translateHooks = {
		registry: {},

		add: function ( name, func ) {
			if ( !this.registry[name] ) {
				this.registry[name] = [];
			}
			this.registry[name].push( func );
		},

		run: function ( /* infinite list of parameters */ ) {
			var args = Array.prototype.slice.call( arguments );
			var name = args.shift();
			if ( this.registry[name] ) {
				var length = this.registry[name].length;
				for ( var i = 0; i < length; i++ ) {
					this.registry[name][i].apply( null, args );
				}
			}
		}
	};
} )( jQuery, mediaWiki );
