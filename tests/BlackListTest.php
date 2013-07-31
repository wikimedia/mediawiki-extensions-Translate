<?php
/**
 * Unit tests.
 *
 * @file
 * @author Santhosh Thottingal
 * @copyright Copyright © 2012-2013, Santhosh Thottingal
 * @license GPL-2.0+
 */

/**
 * Unit tests for blacklisting/whitelisting languages for a message group
 */
class BlackListTest extends MediaWikiTestCase {

	/**
	 * @var MessageGroup
	 */
	protected $group;
	protected $codes;
	protected $groupConfiguration = array(
		'BASIC' => array(
			'class' => 'FileBasedMessageGroup',
			'id' => 'test-id',
			'label' => 'Test Label',
			'namespace' => 'NS_MEDIAWIKI',
			'description' => 'Test description',
		),
		'FILES' => array(
			'class' => 'TestFFS',
		),
	);

	protected function setUp() {
		parent::setUp();
		$this->group = MessageGroupBase::factory( $this->groupConfiguration );
		$this->codes = array_flip( array_keys( TranslateUtils::getLanguageNames( 'en' ) ) );
	}

	protected function tearDown() {
		unset( $this->group );
		parent::tearDown();
	}

	public function testNoLanguageConf() {
		$translatableLanguages = $this->group->getTranslatableLanguages();
		$this->assertNull( $translatableLanguages );
	}

	public function testAllBlackList() {
		$conf = $this->groupConfiguration;
		$conf['LANGUAGES'] = array(
			'blacklist' => '*',
		);
		$group = MessageGroupBase::factory( $conf );
		$translatableLanguages = $group->getTranslatableLanguages();
		$this->assertEquals( count( $translatableLanguages ), 0 );
	}

	public function testAllWhiteList() {
		$conf = $this->groupConfiguration;
		$conf['LANGUAGES'] = array(
			'whitelist' => '*',
		);
		$group = MessageGroupBase::factory( $conf );
		$translatableLanguages = $group->getTranslatableLanguages();
		$this->assertNull( $translatableLanguages );
	}

	public function testWhiteListOverrideBlackList() {
		$conf = $this->groupConfiguration;
		$conf['LANGUAGES'] = array(
			'whitelist' => array( 'en', 'hi', 'ta' ),
			'blacklist' => array( 'ta' ),
		);
		$group = MessageGroupBase::factory( $conf );
		$translatableLanguages = $group->getTranslatableLanguages();
		$this->assertTrue( isset( $translatableLanguages['ta'] ) );
		$this->assertTrue( isset( $translatableLanguages['hi'] ) );
	}

	public function testSomeBlackList() {
		$conf = $this->groupConfiguration;
		$conf['LANGUAGES'] = array(
			'blacklist' => array( 'or', 'hi' ),
		);
		$group = MessageGroupBase::factory( $conf );
		$translatableLanguages = $group->getTranslatableLanguages();
		$this->assertTrue( !isset( $translatableLanguages['hi'] ) );
		$this->assertTrue( isset( $translatableLanguages['he'] ) );
	}
}
