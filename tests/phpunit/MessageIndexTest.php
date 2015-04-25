<?php
/**
 * Tests for different MessageIndex backends.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * @group Database
 * @group large
 */
class MessageIndexTest extends MediaWikiTestCase {
	protected function setUp() {
		parent::setUp();
		$this->setMwGlobals( array(
			'wgTranslateCacheDirectory' => $this->getNewTempDirectory(),
			'wgTranslateTranslationServices' => array(),
		) );
	}

	protected static function getTestData() {
		static $data = null;
		if ( $data === null ) {
			$data = unserialize( file_get_contents( __DIR__ . '/messageindexdata.ser' ) );
		}

		return $data;
	}

	/**
	 * @dataProvider provideMessageIndexImplementation
	 */
	public function testMessageIndexImplementation( $mi ) {
		$data = self::getTestData();
		/** @var TestableDatabaseMessageIndex|TestableCDBMessageIndex|TestableSerializedMessageIndex */
		$mi->store( $data );

		$tests = array_rand( $data, 10 );
		foreach ( $tests as $key ) {
			$this->assertSame(
				$data[$key],
				$mi->get( $key ),
				"Values are preserved for random key $key"
			);
		}

		$cached = $mi->retrieve();

		$tests = array_rand( $data, 10 );
		foreach ( $tests as $key ) {
			$this->assertSame(
				$data[$key],
				$mi->get( $key ),
				"Values are preserved after retrieve for random key $key"
			);
		}

		$this->assertEquals(
			count( $data ),
			count( $cached ),
			'Cache has same number of elements'
		);
		$this->assertEquals( $data, $cached, 'Cache is preserved' );
	}

	public static function provideMessageIndexImplementation() {
		return array(
			array( new TestableDatabaseMessageIndex() ),
			array( new TestableCDBMessageIndex() ),
			array( new TestableSerializedMessageIndex() ),
			array( new TestableHashMessageIndex() ),
			// Not testing CachedMessageIndex because there is no easy way to mockup those.
		);
	}
}

class TestableDatabaseMessageIndex extends DatabaseMessageIndex {
	// @codingStandardsIgnoreStart PHP CodeSniffer warns "Useless method overriding
	// detected", but store() and get() are protected in parent.
	public function store( array $a ) {
		parent::store( $a );
	}

	public function get( $a ) {
		return parent::get( $a );
	} // @codingStandardsIgnoreEnd
}

class TestableCDBMessageIndex extends CDBMessageIndex {
	// @codingStandardsIgnoreStart PHP CodeSniffer warns "Useless method overriding
	// detected", but store() and get() are protected in parent.
	public function store( array $a ) {
		parent::store( $a );
	}

	public function get( $a ) {
		return parent::get( $a );
	} // @codingStandardsIgnoreEnd
}

class TestableSerializedMessageIndex extends SerializedMessageIndex {
	// @codingStandardsIgnoreStart PHP CodeSniffer warns "Useless method overriding
	// detected", but store() and get() are protected in parent.
	public function store( array $a ) {
		parent::store( $a );
	}

	public function get( $a ) {
		return parent::get( $a );
	} // @codingStandardsIgnoreEnd
}

class TestableHashMessageIndex extends HashMessageIndex {
	// @codingStandardsIgnoreStart PHP CodeSniffer warns "Useless method overriding
	// detected", but store() and get() are protected in parent.
	public function store( array $a ) {
		parent::store( $a );
	}

	public function get( $a ) {
		return parent::get( $a );
	} // @codingStandardsIgnoreEnd
}
