'use strict';

const api = new mw.Api();
const ACTION = 'translationentitysearch';

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
	return api.get( {
		action: ACTION,
		query: searchTerm,
		entitytype: entityTypes,
		grouptypes: groupTypes,
		limit
	} ).then( ( results ) => results.translationentitysearch || null );
}

module.exports = get;
