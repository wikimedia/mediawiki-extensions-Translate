<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupConfiguration;

use MediaWikiIntegrationTestCase;

/**
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\MessageGroupConfiguration\MessageGroupConfigurationParser
 */
class MessageGroupConfigurationParserTest extends MediaWikiIntegrationTestCase {
	private MessageGroupConfigurationParser $parser;
	private static string $fixtureDir;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		self::$fixtureDir = __DIR__ . '/../data/MessageGroupConfigurationParser';
	}

	protected function setUp(): void {
		parent::setUp();
		$this->parser = new MessageGroupConfigurationParser();
	}

	private function loadFixture( string $filename ): array {
		$yaml = file_get_contents( self::$fixtureDir . '/' . $filename );
		return $this->parser->getHopefullyValidConfigurations( $yaml );
	}

	private function validateYaml( string $yaml ): void {
		[ $config ] = $this->parser->parseDocuments(
			$this->parser->getDocumentsFromYaml( $yaml )
		);
		$this->parser->validate( $config );
	}

	// --- Test case 1: type:file with valid FILES section ---

	public function testTypeFileWithFilesSection(): void {
		$groups = $this->loadFixture( 'type-file.yaml' );
		$this->assertArrayHasKey( 'type-file-group', $groups );
		$this->assertSame( 'file', $groups['type-file-group']['BASIC']['type'] );
	}

	// --- Test case 2: type:aggregate ---

	public function testTypeAggregate(): void {
		$groups = $this->loadFixture( 'type-aggregate.yaml' );
		$this->assertArrayHasKey( 'type-aggregate-group', $groups );
		$this->assertSame( 'aggregate', $groups['type-aggregate-group']['BASIC']['type'] );
	}

	// --- Test case 3: unknown type ID fails ---

	public function testUnknownTypeIdFails(): void {
		$errors = [];
		$yaml = "BASIC:\n  id: bad\n  namespace: 8\n  type: no-such-type\n";
		$this->parser->getHopefullyValidConfigurations(
			$yaml,
			static function ( $i, $c, $e ) use ( &$errors ) {
				$errors[] = $e;
			}
		);
		$this->assertNotEmpty( $errors );
	}

	// --- Test case 4: both type and class in same group fails ---

	public function testBothTypAndClassFails(): void {
		$this->expectException( InvalidGroupConfigurationException::class );
		$this->validateYaml(
			"BASIC:\n  id: bad\n  namespace: 8\n  type: file\n  class: FileBasedMessageGroup\n"
		);
	}

	// --- Test case 5: neither selector fails ---

	public function testNeitherSelectorFails(): void {
		$this->expectException( InvalidGroupConfigurationException::class );
		$this->validateYaml( "BASIC:\n  id: bad\n  namespace: 8\n" );
	}

	// --- Test case 6: template with class, concrete overrides with type ---

	public function testTemplateClassConcreteType(): void {
		$groups = $this->loadFixture( 'template-class-concrete-type.yaml' );
		$this->assertArrayHasKey( 'concrete-with-type', $groups );
		$basic = $groups['concrete-with-type']['BASIC'];
		$this->assertArrayHasKey( 'type', $basic );
		$this->assertArrayNotHasKey( 'class', $basic );
	}

	// --- Test case 7: template with type, concrete overrides with class ---

	public function testTemplateTypeConcreteClass(): void {
		$groups = $this->loadFixture( 'template-type-concrete-class.yaml' );
		$this->assertArrayHasKey( 'concrete-with-class', $groups );
		$basic = $groups['concrete-with-class']['BASIC'];
		$this->assertArrayHasKey( 'class', $basic );
		$this->assertArrayNotHasKey( 'type', $basic );
	}

	// --- Test case 8: group inherits selector from template without overriding ---

	public function testGroupInheritsSelector(): void {
		$groups = $this->loadFixture( 'template-inherited-selector.yaml' );
		$this->assertArrayHasKey( 'inherits-selector', $groups );
		$basic = $groups['inherits-selector']['BASIC'];
		$this->assertArrayHasKey( 'type', $basic );
		$this->assertSame( 'file', $basic['type'] );
	}

	// --- Test case 9: aggregate with type:aggregate and template containing FILES ---

	public function testAggregateWithTypeStripsFiles(): void {
		$groups = $this->loadFixture( 'template-aggregate-strips-files.yaml' );
		$this->assertArrayHasKey( 'aggregate-with-template-files', $groups );
		$this->assertArrayNotHasKey( 'FILES', $groups['aggregate-with-template-files'] );
	}

	// --- Test case 10: existing built-in class: configuration continues to validate ---

	public function testExistingClassSelectorValidates(): void {
		$groups = $this->loadFixture( 'class-selector-builtin.yaml' );
		$this->assertArrayHasKey( 'legacy', $groups );
	}

	// --- Test case 11: custom class: configuration continues to validate ---

	public function testCustomClassSelectorValidates(): void {
		$groups = $this->loadFixture( 'class-selector-custom.yaml' );
		$this->assertArrayHasKey( 'custom', $groups );
	}

	// --- Test case 12: custom class extra schema continues to work ---

	public function testCustomClassExtraSchemaWorks(): void {
		$groups = $this->loadFixture( 'type-file-extra-schema.yaml' );
		$this->assertArrayHasKey( 'extra-schema', $groups, 'Extra schema from type: should be applied' );
	}
}
