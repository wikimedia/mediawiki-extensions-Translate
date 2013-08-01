<?php
/**
 * Tests for PythonSingle message file format.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2013, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * @see PythonSingleFFS
 */
class PythonSingleFFSTest extends MediaWikiTestCase {
	protected $groupConfiguration;

	public function setUp() {
		parent::setUp();
		$this->groupConfiguration = array(
			'BASIC' => array(
				'class' => 'FileBasedMessageGroup',
				'id' => 'test-id',
				'label' => 'Test Label',
				'namespace' => 'NS_MEDIAWIKI',
				'description' => 'Test description',
			),
			'FILES' => array(
				'class' => 'PythonSingleFFS',
				'sourcePattern' => __DIR__ . '/../data/pythontest.py',
				'targetPattern' => __DIR__ . '/../data/pythontest.py',
				'codeMap' => array(
					'fi' => 'encrypted',
				)
			),
		);
	}

	public function testParsing() {
		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$ffs = new PythonSingleFFS( $group );

		$command = wfEscapeShellArg( "import simplejson as json; print 'mui'" );
		$ret = wfShellExec( "python -c $command" );
		if ( trim( $ret ) !== 'mui' ) {
			$this->markTestSkipped( 'Dependency python simplejson not installed' );

			return;
		}

		$parsed = $ffs->read( 'en' );
		$expected = array(
			'MESSAGES' => array( 'user' => 'Users' )
		);
		$this->assertEquals( $expected, $parsed );

		$parsed = $ffs->read( 'fi' );
		$expected = array(
			'MESSAGES' => array( 'user' => 'Käyttäjät' )
		);
		$this->assertEquals( $expected, $parsed );
	}
}
