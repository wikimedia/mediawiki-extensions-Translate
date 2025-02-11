<template>
	<form>
		<cdx-field
			:status="status"
			:messages="messages"
			:hide-label="true"
		>
			<cdx-lookup
				v-model:selected="selection"
				v-model:input-value="inputValue"
				:menu-items="menuItems"
				:menu-config="{ visibleItemLimit: 10 }"
				:aria-label="$i18n( 'translate-tes-type-to-search' )"
				:placeholder="$i18n( 'translate-tes-type-to-search' )"
				:disabled="isLookupDisabled"
				@input="onInput"
			>
				<template #no-results>
					{{ $i18n( 'translate-tes-entity-not-found' ) }}
				</template>
			</cdx-lookup>
		</cdx-field>
		<cdx-button
			action="progressive"
			weight="primary"
			:disabled="selection === null"
			@click.prevent="onAddClick"
		>
			{{ $i18n( 'tpt-aggregategroup-add' ) }}
		</cdx-button>
	</form>
</template>

<script>
const { CdxButton, CdxLookup, CdxField } = require( '../../../../codex.js' );

const MAX_ENTRIES = 50;

// @vue/component
module.exports = {
	name: 'AggregateGroupAssociation',
	components: { CdxButton, CdxLookup, CdxField },
	inject: [ 'aggregateGroupApi', 'performEntitySearch' ],
	props: {
		aggregateGroupId: {
			type: [ String ],
			required: true
		},
		getExistingGroups: {
			type: [ Function ],
			required: true
		}
	},
	emits: [ 'saved' ],
	data() {
		return {
			selection: null,
			// FIXME: Without the empty object, assigning the array of initial message groups doesn't display
			// them. Check with design system team
			menuItems: [ { label: '', value: '' } ],
			inputValue: '',
			status: 'default',
			messages: { error: '' },
			debounce: null,
			isLookupDisabled: false
		};
	},
	methods: {
		fetchResults( searchTerm ) {
			this.performEntitySearch( {
				searchTerm,
				entityTypes: [ 'groups' ],
				groupTypes: [ 'translatable-pages', 'message-bundles' ],
				limit: MAX_ENTRIES
			} ).then( ( results ) => {
				// Make sure this data is still relevant first.
				if ( this.inputValue !== searchTerm ) {
					return;
				}

				if ( !results ) {
					this.menuItems = [];
					return;
				}

				const mappedItems = results.groups.map( ( searchResult ) => ( {
					label: searchResult.label,
					value: searchResult.group
				} ) );
				const existingGroupIds = this.getExistingGroups();
				const filteredArray = [];
				for ( const entity of mappedItems ) {
					if ( !existingGroupIds.has( entity.value ) ) {
						filteredArray.push( entity );
					}
				}

				this.menuItems = filteredArray;
			} ).catch( ( msg, error ) => {
				mw.log.error( '[translationentitysearch] Error fetching entities', error, searchTerm );

				// This is the only way to hide the dropdown from the Lookup so that users
				// can see the error from the API.
				this.isLookupDisabled = true;
				this.menuItems = [];
				this.displayError( this.$i18n( 'translate-tes-server-error' ).text() );
				setTimeout( () => {
					this.isLookupDisabled = false;
				}, 50 );
			} );
		},
		onInput( value ) {
			clearTimeout( this.debounce );
			this.debounce = setTimeout( () => {
				this.fetchResults( value );
			}, 200 );
		},
		onAddClick() {
			this.resetError();
			this.aggregateGroupApi.associateMessageGroup( this.aggregateGroupId, this.selection )
				.then( ( response ) => {
					this.$emit(
						'saved',
						{
							id: this.selection,
							label: this.inputValue,
							url: response.aggregategroups.groupUrls[ this.selection ]
						}
					);
					this.menuItems = [];
					this.selection = null;
					this.inputValue = '';
				} )
				.catch( ( code, errorData ) => {
					mw.log.error(
						`Error associating ${ this.selection } to ${ this.aggregateGroupId }`,
						code,
						errorData
					);
					this.displayError( errorData.error.info );
				} );
		},
		displayError( errorMessage ) {
			this.status = 'error';
			this.messages.error = errorMessage;
		},
		resetError() {
			this.status = 'default';
			this.messages.error = '';
		}
	},
	mounted() {
		if ( !this.inputValue ) {
			this.fetchResults( '' );
		}
	}
};
</script>
