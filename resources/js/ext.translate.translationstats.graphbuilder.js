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
				.attr( 'class', 'mw-translate-translationstats-graph' ),
			$loadingElement = $( '<div>' )
				.attr( 'class', 'mw-translate-loading-spinner' ),
			$errorElement = $( '<div>' )
				.attr( 'class', 'mw-translate-error-container' ),
			lineChart;

		$graphContainer.append( [
			$graphElement,
			$loadingElement,
			$errorElement
		] );

		// Set the container height and width if passed.
		if ( graphOptions ) {
			if ( graphOptions.width ) {
				$graphContainer.width( graphOptions.width );
			}

			if ( graphOptions.height ) {
				$graphContainer.height( graphOptions.height );
			}
		}

		function display( options ) {
			if ( lineChart ) {
				lineChart.destroy();
			}

			// Set the appropriate height and width and display the loader.
			$graphContainer.width( options.width )
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
		}

		function getAxesLabelsAndData( jsonGraphData ) {
			var labelProp, labels = [], graphData = [],
				labelData, i, labelIndex = 0,
				currentValue, maxValue = 0, minValue = 0;

			for ( labelProp in jsonGraphData ) {
				if ( labels.indexOf( labelProp ) === -1 ) {
					labels.push( labelProp );
				}

				labelData = jsonGraphData[ labelProp ];

				for ( i = 0; i < labelData.length; ++i ) {
					if ( !graphData[ i ] ) {
						graphData[ i ] = [];
					}

					currentValue = labelData[ i ];
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

		return {
			display: display,
			showLoading: showLoading,
			hideLoading: hideLoading
		};
	};

	mw.translate = mw.translate || {};
	mw.translate.TranslationStatsGraphBuilder = mw.translate.TranslationStatsGraphBuilder || GraphBuilder;
}() );
