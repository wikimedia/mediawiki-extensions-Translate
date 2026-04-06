<template>
	<form @submit.prevent="onImportClick">
		<cdx-field
			:disabled="units !== null && saveStatus !== 'saved'"
			:status="messages.error ? 'error' : 'default'"
			:messages="messages"
		>
			<template #label>
				{{ $i18n( 'pm-pagetitle-label' ) }}
			</template>
			<cdx-lookup
				v-model:selected="selection"
				v-model:input-value="inputValue"
				:menu-items="menuItems"
				:menu-config="{ visibleItemLimit: 10 }"
				:disabled="units !== null && saveStatus !== 'saved'"
				@input="onInput"
			></cdx-lookup>
		</cdx-field>
		<cdx-button
			:disabled="units !== null && saveStatus !== 'saved'"
			action="progressive"
			weight="primary"
		>
			{{ $i18n( 'pm-import-button-label' ) }}
		</cdx-button>
	</form>
	<form v-if="units !== null">
		<p v-if="saveStatus === null">
			{{ $i18n( 'pm-on-import-message-text' ) }}
		</p>
		<div class="mw-tpm-sp-units">
			<translation-unit
				v-for="( row, i ) in rows"
				:key="row.identifier"
				:model-value="row.targetText"
				:translation-lang="units.translationLang"
				:translation-dir="units.translationDir"
				:source-text="row.sourceText"
				:last-row="i === rows.length - 1"
				@update:model-value="newValue => units.translationUnits[ i ] = newValue"
				@insert-row="() => onAddClick( i )"
				@swap-rows="() => onSwapClick( i )"
				@delete-row="() => onDeleteClick( i )"
			></translation-unit>
		</div>
		<div>
			<cdx-button
				action="progressive"
				weight="primary"
				:disabled="saveStatus === 'saving'"
				@click.prevent="onSaveClick">
				{{ $i18n( 'pm-savepages-button-label' ) }}
			</cdx-button>
			<cdx-button
				action="destructive"
				weight="quiet"
				:disabled="saveStatus === 'saving'"
				@click.prevent="onCancelClick">
				{{ $i18n( 'pm-cancel-button-label' ) }}
			</cdx-button>
		</div>
		<cdx-message v-if="saveError !== null" type="error">
			{{ saveError }}
		</cdx-message>
		<cdx-message v-if="saveStatus === 'saved'" type="success">
			{{ $i18n( 'pm-on-save-message-text' ) }}
		</cdx-message>
	</form>
</template>

<script>
const { CdxButton, CdxField, CdxMessage, CdxLookup } = require( '../../../../codex.js' );
const TranslationUnit = require( './TranslationUnit.vue' );
const { loadData, typeaheadSearch } = require( '../services.js' );

// @vue/component
module.exports = {
	name: 'PageMigrationApp',
	components: {
		TranslationUnit,
		CdxButton,
		CdxField,
		CdxLookup,
		CdxMessage
	},
	inject: [ 'editSummary' ],
	data() {
		return {
			// Page selection
			inputValue: '',
			selection: null,
			menuItems: [],
			debounce: null,
			messages: { error: '' },

			// Units processing and saving
			units: null,
			/** @type {null|'saving'|'saved'} */
			saveStatus: null,
			saveError: null
		};
	},
	computed: {
		rows() {
			if ( this.units !== null ) {
				const { sourceUnits, translationUnits } = this.units;
				const merged = [];
				for ( let i = 0; ( i < sourceUnits.length || i < translationUnits.length ); ++i ) {
					const sourceUnit = sourceUnits[ i ];
					merged.push( {
						identifier: sourceUnit ? sourceUnit.identifier : '#' + i,
						sourceText: sourceUnit ? sourceUnit.definition : '',
						targetText: translationUnits[ i ] || ''
					} );
				}
				return merged;
			} else {
				return null;
			}
		}
	},
	methods: {
		onInput( inputValue ) {
			clearTimeout( this.debounce );
			this.debounce = setTimeout( () => {
				typeaheadSearch( inputValue ).then( ( results ) => {
					// Make sure the data is still relevant
					if ( this.inputValue === inputValue ) {
						this.menuItems = results.map( ( value ) => ( { value } ) );
					}
				} );
			}, 200 );
		},
		onImportClick() {
			this.saveStatus = null;
			this.saveError = null;
			this.messages.error = '';

			loadData( this.inputValue ).then(
				( data ) => {
					this.units = data;
				},
				( error ) => {
					this.messages.error = String( error );
				}
			);
		},

		onAddClick( i ) {
			const tUnits = this.units.translationUnits;
			tUnits.splice( i + 1, 0, '' );
			// If the last unit was empty, remove it, so that we less often end up with more translation units than source ones
			if ( tUnits[ tUnits.length - 1 ] === '' ) {
				tUnits.splice( tUnits.length - 1, 1 );
			}
		},

		onSwapClick( i ) {
			const tUnits = this.units.translationUnits;
			[ tUnits[ i ], tUnits[ i + 1 ] ] = [ tUnits[ i + 1 ], tUnits[ i ] ];
		},

		onDeleteClick( i ) {
			this.units.translationUnits.splice( i, 1 );
		},

		onSaveClick() {
			this.saveStatus = 'saving';
			this.saveError = null;
			this.units.save( this.editSummary ).then(
				() => {
					this.saveStatus = 'saved';
				},
				( errmsg ) => {
					this.saveStatus = null;
					this.saveError = errmsg;
				}
			);
		},

		onCancelClick() {
			this.units = null;
		}
	}
};
</script>

<style>
	.mw-tpm-sp-units {
		display: table;
		width: 100%;
		border-collapse: separate;
		border-spacing: 5px;
		/* Compensate for the border spacing */
		margin: 0 -5px;
	}

	.mw-tpm-sp-units > div {
		display: table-row;
	}
</style>
