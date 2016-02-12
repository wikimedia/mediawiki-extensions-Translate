/*!
 * Translate editor shortcuts
 */
( function ( $, mw ) {
	'use strict';

	var translateEditorShortcuts = {
		showShortcuts: function () {
			var editorOffset, minTop, maxTop, maxLeft, middle, rtl;

			// Any better way?
			rtl = $( 'body' ).is( '.rtl' );

			editorOffset = this.$editor.offset();
			minTop = editorOffset.top;
			maxTop = minTop + this.$editor.outerHeight();
			middle = minTop + ( maxTop - minTop ) / 2;

			maxLeft = editorOffset.left;
			if ( !rtl ) {
				maxLeft += this.$editor.outerWidth();
			}

			this.hideShortcuts();

			// For scrolling up and down
			$( '<div>' )
				.text( '↑' )
				.offset( { top: middle - 10, left: maxLeft } )
				.addClass( 'shortcut-popup' )
				.appendTo( 'body' );

			$( '<div>' )
				.text( '↓' )
				.offset( { top: middle + 10, left: maxLeft } )
				.addClass( 'shortcut-popup' )
				.appendTo( 'body' );

			this.$editor.find( '.shortcut-activated:visible' ).each( function ( index ) {
				var $this = $( this ),
					offset = $this.offset();

				if ( rtl ) {
					offset.left += $this.outerWidth();
				}

				// Let's not have numbers appear outside the editor over other content
				if ( offset.top > maxTop || offset.top < minTop ) {
					return;
				}

				$( '<div>' )
					.text( index + 1 )
					.offset( offset )
					.addClass( 'shortcut-popup' )
					.appendTo( 'body' );
			} );
		},

		hideShortcuts: function () {
			$( '.shortcut-popup' ).remove();
		}
	};

	mw.translate.editor = mw.translate.editor || {};
	$.extend( mw.translate.editor, translateEditorShortcuts );

}( jQuery, mediaWiki ) );
