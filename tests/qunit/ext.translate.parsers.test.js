/*!
 * Tests for ext.translate.parsers.js.
 *
 * @license GPL-2.0+
 */

( function ( $, mw ) {
	'use strict';

	QUnit.module( 'ext.translate.parsers', QUnit.newMwEnvironment() );

	QUnit.test( '-- External links', 3, function ( assert ) {
		mw.config.set( 'wgArticlePath', '/wiki/$1' );

		assert.strictEqual(
			'This page is [in English]',
			mw.translate.formatMessageGently( 'This page is [in English]' ),
			'Brackets without protocol doesn\'t make a link'
		);

		assert.strictEqual(
			'This page has <a href="https://www.mediawiki.org">a link</a>',
			mw.translate.formatMessageGently( 'This page has [https://www.mediawiki.org a link]' ),
			'Brackets with https:// protocol creates a link'
		);

		assert.strictEqual(
			'No kun <a href="/wiki/m:MassMessage">m:MassMessage</a> ja plum <a href="/wiki/m:">Meta-Wiki</a>.',
			mw.translate.formatMessageGently( 'No kun [[m:MassMessage]] ja plum [[m:|Meta-Wiki]].' ),
			'Link parsing is non-greedy'
		);
	} );
}( jQuery, mediaWiki ) );
