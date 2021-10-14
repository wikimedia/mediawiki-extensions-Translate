/*!
 * Introduces a toggle icon than can be used to hide navigation menu in vector
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
( function () {
	'use strict';

	var $body = $( document.body );

	// Bail out on the new Vector skin
	if ( $( '#mw-sidebar-button' ).length ) {
		return;
	}

	if ( $body.width() < 1000 || mw.storage.get( 'translate-navitoggle' ) === '1' ) {
		$body.addClass( 'tux-navi-collapsed' );
	}

	$( function () {
		var rtl = $body.hasClass( 'rtl' );
		var delim = rtl ?
			$( '#mw-head-base' ).css( 'margin-right' ) :
			$( '#mw-head-base' ).css( 'margin-left' );

		var $miniLogo = $( '#p-logo' )
			.clone()
			.removeAttr( 'id' )
			.addClass( 'tux-navi-minilogo' );

		var $toggle = $( '<div>' )
			.addClass( 'tux-navitoggle' )
			.css( rtl ? 'right' : 'left', delim )
			.on( 'click', function () {
				$body.toggleClass( 'tux-navi-collapsed' );
				mw.storage.set(
					'translate-navitoggle',
					String( Number( $body.hasClass( 'tux-navi-collapsed' ) ) )
				);
			} );

		$body.append( $miniLogo, $toggle );
	} );
}() );
