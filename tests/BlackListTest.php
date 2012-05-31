<?php
/**
 * Unit tests.
 *
 * @file
 * @author Santhosh Thottingal
 * @copyright Copyright © 2012, Santhosh Thottingal
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Unit tests for blacklisting/whitelisting languages for a message group
 */
class BlackListTest extends MediaWikiTestCase {

	/**
	 * @var MessageGroup
	 */
	protected $group;

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

	}

	protected function tearDown() {
		unset( $this->group );
		parent::tearDown();
	}

	public function testNoLanguageConf() {
		$allLangs = Language::getLanguageNames( false );
		$translatableLanguages = TranslateUtils::getTranslatableLanguages( $this->group );
		$this->assertEquals( $allLangs, $translatableLanguages );
	}

	public function testAllBlackList() {
		$conf = $this->groupConfiguration;
		$conf['LANGUAGES'] =  array(
			'blacklist' => '*' );
		$group = MessageGroupBase::factory( $conf );
		$allLangs = Language::getLanguageNames( false );
		$translatableLanguages = TranslateUtils::getTranslatableLanguages( $group );
		$this->assertEquals( count( $translatableLanguages ) , 0 );
	}


	public function testAllWhiteList() {
		$conf = $this->groupConfiguration;
		$conf['LANGUAGES'] =  array(
			'whitelist' => '*', );
		$group = MessageGroupBase::factory( $conf );
		$allLangs = Language::getLanguageNames( false );
		$translatableLanguages = TranslateUtils::getTranslatableLanguages( $group );
		$this->assertEquals( $allLangs, $translatableLanguages );
	}

	public function testWhiteListOverrideBlackList() {
		$conf = $this->groupConfiguration;
		$conf['LANGUAGES'] =  array(
			'whitelist' => array( 'en', 'hi', 'ta' ),
			'blacklist' =>  array( 'ta' ),
			);
		$group = MessageGroupBase::factory( $conf );
		$allLangs = Language::getLanguageNames( false );
		$translatableLanguages = TranslateUtils::getTranslatableLanguages( $group );
		$this->assertTrue( isset( $translatableLanguages['ta'] ) );
	}

}
