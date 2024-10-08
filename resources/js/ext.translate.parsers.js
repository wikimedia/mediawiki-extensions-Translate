/*!
 * A set of simple tools for partial parsing and formatting of translatable
 * messages.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

( function () {
	'use strict';

	mw.translate = mw.translate || {};
	mw.translate = $.extend( mw.translate, {
		/**
		 * Formats some common wikitext elements.
		 *
		 * @internal
		 * @param {string} text Message text
		 * @param {string} [key] Message key
		 * @return {string} Formatted text in html
		 */
		formatMessageGently: function ( text, key ) {
			var protocols = mw.config.get( 'wgUrlProtocols' );

			// Try to keep simple.
			text = $( '<div>' ).text( text ).html();

			// Hack for page translation page titles
			if ( text && key && key.match( /\/Page_display_title$/ ) ) {
				text = '=' + text + '=';
			}

			text = text.replace( /^(=+) ?(.*?) ?(=+)$/gm, function ( match, p1, p2, p3 ) {
				var len = Math.min( p1.length, p3.length, 6 );
				return $( '<div>' ).append( $( '<h' + len + '>' ).html( p2 ) ).html();
			} );

			text = text.replace( /(^\*.*(\n|$))+/gm, function ( match ) {
				match = match.replace( /^\*(.*)/gm, function ( fullMatch, p1 ) {
					return $( '<div>' ).append( $( '<li>' ).html( p1 ) ).html();
				} );
				return $( '<div>' ).append( $( '<ul>' ).html( match ) ).html();
			} );

			text = text.replace( /(^#.*(\n|$))+/gm, function ( match ) {
				match = match.replace( /^#(.*)/gm, function ( fullMatch, p1 ) {
					return $( '<div>' ).append( $( '<li>' ).html( p1 ) ).html();
				} );
				return $( '<div>' ).append( $( '<ol>' ).html( match ) ).html();
			} );

			text = text.replace( /\[\[([^\]|]+?)\|(.+?)\]\]/g, function ( match, p1, p2 ) {
				var $link = $( '<a>' ).html( p2 ).prop( 'href', mw.util.getUrl( p1 ) );
				return $( '<div>' ).append( $link ).html();
			} );

			text = text.replace( /\[\[(.+?)\]\]/g, function ( match, p1 ) {
				var $link = $( '<a>' ).html( p1 ).prop( 'href', mw.util.getUrl( p1 ) );
				return $( '<div>' ).append( $link ).html();
			} );

			var externals = new RegExp( '\\[((' + protocols + ')[^ ]+) (.+?)\\]', 'g' );
			text = text.replace( externals, function ( match, p1, p2, p3 ) {
				var $link = $( '<a>' ).html( p3 ).prop( 'href', p1 );
				return $( '<div>' ).append( $link ).html();
			} );

			text = text.replace( /('')?'''(.+?)('')?'''/g, function ( match, p1, p2, p3 ) {
				p1 = p1 || '';
				p3 = p3 || '';
				if ( /''/.test( p2 ) && ( p1 || p3 ) ) {
					// Move p1 and p3 to p2 (inside <strong> tags)
					// if italic text ends inside bold
					p2 = p1 + p2 + p3;
					p1 = '';
					p3 = '';
				}
				return p1 + $( '<div>' ).append( $( '<strong>' ).html( p2 ) ).html() + p3;
			} );

			text = text.replace( /''(.+?)''/g, function ( match, p1 ) {
				return $( '<div>' ).append( $( '<em>' ).html( p1 ) ).html();
			} );

			text = text.replace( /\n\n/gm, '<br />' );
			return text;
		}
	} );

}() );
