( function ( $, mw ) {
	'use strict';

	// Cache token so we don't have to keep fetching new ones for every single request.
	var cachedToken = null;

	mw.translate = mw.translate || {};

	mw.translate = $.extend( mw.translate, {
		/**
		 * If it's the first time that the user is proofreading using TUX,
		 * the preference 'tux-did-proofread' is set to '1'.
		 */
		markFirstProofread: function () {
			var api,
				didProofreadOption = 'tux-did-proofread';

			if ( mw.user.options.get( didProofreadOption ) === '1' ) {
				return;
			}

			api = new mw.Api();
			mw.user.options.set( didProofreadOption, '1' );

			api.get( {
				action: 'tokens',
				type: 'options'
			} ).done( function ( data ) {
				api.post( {
					action: 'options',
					token: data.tokens.optionstoken,
					optionname: didProofreadOption,
					optionvalue: '1'
				} );
			} );
		},

		/**
		 * Post to translation review API with correct token.
		 * If we have no token, get one and try to post.
		 * If we have a cached token try using that, and if it fails, blank out the
		 * cached token and start over.
		 *
		 * Also, if it's the first time that the user is proofreading using TUX,
		 * the preference 'tux-did-proofread' is set to '1'.
		 *
		 * @param params {Object} API parameters
		 * @param ok {Function} callback for success
		 * @param err {Function} [optional] error callback
		 * @return {jqXHR}
		 */
		proofread: function ( params, ok, err ) {
			var useTokenToPost, getTokenIfBad, promise,
				translate = this,
				api = new mw.Api();

			if ( cachedToken === null ) {
				// We don't have a valid cached token, so get a fresh one and try posting.
				// We do not trap any 'badtoken' or 'notoken' errors, because we don't want
				// an infinite loop. If this fresh token is bad, something else is very wrong.
				useTokenToPost = function ( token ) {
					params.token = token;
					api.post( params, ok, err );
				};

				promise = translate.getProofreadToken( useTokenToPost, err );
			} else {
				// We do have a token, but it might be expired. So if it is 'bad' then
				// start over with a new token.
				params.token = cachedToken;
				getTokenIfBad = function ( code, result ) {
					if ( code === 'badtoken' ) {
						// force a new token, clear any old one
						cachedToken = null;
						translate.proofread( params, ok, err );
					} else {
						err( code, result );
					}
				};

				promise = api.post( params, {
					ok: ok,
					err: getTokenIfBad
				} );
			}

			translate.markFirstProofread();

			return promise;
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
			var targetLanguage, targetLanguageDir, sourceLanguage, sourceLanguageDir,
				$proofreadAction, $proofreadEdit, userId, reviewers, otherReviewers,
				translatedBySelf, proofreadBySelf;

			// List of all reviewers
			reviewers = $( this.message.properties.reviewers );
			// The id of the current user, converted to string as the are in reviewers
			userId = mw.config.get( 'wgUserId' ) + '';
			// List of all reviewers excluding the current user.
			otherReviewers = reviewers.not( [userId] );
			/* Whether the current user if the last translator of this message.
			 * Accepting own translations is prohibited. */
			translatedBySelf = ( this.message.properties['last-translator-text'] === mw.user.getName() );
			proofreadBySelf = $.inArray( userId, reviewers ) > -1;

			sourceLanguage = this.$container.data( 'sourcelangcode' );
			sourceLanguageDir = $.uls.data.getDir( sourceLanguage );
			targetLanguage = this.$container.data( 'targetlangcode' );
			targetLanguageDir = $.uls.data.getDir( targetLanguage );

			$proofreadAction = $( '<div>' )
				.attr( 'title', mw.msg( 'tux-proofread-action-tooltip' ) )
				.addClass(
					'tux-proofread-action ' + this.message.properties.status + ' ' + ( proofreadBySelf ? 'accepted' : '' )
				)
				.tipsy( { gravity: 's' } );

			$proofreadEdit = $( '<div>' )
				.attr( 'title', mw.msg( 'tux-proofread-edit-tooltip' ) )
				.addClass( 'tux-proofread-edit' )
				.tipsy();

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
						translatedBySelf ?
							$( '<div>' )
								.addClass( 'translated-by-self' )
								.attr( 'title', mw.msg( 'tux-proofread-translated-by-self' ) )
								.tipsy( { gravity: 'e' } ):
							$( [] ),
						$proofreadAction,
						otherReviewers.length ?
							$( '<div>' )
								.addClass( 'tux-proofread-count' )
								.data( 'reviewCount', reviewers.length ) // To update when accepting
								.text( mw.language.convertNumber( reviewers.length ) ) :
							$( [] ),
						$proofreadEdit
					)
			)
			.addClass( this.message.properties.status );

			if ( translatedBySelf ) {
				this.$message.addClass( 'own-translation' );
				// Own translations cannot be reviewed, so hide the review button
				this.hide();
			}

			/* Here we need to check that there are reviewers in the first place
			 * before adding review markers */
			if ( reviewers.length && otherReviewers.length ) {
				this.$message.addClass( 'proofread-by-others' );
			}
		},

		hide: function () {
			this.$message.find( '.tux-proofread-action' )
				.addClass( 'hide' );
		},

		/**
		 * Mark this message as proofread.
		 */
		proofread: function () {
			var proofread = this,
				reviews;

			mw.translate.proofread( {
				action: 'translationreview',
				revision: this.message.properties.revision,
				format: 'json'
			}, function () {
				proofread.$message.find( '.tux-proofread-action' )
					.addClass( 'accepted' );

				reviews = proofread.$message.find( '.tux-proofread-count' ).data( 'reviewCount' );
				proofread.$message.find( '.tux-proofread-count' )
					.text( mw.language.convertNumber( reviews + 1 ) );

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
