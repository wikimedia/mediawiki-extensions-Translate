/*!
 * JavaScript functions for embedding jQuery controls
 * into translation notification form.
 *
 * @author Amir E. Aharoni
 * @author Siebrand Mazeland
 * @copyright Copyright © 2012-2013 Amir E. Aharoni, Siebrand Mazeland
 * @license GPL-2.0+
 */

jQuery( document ).ready( function ( $ ) {
	'use strict';

	// Based on UploadWizard, TranslationNotifications
	$( '#start' )
		.datepicker( {
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
