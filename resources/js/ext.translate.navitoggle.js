/*!
 * Introduces a toggle icon than can be used to hide navigation menu in vector
 * @author Niklas Laxström
 * @license GPL-2.0+
 */
( function ( mw, $ ) {
	'use strict';

	var delay = ( function () {
		var timer = 0;

		return function ( callback, milliseconds ) {
			clearTimeout( timer );
			timer = setTimeout( callback, milliseconds );
		};
	}() );

	$( document ).ready( function () {
		var $miniLogo, $toggle, rtl, delim,
			$body = $( 'body' );

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
				// Allow for animations etc to go
				delay( function () {
					$( window ).trigger( 'resize' );
					$( window ).trigger( 'scroll' );
				}, 250 );
			} );

		$( 'body' ).append( $miniLogo, $toggle );

		if ( $body.width() < 1000 ) {
			$body.addClass( 'tux-navi-collapsed' );
		}
	} );
}( mediaWiki, jQuery ) );
