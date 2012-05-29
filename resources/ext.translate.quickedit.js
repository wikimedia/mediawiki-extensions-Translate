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
 * * Instead of hc'd onscript, give them a class and use necessary triggers
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2009-2012 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

(function ( $, mw, undefined ) {
	"use strict";
	var dialogwidth = false,
	    translate,
	    preloads = {};

	function MessageCheckUpdater( callback ) {
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
	}

	/**
	 * This is JS port same method of TranslateUtils.php
	 */
	function convertWhiteSpaceToHTML( text ) {
		return mw.html.escape( text )
			.replace( /^ /gm, '&#160;' )
			.replace( / $/gm, '&#160;' )
			.replace( /  /g, '&#160; ' )
			.replace( /\n/g, '<br />' )
	}

	function addAccessKeys( dialog ) {
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
				.attr( 'title', '[' + mw.util.tooltipAccessKeyPrefix + key + ']' );
		}
	}

	function registerFeatures( callbacks, form, page, group ) {
		// Enable the collapsible element
		var $identical = $( '.mw-identical-title' );
		if ( $.isFunction( $identical.makeCollapsible ) ) {
			$identical.makeCollapsible();
		}

		if ( mw.config.get( 'trlKeys' ) || $( '.tqe-inlineeditable' ).length ) {
			if ( callbacks.next === undefined ) {
				form.find( '.mw-translate-next, .mw-translate-skip' ).attr( 'disabled', 'disabled' )
			} else {
				form.find( '.mw-translate-next' ).click( function () {
					callbacks.next && callbacks.next();
				} );
				form.find( '.mw-translate-skip,' ).click( function () {
					callbacks.close && callbacks.close();
					callbacks.next && callbacks.next();
				} );
			}
		} else {
			form.find( '.mw-translate-next, .mw-translate-skip' )
				.attr( 'disabled', 'disabled' )
				.css( 'display', 'none' );
		}
		form.find( '.mw-translate-close' ).click( function () {
			callbacks.close && callbacks.close();
		} );

		form.find( '.mw-translate-history' ).click( function() {
			window.open( mw.util.wikiScript() + '?action=history&title=' + form.find( 'input[name=title]' ).val() );
			return false;
		} );

		form.find( '.mw-translate-support, .mw-translate-askpermission' ).click( function() {
			// Can use .data() only with 1.4.3 or newer
			window.open( $(this).attr( 'data-load-url' ) );
			return false;
		} );
		
		form.find( 'input, textarea' ).focus( function() {
			addAccessKeys( form );
		} );

		form.find( 'input#summary' ).focus( function() {
			$( this ).css( 'width', '85%' );
		} );

		var textarea = form.find( '.mw-translate-edit-area' );
		textarea.css( 'display', 'block' );
		textarea.autoResize( { maxHeight: 200 } );
		textarea.focus();

		if ( form.find( '.mw-translate-messagechecks' ) ) {
			var checker = new MessageCheckUpdater( function() {
				var url = mw.config.get( 'wgScript' ) + '?title=Special:Translate/editpage&suggestions=checks&page=$1&loadgroup=$2';
				url = url.replace( '$1', encodeURIComponent( page ) ).replace( '$2', encodeURIComponent( group ) );
				$.post( url, { translation: textarea.val() }, function( mydata ) {
					form.find( '.mw-translate-messagechecks' ).replaceWith( mydata );
				} );
			} );

			textarea.keyup( function() { checker.setup(); } );
		}
	}

	translate = {
		init: function() {
			dialogwidth = $( window ).width() * 0.8;
			var $inlines = $( '.tqe-inlineeditable' );
			$inlines.dblclick( mw.translate.inlineEditor );
			
			var $first = $inlines.first();
			if ( $first.length ) {
				var title = $first.data( 'title' );
				var group = $first.data( 'group' );
				mw.translate.loadEditor( null, title, group, $.noop );
			}
			
			var prev = null;
			$inlines.each( function() {
				if ( prev ) {
					prev.next = this;
				}
				prev = this;
			} )
		},

		openDialog: function( page, group ) {
			var id = 'jsedit' +  page.replace( /[^a-zA-Z0-9_]/g, '_' );

			var dialog = $( '#' + id );
			if ( dialog.size() > 0 ) {
				dialog.dialog( 'option', 'position', 'top' );
				dialog.dialog( 'open' );
				return false;
			}
			
			var dialog = $( '<div>' ).attr( 'id', id ).appendTo( $( 'body' ) );
			
			var callbacks = {}
			callbacks.close = function () { dialog.dialog( 'close' ); };
			callbacks.next = function () { mw.translate.openNext( page, group ); };
			mw.translate.openEditor( dialog, page, group, callbacks );

			dialog.dialog( {
				bgiframe: true,
				width: dialogwidth,
				title: page,
				position: 'top',
				resize: function() { $( '#' + id + ' textarea' ).width( '100%' ); },
				resizeStop: function() { dialogwidth = $( '#' + id ).width(); },
			} );

			return false;
		},
 
		loadEditor: function( $target, page, group, callback ) {
			// Try if it has been cached
			var id = 'preload-' +  page.replace( /[^a-zA-Z0-9_]/g, '_' );
			var preload = preloads[id];
			if ( preload !== undefined ) {
				if ( $target ) {
					$target.html( preloads[id] );
					delete preloads[id];
				}
				callback();
				return;
			}

			// Load the editor into provided target or cache it locally
			var url = mw.util.wikiScript();
			var params = {
				title: 'Special:Translate/editpage',
				suggestions: 'sync',
				page: page,
				loadgroup: group
			};
			if ( $target ) {
				$target.load( url, params, callback );
				delete preloads[id];
			} else {
				$.get( url, params, function ( data ) {
					preloads[id] = data;
				} );
			}

		},
 
		openEditor: function( element, page, group, callbacks ) {
			var $target = $( element );
			var spinner = $( '<div>' ).attr( 'class', 'mw-ajax-loader' );
			$target.html( $( '<div>' ).attr( 'class', 'mw-ajax-dialog' ).html( spinner ) );

			mw.translate.loadEditor( $target, page, group, function() {
				callbacks.load && callbacks.load( $target );
				var form = $target.find( 'form' );
				registerFeatures( callbacks, form, page, group );
				form.ajaxForm( {
					dataType: 'json',
					success: function(json) {
						if ( json.error ) {
							if( json.error.code === 'emptypage') {
								alert( mw.msg( 'api-error-emptypage' ) );
							} else {
								alert( json.error.info + ' (' + json.error.code +')' );
							}
						} else if ( json.edit.result === 'Failure' ) {
							alert( mw.msg( 'translate-js-save-failed' ) );
						} else if ( json.edit.result === 'Success' ) {
							callbacks.close && callbacks.close();
							callbacks.success && callbacks.success( form.find( '.mw-translate-edit-area' ).val() );
						} else {
							alert( mw.msg( 'translate-js-save-failed' ) );
						}
					}
				} );
			} );
		},

		openNext: function( title, group ) {
			var messages = mw.config.get( 'trlKeys' );
			var found = false, key, value;

			for ( key in messages ) {
				if ( !messages.hasOwnProperty( key ) ) {
					continue;
				}

				value = messages[key];
				if ( found ) {
					return mw.translate.openDialog( value, group );
				} else if( value === title ) {
					found = true;
				}
			}
			alert( mw.msg( 'translate-js-nonext' ) );
			return;
		},
	
		inlineEditor: function () {
			var $this = $( this );
			if ( $this.hasClass( 'tqe-editor-loaded' ) ) {
				// Editor is open, do not replace it
				return;
			}
			
			var current = $this.html();
			var $target = $( '<td>' ).attr( { colspan: 2 } );
			$this.html( $target );
			$this.addClass( 'tqe-editor-loaded' );
			
			var classes = $this.attr( 'class' );
			var page = $this.data( 'title' );
			var group = $this.data( 'group' );
			var next = $( this.next );
			var callbacks = {}
			callbacks.success = function ( text ) {
				// Update the cell value with the new translation
				$this.find( 'td' ).last()
					.removeClass( 'untranslated' )
					.html( convertWhiteSpaceToHTML( text ) );
			};
			callbacks.close = function () {
				$this.html( current );
				$this.removeClass( 'tqe-editor-loaded' );
			};
			callbacks.load = function ( editor ) {
				var $header = $( '<div class="tqe-fakeheader"></div>' );
				$header.text( page );
				$header.append( '<input type=button class="mw-translate-close" value="X" />' );
				
				$( editor ).find( 'form' ).prepend( $header );
			};
			if ( next.length ) {
				callbacks.next = function () { next.dblclick(); };
				// Preload the next item
				var ntitle = next.data( 'title' );
				var ngroup = next.data( 'group' );
				mw.translate.loadEditor( null, ntitle, ngroup, $.noop );
			}
			mw.translate.openEditor( $target, page, group, callbacks );
			
			// Remove any text selection caused by double clicking
			var sel = window.getSelection ? window.getSelection() : document.selection;
			if ( sel ) {
				sel.removeAllRanges && sel.removeAllRanges();
				sel.empty && sel.empty();
			}
		}
	};

	mw.translate = translate;
	$( document ).ready( translate.init );

} )( jQuery, mediaWiki );
