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
const { cdxIconError } = require( './icons.json' );
const performEntitySearch = require( '../services/translationentitysearch.api.js' );
const messageEntityType = 'message';
const groupEntityType = 'group';

module.exports = defineComponent( {
	name: 'EntitySelector',
	components: { CdxLookup },
	props: {
		inputId: { type: String, default: '' },
		entityType: {
			type: Array,
			default: () => [ 'messages', 'groups' ],
			validator: ( v ) => v.every( ( t ) => [ 'messages', 'groups' ].includes( t ) )
		},
		groupTypes: {
			type: Array,
			default: () => [],
			validator: ( v ) => v.every( ( t ) => [ 'translatable-pages', 'message-bundles' ].includes( t ) )
		},
		limit: { type: Number, default: 10 },
		allowSuggestionsWhenEmpty: { type: Boolean, default: false },
		selected: { type: Object, default: null },
		menuConfig: { type: Object, default: () => ( {} ) }
	},
	emits: [ 'fail', 'update:selected' ],
	setup( props, { emit } ) {
		const selectedValue = ref( null );
		const inputValue = ref( '' );

		const menuItems = ref( [] );

		const defaultOptionsCache = ref( [] );

		const computedMenuConfig = computed( () => Object.assign(
			{ visibleItemLimit: props.limit },
			props.menuConfig
		) );

		const getMessagePrefixMsg = ( count ) => mw.msg( 'translate-tes-message-prefix', count );

		const handleApiResponse = ( response ) => {
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
					type: groupEntityType
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
					supportingText: message.count > 1 ? getMessagePrefixMsg( message.count ) : undefined,
					type: messageEntityType
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
		};

		const performSearch = ( term, isDefault = false ) => {
			performEntitySearch( {
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
						const errorItem = [ {
							label: mw.msg( 'translate-tes-server-error' ),
							value: 'error',
							disabled: true,
							icon: cdxIconError
						} ];

						menuItems.value = errorItem;
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
					menuItems.value = [ {
						label: mw.msg( 'translate-tes-server-error' ),
						value: 'error',
						disabled: true,
						icon: cdxIconError
					} ];
					emit( 'fail', result, mw.msg( 'translate-tes-server-error' ) );
				} );
		};

		const debouncedSearch = mw.util.debounce( ( value ) => {
			if ( value ) {
				performSearch( value, false );
			}
		}, 300 );

		const onInput = ( value ) => {
			if ( !value ) {
				if ( props.allowSuggestionsWhenEmpty ) {
					menuItems.value = defaultOptionsCache.value;
				} else {
					menuItems.value = [];
				}
				debouncedSearch( '' );
				return;
			}

			debouncedSearch( value );
		};

		const onSelect = ( selectedItemValue ) => {
			if ( !selectedItemValue ) {
				return;
			}

			const flatItems = menuItems.value.reduce( ( acc, item ) => item.items ? acc.concat( item.items ) : acc.concat( item ), [] );

			const itemObj = flatItems.find( ( i ) => i.value === selectedItemValue );

			if ( itemObj && itemObj.value !== 'error' ) {
				emit( 'update:selected', {
					value: itemObj.value,
					type: itemObj.type,
					label: itemObj.label
				} );
			}
		};

		if ( props.allowSuggestionsWhenEmpty ) {
			performSearch( '', true );
		}

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
