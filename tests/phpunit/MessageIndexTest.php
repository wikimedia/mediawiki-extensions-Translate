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

	/**
	 * @dataProvider provideTestGetArrayDiff
	 */
	public function testGetArrayDiff( $expected, $old, $new ) {
		$actual = MessageIndex::getArrayDiff( $old, $new );
		$this->assertEquals( $expected['keys'], $actual['keys'], 'key diff' );
		$this->assertEquals( $expected['values'], $actual['values'], 'value diff' );
	}

	public function provideTestGetArrayDiff() {
		$tests = array();

		// Addition
		$old = array();
		$new = array(
			'label' => 'carpet',
		);
		$expected = array(
			'keys' => array(
				'add' => array(
					'label' => array(
						array(),
						array( 'carpet' ),
					),
				),
				'del' => array(),
				'mod' => array(),
			),
			'values' => array( 'carpet' ),
		);
		$tests[] = array( $expected, $old, $new );

		// Deletion
		$old = array(
			'bath' => array( 'goal', 'morals', 'coronation' ),
		);
		$new = array();
		$expected = array(
			'keys' => array(
				'add' => array(),
				'del' => array(
					'bath' => array(
						array( 'goal', 'morals', 'coronation' ),
						array(),
					),
				),
				'mod' => array(),
			),
			'values' => array( 'goal', 'morals', 'coronation' ),
		);
		$tests[] = array( $expected, $old, $new );

		// No change
		$old = $new = array(
			'label' => 'carpet',
			'salt' => array( 'morals' ),
			'bath' => array( 'goal', 'morals', 'coronation' ),
		);
		$expected = array(
			'keys' => array(
				'add' => array(),
				'del' => array(),
				'mod' => array(),
			),
			'values' => array(),
		);
		$tests[] = array( $expected, $old, $new );

		// Modification
		$old = array(
			'bath' => array( 'goal', 'morals', 'coronation' ),
		);
		$new = array(
			'bath' => array( 'goal', 'beliefs', 'coronation', 'showcase' ),
		);
		$expected = array(
			'keys' => array(
				'add' => array(),
				'del' => array(),
				'mod' => array(
					'bath' => array(
						array( 'goal', 'morals', 'coronation' ),
						array( 'goal', 'beliefs', 'coronation', 'showcase' ),
					),
				),
			),
			'values' => array( 'morals', 'beliefs', 'showcase' ),
		);
		$tests[] = array( $expected, $old, $new );

		return $tests;
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
		$diff = MessageIndex::getArrayDiff( array(), $data );
		$mi->store( $data, $diff['keys'] );

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
	public function store( array $a, array $diff ) {
		parent::store( $a, $diff );
	}

	public function get( $a ) {
		return parent::get( $a );
	} // @codingStandardsIgnoreEnd
}

class TestableCDBMessageIndex extends CDBMessageIndex {
	// @codingStandardsIgnoreStart PHP CodeSniffer warns "Useless method overriding
	// detected", but store() and get() are protected in parent.
	public function store( array $a, array $diff ) {
		parent::store( $a, $diff );
	}

	public function get( $a ) {
		return parent::get( $a );
	} // @codingStandardsIgnoreEnd
}

class TestableSerializedMessageIndex extends SerializedMessageIndex {
	// @codingStandardsIgnoreStart PHP CodeSniffer warns "Useless method overriding
	// detected", but store() and get() are protected in parent.
	public function store( array $a, array $diff ) {
		parent::store( $a, $diff );
	}

	public function get( $a ) {
		return parent::get( $a );
	} // @codingStandardsIgnoreEnd
}

class TestableHashMessageIndex extends HashMessageIndex {
	// @codingStandardsIgnoreStart PHP CodeSniffer warns "Useless method overriding
	// detected", but store() and get() are protected in parent.
	public function store( array $a, array $diff ) {
		parent::store( $a, $diff );
	}

	public function get( $a ) {
		return parent::get( $a );
	} // @codingStandardsIgnoreEnd
}
