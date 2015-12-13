/*!
 * Trigger a textchange event on text change in input fields.
 * And make it cross browser compatible.
 *
 * @author Santhosh Thottingal, 2013
 * @see https://gist.github.com/mkelly12/424774
 */
( function ( $ ) {
	'use strict';

	$.event.special.textchange = {

		setup: function () {
			$( this )
				.data( 'lastValue', $( this ).val() )
				.on( 'keyup.textchange', $.event.special.textchange.handler )
				.on( 'cut.textchange paste.textchange input.textchange', $.event.special.textchange.delayedHandler );
		},

		teardown: function () {
			$( this ).unbind( '.textchange' );
		},

		handler: function () {
			$.event.special.textchange.triggerIfChanged( $( this ) );
		},

		delayedHandler: function () {
			var element = $( this );
			setTimeout( function () {
				$.event.special.textchange.triggerIfChanged( element );
			}, 25 );
		},

		triggerIfChanged: function ( element ) {
			var current = element.val();
			if ( current !== element.data( 'lastValue' ) ) {
				element.trigger( 'textchange', [ element.data( 'lastValue' ) ] );
				element.data( 'lastValue', current );
			}
		}
	};

} )( jQuery );
