<?php
/**
 * @author Niklas Laxström
 * @file
 * @license GPL-2.0-or-later
 */

class MessageGroupStatsTest extends MediaWikiTestCase {
	protected function setUp() {
		parent::setUp();

		$this->setTemporaryHook(
			'TranslatePostInitGroups',
			function ( &$list ) {
				$exampleMessageGroup = new WikiMessageGroup( 'theid', 'thesource' );
				$exampleMessageGroup->setLabel( 'thelabel' ); // Example
				$exampleMessageGroup->setNamespace( 5 ); // Example
				$list['theid'] = $exampleMessageGroup;
			}
		);

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => wfGetCache( 'hash' ) ] ) );
		$mg->recache();
	}

	public function testGetDatabaseIdForGroupId() {
		$shortId = 'abab';
		$longId = str_repeat( 'ab', 100 );

		$this->assertLessThanOrEqual(
			100,
			strlen( MessageGroupStats::getDatabaseIdForGroupId( $shortId ) ),
			'Short id is <= 100 bytes long'
		);

		$this->assertLessThanOrEqual(
			100,
			strlen( MessageGroupStats::getDatabaseIdForGroupId( $longId ) ),
			'Long id is <= 100 bytes long'
		);

		$longId1 = str_repeat( 'ab', 100 ) . '1';
		$longId2 = str_repeat( 'ab', 100 ) . '2';

		$this->assertNotEquals(
			MessageGroupStats::getDatabaseIdForGroupId( $longId1 ),
			MessageGroupStats::getDatabaseIdForGroupId( $longId2 ),
			'Two long ids with the same prefix do not collide'
		);
	}

	public function testFunctionReturnFormat() {
		$validLang = MessageGroupStats::forLanguage( 'en', MessageGroupStats::FLAG_CACHE_ONLY );
		$invalidLang = MessageGroupStats::forLanguage( 'ffff', MessageGroupStats::FLAG_CACHE_ONLY );

		$validGroup = MessageGroupStats::forGroup( 'theid',  MessageGroupStats::FLAG_CACHE_ONLY );
		$invalidGroup = MessageGroupStats::forGroup( 'invalid-mg-group',
			MessageGroupStats::FLAG_CACHE_ONLY );

		$this->assertInternalType( 'array', current( $validLang ),
			'forLanguage returns data in valid format for valid language' );
		$this->assertInternalType( 'array', current( $invalidLang ),
			'forLanguage returns data in valid format for invalid language' );

		$this->assertInternalType( 'array', current( $validGroup ),
			'forGroup returns data in valid format for valid group' );
		$this->assertInternalType( 'array', current( $invalidGroup ),
			'forGroup returns data in valid format for invalid group' );
	}
}
