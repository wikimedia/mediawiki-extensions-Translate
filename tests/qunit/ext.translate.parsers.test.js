/*!
 * Tests for ext.translate.parsers.js.
 *
 * @license GPL-2.0-or-later
 */

( function () {
	'use strict';

	QUnit.module( 'ext.translate.parsers', QUnit.newMwEnvironment() );

	QUnit.test( '-- External links', function ( assert ) {
		mw.config.set( 'wgArticlePath', '/wiki/$1' );

		assert.strictEqual(
			mw.translate.formatMessageGently( 'This page is [in English]' ),
			'This page is [in English]',
			'Brackets without protocol doesn\'t make a link'
		);

		assert.strictEqual(
			mw.translate.formatMessageGently( 'This page has [https://www.mediawiki.org a link]' ),
			'This page has <a href="https://www.mediawiki.org">a link</a>',
			'Brackets with https:// protocol creates a link'
		);

		assert.strictEqual(
			mw.translate.formatMessageGently( 'No kun [[m:MassMessage]] ja plum [[m:|Meta-Wiki]].' ),
			'No kun <a href="/wiki/m:MassMessage">m:MassMessage</a> ja plum <a href="/wiki/m:">Meta-Wiki</a>.',
			'Link parsing is non-greedy'
		);
	} );
}() );
