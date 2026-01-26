<template>
	<cdx-lookup
		:id="inputId"
		v-model:selected="selectedValue"
		v-model:input-value="inputValue"
		:menu-items="menuItems"
		:menu-config="computedMenuConfig"
		:aria-label="$i18n( 'translate-tes-type-to-search' )"
		:placeholder="$i18n( 'translate-tes-type-to-search' )"
		class="tes-entity-selector"
		@input="onInput"
		@update:selected="onSelect"
	>
		<template #no-results>
			{{ $i18n( 'translate-tes-entity-not-found' ) }}
		</template>
	</cdx-lookup>
</template>

<script>
const { defineComponent, ref, computed } = require( 'vue' );
const { CdxLookup } = require( '../../../codex.js' );
const useEntitySearch = require( './useEntitySearch.js' );

module.exports = defineComponent( {
	name: 'EntitySelector',
	components: { CdxLookup },
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
		// eslint-disable-next-line vue/no-unused-properties
		allowSuggestionsWhenEmpty: { type: Boolean, default: false },
		selected: { type: Object, default: null },
		menuConfig: { type: Object, default: () => ( {} ) }
	},
	emits: [ 'fail', 'update:selected' ],
	setup( props, { emit } ) {
		const selectedValue = ref( null );
		const inputValue = ref( '' );

		const {
			menuItems,
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

		const onInput = ( value ) => {
			const shouldSearch = handleSearchInput( value );
			if ( shouldSearch ) {
				debouncedSearch( value );
			}
		};

		const onSelect = ( selectedItemValue ) => {
			if ( !selectedItemValue ) {
				return;
			}

			const flatItems = flattenMenuItems( menuItems.value );
			const itemObj = flatItems.find( ( i ) => i.value === selectedItemValue );

			if ( itemObj && itemObj.value !== 'error' ) {
				emit( 'update:selected', {
					value: itemObj.value,
					type: itemObj.type,
					label: itemObj.label
				} );
			}
		};

		// Initialize
		initializeDefaultSearch();

		if ( props.selected ) {
			selectedValue.value = props.selected.value;
			inputValue.value = props.selected.label;
			performSearch( inputValue.value, false );
		}

		return {
			selectedValue,
			inputValue,
			menuItems,
			computedMenuConfig,
			onInput,
			onSelect
		};
	}
} );
</script>

<style lang="less">
.tes-entity-selector {
	.cdx-menu-item__content {
		display: block;

		.cdx-menu-item__text__supporting-text {
			float: right;
		}
	}
}
</style>
