/*!
 * @author Santhosh Thottingal
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

( function () {
	'use strict';

	$( function () {
		$( '#wpUserLanguage' ).multiselectautocomplete( { inputbox: '#tpt-prioritylangs' } );

		$( '#mw-content-text' ).on( 'click', '.mw-translate-jspost', function ( e ) {
			var params,
				uri = new mw.Uri( e.target.href );

			params = uri.query;
			params.token = mw.user.tokens.get( 'csrfToken' );
			$.post( uri.path, params ).done( function () {
				location.reload();
			} );

			e.preventDefault();
		} );
	} );
}() );
