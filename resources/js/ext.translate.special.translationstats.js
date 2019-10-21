/*!
 * JavaScript functions for embedding jQuery controls
 * into translation notification form.
 *
 * @author Amir E. Aharoni
 * @author Siebrand Mazeland
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013 Amir E. Aharoni, Siebrand Mazeland
 * @license GPL-2.0-or-later
 */

( function () {
	'use strict';

	$( function () {
		var widget, defaultValue, defaultDate,
			$input = $( '#start' );

		defaultDate = new Date();
		defaultDate.setDate( 1 );

		if ( $input.val() ) {
			defaultValue = new Date( $input.val() );
		}

		widget = new mw.widgets.datetime.DateTimeInputWidget( {
			formatter: {
				format: '${year|0}-${month|0}-${day|0}',
				defaultDate: defaultDate
			},
			type: 'date',
			value: defaultValue,
			max: new Date()
		} );

		$input.after( widget.$element ).hide();
		widget.on( 'change', function ( data ) {
			$input.val( data + 'T00:00:00' );
		} );
	} );
}() );
