module.exports = ( function () {
	'use strict';

	const api = new mw.Api();
	const ACTION = 'aggregategroups';

	/**
	 * Add an aggregate group
	 *
	 * @param {Object} aggregateGroupDetails
	 * @param {string} aggregateGroupDetails.name Aggregate group name
	 * @param {string|undefined} [aggregateGroupDetails.description] Aggregate group description
	 * @param {string|undefined} [aggregateGroupDetails.languageCode] Aggregate group language code
	 * @return {mw.Api~AbortablePromise}
	 */
	function add( aggregateGroupDetails ) {
		const params = getSaveParams( aggregateGroupDetails );
		params.do = 'add';

		return api.postWithToken( 'csrf', params );
	}

	/**
	 * Update an existing aggregate group
	 *
	 * @param {string} aggregateGroupId
	 * @param {Object} aggregateGroupDetails
	 * @param {string} aggregateGroupDetails.name Aggregate group name
	 * @param {string|undefined} [aggregateGroupDetails.description] Aggregate group description
	 * @param {string|undefined} [aggregateGroupDetails.languageCode] Aggregate group language code
	 * @return {mw.Api~AbortablePromise}
	 */
	function update( aggregateGroupId, aggregateGroupDetails ) {
		const params = getSaveParams( aggregateGroupDetails );
		params.do = 'update';
		params.aggregategroup = aggregateGroupId;

		return api.postWithToken( 'csrf', params );
	}

	/**
	 * Remove an aggregate group
	 *
	 * @param {string} aggregateGroupId
	 * @return {mw.Api~AbortablePromise}
	 */
	function remove( aggregateGroupId ) {
		return api.postWithToken( 'csrf', {
			action: ACTION,
			do: 'remove',
			aggregategroup: aggregateGroupId
		} );
	}

	/**
	 * Remove a sub message group from an aggregate group
	 *
	 * @param {string} aggregateGroupId
	 * @param {string} groupId
	 * @return {mw.Api~AbortablePromise}
	 */
	function removeMessageGroup( aggregateGroupId, groupId ) {
		return api.postWithToken( 'csrf', {
			action: ACTION,
			do: 'dissociate',
			group: groupId,
			aggregategroup: aggregateGroupId
		} );
	}

	/**
	 * Associate a sub message group to an aggregate group
	 *
	 * @param {string} aggregateGroupId
	 * @param {string} groupId
	 * @return {mw.Api~AbortablePromise}
	 */
	function associateMessageGroup( aggregateGroupId, groupId ) {
		return api.postWithToken( 'csrf', {
			action: ACTION,
			do: 'associate',
			group: groupId,
			aggregategroup: aggregateGroupId
		} );
	}

	function getSaveParams( { name, description, languageCode } ) {
		return {
			action: ACTION,
			groupname: name,
			groupdescription: description,
			groupsourcelanguagecode: languageCode
		};
	}

	return {
		add,
		update,
		remove,
		removeMessageGroup,
		associateMessageGroup
	};
} );
