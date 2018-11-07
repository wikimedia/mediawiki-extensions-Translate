/*!
 * Translate editor shortcuts
 */
( function () {
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
			maxLeft = rtl ? editorOffset.left : editorOffset.left + this.$editor.outerWidth();

			this.hideShortcuts();

			// For scrolling up and down
			$( '<div>' )
				.text( '↑' )
				.addClass( 'shortcut-popup' )
				.appendTo( 'body' )
				.offset( { top: middle - 15, left: maxLeft } )
				.css( 'transform', 'translate( -50%, 0 )' );

			$( '<div>' )
				.text( '↓' )
				.addClass( 'shortcut-popup' )
				.appendTo( 'body' )
				.offset( { top: middle + 15, left: maxLeft } )
				.css( 'transform', 'translate( -50%, 0 )' );

			this.$editor.find( '.shortcut-activated:visible' ).each( function ( index ) {
				var offset = getStartCornerOffsetOf( $( this ), rtl );

				// Let's not have numbers appear outside the editor over other content
				if ( offset.top > maxTop || offset.top < minTop ) {
					return;
				}

				$( '<div>' )
					.text( index + 1 )
					.addClass( 'shortcut-popup' )
					.appendTo( 'body' )
					.offset( offset )
					.css( 'transform', 'translate( -50%, -50% )' );
			} );
		},

		hideShortcuts: function () {
			$( '.shortcut-popup' ).remove();
		}
	};

	function getStartCornerOffsetOf( $element, rtl ) {
		var offset = $element.offset();

		if ( rtl ) {
			offset.left += $element.outerWidth();
		}

		return offset;
	}

	mw.translate.editor = mw.translate.editor || {};
	$.extend( mw.translate.editor, translateEditorShortcuts );

}() );
