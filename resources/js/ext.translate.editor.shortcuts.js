/*
 * Translate editor shortcuts
 */
( function ( $ ) {
	'use strict';

	var translateEditorShortcuts = {
		showShortcuts: function () {
			var editorOffset, minTop, maxTop;

			editorOffset = this.$editor.offset();
			minTop = editorOffset.top;
			maxTop = minTop + this.$editor.height();

			this.hideShortcuts();
			this.$editor.find( '.shortcut-activated:visible' ).each( function ( index ) {
				var popup,
					$this = $( this ),
					offset = $this.offset();

				// Let's not have numbers appear outside the editor over other content
				if ( offset.top + 15 > maxTop || offset.top < minTop ) {
					return;
				}

				popup = $( '<div>' )
					.text( index + 1 )
					.offset( offset )
					.addClass( 'shortcut-popup' )
					.css( 'position', 'absolute' )
					.appendTo( 'body' );

				// Insertables need special positioning
				if ( $this.is( '.insertable' ) ) {
					popup.css( 'margin-top', '30px' );
				}
			} );
		},

		hideShortcuts: function () {
			$( '.shortcut-popup' ).remove();
		}
	};

	// Extend the translate editor
	$.extend( $.fn.translateeditor.Constructor.prototype, translateEditorShortcuts );

}( jQuery, mediaWiki ) );
