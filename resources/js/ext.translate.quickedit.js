/*!
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
 * * Instead of hc'd onscript, give them a class and use necessary triggers
 *
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

( function ( $, mw, autosize ) {
	'use strict';
	var dialogwidth = false,
		preloads = {};

	mw.translate = mw.translate || {};
	function MessageCheckUpdater( callback ) {
		this.act = function () {
			callback();
			delete this.timeoutID;
		};

		this.setup = function () {
			var self = this;

			this.cancel();
			this.timeoutID = window.setTimeout( self.act, 1000 );
		};

		this.cancel = function () {
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
			.replace( / {2}/g, '&#160; ' )
			.replace( /\n/g, '<br />' );
	}

	function addAccessKeys( dialog ) {
		var buttons = {
			a: '.mw-translate-save',
			s: '.mw-translate-next',
			d: '.mw-translate-skip',
			h: '.mw-translate-history'
		};

		$.each( buttons, function ( key, selector ) {
			$( selector )
				.val( function ( i, b ) {
					return b.replace( / \(.\)$/, '' );
				} )
				.removeAttr( 'accesskey' )
				.attr( 'title', '' );

			dialog.find( selector )
				.val( function ( i, b ) {
					return b + ' (_)'.replace( '_', key );
				} )
				.attr( 'accesskey', key )
				.attr( 'title', '[' + mw.util.tooltipAccessKeyPrefix + key + ']' );
		} );
	}

	function registerFeatures( callbacks, form, page, group ) {
		var $identical, textarea, checker;

		// Enable the collapsible element
		$identical = $( '.mw-identical-title' );
		if ( $.isFunction( $identical.makeCollapsible ) ) {
			$identical.makeCollapsible();
		}

		if ( mw.config.get( 'trlKeys' ) || $( '.tqe-inlineeditable' ).length ) {
			if ( callbacks.next === undefined ) {
				form.find( '.mw-translate-next, .mw-translate-skip' ).prop( 'disabled', true );
			} else {
				form.find( '.mw-translate-next' ).click( function () {
					if ( callbacks.next ) {
						callbacks.next();
					}
				} );
				form.find( '.mw-translate-skip' ).click( function () {
					if ( callbacks.close ) {
						callbacks.close();
					}
					if ( callbacks.next ) {
						callbacks.next();
					}
				} );
			}
		} else {
			form.find( '.mw-translate-next, .mw-translate-skip' )
				.prop( 'disabled', true )
				.css( 'display', 'none' );
		}
		form.find( '.mw-translate-close' ).click( function () {
			if ( callbacks.close ) {
				callbacks.close();
			}
		} );

		form.find( '.mw-translate-history' ).click( function () {
			window.open( mw.util.getUrl( form.find( 'input[name=title]' ).val(), { action: 'history' } ) );
			return false;
		} );

		form.find( '.mw-translate-support, .mw-translate-askpermission' ).click( function () {
			// Can use .data() only with 1.4.3 or newer
			window.open( $( this ).attr( 'data-load-url' ) );
			return false;
		} );

		form.find( 'input, textarea' ).focus( function () {
			addAccessKeys( form );
		} );

		form.find( 'input#summary' ).focus( function () {
			$( this ).css( 'width', '85%' );
		} );

		textarea = form.find( '.mw-translate-edit-area' );
		textarea.css( 'display', 'block' );
		autosize( textarea );
		textarea[ 0 ].focus();

		if ( form.find( '.mw-translate-messagechecks' ) ) {
			checker = new MessageCheckUpdater( function () {
				var url = mw.util.getUrl( 'Special:Translate/editpage', {
					suggestions: 'checks',
					page: page,
					loadgroup: group
				} );
				$.post( url, { translation: textarea.val() }, function ( mydata ) {
					form.find( '.mw-translate-messagechecks' ).replaceWith( mydata );
				} );
			} );

			textarea.keyup( function () {
				checker.setup();
			} );
		}

	}

	mw.translate = $.extend( mw.translate, {
		init: function () {
			var $inlines, $first, title, group, prev;

			dialogwidth = $( window ).width() * 0.8;
			$inlines = $( '.tqe-inlineeditable' );
			$inlines.dblclick( mw.translate.inlineEditor );

			$first = $inlines.first();
			if ( $first.length ) {
				title = $first.data( 'title' );
				group = $first.data( 'group' );
				mw.translate.loadEditor( null, title, group, $.noop );
			}

			prev = null;
			$inlines.each( function () {
				if ( prev ) {
					prev.next = this;
				}
				prev = this;
			} );
		},

		openDialog: function ( page, group ) {
			var id, dialogElement, dialog, callbacks;

			id = 'jsedit' + page.replace( /[^a-zA-Z0-9_]/g, '_' );
			dialogElement = $( '#' + id );

			if ( dialogElement.size() > 0 ) {
				dialogElement.dialog( 'option', 'position', 'top' );
				dialogElement.dialog( 'open' );
				return false;
			}

			dialog = $( '<div>' ).attr( 'id', id ).appendTo( $( 'body' ) );

			callbacks = {};
			callbacks.close = function () {
				dialog.dialog( 'close' );
			};
			callbacks.next = function () {
				mw.translate.openNext( page, group );
			};
			callbacks.success = function ( text ) {
				var $td = $( '.tqe-inlineeditable' ).filter( function () {
					return $( this ).data( 'title' ) === page.replace( '_', ' ' );
				} );
				$td
					.html( convertWhiteSpaceToHTML( text ) )
					// T41233: hacky, but better than nothing.
					// T130390: must be attr for IE/Edge.
					.attr( 'dir', 'auto' )
					.removeClass( 'untranslated' )
					.addClass( 'justtranslated' );
			};
			mw.translate.openEditor( dialog, page, group, callbacks );

			dialog.dialog( {
				bgiframe: true,
				width: dialogwidth,
				title: page,
				position: 'top',
				resize: function () {
					$( '#' + id + ' textarea' ).width( '100%' );
				},
				resizeStop: function () {
					dialogwidth = $( '#' + id ).width();
				}
			} );

			return false;
		},

		loadEditor: function ( $target, page, group, callback ) {
			var id, preload, url;

			// Try if it has been cached
			id = 'preload-' + page.replace( /[^a-zA-Z0-9_]/g, '_' );
			preload = preloads[ id ];

			if ( preload !== undefined ) {
				if ( $target ) {
					$target.html( preloads[ id ] );
					delete preloads[ id ];
				}
				callback();
				return;
			}

			// Load the editor into provided target or cache it locally
			url = mw.util.getUrl( 'Special:Translate/editpage', {
				suggestions: 'sync',
				page: page,
				loadgroup: group
			} );
			if ( $target ) {
				$target.load( url, callback );
				delete preloads[ id ];
			} else {
				$.get( url, function ( data ) {
					preloads[ id ] = data;
				} );
			}

		},

		openEditor: function ( element, page, group, callbacks ) {
			var $target = $( element ),
				spinner = $( '<div>' ).attr( 'class', 'mw-ajax-loader' );

			$target.html( $( '<div>' ).attr( 'class', 'mw-ajax-dialog' ).html( spinner ) );

			mw.translate.loadEditor( $target, page, group, function () {
				var form;

				if ( callbacks.load ) {
					callbacks.load( $target );
				}

				form = $target.find( 'form' );
				registerFeatures( callbacks, form, page, group );
				form.on( 'submit', function () {
					mw.translateHooks.run( 'beforeSubmit', form );
					$( this ).ajaxSubmit( {
						dataType: 'json',
						success: function ( json ) {
							mw.translateHooks.run( 'afterSubmit', form );
							if ( json.error ) {
								if ( json.error.code === 'emptypage' ) {
									window.alert( mw.msg( 'api-error-emptypage' ) );
								} else {
									window.alert( json.error.info + ' (' + json.error.code + ')' );
								}
							} else if ( json.edit.result === 'Failure' ) {
								window.alert( mw.msg( 'translate-js-save-failed' ) );
							} else if ( json.edit.result === 'Success' ) {
								if ( callbacks.close ) {
									callbacks.close();
								}
								if ( callbacks.success ) {
									callbacks.success( form.find( '.mw-translate-edit-area' ).val() );
								}
							} else {
								window.alert( mw.msg( 'translate-js-save-failed' ) );
							}
						}
					} );
					return false;
				} );
			} );
		},

		openNext: function ( title, group ) {
			var key, value,
				messages = mw.config.get( 'trlKeys' ),
				found = false;

			for ( key in messages ) {
				if ( !messages.hasOwnProperty( key ) ) {
					continue;
				}

				value = messages[ key ];
				if ( found ) {
					return mw.translate.openDialog( value, group );
				} else if ( value === title ) {
					found = true;
				}
			}
			window.alert( mw.msg( 'translate-js-nonext' ) );
		},

		inlineEditor: function () {
			var $this, current, page, group, next, callbacks, ntitle, ngroup, sel;
			$this = $( this );

			if ( $this.hasClass( 'tqe-editor-loaded' ) ) {
				// Editor is open, do not replace it
				return;
			}

			current = $this.html();
			$this.addClass( 'tqe-editor-loaded' );

			page = $this.data( 'title' );
			group = $this.data( 'group' );
			next = $( this.next );
			callbacks = {};

			callbacks.success = function ( text ) {
				// Update the cell value with the new translation
				$this
					.html( convertWhiteSpaceToHTML( text ) )
					// T41233: hacky, but better than nothing.
					// T130390: must be attr for IE/Edge.
					.attr( 'dir', 'auto' )
					.removeClass( 'untranslated' )
					.addClass( 'justtranslated' );
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
				callbacks.next = function () {
					next.dblclick();
				};
				// Preload the next item
				ntitle = next.data( 'title' );
				ngroup = next.data( 'group' );

				mw.translate.loadEditor( null, ntitle, ngroup, $.noop );
			}
			mw.translate.openEditor( $this, page, group, callbacks );

			// Remove any text selection caused by double clicking
			sel = window.getSelection ? window.getSelection() : document.selection;

			if ( sel ) {
				if ( sel.removeAllRanges ) {
					sel.removeAllRanges();
				}
				if ( sel.empty ) {
					sel.empty();
				}
			}
		}
	} );

	$( document ).ready( mw.translate.init );
} )( jQuery, mediaWiki, autosize );
