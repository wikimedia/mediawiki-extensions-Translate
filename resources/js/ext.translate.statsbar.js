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
		// no this.listen() here -
		// init() calls render() and render() calls listen().
	};

	LanguageStatsBar.prototype = {
		init: function () {
			if ( mw.translate.languagestats[this.language] ) {
				this.render();
			} else {
				mw.translate.loadLanguageStats( this.language )
					.done( $.proxy( this.render, this ) );
			}
		},

		/**
		 * Listen for the change events and update the statsbar
		 */
		listen: function () {
			var i,
				statsbar = this,
				languageStats = mw.translate.languagestats[this.language];

			statsbar.$statsBar.on( 'change', function ( event, to, from ) {
				for ( i = 0; i < languageStats.length; i++ ) {
					if ( languageStats[i].group === statsbar.group ) {
						if ( to === 'translated' ) {
							languageStats[i].translated++;
						}
						if ( to === 'proofread' ) {
							languageStats[i].proofread++;
						}
						if ( to === 'fuzzy' ) {
							languageStats[i].fuzzy++;
						}

						if ( from === 'fuzzy' ) {
							languageStats[i].fuzzy--;
						}
						if ( from === 'proofread' ) {
							languageStats[i].proofread--;
						}
						if ( from === 'translated' ) {
							languageStats[i].translated--;
						}
						break;
					}
				}

				// Update the stats bar
				statsbar.update();
			} );

			statsbar.$statsBar.on( 'refresh', function ( e, language ) {
				statsbar.language = language;

				mw.translate.loadLanguageStats( language ).done( function () {
					statsbar.update();
				} );
			} );
		},

		render: function () {
			this.$statsBar = $( '<div>' )
				.addClass( 'tux-statsbar' )
				.data( 'group', this.group );

			this.$statsBar.append(
				$( '<span>' ).addClass( 'tux-proofread' ),
				$( '<span>' ).addClass( 'tux-translated' ),
				$( '<span>' ).addClass( 'tux-fuzzy' ),
				$( '<span>' ).addClass( 'tux-untranslated' )
			);

			// TODO Add a tooltip for the statsbar that says the stats in words.
			this.$container.append( this.$statsBar );
			this.update();

			this.listen();
		},

		update: function () {
			var proofread, translated, fuzzy, untranslated, untranslatedCount,
				stats = this.getStatsForGroup( this.group );

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
			this.$statsBar.attr( 'title',
				mw.msg( 'translate-statsbar-tooltip', stats.total,
					translated.toFixed(2),
					proofread.toFixed(2),
					fuzzy.toFixed(2),
					untranslated.toFixed(2) )
				);
		},

		getStatsForGroup: function ( group ) {
			var i,
				languageStats = mw.translate.languagestats[this.language];

			for ( i = 0; i < languageStats.length; i++ ) {
				if ( languageStats[i].group === group ) {
					return languageStats[i];
				}
			}

			return {
				proofread: 0,
				total: 0,
				fuzzy: 0,
				translated: 0
			};
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

	mw.translate = mw.translate || {};
} ( mediaWiki, jQuery ) );

