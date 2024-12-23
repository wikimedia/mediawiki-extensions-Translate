<template>
	<cdx-dialog
		:open="visible"
		:title="$i18n( 'tpt-aggregategroup-add-new' ).text()"
		:default-action="defaultAction"
		:primary-action="primaryAction"
		@primary="onPrimaryAction"
		@default="$emit( 'close' )"
		@update:open="$emit( 'close' )"
	>
		<!--
		FIXME: See if it's possible to wrap the controls including the button in a form tag
		Button's form attribute can be used to map the button to the form.
		-->
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
const supportedLanguages = require( '../../language-map.json' );

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
	props: {
		visible: {
			type: Boolean,
			required: true
		}
	},
	emits: [ 'close', 'saved' ],
	data() {
		const defaultAction = {
			label: this.$i18n( 'tpt-aggregategroup-close' )
		};
		const primaryAction = {
			label: this.$i18n( 'tpt-aggregategroup-save' ),
			actionType: 'progressive'
		};

		const languageMenuItems = [ {
			label: this.$i18n( 'tpt-aggregategroup-language-none' ).text(),
			value: '-'
		} ];
		Object.keys( supportedLanguages ).forEach( ( languageCode ) => {
			languageMenuItems.push( {
				label: supportedLanguages[ languageCode ],
				value: languageCode
			} );
		} );

		return {
			defaultAction,
			primaryAction,
			languageMenuItems,
			formData: {
				name: '',
				description: '',
				languageCode: ''
			},
			inputNameMessages: null,
			inputNameStatus: 'default',
			apiSaveError: null
		};
	},
	methods: {
		onPrimaryAction() {
			if ( !this.validate() ) {
				return;
			}

			this.apiSaveError = null;
			const api = new mw.Api();
			const params = {
				action: 'aggregategroups',
				do: 'add',
				groupname: this.formData.name,
				groupdescription: this.formData.description,
				groupsourcelanguagecode: this.formData.languageCode
			};

			api.postWithToken( 'csrf', params )
				.done( () => {
					this.resetErrors();
					this.$emit( 'saved' );
				} )
				.fail( ( code, data ) => {
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
		}
	},
	watch: {
		visible( newValue ) {
			if ( newValue ) {
				// Dialog is being opened.
				this.resetErrors();
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
