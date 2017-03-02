<?php
/**
 * Tests for MediaWikiExtensionFFS
 * @author Niklas Laxström
 * @file
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * @see MediaWikiExtensionFFS
 */
class MediaWikiExtensionFFSTest extends MediaWikiTestCase {
	protected $conf = [
		'BASIC' => [
			'class' => 'MediaWikiExtensionMessageGroup',
			'id' => 'test-id',
			'label' => 'Test Label',
			'namespace' => 'NS_MEDIAWIKI',
			'description' => 'Test description',
		],
		'FILES' => [
			'class' => 'MediaWikiExtensionFFS',
		],
	];

	protected function setUp() {
		parent::setUp();
		$this->setMwGlobals( [
			'wgTranslateDocumentationLanguageCode' => 'qqq',
		] );
	}

	public function testWriteReal() {
		if ( !method_exists( 'LanguageNames', 'getNames' ) ) {
			$this->markTestSkipped( 'Cldr extension is not installed' );
		}

		$this->conf['FILES']['sourcePattern'] = __DIR__ . '/../data/Example.i18n.php';
		$ffs = MessageGroupBase::factory( $this->conf )->getFFS();
		$obj = new ReflectionObject( $ffs );
		$method = $obj->getMethod( 'writeReal' );
		$method->setAccessible( true );
		$collection = new MockMessageCollectionForExport();
		$result = $method->invoke( $ffs, $collection );

		$expected = file_get_contents( __DIR__ . '/../data/Example-result.i18n.php' );
		$this->assertEquals( $expected, $result );
	}

	public function testGenerateMessageBlock() {
		$ffs = MessageGroupBase::factory( $this->conf )->getFFS();
		$obj = new ReflectionObject( $ffs );
		$method = $obj->getMethod( 'generateMessageBlock' );
		$method->setAccessible( true );
		$collection = new MockMessageCollectionForExport();
		$mangler = StringMatcher::EmptyMatcher();

		$result = $method->invoke( $ffs, $collection, $mangler );

		$expected = "\n\t'translatedmsg' => 'translation',\n\t'fuzzymsg' => 'translation', # Fuzzy\n";
		$this->assertEquals( $expected, $result );
	}

	/**
	 * @dataProvider provideQuotableStrings
	 */
	public function testQuote( $source, $expected ) {
		$class = new ReflectionClass( 'MediaWikiExtensionFFS' );
		$method = $class->getMethod( 'quote' );
		$method->setAccessible( true );
		$result = $method->invoke( null, $source );
		$this->assertEquals( $expected, $result );
	}

	public static function provideQuotableStrings() {
		return [
			[ 'key', "'key'" ],
			[ 'normal $1 variable', "'normal $1 variable'" ],
			[ 'abnormal $foo variable', "'abnormal \$foo variable'" ],
			[ 'quote " and quote \'', "'quote \" and quote \\''" ],
			[ 'quote " and quote \' twice \'', "\"quote \\\" and quote ' twice '\"" ],
		];
	}

	/**
	 * @dataProvider provideComments
	 */
	public function testParseAuthorsFromString( $source, $expected ) {
		$class = new ReflectionClass( 'MediaWikiExtensionFFS' );
		$method = $class->getMethod( 'parseAuthorsFromString' );
		$method->setAccessible( true );
		$result = $method->invoke( null, $source );
		$this->assertEquals( $expected, $result );
	}

	public static function provideComments() {
		$comment =
			<<<PHP
			/** Message documentation (Message documentation)
 * @author Purodha
 * @author The Evil IP address
 */
PHP;

		return [
			[ $comment, [ 'Purodha', 'The Evil IP address' ] ],
		];
	}
}
