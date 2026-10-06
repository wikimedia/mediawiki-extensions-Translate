<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

use MediaWiki\Content\TextContent;
use MediaWiki\Extension\Translate\MessageGroupConfiguration\MessageGroupConfigurationParser;
use MediaWiki\Extension\Translate\MessageGroups\FileBasedMessageGroup;
use MediaWiki\Extension\Translate\MessageGroups\MessageGroupBase;
use MediaWiki\Extension\Translate\MessageLoading\FatMessage;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;
use MediaWiki\Extension\Translate\MessageLoading\MessageDefinitions;
use MediaWikiIntegrationTestCase;

/** @covers \MediaWiki\Extension\Translate\FileFormatSupport\PlainTextFormat */
class PlainTextFormatTest extends MediaWikiIntegrationTestCase {
	private function configuration(): array {
		return [
			'BASIC' => [
				'type' => 'file', 'id' => 'plain-text-test',
				'label' => 'Plain text test', 'namespace' => 'NS_MEDIAWIKI',
			],
			'FILES' => [ 'format' => 'PlainText', 'sourcePattern' => 'ignored' ],
			'MANGLER' => [ 'prefix' => 'plain-text-', 'patterns' => [ 'message' ] ],
		];
	}

	private function collection( string $key, ?string $translation ): MessageCollection {
		$collection = MessageCollection::newFromDefinitions(
			new MessageDefinitions( [ $key => 'Source text' ], NS_MEDIAWIKI ), 'fi'
		);
		$message = new FatMessage( $key, 'Source text' );
		$message->setTranslation( $translation );
		$collection[$key] = $message;
		return $collection;
	}

	/** @dataProvider provideContents */
	public function testImportExport( string $input, string $content ): void {
		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( $this->configuration() );
		$format = $group->getFFS();
		$this->assertInstanceOf( PlainTextFormat::class, $format );
		$this->assertSame( [ '.txt' ], $format->getFileExtensions() );
		$this->assertSame( 'no', $format->supportsFuzzy() );
		$parsed = $format->readFromVariable( $input );
		$this->assertSame( [
			'AUTHORS' => [], 'MESSAGES' => [ 'plain-text-message' => $content ],
		], $parsed );
		$this->assertSame( $content, TextContent::normalizeLineEndings( $content ),
			'Imported content must survive MediaWiki normalization unchanged' );
		$output = $format->writeIntoVariable( $this->collection( 'plain-text-message', $content ) );
		$this->assertSame( $content . "\n", $output );
		$this->assertSame( $parsed, $format->readFromVariable( $output ) );
		$this->assertSame( $output,
			$format->writeIntoVariable( $this->collection( 'plain-text-message', $input ) ),
			'Export must normalize trailing whitespace and line endings too' );
	}

	public static function provideContents(): array {
		return [
			'single line' => [ 'Wiki-Say-It!', 'Wiki-Say-It!' ],
			'final newline' => [ "Wiki-Say-It!\n", 'Wiki-Say-It!' ],
			'paragraphs' => [ "First\nsecond\n\nLast\n", "First\nsecond\n\nLast" ],
			'markup' => [
				"<b>Title</b> & <a href=\"example\">link</a>\n", '<b>Title</b> & <a href="example">link</a>'
			],
			'trailing whitespace' => [ "  Text\t \n\n\n", '  Text' ],
			'trailing spaces without newline' => [ "Text \t", 'Text' ],
			'internal whitespace' => [ "  First \t\n\n Last  \n", "  First \t\n\n Last" ],
			'only whitespace' => [ " \t\n\n", '' ],
			'literal backslash' => [ "Text \\", "Text \\" ],
			'backslash before newline' => [ "Text \\\n", "Text \\" ],
			'CRLF' => [ "First\r\n\r\nLast\r\n", "First\n\nLast" ],
			'CR' => [ "First\rLast\r", "First\nLast" ],
			'empty' => [ '', '' ],
			'only terminator' => [ "\n", '' ],
			'unicode' => [ "Ääni 日本語\n", 'Ääni 日本語' ],
		];
	}

