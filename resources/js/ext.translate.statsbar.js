/**
 * Translate language statistics bar - jQuery plugin.
 * @author Niklas Laxström
 * @author Santhosh Thottingal
 * @license GPL2+
 * @since 2012-11-30
 */

/*
 * Usage:
 *	$( '<div>' ).languagestatsbar( {
 *		language: 'fi',
 *		group: 'core'
 *	} );
 * The status bar will be rendered to the newly created div. Or use any container.
 */
( function ( mw, $ ) {
	'use strict';

	// Use mw.translate namespace for storing the language statistics.
	mw.translate = mw.translate || {};

	var LanguageStatsBar = function ( container, options ) {
		this.$container = $( container );
		this.group = options.group;
		this.language = options.language;
		this.$statsBar = null;
		this.init();
		this.listen();
	};

	LanguageStatsBar.prototype = {
		init: function () {

			if ( mw.translate.languagestats ) {
				this.render();
			} else {
				this.getStats( this.language, this.render );
			}
		},

		/**
		 * This need to be called only once per page. Before calling check
		 * mw.translate.languagestats defined or not.
		 *
		 * @param language
		 * @param callback
		 */
		getStats: function ( language, callback ) {
			var queryParams,
				apiURL = mw.util.wikiScript( 'api' ),
				req,
				statsbar = this;

			queryParams = {
				action: 'query',
				format: 'json',
				meta: 'languagestats',
				lslanguage: language
			};

			req = $.get( apiURL, queryParams );

			req.then( function ( result ) {
				mw.translate.languagestats = result.query.languagestats;
				if ( $.isFunction( callback ) ) {
					callback.call( statsbar );
				}
			} );

			return req;
		},

		/**
		 * Listen for the change events and update the statsbar
		 */
		listen: function () {
			var i, statsbar = this;

			statsbar.$statsBar.on( 'change', function ( event, to, from ) {
				for ( i = 0; i < mw.translate.languagestats.length; i++ ) {
					if ( mw.translate.languagestats[i].group === statsbar.group ) {

						if ( to === 'translated' ) {
							mw.translate.languagestats[i].translated++;
						}
						if ( to === 'proofread' ) {
							mw.translate.languagestats[i].proofread++;
						}
						if ( to === 'fuzzy' ) {
							mw.translate.languagestats[i].fuzzy++;
						}

						if ( from === 'fuzzy' ) {
							mw.translate.languagestats[i].fuzzy--;
						}
						if ( from === 'proofread' ) {
							mw.translate.languagestats[i].proofread--;
						}
						if ( from === 'translated' ) {
							mw.translate.languagestats[i].translated--;
						}
						break;
					}
				}

				// Update the stats bar
				statsbar.update();
			} );
		},

		render: function () {
			this.$statsBar = $( '<div>' )
				.addClass( 'tux-statsbar' )
				.data( 'group', this.group )
				.data( 'language', this.language );

			this.$statsBar.append(
				$( '<span>' ).addClass( 'tux-proofread' ),
				$( '<span>' ).addClass( 'tux-translated' ),
				$( '<span>' ).addClass( 'tux-fuzzy' ),
				$( '<span>' ).addClass( 'tux-untranslated' )
			);

			// TODO Add a tooltip for the statsbar that says the stats in words.
			this.$container.append( this.$statsBar );
			this.update();
		},

		update: function () {
			var stats, proofread, translated, fuzzy, untranslated, untranslatedCount;

			stats = getStatsForGroup( this.group );

			this.$statsBar.data( 'total', stats.total );

			proofread = 100 * stats.proofread / stats.total;
			// Proofread messages are also translated, so remove those for
			// the bar showing only translated count.
			translated = stats.translated - stats.proofread;
			translated = 100 * translated / stats.total;
			fuzzy = 100 * stats.fuzzy / stats.total;
			untranslated = 100 - proofread - translated - fuzzy;
			// Again, proofread counts are subset of translated counts
			untranslatedCount = stats.total - stats.translated - stats.fuzzy;

			this.$statsBar.find( '.tux-proofread' )
						.data( 'proofread', stats.proofread )
						.css( 'width', proofread + '%' );
			this.$statsBar.find( '.tux-translated' )
						.data( 'translated', stats.translated )
						.css( 'width', translated + '%' );
			this.$statsBar.find( '.tux-fuzzy' )
						.data( 'fuzzy', stats.fuzzy )
						.css( 'width', fuzzy + '%' );
			this.$statsBar.find( '.tux-untranslated' )
						.data( 'untranslated', untranslatedCount )
						.css( 'width', untranslated + '%' );
		}
	};

	/*
	 * languagestatsbar PLUGIN DEFINITION
	 */

	$.fn.languagestatsbar = function ( options ) {
		return this.each( function () {
			var $this = $( this ),
				data = $this.data( 'languagestatsbar' );

			if ( !data ) {
				$this.data( 'languagestatsbar', ( data = new LanguageStatsBar( this, options ) ) );
			}

			if ( typeof options === 'string' ) {
				data[options].call( $this );
			}
		} );
	};

	$.fn.languagestatsbar.Constructor = LanguageStatsBar;

	function getStatsForGroup ( group ) {
		var i,
			stats = {
				proofread: 0,
				total: 0,
				fuzzy: 0,
				translated: 0
			};

		for ( i = 0; i < mw.translate.languagestats.length; i++ ) {
			if ( mw.translate.languagestats[i].group === group ) {
				stats = mw.translate.languagestats[i];
				break;
			}
		}
		return stats;
	}
} ( mediaWiki, jQuery ) );
