/*!
 * Tests for ext.translate.special.pagemigration.js.
 *
 * @license GPL-2.0-or-later
 */

( function ( $, mw ) {
	'use strict';

	QUnit.module( 'ext.translate.special.pagemigration', QUnit.newMwEnvironment( {
		setup: function () {
			this.server = this.sandbox.useFakeServer();
		}
	} ) );

	QUnit.test( '-- Source units', function ( assert ) {
		var done, data = '{ "query": { "messagecollection": [ { "key": "key_",' +
			' "definition": "definition_", "title": "title_" }, { "key": "key_1",' +
			' "definition": "definition_1", "title": "title_1" } ] } }';

		done = assert.async();
		mw.translate.getSourceUnits( 'Help:Special pages' ).done( function ( sourceUnits ) {
			assert.strictEqual( 1, sourceUnits.length, 'Source units retrieved' );
			done();
		} );

		this.server.respond( function ( request ) {
			request.respond( 200, { 'Content-Type': 'application/json' }, data );
		} );
	} );

	QUnit.test( '-- Page does not exist', function ( assert ) {
		var done, data = '{ "query": { "pages": { "-1": { "missing": "" } } } }';

		done = assert.async();
		mw.translate.getFuzzyTimestamp( 'ugagagagagaga/uga' ).fail( function ( timestamp ) {
			assert.strictEqual( undefined, timestamp, 'Page does not exist' );
			done();
		} );

		this.server.respond( function ( request ) {
			request.respond( 200, { 'Content-Type': 'application/json' }, data );
		} );
	} );

	QUnit.test( '-- Fuzzy timestamp', function ( assert ) {
		var done, data = '{ "query": { "pages": { "19563": {"revisions": ' +
			'[ {"timestamp": "2014-02-18T20:59:58Z" }, { "timestamp": "t2" } ] } } } }';

		done = assert.async();
		mw.translate.getFuzzyTimestamp( 'Help:Special pages/fr' ).done( function ( timestamp ) {
			assert.strictEqual( '2014-02-18T20:59:57.000Z', timestamp, 'Fuzzy timestamp retrieved' );
			done();
		} );

		this.server.respond( function ( request ) {
			request.respond( 200, { 'Content-Type': 'application/json' }, data );
		} );
	} );

	QUnit.test( '-- Split translation page', function ( assert ) {
		var done, data = '{ "query": { "pages": { "19563": { "revisions": ' +
			'[ { "*": "unit1\\n\\nunit2\\n\\nunit3" } ] } } } }';

		done = assert.async();
		mw.translate.splitTranslationPage( '2014-02-18T20:59:57.000Z', 'Help:Special pages/fr' )
			.done( function ( translationUnits ) {
				assert.strictEqual( 3, translationUnits.length, 'Translation page split into units' );
				done();
			} );

		this.server.respond( function ( request ) {
			request.respond( 200, { 'Content-Type': 'application/json' }, data );
		} );
	} );

	QUnit.test( '-- Align h2 headers', function ( assert ) {
		var sourceUnits, translationUnits1, result1,
			translationUnits2, result2;

		sourceUnits = [ { identifier: '1', definition: 'abc' }, { identifier: '2', definition: '==123==' },
			{ identifier: '3', definition: 'pqr' }, { identifier: '4', definition: 'xyz' },
			{ identifier: '5', definition: 'mno' }, { identifier: '6', definition: '==456==' } ];

		translationUnits1 = [ '==123==', 'pqr', '==456==' ];

		translationUnits2 = [ 'abc', 'lmn', '==123==', 'pqr', '==456==' ];

		result1 = [ '', '==123==', 'pqr', '', '', '==456==' ];

		result2 = [ 'abc\nlmn\n', '==123==', 'pqr', '', '', '==456==' ];

		translationUnits1 = mw.translate.alignHeaders( sourceUnits, translationUnits1 );
		assert.deepEqual( translationUnits1, result1, 'h2 headers aligned without merging' );

		translationUnits2 = mw.translate.alignHeaders( sourceUnits, translationUnits2 );
		assert.deepEqual( translationUnits2, result2, 'h2 headers aligned with merging' );
	} );
}( jQuery, mediaWiki ) );
