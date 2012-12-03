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
		init: function() {

			if ( mw.translate.languagestats ) {
				this.render();
			} else {
				this.getStats( this.render );
			}
		},

		/**
		 * This need to be called only once per page. Before calling check
		 * mw.translate.languagestats defined or not.
		 *
		 * @param callback
		 */
		getStats: function ( callback ) {
			var queryParams, apiURL,
 				statsbar = this;

			queryParams = {
				action: 'query',
				format: 'json',
				meta: 'languagestats',
				lslanguage: this.language
			};

			apiURL = mw.util.wikiScript( 'api' );
			$.get( apiURL, queryParams, function ( result ) {
				mw.translate.languagestats = result.query.languagestats;
				callback.call( statsbar );
			} );
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

			proofread = Math.floor( 100 * stats.proofread / stats.total );
			translated = Math.floor( 100 * stats.translated / stats.total );
			fuzzy = Math.floor( 100 * stats.fuzzy / stats.total );
			untranslated = 100 - proofread - translated - fuzzy;
			untranslatedCount = stats.total - stats.proofread - stats.translated - stats.fuzzy;

			$bar = $( '<div>' )
				.addClass( 'tux-statsbar' )
				.data( 'total', stats.total )
				.data( 'group', this.group )
				.data( 'language', this.language );

			$( '<span>' )
				.addClass( 'tux-proofread' )
				.text( stats.proofread )
				.css( 'width', proofread + '%' )
				.appendTo( $bar );
			$( '<span>' )
				.addClass( 'tux-translated' )
				.text( stats.translated )
				.css( 'width', translated + '%' )
				.appendTo( $bar );
			$( '<span>' )
				.addClass( 'tux-fuzzy' )
				.text( stats.fuzzy )
				.css( 'width', fuzzy + '%' )
				.appendTo( $bar );
			$( '<span>' )
				.addClass( 'tux-untranslated' )
				.text( untranslatedCount )
				.css( 'width', untranslated + '%' )
				.appendTo( $bar );

			// TODO: Add a tooltip for the statsbar that says the stats in words.
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

} ( mediaWiki, jQuery ) );
