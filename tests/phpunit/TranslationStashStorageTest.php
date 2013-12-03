<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * @group Database
 */
class TranslationStashStorageTest extends MediaWikiTestCase {

	public function testAdd() {
		$storage = new TranslationStashStorage( wfGetDB( DB_MASTER ) );

		$translation1 = new StashedTranslation(
			User::newFromId( 1 ),
			Title::makeTitle( NS_MAIN, __METHOD__ ),
			'test value',
			array( 'kissa', 'kala' )
		);

		$translation2 = new StashedTranslation(
			User::newFromId( 2 ),
			Title::makeTitle( NS_MAIN, __METHOD__ ),
			'test value 2',
			array( 'kissa', 'kala' )
		);

		$storage->addTranslation( $translation1 );
		$storage->addTranslation( $translation2 );

		$ret = $storage->getTranslations( User::newFromId( 1 ) );
		$this->assertCount( 1, $ret, 'One stashed translation for this user' );

		// AssertSame required same reference, assert equals only same content
		$this->assertEquals( $translation1, $ret[0], 'Data roundtrips' );
	}
}
