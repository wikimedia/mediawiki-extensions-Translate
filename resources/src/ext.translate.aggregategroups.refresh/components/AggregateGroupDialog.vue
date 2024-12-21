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
		<cdx-field>
			<template #label>
				{{ $i18n( "tpt-aggregategroup-edit-name" ) }}
			</template>
			<cdx-text-input></cdx-text-input>
		</cdx-field>
		<cdx-field :optional="true">
			<template #label>
				{{ $i18n( "tpt-aggregategroup-edit-description" ) }}
			</template>
			<cdx-text-area></cdx-text-area>
		</cdx-field>
		<!-- FIXME: Remove the double (optional) suffix that appears by updating the English string -->
		<cdx-field :optional="true">
			<template #label>
				{{ $i18n( "tpt-aggregategroup-select-source-language" ) }}
			</template>
			<!-- TODO: Maybe better to use a Codex Lookup here? -->
			<cdx-select :menu-items="languageMenuItems" selected="">
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
	CdxSelect
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
		CdxSelect
	},
	props: {
		visible: {
			type: Boolean,
			required: true
		}
	},
	emits: [ 'close' ],
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
			value: ''
		} ];
		Object.keys( supportedLanguages ).forEach( ( languageCode ) => {
			languageMenuItems.push( {
				label: supportedLanguages[ languageCode ],
				value: languageCode
			} );
		} );

		function onPrimaryAction() {
			mw.log( 'primary action' );
		}

		return {
			defaultAction,
			primaryAction,
			onPrimaryAction,
			languageMenuItems
		};
	}
};
</script>
