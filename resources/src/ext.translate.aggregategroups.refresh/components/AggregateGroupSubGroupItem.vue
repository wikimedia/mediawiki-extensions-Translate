<template>
	<li :data-group-id="groupId">
		<a :href="groupURL">{{ groupLabel }}</a>
		<button
			class="cdx-button cdx-button--icon-only cdx-button--action-destructive cdx-button--weight-quiet js-button-subgroup-delete cdx-aggregate-group-subgroup-delete"
			:aria-label="$i18n( 'tpt-aggregategroup-remove-item' )"
		>
			<span class="cdx-button__icon cdx-aggregate-group-subgroup-icon--delete"></span>
		</button>
	</li>
</template>

<script>
// @vue/component
module.exports = {
	name: 'AggregateGroupSubGroupItem',
	props: {
		groupId: {
			type: String,
			required: true
		},
		groupLabel: {
			type: String,
			required: true
		},
		groupURL: {
			type: String,
			required: true
		}
	}
};
</script>
