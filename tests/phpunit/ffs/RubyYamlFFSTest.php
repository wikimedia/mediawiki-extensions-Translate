<?php

class RubyYamlFFSTest extends MediaWikiTestCase {
	/** @var MessageGroup */
	protected $group;

	/** @var FFS */
	protected $ffs;

	protected $groupConfiguration = array(
		'BASIC' => array(
			'class' => 'FileBasedMessageGroup',
			'id' => 'test-id',
			'label' => 'Test Label',
			'namespace' => 'NS_MEDIAWIKI',
			'description' => 'Test description',
		),
		'FILES' => array(
			'class' => 'RubyYamlFFS',
		),
	);

	protected function setUp() {
		parent::setUp();
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		/** @var YamlFFS $ffs */
		$this->ffs = $group->getFFS();
	}

	public function testFlattenPluralWithNoPlurals() {
		$input = array(
			'much' => 'a lot',
			'less' => 'not so much',
		);
		$output = false;
		$this->assertEquals( $output, $this->ffs->flattenPlural( $input ) );
	}

	public function testFlattenPluralWithPlurals() {
		$input = array(
			'one' => 'just a tiny bit',
			'two' => 'not so much',
			'other' => 'maybe a lot',
		);
		$output = '{{PLURAL|one=just a tiny bit|two=not so much|maybe a lot}}';
		$this->assertEquals( $output, $this->ffs->flattenPlural( $input ) );
	}

	public function testFlattenPluralWithArrays() {
		$input = array(
			'one' => array(
				'multi' => 'he lives in a multistorey house',
				'row' => 'he lives in a row house',
			),
			'other' => array(
				'multi' => 'he lives in mountain cave',
				'row' => 'he lives in a cave near the river',
			),
		);
		$output = false;
		$this->assertEquals( $output, $this->ffs->flattenPlural( $input ) );
	}

	/**
	 * @expectedException MWException
	 * @expectedExceptionMessage Reserved plural keywords mixed with other keys
	 * @dataProvider flattenPluralsWithMixedKeywordsProvider
	 */

	public function testFlattenPluralsWithMixedKeywords( $input, $comment ) {
		$this->ffs->flattenPlural( $input );
	}

	public function flattenPluralsWithMixedKeywordsProvider() {
		return array(
			array(
				array(
					'carrot' => 'I like carrots',
					'other' => 'I like milk',
				),
				'reserved keyword at the end',
			),
			array(
				array(
					'one' => 'I am the one leader',
					'club' => 'I am the club leader',
				),
				'reserved keyword at the beginning',
			)
		);
	}

	/**
	 * @dataProvider unflattenDataProvider
	 */
	public function testUnflattenPural( $key, $value, $result ) {
		$this->assertEquals(
			$result,
			$this->ffs->unflattenPlural( $key, $value )
		);
	}

	public function unflattenDataProvider() {
		return array(
			array( 'key', '{{PLURAL}}', false ),
			array( 'key', 'value', array( 'key' => 'value' ) ),
			array( 'key', '{{PLURAL|one=cat|other=cats}}',
				array( 'key.one' => 'cat', 'key.other' => 'cats' )
			),
			array( 'key', '{{PLURAL|one=шляху %{related_ways}|шляхоў %{related_ways}}}',
				array(
					'key.one' => 'шляху %{related_ways}',
					'key.other' => 'шляхоў %{related_ways}'
				)
			),
			array( 'key', '{{PLURAL|foo=cat}}',
				array( 'key.other' => 'foo=cat' )
			),
			array( 'key', '{{PLURAL|zero=0|one=1|two=2|few=3|many=160|other=898}}',
				array( 'key.zero' => '0', 'key.one' => '1', 'key.two' => '2',
					'key.few' => '3', 'key.many' => '160', 'key.other' => '898' )
			),
		);
	}
}
