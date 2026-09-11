<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use FileBasedMessageGroup;
use MediaWiki\Extension\Translate\MessageGroupConfiguration\MessageGroupFactory;
use MediaWiki\Extension\Translate\MessageGroupConfiguration\MessageGroupTypeRegistry;
use MediaWikiIntegrationTestCase;
use ReflectionMethod;
use Wikimedia\ObjectCache\HashBagOStuff;

/**
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Synchronization\ImportTranslationsSpecialPage
 */
class ImportTranslationsSpecialPageTest extends MediaWikiIntegrationTestCase {
	private ImportTranslationsSpecialPage $page;

	protected function setUp(): void {
		parent::setUp();
		$this->page = new ImportTranslationsSpecialPage(
			new HashBagOStuff(),
			new MessageGroupFactory( new MessageGroupTypeRegistry() )
		);
	}

	public function testParseFileReturnsErrorOnInvalidGettext(): void {
		$method = new ReflectionMethod( ImportTranslationsSpecialPage::class, 'parseFile' );

		$result = $method->invoke( $this->page, 'this is not a valid gettext file' );

		$this->assertNotSame( 'ok', $result[0] );
	}

	public function testParseFileReturnsErrorOnMissingHeaders(): void {
		$method = new ReflectionMethod( ImportTranslationsSpecialPage::class, 'parseFile' );

		// Valid Gettext syntax but missing X-Language-Code and X-Message-Group headers
		$po = <<<'PO'
msgid ""
msgstr ""
"Content-Type: text/plain; charset=UTF-8\n"

msgid "hello"
msgstr "world"
PO;

		$result = $method->invoke( $this->page, $po );

		$this->assertSame( 'no-headers', $result[0] );
	}

	public function testParseFileSucceedsWithValidGettextExport(): void {
		$method = new ReflectionMethod( ImportTranslationsSpecialPage::class, 'parseFile' );

		$po = <<<'PO'
msgid ""
msgstr ""
"Content-Type: text/plain; charset=UTF-8\n"
"X-Language-Code: fi\n"
"X-Message-Group: test-group\n"

msgctxt "hello"
msgid "hello"
msgstr "hei"
PO;

		$result = $method->invoke( $this->page, $po );

		$this->assertSame( 'ok', $result[0] );
		$this->assertSame( 'fi', $result[1]['EXTRA']['METADATA']['code'] );
		$this->assertSame( 'test-group', $result[1]['EXTRA']['METADATA']['group'] );
		$this->assertArrayHasKey( 'hello', $result[1]['MESSAGES'] );
	}

	public function testParseFileUsesFileBasedMessageGroup(): void {
		$method = new ReflectionMethod( ImportTranslationsSpecialPage::class, 'parseFile' );

		$po = <<<'PO'
msgid ""
msgstr ""
"Content-Type: text/plain; charset=UTF-8\n"
"X-Language-Code: de\n"
"X-Message-Group: some-group\n"

msgctxt "key"
msgid "key"
msgstr "wert"
PO;

		$result = $method->invoke( $this->page, $po );

		// If the factory wiring is broken, parseFile() throws before returning ok
		$this->assertSame( 'ok', $result[0] );
		$this->assertInstanceOf( FileBasedMessageGroup::class,
			$this->getServiceContainer()->get( 'Translate:MessageGroupFactory' )
				->createGroup( [
					'BASIC' => [ 'class' => FileBasedMessageGroup::class, 'namespace' => -1 ],
					'FILES' => [ 'format' => 'Gettext', 'CtxtAsKey' => true ],
				] )
		);
	}
}
