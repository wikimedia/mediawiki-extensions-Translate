/**
 * JavaScript that implements the Ajax translation interface, which was at the
 * time of writing this probably the biggest usability problem in the extension.
 * Most importantly, it speeds up translating and keeps the list of translatable
 * messages open. It also allows multiple translation dialogs, for doing quick
 * updates to other messages or documentation, or translating multiple languages
 * simultaneously together with the "In other languages" display included in
 * translation helpers and implemented by utils/TranslationhHelpers.php.
 * The form itself is implemented by utils/TranslationEditPage.php, which is
 * called from Special:Translate/editpage?page=Namespace:pagename.
 *
 * TODO list:
 * * On succesful save, update the MessageTable display too.
 * * Autoload ui classes
 * * Instead of hc'd onscript, give them a class and use necessary triggers
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2009-2011 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

(function( $ ) {
	"use strict";
	var mw = mediaWiki;
	
	var translate = {
		dialogwidth: false,
 
		init: function() {
			mw.translate.dialogwidth = parseInt( mw.translate.windowWidth()*0.8, 10 );
		},

		MessageCheckUpdater: function( callback ) {
			this.act = function() {
				callback();
				delete this.timeoutID;
			};

			this.setup = function() {
				this.cancel();
				var self = this;
				this.timeoutID = window.setTimeout( self.act, 1000 );
			};

			this.cancel = function() {
				if ( typeof this.timeoutID === 'number' ) {
					window.clearTimeout( this.timeoutID );
					delete this.timeoutID;
				}
			};
		},

		windowWidth: function() {
			return window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
		},

		addAccessKeys: function( dialog ) {
			var buttons = {
				a: '.mw-translate-save',
				s: '.mw-translate-next',
				d: '.mw-translate-skip',
				h: '.mw-translate-history'
			};

			for ( var key in buttons ) {
				if ( !buttons.hasOwnProperty( key ) ) {
					continue;
				}

				$( buttons[key] )
					.val( function( i, b ) { return b.replace( / \(.\)$/, '' ); } )
					.removeAttr( 'accesskey' )
					.attr( 'title', '' );

				dialog.find( buttons[key] )
					.val( function( i, b ) { return b + ' (_)'.replace( '_', key ); } )
					.attr( 'accesskey', key )
					.attr( 'title', '[' + tooltipAccessKeyPrefix + key + ']' );
			}
		},
 
		registerFeatures: function( dialog, form, page, group ) {
			// Enable the collapsible element
			var $identical = $( '.mw-identical-title' );
			if ( $.isFunction( $identical.makeCollapsible ) ) {
				$identical.makeCollapsible();
			}
			
			if ( mw.config.get( 'trlKeys' ) ) {
				form.find( '.mw-translate-next' ).click( function() {
					mw.translate.openNext( page );
				} );

				form.find( '.mw-translate-skip' ).click( function() {
					mw.translate.openNext( page );
					dialog.dialog( 'close' );
					return false;
				} );
			} else {
				form.find( '.mw-translate-next, .mw-translate-skip' )
					.attr( 'disabled', 'disabled' )
					.css( 'display', 'none' );
			}

			form.find( '.mw-translate-history' ).click( function() {
				window.open( mw.config.get( 'wgServer' ) + mw.config.get( 'wgScript' ) + '?action=history&title=' + form.find( 'input[name=title]' ).val() );
				return false;
			} );
			
			form.find( '.mw-translate-support, .mw-translate-askpermission' ).click( function() {
				// Can use .data() only with 1.4.3 or newer
				window.open( $(this).attr( 'data-load-url' ) );
				return false;
			} );

			form.find( 'input#summary' ).focus( function() {
				$(this).css( 'width', '85%' );
			} );
			
			var textarea = form.find( '.mw-translate-edit-area' );
			textarea.css( 'display', 'block' );
			textarea.autoResize( {extraSpace: 15, limit: 200} ).trigger( 'change' );
			textarea.focus();

			if ( form.find( '.mw-translate-messagechecks' ) ) {
				var checker = new mw.translate.MessageCheckUpdater( function() {
					var url = mw.config.get( 'wgScript' ) + '?title=Special:Translate/editpage&suggestions=checks&page=$1&loadgroup=$2';
					url = url.replace( '$1', encodeURIComponent( page ) ).replace( '$2', encodeURIComponent( group ) );
					$.post( url, { translation: textarea.val() }, function( mydata ) {
						form.find( '.mw-translate-messagechecks' ).replaceWith( mydata );
					} );
				} );

				textarea.keyup( function() { checker.setup(); } );
			}
		},

		openDialog: function( page, group ) {
			var url = mw.config.get( 'wgScript' ) +  '?title=Special:Translate/editpage&suggestions=async&page=$1&loadgroup=$2';
			url = url.replace( '$1', encodeURIComponent( page ) ).replace( '$2', encodeURIComponent( group ) );
			var id = 'jsedit' +  page.replace( /[^a-zA-Z0-9_]/g, '_' );

			var dialog = $( '#' + id );
			if ( dialog.size() > 0 ) {
				dialog.dialog( 'option', 'position', 'top' );
				dialog.dialog( 'open' );
				return false;
			}

			$( '<div>' ).attr( 'id', id ).appendTo( $( 'body' ) );
			dialog = $( '#' + id );

			var spinner = $( '<div>' ).attr( 'class', 'mw-ajax-loader' );
			dialog.html( $( '<div>' ).attr( 'class', 'mw-ajax-dialog' ).html( spinner ) );

			dialog.load( url, false, function() {
				var form = $( '#' + id + ' form' );

				mw.translate.registerFeatures( dialog, form, page, group );
				mw.translate.addAccessKeys( form );
				form.hide().slideDown();

				form.ajaxForm( {
					dataType: 'json',
					success: function(json) {
						if ( json.error ) {
							alert( json.error.info + ' (' + json.error.code +')' );
						} else if ( json.edit.result === 'Failure' ) {
							alert( mw.msg( 'translate-js-save-failed' ) );
						} else if ( json.edit.result === 'Success' ) {
							dialog.dialog( 'close' );
						} else {
							alert( mw.msg( 'translate-js-save-failed' ) );
						}
					}
				} );
			} );

			dialog.dialog( {
				bgiframe: true,
				width: mw.translate.dialogwidth,
				title: page,
				position: 'top',
				resize: function() { $( '#' + id + ' textarea' ).width( '100%' ); },
				resizeStop: function() { mw.translate.dialogwidth = $( '#' + id ).width(); },
				focus: function() { mw.translate.addAccessKeys( dialog ); },
				close: function() { mw.translate.addAccessKeys( $([]) ); }
			} );

			return false;
		},

		openNext: function( title ) {
			var messages = mw.config.get( 'trlKeys' );
			var page = title.replace( /[^:]+:/, '' );
			var namespace = title.replace( /:.*/, '' );
			var found = false;
			for ( var key in messages ) {
				if ( !messages.hasOwnProperty( key ) ) {
					continue;
				}

				var value = messages[key];
				if ( found ) {
					return mw.translate.openDialog( namespace + ':' + value );
				} else if( page === value ) {
					found = true;
				}
			}
			alert( mw.msg( 'translate-js-nonext' ) );
			return;
		}
	};
	
	mw.translate = translate;
	$( document ).ready( mw.translate.init );

} )( jQuery );