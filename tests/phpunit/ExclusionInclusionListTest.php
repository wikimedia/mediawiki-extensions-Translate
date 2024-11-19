<?php
/**
 * @file
 * @author Santhosh Thottingal
 * @copyright Copyright © 2012-2013, Santhosh Thottingal
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\Utilities\Utilities;

/** @covers \FileBasedMessageGroup */
class ExclusionInclusionListTest extends MediaWikiIntegrationTestCase {

	private MessageGroup $group;
	private array $codes;
	private array $groupConfiguration = [
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
		$this->codes = array_flip( array_keys( Utilities::getLanguageNames( 'en' ) ) );
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
		$this->assertCount( 0, $translatableLanguages );
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
