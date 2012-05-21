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

jQuery( document ).ready( function( $ ) {
	// Based on UploadWizard, TranslationNotifications
	$( '#start' ).datepicker( {
		dateFormat: 'yy-mm-dd',
		constrainInput: false,
		showOn: 'focus',
		changeMonth: true,
		changeYear: true,
		showAnim: false,
		showButtonPanel: true,
		maxDate: new Date()
	} )
	.attr( 'autocomplete', 'off' );
} );
