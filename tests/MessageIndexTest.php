<?php
/**
 * Tests for different MessageIndex backends.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * @group Database
 */
class MessageIndexTest extends MediaWikiTestCase {
	protected $config;

	public function setUp() {
		parent::setUp();
		$this->testdata = unserialize( file_get_contents( __DIR__ . '/messageindexdata.ser' ) );
		global $wgTranslateCacheDirectory;
		$this->config = $wgTranslateCacheDirectory;
		// Only in 1.20, but who runs tests again older versions anyway?
		$wgTranslateCacheDirectory = $this->getNewTempDirectory();
	}

	public function tearDown() {
		global $wgTranslateCacheDirectory;
		$wgTranslateCacheDirectory = $this->config;
	}

	/**
	 * @dataProvider MessageIndexImplementationProvider
	 */
	public function testMessageIndexImplementation( $mi ) {
		$data = $this->testdata;
		$mi->store( $data );

		$tests = array_rand( $data, 10 );
		foreach ( $tests as $key ) {
			$this->assertSame( $data[$key], $mi->get( $key ), "Values are preserved for random key $key" );
		}

		$cached = $mi->retrieve();

		$tests = array_rand( $data, 10 );
		foreach ( $tests as $key ) {
			$this->assertSame( $data[$key], $mi->get( $key ), "Values are preserved after retrieve for random key $key" );
		}

		$this->assertEquals( count( $data ), count( $cached ), 'Cache has same number of elements' );
		$this->assertEquals( $data, $cached, 'Cache is preserved' );
	}

	public function MessageIndexImplementationProvider() {
		return array(
			array( new TestableDatabaseMessageIndex() ),
			array( new TestableCDBMessageIndex() ),
			array( new TestableSerializedMessageIndex() ),
			// Not testing CachedMessageIndex because there is no easy way to mockup those.
		);
	}

}

class TestableDatabaseMessageIndex extends DatabaseMessageIndex {
	public function store( array $a ) {
		parent::store( $a );
	}

	public function get( $a ) {
		return parent::get( $a );
	}
}

class TestableCDBMessageIndex extends CDBMessageIndex {
	public function store( array $a ) {
		parent::store( $a );
	}

	public function get( $a ) {
		return parent::get( $a );
	}
}

class TestableSerializedMessageIndex extends SerializedMessageIndex {
	public function store( array $a ) {
		parent::store( $a );
	}

	public function get( $a ) {
		return parent::get( $a );
	}
}