'use strict';

const Vue = require( 'vue' );
const EntitySelector = require( './EntitySelector.vue' );

/**
 * Creates and mounts the EntitySelector Vue component.
 *
 * @param {Object} [config] Configuration options
 * @param {Function} [config.onFail] Callback function triggered when an error occurs
 * @param {Function} [config.onSelect] Callback function triggered when an item is selected
 * @param {Array} [config.entityType] Entity type to query for - "groups" and/or "messages"
 * @param {string} [config.value] Initial value
 * @param {number} [config.limit]
 * @param {boolean} [config.allowSuggestionsWhenEmpty]
 * @param {string} [config.inputId] ID for the input element
 * @return {Vue.App} The Vue application instance
 */
function getEntitySelector( config ) {
	return Vue.createMwApp( {
		data: function () {
			return {
				selected: config.value || null
			};
		},
		render: function () {
			return Vue.h( EntitySelector, {
				inputId: config.inputId,
				entityType: config.entityType,
				groupTypes: config.groupTypes,
				limit: config.limit,
				allowSuggestionsWhenEmpty: config.allowSuggestionsWhenEmpty,
				selected: this.selected,
				'onUpdate:selected': ( payload ) => {
					this.selected = payload.value;
					if ( config.onSelect ) {
						config.onSelect( payload.value, payload.type, payload.label );
					}
				},
				onFail: ( error, message ) => {
					if ( config.onFail ) {
						config.onFail( error, message );
					}
				}
			} );
		}
	} );
}

module.exports = {
	getEntitySelector
};
