<template>
	<cdx-button action="progressive" @click="showAggregateGroupDialog = true">
		<cdx-icon :icon="cdxIconAdd"></cdx-icon>{{ $i18n( "tpt-aggregategroup-add-new" ) }}
	</cdx-button>
	<aggregate-group-dialog
		:visible="showAggregateGroupDialog"
		@close="showAggregateGroupDialog = false"
		@saved="onAggregateGroupSave"
	>
	</aggregate-group-dialog>
</template>

<script>
const { ref } = require( 'vue' );
const { CdxButton, CdxIcon } = require( '../../../../codex.js' );
const AggregateGroupDialog = require( './AggregateGroupDialog.vue' );
const { cdxIconAdd } = require( '../icons.json' );

// @vue/component
module.exports = {
	name: 'AggregateGroupsToolboxApp',
	components: {
		CdxButton,
		CdxIcon,
		AggregateGroupDialog
	},
	data() {
		const showAggregateGroupDialog = ref( false );
		return {
			cdxIconAdd,
			showAggregateGroupDialog
		};
	},
	methods: {
		onAggregateGroupSave() {
			window.location.reload();
		}
	}
};
</script>
