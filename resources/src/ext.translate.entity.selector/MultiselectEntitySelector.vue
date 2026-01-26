<template>
	<cdx-multiselect-lookup
		:id="inputId"
		v-model:selected="selectedValues"
		v-model:input-chips="inputChips"
		v-model:input-value="inputValue"
		:menu-items="menuItems"
		:menu-config="computedMenuConfig"
		:aria-label="$i18n( 'translate-tes-type-to-search' )"
		:placeholder="$i18n( 'translate-tes-type-to-search' )"
		class="tes-entity-selector tes-multiselect-entity-selector"
		@input="onInput"
		@update:selected="onSelect"
	>
		<template #no-results>
			{{ $i18n( 'translate-tes-entity-not-found' ) }}
		</template>
	</cdx-multiselect-lookup>
	<input
		v-if="inputName"
		type="hidden"
		:name="inputName"
		:value="selectedValues.join( ',' )"
	>
</template>

<script>
const { defineComponent, ref, computed, watch } = require( 'vue' );
const { CdxMultiselectLookup } = require( '../../../codex.js' );
const useEntitySearch = require( './useEntitySearch.js' );

/**
 * Resolves labels for message groups from the API.
 *
 * @param {Array} items - Items with value and type
 * @return {Promise<Array>} Resolved items with labels
 */
function resolveItemLabels( items ) {
	const ids = items.map( ( item ) => item.value );
	return new mw.Api().get( {
		action: 'query',
		meta: 'messagegroups',
		mgfilter: ids.join( '|' ),
		mgprop: 'label|id',
		formatversion: 2
	} ).then( ( response ) => {
		const labelMap = new Map();
		const groups = ( response && response.query && response.query.messagegroups ) || [];
		groups.forEach( ( group ) => {
			labelMap.set( group.id, group.label );
		} );
		return items.map( ( item ) => {
			const label = labelMap.get( item.value );
			return label ? { value: item.value, label, type: item.type || 'group' } : item;
		} );
	} ).catch( () => items );
}

module.exports = defineComponent( {
	name: 'MultiselectEntitySelector',
	components: { CdxMultiselectLookup },
	props: {
		inputId: { type: String, default: '' },
		limit: { type: Number, default: 10 },
		// eslint-disable-next-line vue/no-unused-properties
		entityType: {
			type: Array,
			default: () => [ 'messages', 'groups' ],
			validator: ( v ) => v.every( ( t ) => [ 'messages', 'groups' ].includes( t ) )
		},
		// eslint-disable-next-line vue/no-unused-properties
		groupTypes: {
			type: Array,
			default: () => [],
			validator: ( v ) => v.every( ( t ) => [ 'translatable-pages', 'message-bundles' ].includes( t ) )
		},
		allowSuggestionsWhenEmpty: { type: Boolean, default: false },
		selected: { type: Array, default: () => [] },
		inputName: { type: String, default: '' },
		menuConfig: { type: Object, default: () => ( {} ) }
	},
	emits: [ 'fail', 'update:selected' ],
	setup( props, { emit } ) {
		const selectedValues = ref( [] );
		const inputChips = ref( [] );
		const inputValue = ref( '' );

		const {
			menuItems,
			defaultOptionsCache,
			performSearch,
			debouncedSearch,
			handleSearchInput,
			initializeDefaultSearch,
			flattenMenuItems
		} = useEntitySearch( props, emit );

		const computedMenuConfig = computed( () => Object.assign(
			{ visibleItemLimit: props.limit },
			props.menuConfig
		) );

		// Watch for changes to the selected prop to sync initial values
		watch( () => props.selected, ( newValue ) => {
			const items = newValue || [];
			selectedValues.value = items.map( ( item ) => item.value );
			inputChips.value = items.map( ( item ) => ( {
				value: item.value,
				label: item.label || item.value
			} ) );

			// Fetch accurate labels from the API only for items that are missing them
			const itemsMissingLabels = items.filter( ( item ) => !item.label );
			if ( itemsMissingLabels.length > 0 ) {
				resolveItemLabels( itemsMissingLabels ).then( ( resolvedItems ) => {
					const labelMap = new Map( resolvedItems.map( ( i ) => [ i.value, i.label ] ) );
					inputChips.value = items.map( ( item ) => ( {
						value: item.value,
						label: item.label || labelMap.get( item.value ) || item.value
					} ) );
				} );
			}
		}, { immediate: true } );

		const onInput = ( value ) => {
			const shouldSearch = handleSearchInput( value );
			if ( shouldSearch ) {
				debouncedSearch( value );
			} else if ( value === '' && props.allowSuggestionsWhenEmpty && defaultOptionsCache.value.length === 0 ) {
				// Initial load of suggestions when empty
				performSearch( '', true );
			}
		};

		const onSelect = ( selectedIds ) => {
			const flatItems = flattenMenuItems( menuItems.value );
			const selectedItems = selectedIds.map( ( id ) => {
				// First try to find in current chips to preserve labels
				const existingChip = inputChips.value.find( ( chip ) => chip.value === id );
				if ( existingChip ) {
					// Find the full item to get the type
					const menuItem = flatItems.find( ( flatItem ) => flatItem.value === id );
					return {
						value: existingChip.value,
						label: existingChip.label,
						type: menuItem ? menuItem.type : 'group'
					};
				}
				// Fall back to looking up in current menu items
				const item = flatItems.find( ( menuItem ) => menuItem.value === id );
				return item || { value: id, label: id, type: 'group' };
			} );
			emit( 'update:selected', selectedItems );
		};

		// Initialize
		initializeDefaultSearch();

		return {
			selectedValues,
			inputChips,
			inputValue,
			menuItems,
			computedMenuConfig,
			onInput,
			onSelect
		};
	}
} );
</script>
