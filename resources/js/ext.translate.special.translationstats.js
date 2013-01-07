/**
 * JavaScript functions for embedding jQuery controls
 * into translation notification form.
 *
 * @author Amir E. Aharoni
 * @author Siebrand Mazeland
 * @copyright Copyright © 2012 Amir E. Aharoni
 * @copyright Copyright © 2012 Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

jQuery( document ).ready( function ( $ ) {
	'use strict';

	// Based on UploadWizard, TranslationNotifications
	$( '#start' )
		.datepicker( {
			dateFormat: 'yymmdd',
			constrainInput: false,
			showOn: 'focus',
			changeMonth: true,
			changeYear: true,
			showAnim: false,
			showButtonPanel: true,
			maxDate: new Date(),
			onClose: function ( dateText, inst ) {
				// TranslationStats works with the yyyymmddhhmmss format,
				// so zeros that represents generic hh:mm:ss must be added.
				// The zeros are added only if a date was actually selected
				// and is not currently displayed.
				if ( dateText !== '' && inst.input.val().length < 14 ) {
					inst.input.val( dateText + '000000' );
				}
			}
		} )
		.attr( 'autocomplete', 'off' );
} );
