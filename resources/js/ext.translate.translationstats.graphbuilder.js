/*!
 * Graph component to display translation stats using ChartJS
 * @license GPL-2.0-or-later
 */

( function () {
	'use strict';
	var graphInfo = {
			edits: mw.msg( 'translate-statsf-count-edits' ),
			users: mw.msg( 'translate-statsf-count-users' ),
			registrations: mw.msg( 'translate-statsf-count-registrations' ),
			reviews: mw.msg( 'translate-statsf-count-reviews' ),
			reviewers: mw.msg( 'translate-statsf-count-reviewers' )
		}, granularityInfo = {
			years: mw.msg( 'translate-statsf-scale-years' ),
			months: mw.msg( 'translate-statsf-scale-months' ),
			weeks: mw.msg( 'translate-statsf-scale-weeks' ),
			days: mw.msg( 'translate-statsf-scale-days' ),
			hours: mw.msg( 'translate-statsf-scale-hours' )
		}, graphColors = [
			'skyblue', 'green', 'orange', 'blue', 'red', 'darkgreen', 'purple', 'peru',
			'cyan', 'salmon', 'slateblue', 'yellowgreen', 'magenta', 'aquamarine', 'gold', 'violet'
		],
		GraphBuilder;

	/**
	 * Used to display translation stats graph. Each instance of this class manages one
	 * instance of the graph.
	 *
	 * @param {Object} $graphContainer The title of the page including language code
	 *   to store the translation.
	 * @param {Object} graphOptions Graph options, current only processes the width and height.
	 * @return {Object} Instance of the graph builder
	 */
	GraphBuilder = function ( $graphContainer, graphOptions ) {
		var $graphElement = $( '<canvas>' )
				.attr( 'class', 'mw-translate-translationstats-graph' )
				.attr( 'role', 'img' )
				.attr( 'tabindex', 0 )
				.text( mw.msg( 'translate-statsf-graph-alt-text-info' ) ),
			$graphWrapper = $( '<div>' )
				.attr( 'class', 'mw-translationstats-graph-container' ),
			$loadingElement = $( '<div>' )
				.attr( 'class', 'mw-translate-loading-spinner' ),
			$errorElement = $( '<div>' )
				.attr( 'class', 'mw-translate-error-container' ),
			$tableElement = $( '<table>' )
				.addClass( 'wikitable mw-translate-translationstats-table' )
				.attr( 'tabindex', 0 )
				.attr(
					'summary', mw.msg( 'translate-statsf-alt-text' )
				),
			lineChart;

		$graphWrapper
			.width( graphOptions && graphOptions.width )
			.height( graphOptions && graphOptions.height );

		// Set the container height and width if passed.
		$graphContainer.append( [
			$graphWrapper,
			$loadingElement,
			$errorElement
		] );

		$graphWrapper.append( $graphElement );

		function display( options ) {
			if ( lineChart ) {
				cleanup();
			}

			// Set the appropriate height and width and display the loader.
			$graphWrapper
				.width( options.width )
				.height( options.height );

			showLoading();

			return getData( options ).then( function ( graphData ) {
				// Hide the loader before displaying the data.
				showData( graphData, options );
			} ).fail( function ( errorCode, results ) {
				var errorInfo = results && results.error ? results.error.info :
					mw.msg( 'translate-statsf-unknown-error' );
				displayError( mw.msg( 'translate-statsf-error-message', errorInfo ) );
			} ).always( function () {
				hideLoading();
			} );
		}

		function showData( apiResponse, options ) {
			var graphData = getAxesLabelsAndData( apiResponse.data ),
				graphDatasets = [],
				datasetLabels = apiResponse.labels;

			if ( graphData.data.length ) {
				graphData.data.forEach( function ( dataset, datasetIndex ) {
					var graphDataset = {
						data: dataset,
						fill: false,
						borderColor: getLineColor( datasetIndex )
					};

					if ( datasetLabels[ datasetIndex ] ) {
						graphDataset.label = datasetLabels[ datasetIndex ];
					}

					graphDatasets.push( graphDataset );
				} );
			}

			lineChart = new Chart( $graphElement, {
				type: 'line',
				data: {
					labels: graphData.axesLabels,
					datasets: graphDatasets
				},
				options: {
					maintainAspectRatio: false,
					legend: {
						display: datasetLabels.length !== 0
					},
					scales: {
						yAxes: [ {
							scaleLabel: {
								display: true,
								labelString: getXAxesLabel( options.measure )
							},
							ticks: {
								beginAtZero: true,
								precision: 0,
								callback: function ( value ) {
									return mw.language.convertNumber( Number( value ) );
								}
							}
						} ],
						xAxes: [ {
							scaleLabel: {
								display: true,
								labelString: getYAxesLabel( options.granularity )
							},
							ticks: {
								maxTicksLimit: 15
							},
							gridLines: {
								display: false
							}
						} ]
					},
					tooltips: {
						callbacks: {
							label: function ( tooltipItem, data ) {
								var convertedValue = mw.language.convertNumber( Number( tooltipItem.yLabel ) ),
									label = data.datasets[ tooltipItem.datasetIndex ].label;

								if ( label ) {
									return label + ': ' + convertedValue;
								}

								return convertedValue;
							}
						}
					}
				}
			} );

			// Generate table inside the canvas element to improve accessibility.
			showTable( graphData, datasetLabels, options );
		}

		function getAxesLabelsAndData( jsonGraphData ) {
			var labels = [], graphData = [],
				labelIndex = 0,
				maxValue = 0, minValue = 0;

			for ( var labelProp in jsonGraphData ) {
				if ( labels.indexOf( labelProp ) === -1 ) {
					labels.push( labelProp );
				}

				var labelData = jsonGraphData[ labelProp ];

				for ( var i = 0; i < labelData.length; ++i ) {
					if ( !graphData[ i ] ) {
						graphData[ i ] = [];
					}

					var currentValue = labelData[ i ];
					graphData[ i ][ labelIndex ] = currentValue;
					if ( currentValue < minValue ) {
						minValue = currentValue;
					}

					if ( currentValue > maxValue ) {
						maxValue = currentValue;
					}
				}

				++labelIndex;
			}

			return {
				axesLabels: labels,
				data: graphData,
				max: maxValue,
				min: minValue
			};
		}

		function getXAxesLabel( measure ) {
			return graphInfo[ measure ];
		}

		function getYAxesLabel( granularity ) {
			return granularityInfo[ granularity ];
		}

		function getData( filterOptions ) {
			var api = new mw.Api(),
				apiParams = {
					action: 'translationstats',
					count: filterOptions.measure,
					days: filterOptions.days,
					start: filterOptions.start || null,
					scale: filterOptions.granularity,
					group: filterOptions.group,
					language: filterOptions.language,
					formatversion: 2
				};

			// Remove null or empty array from request object
			Object.keys( apiParams ).forEach( function ( apiParamKey ) {
				var apiParamValue = apiParams[ apiParamKey ];
				if (
					apiParamValue === null ||
						( Array.isArray( apiParamValue ) && apiParamValue.length === 0 )
				) {
					delete apiParams[ apiParamKey ];
				}
			} );

			return api.get( apiParams ).then( function ( result ) {
				return result.translationstats;
			} );
		}

		function getLineColor( index ) {
			var colorIndex = index % graphColors.length,
				colorName = graphColors[ colorIndex ];
			return colorName;
		}

		function displayError( errorMessage ) {
			$errorElement.text( errorMessage );
			$graphContainer.addClass( 'mw-translate-has-error' )
				.height( 'auto' );
		}

		function showLoading() {
			// show loading, and hide error messages.
			$graphContainer.addClass( 'mw-translate-loading' )
				.removeClass( 'mw-translate-has-error' );
		}

		function hideLoading() {
			$graphContainer.removeClass( 'mw-translate-loading' );
		}

		function showTable( graphData, datasetLabels, options ) {
			$tableElement
				.append(
					$( '<caption>' ).text( getGraphSummary( options ) )
				)
				.append( getTableHead( datasetLabels, options ) )
				.append( getTableBody( graphData ) );

			$graphContainer.append( $tableElement );
		}

		function getTableHead( datasetLabels, options ) {
			var $tableHead = $( '<thead>' ),
				$tableHeadRow = $( '<tr>' ),
				i = 0;

			$tableHeadRow.append( $( '<th>' ).text( getYAxesLabel( options.granularity ) ) );

			if ( datasetLabels && datasetLabels.length ) {
				for ( ; i < datasetLabels.length; ++i ) {
					$tableHeadRow.append( $( '<th>' ).text( datasetLabels[ i ] ) );
				}
			} else {
				$tableHeadRow.append( $( '<th>' ).text( getXAxesLabel( options.measure ) ) );
			}

			return $tableHead.append( $tableHeadRow );
		}

		function getTableBody( graphData ) {
			var $tbody = $( '<tbody>' );

			for ( var scaleIndex = 0; scaleIndex < graphData.axesLabels.length; scaleIndex++ ) {
				var $tBodyRow = $( '<tr>' )
					.append( $( '<td>' ).text( graphData.axesLabels[ scaleIndex ] ) );

				for ( var datasetIndex = 0; datasetIndex < graphData.data.length; datasetIndex++ ) {
					var columnValue = '';
					if (
						graphData.data[ datasetIndex ] &&
						graphData.data[ datasetIndex ][ scaleIndex ] !== undefined
					) {
						columnValue =
							mw.language.convertNumber(
								Number( graphData.data[ datasetIndex ][ scaleIndex ] )
							);
					}
					$tBodyRow.append( $( '<td>' ).text( columnValue ) );
				}

				$tbody.append( $tBodyRow );
			}

			return $tbody;
		}

		function cleanup() {
			lineChart.destroy();
			$tableElement.remove();
		}

		function getGraphSummary( options ) {
			return getXAxesLabel( options.measure ) + ' / ' +
				getYAxesLabel( options.granularity );
		}

		return {
			display: display,
			showLoading: showLoading,
			hideLoading: hideLoading
		};
	};

	mw.translate = mw.translate || {};
	mw.translate.TranslationStatsGraphBuilder = mw.translate.TranslationStatsGraphBuilder || GraphBuilder;
}() );
