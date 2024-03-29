/*!
 * @license GPL-2.0-or-later
 */
QUnit.module( 'ext.translate.special.pagemigration', function ( hooks ) {
	'use strict';

	hooks.beforeEach( function () {
		this.server = this.sandbox.useFakeServer();
	} );

	QUnit.test( 'Source units', function ( assert ) {
		var data = '{ "query": { "messagecollection": [ { "key": "key_",' +
			' "definition": "definition_", "title": "title_" }, { "key": "key_1",' +
			' "definition": "definition_1", "title": "title_1" } ] } }';

		var done = assert.async();
		mw.translate.getSourceUnits( 'Help:Special pages' ).done( function ( sourceUnits ) {
			assert.strictEqual( sourceUnits.length, 2, 'Source units retrieved' );
			done();
		} );

		this.server.respond( function ( request ) {
			request.respond( 200, { 'Content-Type': 'application/json' }, data );
		} );
	} );

	QUnit.test( 'Page does not exist', function ( assert ) {
		var data = '{ "query": { "pages": { "-1": { "missing": "" } } } }';

		var done = assert.async();
		mw.translate.getFuzzyTimestamp( 'ugagagagagaga/uga' ).fail( function ( timestamp ) {
			assert.strictEqual( timestamp, undefined, 'Page does not exist' );
			done();
		} );

		this.server.respond( function ( request ) {
			request.respond( 200, { 'Content-Type': 'application/json' }, data );
		} );
	} );

	QUnit.test( 'Fuzzy timestamp', function ( assert ) {
		var data = '{ "query": { "pages": [ { "pageid": "19563", "revisions": ' +
			'[ {"timestamp": "2014-02-18T20:59:58Z" }, { "timestamp": "t2" } ] } ] } }';

		var done = assert.async();
		mw.translate.getFuzzyTimestamp( 'Help:Special pages/fr' ).done( function ( timestamp ) {
			assert.strictEqual( timestamp, '2014-02-18T20:59:57.000Z', 'Fuzzy timestamp retrieved' );
			done();
		} );

		this.server.respond( function ( request ) {
			request.respond( 200, { 'Content-Type': 'application/json' }, data );
		} );
	} );

	QUnit.test( 'Split translation page', function ( assert ) {
		var data = '{ "query": { "pages": [ { "pageid": "19563", "revisions": ' +
			'[ { "content": "unit1\\n\\nunit2\\n\\nunit3" } ] } ] } }';

		var done = assert.async();
		mw.translate.splitTranslationPage( '2014-02-18T20:59:57.000Z', 'Help:Special pages/fr' )
			.done( function ( translationUnits ) {
				assert.strictEqual( translationUnits.length, 3, 'Translation page split into units' );
				done();
			} );

		this.server.respond( function ( request ) {
			request.respond( 200, { 'Content-Type': 'application/json' }, data );
		} );
	} );

	QUnit.test( 'Split headers', function ( assert ) {
		var translationUnits, expected, result;
		translationUnits = [
			'== already split ==',
			'some text\nwith a newline',
			'==nospace l2==\nabc',
			'===nospacel3===\ndef',
			'== spaced l2 ==\nghi',
			'=== spaced l3 ===\njkl',
			'== bad spacing==\nmno',
			'== multiple ==\n===headers===\nin\n===succession===\npqr',
			'== header ==\nmore text\nwith a newline'
		];
		expected = [
			'== already split ==',
			'some text\nwith a newline',
			'==nospace l2==',
			'abc',
			'===nospacel3===',
			'def',
			'== spaced l2 ==',
			'ghi',
			'=== spaced l3 ===',
			'jkl',
			'== bad spacing==',
			'mno',
			'== multiple ==',
			'===headers===',
			'in\n',
			'===succession===',
			'pqr',
			'== header ==',
			'more text\nwith a newline'
		];
		result = mw.translate.splitHeaders( translationUnits );
		assert.deepEqual( result, expected, 'Headers split into separate units' );

	} );

	QUnit.test( 'Align h2 headers', function ( assert ) {
		var sourceUnits = [
			{ identifier: '1', definition: 'abc' }, { identifier: '2', definition: '==123==' },
			{ identifier: '3', definition: 'pqr' }, { identifier: '4', definition: 'xyz' },
			{ identifier: '5', definition: 'mno' }, { identifier: '6', definition: '==456==' }
		];

		var translationUnits1 = [ '==123==', 'pqr', '==456==' ];
		var translationUnits2 = [ 'abc', 'lmn', '==123==', 'pqr', '==456==' ];

		var result1 = [ '', '==123==', 'pqr', '', '', '==456==' ];
		var result2 = [ 'abc\nlmn\n', '==123==', 'pqr', '', '', '==456==' ];

		translationUnits1 = mw.translate.alignHeaders( sourceUnits, translationUnits1 );
		assert.deepEqual( result1, translationUnits1, 'h2 headers aligned without merging' );

		translationUnits2 = mw.translate.alignHeaders( sourceUnits, translationUnits2 );
		assert.deepEqual( result2, translationUnits2, 'h2 headers aligned with merging' );
	} );
} );
