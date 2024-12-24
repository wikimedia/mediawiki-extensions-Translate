const Vue = require( 'vue' );
const App = require( './components/AggregateGroupsToolboxApp.vue' );
const DeleteDialogApp = require( './components/AggregateGroupDeleteDialog.vue' );

Vue.createMwApp( App )
	.mount( '#ext-translate-aggregategroups-refresh' );

/**
 * Adds the delete click handler, and loads the AggregateGroupDeleteDialog component in order to
 * handle aggregate group deletion.
 */
function addDeleteAction() {
	const deleteButtons = document.querySelectorAll( '.js-button-delete' );
	deleteButtons.forEach( ( button ) => {
		button.addEventListener( 'click', onDeleteClick );
	} );

	const props = Vue.reactive( {
		aggregateGroupId: null,
		visible: false
	} );

	const vmDeleteDialogApp = Vue.createMwApp( {
		setup() {
			const onDeletedEvent = () => {
				window.location.reload();
			};

			const onCloseEvent = () => {
				reset();
			};

			function reset() {
				props.visible = false;
				props.aggregateGroupId = null;
			}

			return () => Vue.h(
				DeleteDialogApp,
				Object.assign(
					props,
					{
						onDeleted: onDeletedEvent,
						onClose: onCloseEvent
					}
				)
			);
		}
	} );

	const div = document.createElement( 'div' );
	document.querySelector( '#ext-translate-aggregategroups-refresh' )
		.insertAdjacentElement( 'afterend', div );
	vmDeleteDialogApp.mount( div );

	function onDeleteClick( event ) {
		props.visible = true;
		props.aggregateGroupId = getParentGroupId( event.target );
	}
}

function getParentGroupId( element ) {
	return element.closest( 'details.cdx-accordion' ).dataset.groupId;
}

addDeleteAction();
