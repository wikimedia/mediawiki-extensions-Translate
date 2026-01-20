/*!
 * Entity selector for Special:ExportTranslations that allows users to load
 * messages from typing in a group name.
 * @author Eugene Wang'ombe
 * @license GPL-2.0-or-later, CC-BY-SA-3.0
 */

( function () {
	'use strict';

	function activateEntitySelector( $group ) {
		// hide the message group selector
		const $groupContainer = $( '.message-group-selector' );

		// Change the label, and update the for attribute, and remove the click handler
		// which causes the entity selector to become un-responsive when triggered
		$groupContainer
			.attr( 'for', 'mw-entity-selector-input' )
			.off( 'click' );

		// Determine what value was set, and set it on the entity selector
		const selectedGroup = $group.find( 'select option:selected' ).text();
		const selectedGroupId = $group.find( 'select' ).val();

		// load the entity selector and set the value
		const { getEntitySelector } = require( 'ext.translate.entity.selector' );
		const entitySelector = getEntitySelector( {
			onSelect: onEntitySelect,
			entityType: [ 'groups' ],
			inputId: 'mw-entity-selector-input',
			value: {
				label: selectedGroup,
				value: selectedGroupId,
				type: 'group'
			}
		} );

		$group.addClass( 'mw-translate-hide' );
		const container = document.createElement( 'div' );
		$group.after( container );
		entitySelector.mount( container );
	}

	function onEntitySelect( selectedId ) {
		$( 'select[name="group"]' ).val( selectedId );
	}

	function onSubmit() {
		const selectedGroupName = $( 'select[name="group"]' ).find( 'option:selected' ).text();
		const currentVal = $( '.tes-entity-selector' ).find( 'input[type="text"]' ).val();

		// Check if the user has selected an invalid entity.
		if ( currentVal !== selectedGroupName ) {
			mw.notify(
				mw.msg( 'translate-mgs-invalid-group', currentVal ),
				{
					type: 'error',
					tag: 'invalid-selection'
				}
			);
			return false;
		}
	}

	$( () => {
		activateEntitySelector( $( '#group' ) );

		$( '#mw-export-message-group-form' ).on( 'submit', onSubmit );
	} );
}() );
