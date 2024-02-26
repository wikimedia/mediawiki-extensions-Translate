/*!
 * Tests for ext.translate.parsers.js.
 *
 * @license GPL-2.0-or-later
 */

( function () {
	'use strict';

	QUnit.module( 'ext.translate.parsers', QUnit.newMwEnvironment() );

	QUnit.test( '-- Page titles and headings', function ( assert ) {
		mw.config.set( 'wgArticlePath', '/wiki/$1' );

		assert.strictEqual(
			mw.translate.formatMessageGently( '== Heading with = sign ==' ),
			'<h2>Heading with = sign</h2>',
			'Heading equal signs should always detect the line-end equal signs and strip the surronding whitespaces'
		);

		assert.strictEqual(
			mw.translate.formatMessageGently( '==Heading with = sign without suggested surronding whitespaces==' ),
			'<h2>Heading with = sign without suggested surronding whitespaces</h2>',
			'Heading equal signs should always detect the line-end equal signs without suggested surronding whitespaces'
		);

		assert.strictEqual(
			mw.translate.formatMessageGently( '== Heading with = sign ==\nText' ),
			'<h2>Heading with = sign</h2>\nText',
			'Headings with text in new line should able to be rendered correctly'
		);
	} );

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

	QUnit.test( '-- Bold and italics', function ( assert ) {
		mw.config.set( 'wgArticlePath', '/wiki/$1' );

		assert.strictEqual(
			mw.translate.formatMessageGently( "'''Bold text.''' Normal text." ),
			'<strong>Bold text.</strong> Normal text.',
			'Bold parsing is correct'
		);

		assert.strictEqual(
			mw.translate.formatMessageGently( "''Italic text.'' Normal text." ),
			'<em>Italic text.</em> Normal text.',
			'Italic parsing is correct'
		);

		assert.strictEqual(
			mw.translate.formatMessageGently( "'''''Bold and italic text.''''' Normal text." ),
			'<em><strong>Bold and italic text.</strong></em> Normal text.',
			'Bold and italic parsing is correct'
		);

		assert.strictEqual(
			mw.translate.formatMessageGently( "'''''Bold and italic.'' Just bold.''' Normal." ),
			'<strong><em>Bold and italic.</em> Just bold.</strong> Normal.',
			'Parsing of bold text with italic at the start is correct'
		);

		assert.strictEqual(
			mw.translate.formatMessageGently( "'''''Bold and italic.''' Just italic.'' Normal." ),
			'<em><strong>Bold and italic.</strong> Just italic.</em> Normal.',
			'Parsing of italic text with bold at the start is correct'
		);

		assert.strictEqual(
			mw.translate.formatMessageGently( "'''Bold first, ''then italic.''''' Normal." ),
			'<strong>Bold first, <em>then italic.</em></strong> Normal.',
			'Parsing of bold text ending with italic text is correct'
		);

		assert.strictEqual(
			mw.translate.formatMessageGently( "''Italic first, '''then bold.''''' Normal." ),
			'<em>Italic first, <strong>then bold.</strong></em> Normal.',
			'Parsing of italic text ending with bold text is correct'
		);
	} );
}() );
