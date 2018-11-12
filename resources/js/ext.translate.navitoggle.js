/*!
 * Introduces a toggle icon than can be used to hide navigation menu in vector
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
( function () {
	'use strict';

	var $body = $( 'body' );

	if ( $body.width() < 1000 || mw.storage.get( 'translate-navitoggle' ) === '1' ) {
		$body.addClass( 'tux-navi-collapsed' );
	}

	$( function () {
		var $miniLogo, $toggle, rtl, delim;

		rtl = $body.hasClass( 'rtl' );
		delim = rtl ?
			$( '#mw-head-base' ).css( 'margin-right' ) :
			$( '#mw-head-base' ).css( 'margin-left' );

		$miniLogo = $( '#p-logo' )
			.clone()
			.removeAttr( 'id' )
			.addClass( 'tux-navi-minilogo' );

		$toggle = $( '<div>' )
			.addClass( 'tux-navitoggle' )
			.css( rtl ? 'right' : 'left', delim )
			.click( function () {
				$body.toggleClass( 'tux-navi-collapsed' );
				mw.storage.set(
					'translate-navitoggle',
					String( Number( $body.hasClass( 'tux-navi-collapsed' ) ) )
				);
			} );

		$body.append( $miniLogo, $toggle );
	} );
}() );
