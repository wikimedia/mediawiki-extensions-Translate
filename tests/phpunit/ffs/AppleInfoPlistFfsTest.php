<?php
/**
 * The AppleInfoPListFfs class is responsible for loading messages from .strings
 * files, which are used in many iOS and Mac OS X projects.
 * These tests check that the message keys are loaded, mangled and unmangled
 * correctly.
 *
 * @file
 */

/** @covers AppleInfoPlistFfs */
class AppleInfoPlistFfsTest extends MediaWikiIntegrationTestCase {

	protected $groupConfigurationInfoPList = [
		'BASIC' => [
			'class' => FileBasedMessageGroup::class,
			'id' => 'test-id',
			'label' => 'Test Label',
			'namespace' => 'NS_MEDIAWIKI',
			'description' => 'Test description',
		],
		'FILES' => [
			'class' => AppleInfoPlistFfs::class,
		],
	];

	/**
	 * @covers AppleInfoPlistFfs::readRow
	 * @dataProvider stringProvider
	 */
	public function testInfoPlistException( $input, $exceptionMessage ) {
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( $exceptionMessage );

		$group = MessageGroupBase::factory( $this->groupConfigurationInfoPList );
		$ffs = new AppleInfoPlistFfs( $group );
		$ffs->readFromVariable( $input );
	}

	/**
	 * @covers AppleInfoPlistFfs::readRow
	 * @covers AppleInfoPlistFfs::writeRow
	 */
	public function testInfoPlistFileRoundtrip() {
		$infile = file_get_contents( __DIR__ . '/../data/AppleInfoPlistFfsTest1.strings' );
		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( $this->groupConfigurationInfoPList );
		$ffs = new AppleInfoPlistFfs( $group );
		$parsed = $ffs->readFromVariable( $infile );

		$outfile = '';
		foreach ( $parsed['MESSAGES'] as $key => $value ) {
			$outfile .= AppleInfoPlistFfs::writeRow( $key, $value );
		}
		$reparsed = $ffs->readFromVariable( $outfile );

		$this->assertSame( $parsed['MESSAGES'], $reparsed['MESSAGES'],
			'Messages survive roundtrip through write and read' );
	}

	public function stringProvider() {
		$input = <<<STRINGS
website = "<nowiki>http://en.wikipedia.org/</nowiki>";
"language" = "English";
STRINGS;
		yield [ $input, 'Empty or invalid key in line: "language" = "English"' ];

		$input = <<<STRINGS
website = "<nowiki>http://en.wikipedia.org/</nowiki>";
language key = "English";
STRINGS;
		yield [ $input, 'Key with space found in line: language key = "English"' ];
	}
}
