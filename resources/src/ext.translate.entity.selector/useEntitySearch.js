const { ref } = require( 'vue' );
const { cdxIconError } = require( './icons.json' );
const performEntitySearch = require( '../services/translationentitysearch.api.js' );

const MESSAGE_ENTITY_TYPE = 'message';
const GROUP_ENTITY_TYPE = 'group';

/**
 * Composable for entity search functionality
 *
 * @param {Object} props - Component props
 * @param {Function} emit - Component emit function
 * @return {Object} Search functionality and state
 */
function useEntitySearch( props, emit ) {
	const menuItems = ref( [] );
	const defaultOptionsCache = ref( [] );

	/**
	 * Transform API response into menu items structure
	 *
	 * @param {Object} response - API response
	 * @return {Array|null} Menu items or null on error
	 */
	function handleApiResponse( response ) {
		if ( !response || response.error ) {
			return null;
		}

		const items = [];
		const groups = response.groups || [];
		const messages = response.messages || [];

		if ( groups.length ) {
			const groupItems = groups.map( ( group ) => ( {
				label: group.label,
				value: group.group,
				type: GROUP_ENTITY_TYPE
			} ) );

			if ( props.entityType.length !== 1 ) {
				items.push( {
					label: mw.msg( 'translate-tes-optgroup-group' ),
					items: groupItems
				} );
			} else {
				items.push( ...groupItems );
			}
		}

		if ( messages.length ) {
			const messageItems = messages.map( ( message ) => ( {
				label: message.pattern,
				value: message.pattern,
				supportingText: message.count > 1 ? mw.msg( 'translate-tes-message-prefix', message.count ) : undefined,
				type: MESSAGE_ENTITY_TYPE
			} ) );

			if ( props.entityType.length !== 1 ) {
				items.push( {
					label: mw.msg( 'translate-tes-optgroup-message' ),
					items: messageItems
				} );
			} else {
				items.push( ...messageItems );
			}
		}

		return items;
	}

	/**
	 * Create error menu item
	 *
	 * @return {Array} Error menu item
	 */
	function createErrorItem() {
		return [ {
			label: mw.msg( 'translate-tes-server-error' ),
			value: 'error',
			disabled: true,
			icon: cdxIconError
		} ];
	}

	/**
	 * Perform entity search
	 *
	 * @param {string} term - Search term
	 * @param {boolean} isDefault - When true, caches the results in defaultOptionsCache so that
	 *   clearing the search input can restore the suggestions without an additional API call.
	 *   Should only be set for the initial empty-state search triggered by initializeDefaultSearch.
	 * @return {Promise} Search promise
	 */
	function performSearch( term, isDefault = false ) {
		return performEntitySearch( {
			searchTerm: term,
			entityTypes: props.entityType,
			groupTypes: props.groupTypes,
			limit: props.limit
		} )
			.then( ( response ) => {
				const items = handleApiResponse( response );

				if ( items === null ) {
					// API Error
					const errorMsg = response && response.error ? response.error : mw.msg( 'translate-tes-server-error' );
					menuItems.value = createErrorItem();
					emit( 'fail', errorMsg, mw.msg( 'translate-tes-server-error' ) );
				} else {
					if ( isDefault ) {
						defaultOptionsCache.value = items;
					}
					menuItems.value = items;
				}
			} )
			.catch( ( code, result ) => {
				if ( code === 'abort' ) {
					return;
				}
				menuItems.value = createErrorItem();
				emit( 'fail', code, result );
			} );
	}

	/**
	 * Handle search input - manages empty state
	 *
	 * @param {string} value - Input value
	 * @return {boolean} Whether a search should be performed
	 */
	function handleSearchInput( value ) {
		if ( !value ) {
			if ( props.allowSuggestionsWhenEmpty ) {
				menuItems.value = defaultOptionsCache.value;
			} else {
				menuItems.value = [];
			}
			return false;
		}

		return true;
	}

	/**
	 * Initialize default search if allowed
	 */
	function initializeDefaultSearch() {
		if ( props.allowSuggestionsWhenEmpty ) {
			performSearch( '', true );
		}
	}

	/**
	 * Get flat list of items from menu structure
	 *
	 * @param {Array} items - Menu items (may include groups)
	 * @return {Array} Flat list of items
	 */
	function flattenMenuItems( items ) {
		return items.reduce(
			( acc, item ) => item.items ? acc.concat( item.items ) : acc.concat( item ),
			[]
		);
	}

	const debouncedSearch = mw.util.debounce( ( value ) => {
		performSearch( value, false );
	}, 300 );

	return {
		menuItems,
		defaultOptionsCache,
		performSearch,
		debouncedSearch,
		handleSearchInput,
		initializeDefaultSearch,
		flattenMenuItems,
		MESSAGE_ENTITY_TYPE,
		GROUP_ENTITY_TYPE
	};
}

module.exports = useEntitySearch;
