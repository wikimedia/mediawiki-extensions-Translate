/*!
 * JavaScript functions for embedding jQuery controls
 * into translation notification form.
 *
 * @author Amir E. Aharoni
 * @author Siebrand Mazeland
 * @copyright Copyright © 2012-2013 Amir E. Aharoni, Siebrand Mazeland
 * @license GPL-2.0+
 */

( function ( $, mw ) {
	'use strict';

	$( function () {
		var $input = $( '#start' ),
			datepicker = mw.loader.getState( 'mediawiki.widgets.datetime' ) === null;

		// Remove when MediaWiki 1.27 is no longer supported
		if ( datepicker ) {
			mw.loader.using( 'jquery.ui.datepicker' ).done( function () {
				$input.datepicker( {
					dateFormat: 'yy-mm-ddT00:00:00',
					constrainInput: false,
					showOn: 'focus',
					changeMonth: true,
					changeYear: true,
					showAnim: false,
					showButtonPanel: true,
					maxDate: new Date(),
				} )
				.attr( 'autocomplete', 'off' );
			} );
		} else {
			mw.loader.using( 'mediawiki.widgets.datetime' ).done( function () {
				var widget, defaultDate;

				defaultDate = new Date();
				defaultDate.setDate( 1 )
				defaultDate.setHours( 0, 0, 0, 0 );

				widget = new mw.widgets.datetime.DateTimeInputWidget( {
					formatter: {
						format: '${year|0}-${month|0}-${day|0}T${hour|0}:${minute|0}:${second|0}',
						value: Date.parse( $input.val() ),
						defaultDate: defaultDate,
						local: true
					},
					value: Date.parse( $input.val() ), // FIXME: does not work
					max: new Date()
				} );

				$input.after( widget.$element ).hide();
				widget.on( 'change', function ( data ) {
					$input.val( data.replace( /\.?0+$/, '' ) );
				} );
			} );
		};
	} );
}( jQuery, mediaWiki ) );
