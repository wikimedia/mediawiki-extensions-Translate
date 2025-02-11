<template>
	<cdx-dialog
		:open="visible"
		:title="$i18n( 'tpt-aggregategroup-remove-confirm-title' ).text()"
		:primary-action="primaryAction"
		:default-action="defaultAction"
		@primary="onPrimaryAction"
		@default="onDefaultAction"
		@update:open="onDefaultAction"
	>
		<cdx-message
			v-if="apiSaveError"
			type="error"
			allow-user-dismiss
		>
			{{ apiSaveError }}
		</cdx-message>
		<p>{{ $i18n( 'tpt-aggregategroup-remove-confirm' ) }}</p>
	</cdx-dialog>
</template>

<script>
const { CdxDialog, CdxMessage } = require( '../../../../codex.js' );

// @vue/component
module.exports = {
	name: 'AggregateGroupDeleteDialog',
	components: {
		CdxDialog,
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
	emits: [ 'deleted', 'close' ],
	data() {
		const primaryAction = {
			label: this.$i18n( 'tpt-aggregategroup-delete' ),
			actionType: 'destructive'
		};

		const defaultAction = {
			label: this.$i18n( 'tpt-aggregategroup-update-cancel' )
		};

		return {
			primaryAction,
			defaultAction,
			apiSaveError: null
		};
	},
	methods: {
		onPrimaryAction() {
			this.apiSaveError = null;
			this.aggregateGroupApi.remove( this.aggregateGroupId )
				.then( () => this.$emit( 'deleted' ) )
				.catch( ( code, data ) => {
					this.apiSaveError = data.error && data.error.info;
				} );
		},
		onDefaultAction() {
			this.apiSaveError = null;
			this.$emit( 'close' );
		}
	}
};
</script>
