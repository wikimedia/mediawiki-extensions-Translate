<template>
	<cdx-dialog
		:open="visible"
		:title="dialogTitle"
		:default-action="defaultAction"
		:primary-action="primaryAction"
		@primary="onPrimaryAction"
		@default="$emit( 'close' )"
		@update:open="$emit( 'close' )"
	>
		<cdx-message
			v-if="apiLoadError"
			type="error"
			allow-user-dismiss
		>
			{{ apiLoadError }}
		</cdx-message>
		<cdx-message
			v-if="apiSaveError"
			type="error"
			allow-user-dismiss
		>
			{{ apiSaveError }}
		</cdx-message>
		<cdx-field :status="inputNameStatus" :messages="inputNameMessages">
			<template #label>
				{{ $i18n( "tpt-aggregategroup-edit-name" ) }}
			</template>
			<cdx-text-input
				v-model="formData.name"
				maxlength="200"
				required
				@input="onInputName"
			></cdx-text-input>
		</cdx-field>
		<cdx-field :optional="true">
			<template #label>
				{{ $i18n( "tpt-aggregategroup-edit-description" ) }}
			</template>
			<cdx-text-area v-model="formData.description"></cdx-text-area>
		</cdx-field>
		<!-- FIXME: Remove the double (optional) suffix that appears by updating the English string -->
		<cdx-field :optional="true">
			<template #label>
				{{ $i18n( "tpt-aggregategroup-select-source-language" ) }}
			</template>
			<!-- TODO: Maybe better to use a Codex Lookup here? -->
			<cdx-select v-model:selected="formData.languageCode" :menu-items="languageMenuItems">
			</cdx-select>
		</cdx-field>
	</cdx-dialog>
</template>

<script>
const {
	CdxDialog,
	CdxField,
	CdxTextArea,
	CdxTextInput,
	CdxSelect,
	CdxMessage
} = require( '../../../../codex.js' );
const { supportedLanguages, undeterminedLanguageCode } = require( '../../language-map.json' );

// @vue/component
module.exports = {
	name: 'AggregateGroupDialog',
	components: {
		CdxDialog,
		CdxField,
		CdxTextArea,
		CdxTextInput,
		CdxSelect,
		CdxMessage
	},
	inject: [ 'aggregateGroupApi' ],
	props: {
		visible: {
			type: Boolean,
			required: true
		},
		aggregateGroupId: {
			type: [ String, null ],
			default: null
		}
	},
	emits: [ 'close', 'saved' ],
	data() {
		const defaultAction = {
			label: this.$i18n( 'tpt-aggregategroup-close' )
		};

		const languageMenuItems = [ {
			label: this.$i18n( 'tpt-aggregategroup-language-none' ).text(),
			value: undeterminedLanguageCode
		} ];
		Object.keys( supportedLanguages ).forEach( ( languageCode ) => {
			languageMenuItems.push( {
				label: supportedLanguages[ languageCode ],
				value: languageCode
			} );
		} );

		return {
			defaultAction,
			languageMenuItems,
			formData: {
				name: '',
				description: '',
				languageCode: undeterminedLanguageCode
			},
			inputNameMessages: null,
			inputNameStatus: 'default',
			apiLoadError: null,
			apiSaveError: null
		};
	},
	computed: {
		dialogTitle() {
			return this.aggregateGroupId ?
				mw.msg( 'tpt-aggregategroup-edit' ) : mw.msg( 'tpt-aggregategroup-add-new' );
		},
		primaryAction() {
			return {
				label: this.$i18n( 'tpt-aggregategroup-save' ),
				actionType: 'progressive',
				disabled: !!this.apiLoadError
			};
		}
	},
	methods: {
		fetchAggregateGroupInfo( aggregateGroupId ) {
			const params = {
				meta: 'messagegroups',
				mgformat: 'flat',
				mgprop: 'id|label|description|sourcelanguage',
				mgroot: aggregateGroupId,
				formatversion: 2,
				uselang: mw.config.get( 'wgUserLanguage' )
			};

			const api = new mw.Api();
			api.get( params )
				.done( ( result ) => {
					const messageGroup = result.query.messagegroups[ 0 ];
					if ( !messageGroup ) {
						this.apiLoadError = mw.msg( 'tpt-aggregategroup-not-found' );
						return;
					}
					this.formData.name = messageGroup.label;
					this.formData.languageCode = messageGroup.sourcelanguage;
					this.formData.description = messageGroup.description;
				} )
				.fail( ( code, data ) => {
					mw.log.error( 'Error while fetching aggregate group', code, data );
					this.apiLoadError = mw.msg( 'tpt-aggregategroup-load-error' );
				} );
		},
		onPrimaryAction() {
			if ( !this.validate() ) {
				return;
			}

			this.apiSaveError = null;
			let apiPromise;
			if ( this.aggregateGroupId ) {
				apiPromise = this.aggregateGroupApi.update( this.aggregateGroupId, this.formData );
			} else {
				apiPromise = this.aggregateGroupApi.add( this.formData );
			}

			apiPromise
				.then( () => {
					this.resetErrors();
					this.$emit( 'saved' );
				} )
				.catch( ( code, data ) => {
					this.apiSaveError = data.error && data.error.info;
				} );
		},
		onInputName( event ) {
			if ( event.target.value !== '' ) {
				this.inputNameStatus = 'default';
				this.inputNameMessages = null;
			}
		},
		validate() {
			if ( this.formData.name.trim() === '' ) {
				this.inputNameStatus = 'error';
				this.inputNameMessages = {
					error: this.$i18n( 'tpt-aggregategroup-empty-name' )
				};

				return false;
			}

			return true;
		},
		resetErrors() {
			this.inputNameStatus = 'default';
			this.inputNameMessages = null;
			this.apiSaveError = null;
			this.apiLoadError = null;
		}
	},
	watch: {
		visible( newValue ) {
			if ( newValue ) {
				// Dialog is being opened.
				this.resetErrors();
			}
		},
		aggregateGroupId( newValue ) {
			if ( newValue ) {
				this.fetchAggregateGroupInfo( newValue );
			} else {
				this.formData = {
					name: '',
					description: '',
					languageCode: undeterminedLanguageCode
				};
			}
		}
	}
};
</script>

<style lang="less">
// FIXME: These styles should not be needed. Report an issue in Codex
// See: https://phabricator.wikimedia.org/F58151207
.cdx-dialog__body .cdx-message:first-child {
	padding-top: inherit;
}
</style>
