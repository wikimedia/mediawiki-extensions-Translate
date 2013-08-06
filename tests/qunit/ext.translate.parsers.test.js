/**
 * Tests for ext.translate.parsers.js.
 *
 * @file
 * @licence GPL-2.0+
 */

( function ( $, mw ) {
	'use strict';

	QUnit.module( 'ext.translate.parsers', QUnit.newMwEnvironment() );

	QUnit.test( '-- External links', 2, function ( assert ) {
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
	} );


}( jQuery, mediaWiki ) );
