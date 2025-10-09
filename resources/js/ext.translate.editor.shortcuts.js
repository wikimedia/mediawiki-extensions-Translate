/*!
 * Translate editor shortcuts
 */
( function () {
	'use strict';
	var translateEditorShortcuts = {
		/**
		 * @internal
		 */
		showShortcuts: function () {
			// Any better way?
			var rtl = $( document.body ).is( '.rtl' );

			var editorOffset = this.$editor.offset();
			var minTop = editorOffset.top;
			var maxTop = minTop + this.$editor.outerHeight();
			var middle = minTop + ( maxTop - minTop ) / 2;
			var maxLeft = rtl ? editorOffset.left : editorOffset.left + this.$editor.outerWidth();

			this.hideShortcuts();

			// For scrolling up and down
			$( '<div>' )
				.text( '↑' )
				.addClass( 'shortcut-popup' )
				.appendTo( document.body )
				.offset( { top: middle - 15, left: maxLeft } )
				.css( 'transform', 'translate( -50%, 0 )' );

			$( '<div>' )
				.text( '↓' )
				.addClass( 'shortcut-popup' )
				.appendTo( document.body )
				.offset( { top: middle + 15, left: maxLeft } )
				.css( 'transform', 'translate( -50%, 0 )' );

			this.$editor.find( '.shortcut-activated:visible' ).each( function ( index ) {
				// Make indexing human-friendly
				index += 1;

				// Index 10 actually gets the shortcut 0
				if ( index === 10 ) {
					index = 0;
				}

				// Don't display two-digit indexes
				if ( index > 10 ) {
					return;
				}

				// Let's not have numbers appear outside the editor over other content
				var offset = getStartCornerOffsetOf( $( this ), rtl );
				if ( offset.top > maxTop || offset.top < minTop ) {
					return;
				}

				$( '<div>' )
					.text( index )
					.addClass( 'shortcut-popup' )
					.appendTo( document.body )
					.offset( offset )
					.css( 'transform', 'translate( -50%, -50% )' );
			} );
		},

		/**
		 * @internal
		 */
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
