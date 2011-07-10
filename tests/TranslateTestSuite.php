<?php
require_once 'PHPUnit/Framework/TestSuite.php';
class TranslateTestSuite extends PHPUnit_Framework_TestSuite {
	public static function registerUnitTests( &$files ) {
		$testDir = dirname( __FILE__ ) . '/';
		$files[] = $testDir . 'MessageGroupBaseTest.php';
		return true;
	}

	public function __construct() {
		$this->setName ( 'TranslateTestSuite' );
		$this->addTestSuite ( 'MessageGroupBase' );
	}

	public static function suite() {
		return new self();
	}
}
