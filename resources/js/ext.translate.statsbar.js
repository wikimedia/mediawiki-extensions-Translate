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
		this.init();
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

		render: function () {
			var $bar, i, stats, proofread, translated, fuzzy, untranslated, untranslatedCount;

			stats = {};

			for ( i = 0; i < mw.translate.languagestats.length; i++ ) {
				if ( mw.translate.languagestats[i].group === this.group ) {
					stats = mw.translate.languagestats[i];
					break;
				}
			}

			proofread = 100 * stats.proofread / stats.total;
			// Proofread messages are also translated, so remove those for
			// the bar showing only translated count.
			translated = stats.translated - stats.proofread;
			translated = 100 * translated / stats.total;
			fuzzy = 100 * stats.fuzzy / stats.total;
			untranslated = 100 - proofread - translated - fuzzy;
			// Again, proofread counts are subset of translated counts
			untranslatedCount = stats.total - stats.translated - stats.fuzzy;

			$bar = $( '<div>' )
				.addClass( 'tux-statsbar' )
				.data( 'total', stats.total )
				.data( 'group', this.group )
				.data( 'language', this.language )
				.append(
					$( '<span>' )
						.addClass( 'tux-proofread' )
						.data( 'proofread', stats.proofread )
						.css( 'width', proofread + '%' ),
					$( '<span>' )
						.addClass( 'tux-translated' )
						.data( 'translated', stats.translated )
						.css( 'width', translated + '%' ),
					$( '<span>' )
						.addClass( 'tux-fuzzy' )
						.data( 'fuzzy', stats.fuzzy )
						.css( 'width', fuzzy + '%' ),
					$( '<span>' )
						.addClass( 'tux-untranslated' )
						.data( 'untranslated', untranslatedCount )
						.css( 'width', untranslated + '%' )
			);

			// TODO Add a tooltip for the statsbar that says the stats in words.
			this.$container.append( $bar );
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

} ( mediaWiki, jQuery ) );
