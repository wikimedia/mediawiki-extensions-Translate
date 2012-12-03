/**
 * JavaScript version of the PHP StatsBar class
 * @author Niklas Laxström
 * @license GPL2+
 * @since 2012-11-30
 */

( function ( mw, $ ) {
	'use strict';

	mw.translate = mw.translate || {};
	mw.translate.statsbar = function ( group, language, stats ) {
		var $bar, proofread, translated, fuzzy, untranslated, untranslatedCount;

		proofread = Math.floor( 100 * stats.proofread / stats.total );
		translated = Math.floor( 100 * stats.translated / stats.total );
		fuzzy = Math.floor( 100 * stats.fuzzy / stats.total );
		untranslated = 100 - proofread - translated - fuzzy;
		untranslatedCount = stats.total - stats.proofread - stats.translated - stats.fuzzy;

		$bar = $( '<div>' )
			.addClass( 'tux-statsbar' )
			.data( 'total', stats.total )
			.data( 'group', group )
			.data( 'language', language );

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
		return $bar;
	};

} ( mediaWiki, jQuery ) );
