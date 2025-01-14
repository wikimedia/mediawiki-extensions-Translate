const Vue = require( 'vue' );
const App = require( './components/AggregateGroupsToolboxApp.vue' );
const DeleteDialogApp = require( './components/AggregateGroupDeleteDialog.vue' );
const AggregateGroupDialog = require( './components/AggregateGroupDialog.vue' );
const AggregateGroupAssociation = require( './components/AggregateGroupAssociation.vue' );
const AggregateGroupSubGroupItem = require( './components/AggregateGroupSubGroupItem.vue' );
const createAggregateGroupApiFactory = require( '../services/aggregategroup.api.factory.js' );
const performEntitySearch = require( '../services/translationentitysearch.api.js' );

const aggregateGroupApi = createAggregateGroupApiFactory();
const aggregateGroupsManageApp = Vue.createMwApp( App );
aggregateGroupsManageApp.provide( 'aggregateGroupApi', aggregateGroupApi );
aggregateGroupsManageApp.mount( '#ext-translate-aggregategroups-refresh' );

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
	vmDeleteDialogApp.provide( 'aggregateGroupApi', aggregateGroupApi );
	vmDeleteDialogApp.mount( div );

	function onDeleteClick( event ) {
		props.visible = true;
		props.aggregateGroupId = getParentGroupId( event.target );
	}
}

/**
 * Adds the edit click handler, and loads the AggregateGroupDialog component in order to
 * handle aggregate group editing.
 */
function addEditAction() {
	const editButtons = document.querySelectorAll( '.js-button-edit' );
	editButtons.forEach( ( button ) => {
		button.addEventListener( 'click', onEditClick );
	} );

	const props = Vue.reactive( {
		aggregateGroupId: null,
		visible: false
	} );

	const vmEditDialogApp = Vue.createMwApp( {
		setup() {
			const onCloseEvent = () => {
				props.visible = false;
				props.aggregateGroupId = null;
			};

			const onSavedEvent = () => {
				window.location.reload();
			};

			return () => Vue.h(
				AggregateGroupDialog,
				Object.assign(
					props,
					{
						onClose: onCloseEvent,
						onSaved: onSavedEvent
					}
				)
			);
		}
	} );

	const div = document.createElement( 'div' );
	document.querySelector( '#ext-translate-aggregategroups-refresh' )
		.insertAdjacentElement( 'afterend', div );
	vmEditDialogApp.provide( 'aggregateGroupApi', aggregateGroupApi );
	vmEditDialogApp.mount( div );

	function onEditClick( event ) {
		props.visible = true;
		props.aggregateGroupId = getParentGroupId( event.target );
	}
}

function addDeleteSubGroupAction() {
	// Avoid adding too many click handlers by adding the handler to the accordion
	// and then determining if the delete button was clicked.
	const accordionContentOrderedList =
		document.querySelectorAll( '.cdx-accordion__content ol' );
	accordionContentOrderedList.forEach( ( element ) => {
		element.addEventListener( 'click', onAccordionContentClick );
	} );

	function onAccordionContentClick( event ) {
		const deleteButton = event.target.closest( '.js-button-subgroup-delete' );
		if ( !deleteButton ) {
			return;
		}

		const aggregateGroupId = getParentGroupId( deleteButton );
		const listItem = deleteButton.closest( 'li' );
		const subGroupId = listItem.dataset.groupId;
		aggregateGroupApi.removeMessageGroup( aggregateGroupId, subGroupId )
			.then( () => listItem.remove() )
			.catch( ( code, data ) => {
				mw.log.error(
					`Dissociating '${ subGroupId }' from '${ aggregateGroupId }' failed`,
					code,
					data
				);
				mw.notify(
					data.error && data.error.info || mw.msg( 'tpt-aggregategroup-disassociate-error' ),
					{ type: 'error' }
				);
			} );
	}
}

function addAssociateSubGroupAction() {
	const associationContainer =
		document.querySelectorAll( '.mw-translate-aggregategroup-associate' );

	associationContainer.forEach( ( element ) => {
		const parentAggregateGroup = element.closest( '.mw-translate-aggregategroup-container' );

		/**
		 * Callback to gather groups that are already part of the aggregate group.
		 *
		 * @return {Set<string>}
		 */
		function getExistingGroups() {
			const existingGroupListItems = parentAggregateGroup.querySelectorAll( 'ol li' );
			const existingGroupIds = new Set();
			existingGroupListItems.forEach( ( listItem ) => {
				const groupId = listItem.dataset.groupId;
				if ( groupId ) {
					existingGroupIds.add( groupId );
				}
			} );

			return existingGroupIds;
		}

		function onSubGroupAdded( groupDetails ) {
			const vmSubGroupItemApp = Vue.createMwApp(
				AggregateGroupSubGroupItem,
				{
					groupId: groupDetails.id,
					groupLabel: groupDetails.label,
					groupURL: groupDetails.url
				}
			);
			const tempDiv = document.createElement( 'div' );
			// Use the HTML from the template only component to create the list item
			const instance = vmSubGroupItemApp.mount( tempDiv );
			parentAggregateGroup.querySelector( 'ol' ).appendChild( instance.$el );
		}

		// Create and mount the app.
		const vmGroupAssociationApp = Vue.createMwApp( {
			setup() {
				return () => Vue.h(
					AggregateGroupAssociation,
					{
						aggregateGroupId: parentAggregateGroup.dataset.groupId,
						getExistingGroups,
						onSaved: onSubGroupAdded
					}
				);
			}
		} );
		vmGroupAssociationApp.provide( 'performEntitySearch', performEntitySearch );
		vmGroupAssociationApp.provide( 'aggregateGroupApi', aggregateGroupApi );
		vmGroupAssociationApp.mount( element );
	} );
}

function getParentGroupId( element ) {
	return element.closest( 'details.cdx-accordion' ).dataset.groupId;
}

addDeleteAction();
addEditAction();
addDeleteSubGroupAction();
addAssociateSubGroupAction();
