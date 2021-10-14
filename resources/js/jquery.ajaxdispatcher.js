( function () {
	'use strict';

	/**
	 * Call list of callbacks returning promises in serial order and returns a list of promises.
	 *
	 * @author Niklas Laxström
	 *
	 * @param {Function[]} list List of callbacks returning promises.
	 * @param {number} maxRetries Maximum number of times a failed promise is retried.
	 * @return {jQuery.Promise}
	 */
	function ajaxDispatcher( list, maxRetries ) {
		var deferred = $.Deferred();

		maxRetries = maxRetries || 0;

		return $.when( helper( list, maxRetries ) )
			.then( function ( promises ) {
				return deferred.resolve( promises );
			} ).fail( function ( errmsg ) {
				return deferred.reject( errmsg );
			} );
	}

	function helper( list, maxRetries ) {
		var deferred = $.Deferred();

		if ( list.length === 0 ) {
			deferred.resolve( [] );
			return deferred;
		}

		var first = list.slice( 0, 1 )[ 0 ];
		var rest = list.slice( 1 );

		var retries = 0;
		var retrier = function ( result, promise ) {
			if ( !promise.state ) {
				return;
			}

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

		first.call().always( retrier ).catch( function ( errmsg ) {
			return deferred.reject( errmsg );
		} );

		return deferred;
	}

	$.extend( $, { ajaxDispatcher: ajaxDispatcher } );

}() );
