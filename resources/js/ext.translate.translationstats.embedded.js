'use strict';
/* eslint-disable no-implicit-globals */

/*!
 * Used to embed translation stats graph on other pages.
 * @license GPL-2.0-or-later
 */
var EmbeddedHandler = function ( $graphContainer ) {
	var graphOptions = JSON.parse(
		$graphContainer.find( '[name="translationStatsGraphOptions"]' ).val()
	);

	function getHeight() {
		return parseInt( graphOptions.height, 10 );
	}

	function getWidth() {
		return parseInt( graphOptions.width, 10 );
	}

	function getAllOptions() {
		return {
			measure: graphOptions.count,
			days: graphOptions.days,
			start: graphOptions.start,
			granularity: graphOptions.scale,
			group: graphOptions.group,
			language: graphOptions.language,
			height: getHeight(),
			width: getWidth()
		};
	}

	return {
		getAllOptions: getAllOptions
	};
};

$( function () {
	var $graphContainers = $( '.mw-translate-translationstats-container' ),
		currentGraph = 0,
		graphInstances = [];

	function loadGraph() {
		var currentGraphBuilder = graphInstances[ currentGraph ].graphBuilder,
			currentOptions = graphInstances[ currentGraph ].options.getAllOptions();

		currentGraphBuilder
			.display( currentOptions )
			.always( function () {
				++currentGraph;
				if ( currentGraph < graphInstances.length ) {
					loadGraph();
				}
			} );
	}

	// Create graph and options instances, then display loader
	function initGraph( $graphContainer ) {
		var graphOptions = new EmbeddedHandler( $graphContainer );
		var graphBuilder = new mw.translate.TranslationStatsGraphBuilder(
			$graphContainer, graphOptions.getAllOptions()
		);
		graphBuilder.showLoading();

		return {
			graphBuilder: graphBuilder,
			options: graphOptions
		};
	}

	for ( ;currentGraph < $graphContainers.length; ++currentGraph ) {
		graphInstances.push(
			initGraph( $graphContainers.eq( currentGraph ) )
		);
	}

	currentGraph = 0;
	setTimeout( function () {
		// Give time to display the loaders.
		loadGraph();
	} );
} );
