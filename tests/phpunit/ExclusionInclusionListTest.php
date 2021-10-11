<?php
/**
 * @file
 * @author Santhosh Thottingal
 * @copyright Copyright © 2012-2013, Santhosh Thottingal
 * @license GPL-2.0-or-later
 */

class ExclusionInclusionListTest extends MediaWikiIntegrationTestCase {

	/** @var MessageGroup */
	protected $group;
	protected $codes;
	protected $groupConfiguration = [
		'BASIC' => [
			'class' => FileBasedMessageGroup::class,
			'id' => 'test-id',
			'label' => 'Test Label',
			'namespace' => 'NS_MEDIAWIKI',
			'description' => 'Test description',
		],
		'FILES' => [
			'class' => TestFFS::class,
		],
	];

	protected function setUp(): void {
		parent::setUp();
		$this->group = MessageGroupBase::factory( $this->groupConfiguration );
		$this->codes = array_flip( array_keys( TranslateUtils::getLanguageNames( 'en' ) ) );
	}

	protected function tearDown(): void {
		unset( $this->group );
		parent::tearDown();
	}

	public function testNoLanguageConf() {
		$translatableLanguages = $this->group->getTranslatableLanguages();
		$this->assertNull( $translatableLanguages );
	}

	public function testAllExclusionList() {
		$conf = $this->groupConfiguration;
		$conf['LANGUAGES'] = [
			'exclude' => '*',
		];
		$group = MessageGroupBase::factory( $conf );
		$translatableLanguages = $group->getTranslatableLanguages();
		$this->assertEquals( count( $translatableLanguages ), 0 );
	}

	public function testAllInclusionList() {
		$conf = $this->groupConfiguration;
		$conf['LANGUAGES'] = [
			'include' => '*',
		];
		$group = MessageGroupBase::factory( $conf );
		$translatableLanguages = $group->getTranslatableLanguages();
		$this->assertNull( $translatableLanguages );
	}

	public function testInclusionListOverridesExclusionList() {
		$conf = $this->groupConfiguration;
		$conf['LANGUAGES'] = [
			'include' => [ 'en', 'hi', 'ta' ],
			'exclude' => [ 'ta' ],
		];
		$group = MessageGroupBase::factory( $conf );
		$translatableLanguages = $group->getTranslatableLanguages();
		$this->assertTrue( isset( $translatableLanguages['ta'] ) );
		$this->assertTrue( isset( $translatableLanguages['hi'] ) );
	}

	public function testSomeExclusionList() {
		$conf = $this->groupConfiguration;
		$conf['LANGUAGES'] = [
			'exclude' => [ 'or', 'hi' ],
		];
		$group = MessageGroupBase::factory( $conf );
		$translatableLanguages = $group->getTranslatableLanguages();
		$this->assertTrue( !isset( $translatableLanguages['hi'] ) );
		$this->assertTrue( isset( $translatableLanguages['he'] ) );
	}
}
