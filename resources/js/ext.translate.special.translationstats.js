'use strict';
/* eslint-disable no-implicit-globals */

/*!
 * Display translation stats via a form.
 * @author Amir E. Aharoni
 * @author Siebrand Mazeland
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013 Amir E. Aharoni, Siebrand Mazeland
 * @license GPL-2.0-or-later
 */
var FormHandler = function () {
	var $form = $( '#translationStatsConfig' ),
		onFilter = null;

	function getHeight() {
		return parseInt( $form.find( 'input[name="height"]' ).val(), 10 );
	}

	function getWidth() {
		return parseInt( $form.find( 'input[name="width"]' ).val(), 10 );
	}

	function getLanguages() {
		var languages = $form.find( 'input[name="language"]' ).val().trim();

		if ( languages.length > 0 ) {
			return getSplitValues( languages );
		}

		return [];
	}

	function getGroups() {
		var groups = $form.find( 'input[name="group"]' ).val().trim();

		if ( groups.length > 0 ) {
			return getSplitValues( groups );
		}

		return [];
	}

	function getGranularity() {
		return $form.find( 'input[name="scale"]:checked' ).val().trim();
	}

	function getMeasure() {
		return $form.find( 'input[name="count"]:checked' ).val().trim();
	}

	function getDays() {
		return parseInt( $form.find( 'input[name="days"]' ).val(), 10 );
	}

	function getStart() {
		return $form.find( '#start' ).val();
	}

	function getAllOptions() {
		return {
			measure: getMeasure(),
			days: getDays(),
			start: getStart(),
			granularity: getGranularity(),
			group: getGroups(),
			language: getLanguages(),
			height: getHeight(),
			width: getWidth()
		};
	}

	function getSplitValues( values ) {
		return values.split( ',' ).map( function ( value ) {
			return value.trim();
		} );
	}

	function filter() {
		if ( !this.onFilter ) {
			return;
		}

		this.onFilter( this.getAllOptions() );
	}

	return {
		onFilter: onFilter,
		filter: filter,
		getAllOptions: getAllOptions
	};
};

$( function () {
	var $input = $( '#start' ),
		formHandler = new FormHandler(),
		$graphContainer = $( '#translationStatsGraphContainer' );

	var defaultDate = new Date();
	defaultDate.setDate( 1 );

	var defaultValue;
	if ( $input.val() ) {
		defaultValue = new Date( $input.val() );
	}

	var widget = new mw.widgets.datetime.DateTimeInputWidget( {
		formatter: {
			format: '${year|0}-${month|0}-${day|0}',
			defaultDate: defaultDate
		},
		type: 'date',
		value: defaultValue,
		max: new Date()
	} );

	$input.after( widget.$element ).addClass( 'mw-translate-translationstats-hide' );
	widget.on( 'change', function ( data ) {
		$input.val( data + 'T00:00:00' );
	} );

	// Check if the graph container has been loaded
	if ( $graphContainer.length !== 0 ) {
		var graphBuilder = new mw.translate.TranslationStatsGraphBuilder( $graphContainer );
		graphBuilder.display( formHandler.getAllOptions() );
	}
} );
