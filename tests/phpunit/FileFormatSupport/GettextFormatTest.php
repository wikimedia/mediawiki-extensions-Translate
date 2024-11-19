<?php
declare( strict_types=1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

use FileBasedMessageGroup;
use Generator;
use MediaWiki\Extension\Translate\MessageLoading\FatMessage;
use MediaWikiIntegrationTestCase;
use MessageGroupBase;
use ReflectionObject;

/**
 * Tests for Gettext message file format.
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\FileFormatSupport\GettextFormat
 */
class GettextFormatTest extends MediaWikiIntegrationTestCase {
	private array $groupConfiguration;

	protected function setUp(): void {
		parent::setUp();
		$this->groupConfiguration = [
			'BASIC' => [
				'class' => FileBasedMessageGroup::class,
				'id' => 'test-id',
				'label' => 'Test Label',
				'namespace' => 'NS_MEDIAWIKI',
				'description' => 'Test description',
			],
			'FILES' => [
				'format' => 'Gettext',
				'sourcePattern' => __DIR__ . '/../data/gettext.po',
			],
		];
	}

	/** @dataProvider provideMangling */
	public function testMangling( string $expected, array $item, string $algo ): void {
		$gettextFormat = $this->getGettextInstance();
		$this->assertEquals( $expected, $gettextFormat->generateKeyFromItem( $item, $algo ) );
	}

	public static function provideMangling(): array {
		return [
			[
				'3f9999051ce0bc6e98f43224fe6ee1c220e34e49-Hello!_world_loooooooooooooooo',
				[ 'id' => 'Hello! world loooooooooooooooooooooooooooooooooooooooooong', 'ctxt' => 'baa' ],
				'legacy'
			],
			[
				'3f9999-Hello!_world_loooooooooooooooo',
				[ 'id' => 'Hello! world loooooooooooooooooooooooooooooooooooooooooong', 'ctxt' => 'baa' ],
				'simple'
			],

			[
				'1437e478b59e220640bf530f7e3bac93950eb8ae-"¤_=FJQ"_¤r_£_ab',
				[ 'id' => '"¤#=FJQ"<>¤r £}[]}%ab', 'ctxt' => false ],
				'legacy'
			],
			[
				'1437e4-"¤#=FJQ"<>¤r_£}[]}%ab',
				[ 'id' => '"¤#=FJQ"<>¤r £}[]}%ab', 'ctxt' => false ],
				'simple'
			],

		];
	}

	public function testHashing(): void {
		$item1 = [
			'id' => 'a',
			'str' => 'b',
			'ctxt' => false,
		];

		$item2 = [
			'id' => 'a',
			'str' => 'b',
			'ctxt' => '',
		];
		$gettextFormat = $this->getGettextInstance();

		$this->assertNotEquals(
			$gettextFormat->generateKeyFromItem( $item1, 'legacy' ),
			$gettextFormat->generateKeyFromItem( $item2, 'legacy' ),
			'Empty msgctxt is different from no msgctxt'
		);

		$this->assertNotEquals(
			$gettextFormat->generateKeyFromItem( $item1, 'simple' ),
			$gettextFormat->generateKeyFromItem( $item2, 'simple' ),
			'Empty msgctxt is different from no msgctxt'
		);

		$this->assertEquals(
			sha1( $item1['id'] ) . '-' . $item1['id'],
			$gettextFormat->generateKeyFromItem( $item1, 'legacy' )
		);

		$this->assertEquals(
			substr( sha1( $item1['id'] ), 0, 6 ) . '-' . $item1['id'],
			$gettextFormat->generateKeyFromItem( $item1, 'simple' )
		);
	}

	public function testMsgctxtExport(): void {
		$gettextFormat = $this->getGettextInstance();
		$object = new ReflectionObject( $gettextFormat );
		$method = $object->getMethod( 'formatMessageBlock' );
		$method->setAccessible( true );

		$key = 'key';
		$m = new FatMessage( 'key', 'definition' );
		$m->setTranslation( 'translation' );
		$trans = [];
		$pot = [];
		$pluralCount = 0;

		$results =
			/** @lang Locale */
			<<<'GETTEXT'
			#
			msgid "definition"
			msgstr "translation"

			#
			msgctxt ""
			msgid "definition"
			msgstr "translation"

			#
			msgctxt "context"
			msgid "definition"
			msgstr "translation"
			GETTEXT;

		$results = preg_split( '/\n\n/', $results );

		// Case 1: no context
		$this->assertEquals(
			$results[0],
			trim( $method->invoke( $gettextFormat, $key, $m, $trans, $pot, $pluralCount, null ) )
		);

		// Case 2: empty context
		$pot['ctxt'] = '';
		$this->assertEquals(
			$results[1],
			trim( $method->invoke( $gettextFormat, $key, $m, $trans, $pot, $pluralCount, null ) )
		);

		// Case 3: context
		$pot['ctxt'] = 'context';
		$this->assertEquals(
			$results[2],
			trim( $method->invoke( $gettextFormat, $key, $m, $trans, $pot, $pluralCount, null ) )
		);
	}

	/** @dataProvider provideShouldOverwrite */
	public function testShouldOverwrite( string $a, string $b, bool $expected ): void {
		$gettextFormat = $this->getGettextInstance();
		$actual = $gettextFormat->shouldOverwrite( $a, $b );
		$this->assertEquals( $expected, $actual );
	}

	public static function provideShouldOverwrite(): Generator {
		yield 'Date only change should not override' => [
			/** @lang Locale */
			<<<'GETTEXT'
			#
			msgid ""
			msgstr ""
			""
			"PO-Revision-Date: 2017-02-09 07:24:07+0000\n"
			"X-POT-Import-Date: 2016-08-11 04:53:15+0000\n"
			"Content-Type: text/plain; charset=UTF-8\n"
			"Content-Transfer-Encoding: 8bit\n"
			"Language: azb\n"
			"X-Generator: MediaWiki 1.29.0-alpha; Translate 2017-01-24\n"
			"Plural-Forms: nplurals=2; plural=(n != 1);\n"

			#: frontend/templates/index.html:38
			msgid "About the map"
			msgstr ""
			GETTEXT,
			/** @lang Locale */
			<<<'GETTEXT'
			#
			msgid ""
			msgstr ""
			""
			"PO-Revision-Date: 2017-02-06 07:07:03+0000\n"
			"X-POT-Import-Date: 2016-08-11 04:53:15+0000\n"
			"Content-Type: text/plain; charset=UTF-8\n"
			"Content-Transfer-Encoding: 8bit\n"
			"Language: azb\n"
			"X-Generator: MediaWiki 1.29.0-alpha; Translate 2017-01-24\n"
			"Plural-Forms: nplurals=2; plural=(n != 1);\n"

			#: frontend/templates/index.html:38
			msgid "About the map"
			msgstr ""
			GETTEXT,
			false
		];

		yield 'Content change should override' => [
			/** @lang Locale */
			<<<'GETTEXT'
			#
			msgid ""
			msgstr ""
			""
			"PO-Revision-Date: 2017-02-09 07:24:07+0000\n"
			"X-POT-Import-Date: 2016-08-11 04:53:15+0000\n"
			"Content-Type: text/plain; charset=UTF-8\n"
			"Content-Transfer-Encoding: 8bit\n"
			"Language: azb\n"
			"X-Generator: MediaWiki 1.29.0-alpha; Translate 2017-01-24\n"
			"Plural-Forms: nplurals=2; plural=(n != 1);\n"

			#: frontend/templates/index.html:38
			msgid "About the map"
			msgstr ""
			GETTEXT,
			/** @lang Locale */
			<<<'GETTEXT'
			#
			msgid ""
			msgstr ""
			""
			"PO-Revision-Date: 2017-02-06 07:07:03+0000\n"
			"X-POT-Import-Date: 2016-08-11 04:53:15+0000\n"
			"Content-Type: text/plain; charset=UTF-8\n"
			"Content-Transfer-Encoding: 8bit\n"
			"Language: fi\n"
			"X-Generator: MediaWiki 1.29.0-alpha; Translate 2017-01-24\n"
			"Plural-Forms: nplurals=2; plural=(n != 1);\n"

			#: frontend/templates/index.html:38
			msgid "About the map"
			msgstr "Tietoja kartasta"
			GETTEXT,
			true
		];
	}

	public function testIsContentEqual(): void {
		$gettextFormat = $this->getGettextInstance();

		$this->assertTrue( $gettextFormat->isContentEqual( 'Foo bar', 'Foo bar' ) );
		$this->assertTrue( $gettextFormat->isContentEqual(
			'The bunnies stole {{PLURAL:GETTEXT|one carrot|%{count} carrots}}.',
			'{{PLURAL:GETTEXT|The bunnies stole one carrot.|The bunnies stole %{count} carrots.}}' ) );

		$this->assertFalse( $gettextFormat->isContentEqual( 'Foo bar', 'Foo baz' ) );
		$this->assertFalse( $gettextFormat->isContentEqual(
			'The bunnies stole {{PLURAL:GETTEXT|one banana|%{count} carrots}}.',
			'{{PLURAL:GETTEXT|The bunnies stole one carrot.|The bunnies stole %{count} carrots.}}' ) );
	}

	private function getGettextInstance(): GettextFormat {
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		return new GettextFormat( $group );
	}
}