	public function testMissingTranslationAndFuzzyExport(): void {
		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( $this->configuration() );
		$format = $group->getFFS();
		$this->assertSame( '', $format->writeIntoVariable( $this->collection( 'plain-text-message', null ) ) );
		$this->assertSame( '', $format->writeIntoVariable( new MessageCollection( 'fi' ) ) );
		$this->assertSame( "Translation\n", $format->writeIntoVariable(
			$this->collection( 'plain-text-message', TRANSLATE_FUZZY . 'Translation' )
		) );
		$this->assertSame( '', $format->writeIntoVariable( $this->collection( 'unrelated', 'Other text' ) ) );
		$collection = $this->collection( 'plain-text-message', 'Translation' );
		unset( $collection['plain-text-message'] );
		$this->assertSame( '', $format->writeIntoVariable( $collection ), 'Excluded messages must not be exported' );
	}

	public function testSourceChangesAndFilenameRenamesKeepIdentity(): void {
		$root = $this->getNewTempDirectory();
		$config = $this->configuration();
		$config['FILES']['sourcePattern'] = "$root/%CODE%/title.txt";
		$config['FILES']['targetPattern'] = '%CODE%/title.txt';
		wfMkdirParents( "$root/en" );
		file_put_contents( "$root/en/title.txt", "Original\n" );
		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( $config );
		$this->assertSame( [ 'plain-text-message' => 'Original' ], $group->load( 'en' ) );
		file_put_contents( "$root/en/title.txt", "Updated\n" );
		$updated = $group->load( 'en' );
		$this->assertSame( [ 'plain-text-message' => 'Updated' ], $updated );
		rename( "$root/en/title.txt", "$root/en/renamed.txt" );
		$config['FILES']['sourcePattern'] = "$root/%CODE%/renamed.txt";
		$config['FILES']['targetPattern'] = '%CODE%/renamed.txt';
		/** @var FileBasedMessageGroup $renamed */
		$renamed = MessageGroupBase::factory( $config );
		$this->assertSame( $group->getId(), $renamed->getId() );
		$this->assertSame( $updated, $renamed->load( 'en' ) );
		$format = $renamed->getFFS();
		$format->setWritePath( $root );
		$format->write( $this->collection( 'plain-text-message', 'Translation' ) );
		$this->assertSame( "Translation\n", file_get_contents( "$root/fi/renamed.txt" ) );
		$this->assertFileDoesNotExist( "$root/fi/title.txt" );
	}

	public function testFastlaneMetadata(): void {
		$root = $this->getNewTempDirectory();
		$output = $this->getNewTempDirectory();
		$this->overrideConfigValue( 'TranslateGroupRoot', $root );
		$parser = new MessageGroupConfigurationParser();
		$configs = $parser->getHopefullyValidConfigurations(
			file_get_contents( __DIR__ . '/../data/PlainTextFormat-fastlane.yaml' ),
			function ( $index, $config, $error ) {
				$this->fail( $error );
			}
		);
		$this->assertCount( 4, $configs );
		$aggregate = $configs['wiki-say-it-metadata'];
		$this->assertSame( 'aggregate', $aggregate['BASIC']['type'] );
		$this->assertCount( 3, $aggregate['GROUPS'] );
		$contents = [
			'title.txt' => 'Wiki-Say-It!',
			'short_description.txt' => 'Listen to Wikipedia articles',
			'full_description.txt' => "<b>Wiki-Say-It!</b>\n\nListen to articles.\nSave them for later.",
		];
		$sourceDirectory = "$root/wiki-say-it/fastlane/metadata/android/en-US";
		wfMkdirParents( $sourceDirectory );
		foreach ( $aggregate['GROUPS'] as $id ) {
			/** @var FileBasedMessageGroup $group */
			$group = MessageGroupBase::factory( $configs[$id] );
			$filename = basename( $group->getSourceFilePath( 'en' ) );
			$content = $contents[$filename];
			file_put_contents( "$sourceDirectory/$filename", $content . "\n" );
			$key = $id . '-message';
			$this->assertSame( [ $key => $content ], $group->load( 'en' ) );
			$format = $group->getFFS();
			$format->setWritePath( $output );
			$translation = "Suomeksi: $content";
			$format->write( $this->collection( $key, $translation ) );
			$target = "$output/fastlane/metadata/android/fi-FI/$filename";
			$this->assertSame( $translation . "\n", file_get_contents( $target ) );
			wfMkdirParents( dirname( $group->getSourceFilePath( 'fi' ) ) );
			copy( $target, $group->getSourceFilePath( 'fi' ) );
			$this->assertSame( [ $key => $translation ], $group->load( 'fi' ) );
		}
	}
}
