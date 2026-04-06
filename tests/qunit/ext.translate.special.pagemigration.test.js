/*!
 * @license GPL-2.0-or-later
 */
QUnit.module( 'ext.translate.special.pagemigration', function ( hooks ) {
	'use strict';

	hooks.beforeEach( function () {
		this.server = this.sandbox.useFakeServer();
		this.server.respondImmediately = true;
	} );

	const services = require( 'ext.translate.special.pagemigration/resources/src/ext.translate.pagemigration/services.js' );

	const goodMessageCollection = '{ "query": { "messagecollection": [ { "key": "key_",' +
		' "definition": "definition_", "title": "title_" }, { "key": "key_1",' +
		' "definition": "definition_1", "title": "title_1" } ] } }';
	const badMessageCollection = '{ "error": { "code": "badparameter", "info": "Invalid value for parameter \\"mcgroup\\"." } }';
	const goodFuzzyBotRevisions = '{ "query": { "pages": [ { "pageid": "19563", "revisions": ' +
		'[ {"timestamp": "2014-02-18T20:59:58Z" }, { "timestamp": "t2" } ] } ] } }';
	const badFuzzyBotRevisions = '{ "query": { "pages": [ { "ns": 0, "title": "Special pages/fr", "missing": true } ] } }';
	const goodPreFuzzyBotRevisions = '{ "query": { "pages": [ { "pageid": "19563", "revisions": ' +
		'[ { "content": "unit1\\n\\nunit2\\n\\nunit3" } ] } ] } }';
	const badPreFuzzyBotRevisions = '{ "query": { "pages": [ { "pageid": "19563" } ] } }';

	QUnit.test( 'Load data', function ( assert ) {
		const expected = {
			sourceUnits: [ { identifier: 'key_', definition: 'definition_' }, { identifier: 'key_1', definition: 'definition_1' } ],
			translationUnits: [ 'unit1', 'unit2', 'unit3' ],
			translationLang: 'fr',
			translationDir: 'ltr'
		};

		this.server.respondWith( /list=messagecollection/, [ 200, { 'Content-Type': 'application/json' }, goodMessageCollection ] );
		this.server.respondWith( /prop=revisions.*&rvuser=FuzzyBot/, [ 200, { 'Content-Type': 'application/json' }, goodFuzzyBotRevisions ] );
		this.server.respondWith( /prop=revisions.*&rvstart=.*/, [ 200, { 'Content-Type': 'application/json' }, goodPreFuzzyBotRevisions ] );

		var done = assert.async();
		services.loadData( 'Special pages/fr' ).then( function ( units ) {
			// Deep equality won’t work with functions, but neither are we interested
			delete units.save;

			assert.deepEqual( units, expected, 'Data loaded' );
			done();
		} );
	} );

	QUnit.test( 'Load data about a source-language translation (which was created by FuzzyBot)', function ( assert ) {
		this.server.respondWith( /list=messagecollection/, [ 200, { 'Content-Type': 'application/json' }, goodMessageCollection ] );
		this.server.respondWith( /prop=revisions.*&rvuser=FuzzyBot/, [ 200, { 'Content-Type': 'application/json' }, goodFuzzyBotRevisions ] );
		this.server.respondWith( /prop=revisions.*&rvstart=.*/, [ 200, { 'Content-Type': 'application/json' }, badPreFuzzyBotRevisions ] );

		assert.rejects( services.loadData( 'Special pages/en' ), /^\(pm-old-translations-missing: Special pages\/en\)$/, 'Missing revision handled' );
	} );

	QUnit.test( 'Load data about non-existing translation', function ( assert ) {
		this.server.respondWith( /list=messagecollection/, [ 200, { 'Content-Type': 'application/json' }, goodMessageCollection ] );
		this.server.respondWith( /prop=revisions.*&rvuser=FuzzyBot/, [ 200, { 'Content-Type': 'application/json' }, badFuzzyBotRevisions ] );

		assert.rejects( services.loadData( 'Special pages/fr' ), /^\(pm-old-translations-missing: Special pages\/fr\)$/, 'Missing revision handled' );
	} );

	QUnit.test( 'Load data about a page no longer using Translate', function ( assert ) {
		this.server.respondWith( /list=messagecollection/, [ 200, { 'Content-Type': 'application/json' }, badMessageCollection ] );
		this.server.respondWith( /prop=revisions.*&rvuser=FuzzyBot/, [ 200, { 'Content-Type': 'application/json' }, goodFuzzyBotRevisions ] );
		this.server.respondWith( /prop=revisions.*&rvstart=.*/, [ 200, { 'Content-Type': 'application/json' }, goodPreFuzzyBotRevisions ] );

		assert.rejects( services.loadData( 'Special pages/fr' ), /^\(pm-pagetitle-not-translatable: Special pages\)$/, 'Missing message group handled' );
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
		result = services.splitHeaders( translationUnits );
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

		services.alignHeaders( sourceUnits, translationUnits1 );
		assert.deepEqual( result1, translationUnits1, 'h2 headers aligned without merging' );

		services.alignHeaders( sourceUnits, translationUnits2 );
		assert.deepEqual( result2, translationUnits2, 'h2 headers aligned with merging' );
	} );
} );
