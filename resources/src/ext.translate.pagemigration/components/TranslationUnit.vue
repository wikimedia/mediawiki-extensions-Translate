<template>
	<div>
		<cdx-text-area
			class="mw-tpm-sp-unit__source"
			:model-value="sourceText"
			rows="6"
			readonly
		></cdx-text-area>
		<div class="mw-tpm-sp-unit__target">
			<cdx-text-area
				:model-value="modelValue"
				rows="6"
				:lang="translationLang"
				:dir="translationDir"
				@update:model-value="newValue => $emit( 'update:modelValue', newValue.trim() )"
			></cdx-text-area>
			<button
				v-if="modelValue"
				type="button"
				class="mw-tpm-sp-action--clear"
				:title="$i18n( 'pm-clear-icon-hover-text' )"
				@click.prevent="() => $emit( 'update:modelValue', '' )"
			>
				<cdx-icon :icon="cdxIconClear" size="small"></cdx-icon>
			</button>
		</div>
		<div class="mw-tpm-sp-unit__actions">
			<div>
				<cdx-button
					v-if="!lastRow"
					:title="$i18n( 'pm-add-icon-hover-text' )"
					:aria-label="$i18n( 'pm-add-icon-hover-text' )"
					weight="quiet"
					type="button"
					@click.prevent="$emit( 'insertRow' )">
					<cdx-icon :icon="cdxIconAdd"></cdx-icon>
				</cdx-button>
				<cdx-button
					v-if="!lastRow"
					:title="$i18n( 'pm-swap-icon-hover-text' )"
					:aria-label="$i18n( 'pm-swap-icon-hover-text' )"
					weight="quiet"
					type="button"
					@click.prevent="$emit( 'swapRows' )">
					<cdx-icon :icon="cdxIconTableMoveRowAfter"></cdx-icon>
				</cdx-button>
				<cdx-button
					:title="$i18n( 'pm-delete-icon-hover-text' )"
					:aria-label="$i18n( 'pm-delete-icon-hover-text' )"
					weight="quiet"
					type="button"
					@click.prevent="$emit( 'deleteRow' )">
					<cdx-icon :icon="cdxIconTrash"></cdx-icon>
				</cdx-button>
			</div>
		</div>
	</div>
</template>

<script>
const { CdxButton, CdxIcon, CdxTextArea } = require( '../../../../codex.js' );
const { cdxIconAdd, cdxIconClear, cdxIconTableMoveRowAfter, cdxIconTrash } = require( '../icons.json' );

// @vue/component
module.exports = {
	name: 'TranslationUnit',
	components: {
		CdxButton,
		CdxIcon,
		CdxTextArea
	},
	props: {
		modelValue: {
			type: String,
			required: true
		},
		translationLang: {
			type: String,
			required: true
		},
		translationDir: {
			type: String,
			required: true
		},
		sourceText: {
			type: String,
			required: true
		},
		lastRow: {
			type: Boolean,
			required: true
		}
	},
	emits: [ 'update:modelValue', 'insertRow', 'swapRows', 'deleteRow' ],
	data() {
		return {
			cdxIconAdd,
			cdxIconClear,
			cdxIconTableMoveRowAfter,
			cdxIconTrash
		};
	}
};
</script>

<style lang="less">
	@import 'mediawiki.skin.variables.less';

	.mw-tpm-sp-unit__source,
	.mw-tpm-sp-unit__target {
		display: table-cell;
		width: 41.667%;
		vertical-align: top;
	}

	.mw-tpm-sp-unit__target {
		position: relative;
	}

	.mw-tpm-sp-action--clear {
		cursor: pointer;
		position: absolute;
		top: 5px;
		right: 5px;
		border: 0;
		padding: 0;
		background: none;

		> span {
			vertical-align: top;
			color: @color-subtle;
		}
	}

	.mw-tpm-sp-unit__actions {
		display: table-cell;
		width: 16.667%;
		vertical-align: middle;

		> div {
			display: flex;
			flex-flow: row wrap;
			align-items: center;
			gap: 10px;
		}
	}
</style>
