( function ( $ ) {
	'use strict';

	/**
	 * Call list of callbacks returning promises in serial order and returns a list of promises.
	 *
	 * @author Niklas Laxström
	 *
	 * @param {callable[]} list List of callbacks returning promises.
	 * @return {jQuery.Promise}
	 */
	function ajaxDispatcher( list, maxRetries ) {
		var deferred = $.Deferred();

		maxRetries = maxRetries || 0;

		return $.when( helper( list, maxRetries ) )
			.then( function ( promises ) {
				return deferred.resolve( promises );
			} );
	}

	function helper( list, maxRetries ) {
		var first, rest, retries, retrier,
			deferred = $.Deferred();

		if ( list.length === 0 ) {
			deferred.resolve( [] );
			return deferred;
		}

		first = list.slice( 0, 1 )[ 0 ];
		rest = list.slice( 1 );

		retries = 0;
		retrier = function () {
			var promise = this;

			if ( promise.state() === 'rejected' ) {
				if ( retries < maxRetries ) {
					retries += 1;
					return first.call().always( retrier );
				}
			}

			if ( promise.state() !== 'pending' ) {
				helper( rest, maxRetries ).always( function ( promises ) {
					deferred.resolve( [].concat( promise, promises ) );
				} );
			}
		};

		first.call().always( retrier );

		return deferred;
	}

	$.extend( $, { ajaxDispatcher: ajaxDispatcher } );

}( jQuery ) );
