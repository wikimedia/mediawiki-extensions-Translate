/*
 * A set of simple tools for partial parsing and formatting of translatable
 * messages.
 *
 * @author Niklas Laxström, 2013
 * @license GPL2+
 */

( function ( $, mw ) {
	'use strict';

	mw.translate = mw.translate || {};

	mw.translate = $.extend( mw.translate, {
		/**
		 * Formats some common wikitext elements
		 * @param {String} text Message text
		 * @param {String} [key] Message key
		 * @return {String} Formatted text in html
		 */
		formatMessageGently: function ( text, key ) {
			// Try to keep simple.
			text = $( '<div>' ).text( text ).html();

			// Hack for page translation page titles
			if ( text && key && key.match( /\/Page_display_title$/ ) ) {
				text = '=' + text + '=';
			}

			text = text.replace( /^(=+)(.*?)(=+)/, function ( match, p1, p2, p3 ) {
				var len = Math.min( p1.length, p3.length, 6 );
				return $( '<div>' ).append( $( '<h' + len + '>' ).html( p2 ) ).html();
			} );

			text = text.replace( /(^\*.*(\n|$))+/gm, function ( match ) {
				match = match.replace( /^\*(.*)/gm, function ( match, p1 ) {
					return $( '<div>' ).append( $( '<li>' ).html( p1 ) ).html();
				} );
				return $( '<div>' ).append( $( '<ul>' ).html( match ) ).html();
			} );

			text = text.replace( /(^#.*(\n|$))+/gm, function ( match ) {
				match = match.replace( /^#(.*)/gm, function ( match, p1 ) {
					return $( '<div>' ).append( $( '<li>' ).html( p1 ) ).html();
				} );
				return $( '<div>' ).append( $( '<ol>' ).html( match ) ).html();
			} );

			text = text.replace( /\[\[(.+?)\|(.+?)\]\]/g, function ( match, p1, p2 ) {
				var link = $( '<a>' ).html( p2 ).prop( 'href', mw.util.wikiGetlink( p1 ) );
				return $( '<div>' ).append( link ).html();
			} );

			text = text.replace( /\[\[(.+?)\]\]/g, function ( match, p1 ) {
				var link = $( '<a>' ).html( p1 ).prop( 'href', mw.util.wikiGetlink( p1 ) );
				return $( '<div>' ).append( link ).html();
			} );

			text = text.replace( /\[([^ ]+) (.+?)\]/g, function ( match, p1, p2 ) {
				var link = $( '<a>' ).html( p2 ).prop( 'href', p1 );
				return $( '<div>' ).append( link ).html();
			} );

			text = text.replace( /'''(.+?)'''/g, function ( match, p1 ) {
				return $( '<div>' ).append( $( '<b>' ).html( p1 ) ).html();
			} );

			text = text.replace( /''(.+?)''/g, function ( match, p1 ) {
				return $( '<div>' ).append( $( '<i>' ).html( p1 ) ).html();
			} );

			text = text.replace( /\n\n/gm, '<br />' );
			return text;
		}
	} );

} ( jQuery, mediaWiki ) );
