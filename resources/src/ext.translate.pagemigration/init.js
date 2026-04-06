if ( !window.QUnit ) {
	const Vue = require( 'vue' );
	const App = require( './components/PageMigrationApp.vue' );

	const appContainer = document.getElementById( 'mw-tpm-sp-container' );
	const editSummary = appContainer.dataset.editsummary;
	const aggregateGroupsManageApp = Vue.createMwApp( App );
	aggregateGroupsManageApp.provide( 'editSummary', editSummary );
	aggregateGroupsManageApp.mount( appContainer );
}
