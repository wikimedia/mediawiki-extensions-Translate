'use strict';

const api = new mw.Api();
const ACTION = 'translationentitysearch';

/** Simple request tracker to avoid duplicate API calls */
const requestTracker = {
	// TODO: Add a max limit or expiry time in the future.
	/** @type {Object.<string,mw.Api~AbortablePromise>} Pending requests by request signature */
	pendingRequests: {},

	/**
	 * Execute API request or return existing promise if identical request is pending
	 *
	 * @param {Object} params Request parameters
	 * @return {mw.Api~AbortablePromise}
	 */
	execute( params ) {
		const key = JSON.stringify( params );

		// If we already have this exact request in progress, return its promise
		if ( this.pendingRequests[ key ] ) {
			return this.pendingRequests[ key ];
		}

		// Make the API call and store the promise
		const promise = api.get( params )
			.then( ( results ) => results.translationentitysearch || null )
			.catch( ( error ) => {
				delete this.pendingRequests[ key ];
				throw error;
			} );

		// Store the promise
		this.pendingRequests[ key ] = promise;
		return promise;
	}
};

/**
 * Fetch entities matching the specified criteria
 *
 * @param {Object} request
 * @param {string} request.searchTerm
 * @param {('groups'|'messages')[]} request.entityTypes
 * @param {('aggregate-groups'|'message-bundles'|'translatable-pages')[]} request.groupTypes
 * @param {number} request.limit
 * @return {mw.Api~AbortablePromise}
 */
function get( { searchTerm, entityTypes, groupTypes, limit } ) {
	return requestTracker.execute( {
		action: ACTION,
		query: searchTerm,
		entitytype: entityTypes,
		grouptypes: groupTypes,
		limit
	} );
}

module.exports = get;
