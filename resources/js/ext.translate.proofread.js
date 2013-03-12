( function ( $, mw ) {
	'use strict';

	// Cache token so we don't have to keep fetching new ones for every single request.
	var cachedToken = null;

	mw.translate = mw.translate || {};

	mw.translate = $.extend( mw.translate, {

		/**
		 * Post to translation review API with correct token.
		 * If we have no token, get one and try to post.
		 * If we have a cached token try using that, and if it fails, blank out the
		 * cached token and start over.
		 *
		 * @param params {Object} API parameters
		 * @param ok {Function} callback for success
		 * @param err {Function} [optional] error callback
		 * @return {jqXHR}
		 */
		proofread: function ( params, ok, err ) {
			var useTokenToPost, getTokenIfBad,
				api = this;
			if ( cachedToken === null ) {
				// We don't have a valid cached token, so get a fresh one and try posting.
				// We do not trap any 'badtoken' or 'notoken' errors, because we don't want
				// an infinite loop. If this fresh token is bad, something else is very wrong.
				useTokenToPost = function ( token ) {
					params.token = token;
					new mw.Api().post( params, ok, err );
				};
				return api.getProofreadToken( useTokenToPost, err );
			} else {
				// We do have a token, but it might be expired. So if it is 'bad' then
				// start over with a new token.
				params.token = cachedToken;
				getTokenIfBad = function ( code, result ) {
					if ( code === 'badtoken' ) {
						// force a new token, clear any old one
						cachedToken = null;
						api.proofread( params, ok, err );
					} else {
						err( code, result );
					}
				};
				return new mw.Api().post( params, { ok : ok, err : getTokenIfBad });
			}
		},

		/**
		 * Api helper to grab an translationreview token
		 *
		 * token callback has signature ( String token )
		 * error callback has signature ( String code, Object results, XmlHttpRequest xhr, Exception exception )
		 * Note that xhr and exception are only available for 'http_*' errors
		 * code may be any http_* error code (see mw.Api), or 'token_missing'
		 *
		 * @param tokenCallback {Function} received token callback
		 * @param err {Function} error callback
		 * @return {jqXHR}
		 */
		getProofreadToken: function ( tokenCallback, err ) {
			var parameters = {
					action: 'tokens',
					type: 'translationreview'
				},
				ok = function ( data ) {
					var token;
					// If token type is not available for this user,
					// key 'translationreviewtoken' is missing or can contain Boolean false
					if ( data.tokens && data.tokens.translationreviewtoken ) {
						token = data.tokens.translationreviewtoken;
						cachedToken = token;
						tokenCallback( token );
					} else {
						err( 'token-missing', data );
					}
				},
				ajaxOptions = {
					ok: ok,
					err: err,
					// Due to the API assuming we're logged out if we pass the callback-parameter,
					// we have to disable jQuery's callback system, and instead parse JSON string,
					// by setting 'jsonp' to false.
					jsonp: false
				};

			return new mw.Api().get( parameters, ajaxOptions );
		}
	} );

	function Proofread( element ) {
		this.$message = $( element );
		this.$container =  $( '.tux-messagelist' );
		this.message = this.$message.data( 'message' );
		this.init();
		this.listen();
	}

	Proofread.prototype = {

		/**
		 * Initialize the plugin
		 */
		init: function () {
			var proofread = this;

			this.render();
			// No self review
			if ( this.message.properties['last-translator-text'] === mw.user.id() ) {
				this.hide();
			}

			// No review before translating.
			if ( !this.message.translation ) {
				this.hide();
			}

			// No review for fuzzy messages.
			if ( this.message.properties.status === 'fuzzy' ) {
				this.hide();
			}

			if ( !mw.translate.canProofread() ) {
				this.hide();
			}

			proofread.$message.translateeditor( {
				message: proofread.message,
				onSave: function ( translation ) {
					proofread.$message.find( '.tux-proofread-translation' )
						.text( translation );
					proofread.message.translation = translation;
				}
			} );

		},

		render: function () {
			var targetLanguage, targetLanguageDir, sourceLanguage, sourceLanguageDir;

			sourceLanguage = this.$container.data( 'sourcelangcode' );
			sourceLanguageDir = $.uls.data.getDir( sourceLanguage );
			targetLanguage = this.$container.data( 'targetlangcode' );
			targetLanguageDir = $.uls.data.getDir( targetLanguage );

			this.$message.append(
				$( '<div>' )
					.addClass( 'one column tux-proofread-status ' + this.message.properties.status ),
				$( '<div>' )
					.addClass( 'five columns tux-proofread-source' )
					.attr( {
						lang: sourceLanguage,
						dir: sourceLanguageDir
					} )
					.text( this.message.definition ),
				$( '<div>' )
					.addClass( 'five columns tux-proofread-translation' )
					.attr( {
						lang: targetLanguage,
						dir: targetLanguageDir
					} )
					.text( this.message.translation || '' ),
				$( '<div>' )
					.addClass( 'tux-proofread-action-block one column' )
					.append(
						$( '<div>' )
							.addClass( 'tux-proofread-action ' + this.message.properties.status ),
						this.message.properties.reviewers ?
							$( '<div>' )
								.addClass( 'tux-proofread-count right' )
								.text( mw.language.convertNumber(
									this.message.properties.reviewers.length ) ) :
							$( [] ),
						$( '<div>' )
							.addClass( 'tux-proofread-edit' )
					)
			)
			.addClass( this.message.properties.status );
		},

		hide: function () {
			this.$message.find( '.tux-proofread-action' )
				.addClass( 'hide' );
		},

		/**
		 * Mark this message as proofread.
		 */
		proofread: function () {
			var proofread = this;

			mw.translate.proofread( {
				action: 'translationreview',
				revision: this.message.properties.revision,
				format: 'json'
			}, function () {
				proofread.$message.find( '.tux-proofread-action' )
					.addClass( 'proofread' );

				// Update stats
				$( '.tux-action-bar .tux-statsbar' ).trigger( 'change', [ 'proofread', proofread.message.properties.state ] );
			}, function () {
				mw.log( 'Error while submitting the message for proofread.' );
			} );
		},

		/**
		 * Attach event listeners
		 */
		listen: function () {
			var proofread = this;

			this.$message.find( '.tux-proofread-action' ).on( 'click', function () {
				proofread.proofread();
				return false;
			} );

			this.$message.find( '.tux-proofread-edit' ).on( 'click', function () {
				proofread.$message.data( 'translateeditor' ).show();
				return false;
			} );
		}
	};

	/*
	 * proofread PLUGIN DEFINITION
	 */
	$.fn.proofread = function ( options ) {
		return this.each( function () {
			var $this = $( this ),
				data = $this.data( 'proofread' );

			if ( !data ) {
				$this.data( 'proofread',
					( data = new Proofread( this, options ) )
				);
			}

		} );
	};

	$.fn.proofread.Constructor = Proofread;

}( jQuery, mediaWiki ) );
