/**
 * Javascript version of the PHP StatsBar class
 * @author Niklas Laxström
 * @license GPL2+
 * @since 2012-11-30
 */

( function ( $ ) {
	'use strict';

	ext = ext || {};
	ext.translate = ext.translate || {};
	ext.translate.statsbar = function ( group, language, stats ) {
		var $bar, proofread, translated, fuzzy, untranslated;

		proofread = floor( 100 * stats.proofread / stats.total );
		translated = floor( 100 * stats.translated / stats.total );
		fuzzy = floor( 100 * stats.fuzzy / stats.total );
		untranslated = 100 - proofread - translated - fuzzy;

		$bar = $( '<div>' )
			.addClass( 'tux-statsbar' )
			.data( 'total', stats.total )
			.data( 'group', group )
			.data( 'language', language );

		$( '<span>' )
			.addClass( 'tux-proofread' )
			.text( stats.proofread )
			.style( 'width', proofread + '%' )
			.appendTo( $bar );
		$( '<span>' )
			.addClass( 'tux-translated' )
			.text( stats.translated )
			.style( 'width', translate + '%' )
			.appendTo( $bar );
		$( '<span>' )
			.addClass( 'tux-fuzzy' )
			.text( stats.fuzzy )
			.style( 'width', fuzzy + '%' )
			.appendTo( $bar );
		$( '<span>' )
			.addClass( 'tux-untranslated' )
			.text( untranslated )
			.style( 'width', untranslated + '%' )
			.appendTo( $bar );

		return $bar;
	};
	
} ( jQuery ) );