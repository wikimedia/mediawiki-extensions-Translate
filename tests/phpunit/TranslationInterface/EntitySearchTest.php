<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

use Generator;
use HashBagOStuff;
use MediaWikiIntegrationTestCase;
use MessageGroup;
use MessageGroups;
use WANObjectCache;

/**
 * @covers \MediaWiki\Extension\Translate\TranslatorInterface\EntitySearch
 * @license GPL-2.0-or-later
 */
class EntitySearchTest extends MediaWikiIntegrationTestCase {
	/** @var EntitySearch */
	private $entitySearch;

	protected function setUp(): void {
		parent::setUp();

		$this->entitySearch = new EntitySearch(
			new WANObjectCache( [ 'cache' => new HashBagOStuff() ] ),
			$this->getServiceContainer()->getCollationFactory()->makeCollation( 'uca-default-u-kn' ),
			$this->getMessageGroupFactoryStub()
		);
	}

	public function getTestData(): array {
		$data = <<<EOF
Page
Translatable page
Pägë
Translatable pägë
Page 1
Page 10
Page 2
Page Page Page Page
page page page page
EOF;

		return explode( "\n", $data );
	}

	public function getMessageGroupFactoryStub(): MessageGroups {
		$data = $this->getTestData();
		$stubGroups = [];
		foreach ( $data as $dataItem ) {
			$stubGroup = $this->createStub( MessageGroup::class );
			$stubGroup->method( 'getLabel' )->willReturn( $dataItem );
			$stubGroup->method( 'getId' )->willReturn( $this->makeGroupId( $dataItem ) );
			$stubGroups[] = $stubGroup;
		}

		$stub = $this->createStub( MessageGroups::class );
		$stub->method( 'getGroups' )->willReturn( $stubGroups );
		return $stub;
	}

	private function makeGroupId( string $x ): string {
		return "page-$x";
	}

	/** @dataProvider provideTestSearchStaticMessageGroups */
	public function testSearchStaticMessageGroups( string $query, int $maxResults, array $expected ) {
		$actual = $this->entitySearch->searchStaticMessageGroups( $query, $maxResults );
		$this->assertEquals( $expected, $actual );
	}

	public function provideTestSearchStaticMessageGroups(): Generator {
		yield [
			'Page',
			10,
			[
				[
					'label' => 'Page',
					'group' => 'page-Page',
				],
				[
					'label' => 'Page 1',
					'group' => 'page-Page 1',
				],
				[
					'label' => 'Page 2',
					'group' => 'page-Page 2',
				],
				[
					'label' => 'Page 10',
					'group' => 'page-Page 10',
				],
				[
					'label' => 'page page page page',
					'group' => 'page-page page page page',
				],
				[
					'label' => 'Page Page Page Page',
					'group' => 'page-Page Page Page Page',
				],
				[
					'label' => 'Translatable page',
					'group' => 'page-Translatable page',
				],
			]
		];

		yield [
			'P',
			1,
			[

				[
					'label' => 'Page',
					'group' => 'page-Page',
				],
			]
		];

		yield [
			'Pägë',
			10,
			[
				[
					'label' => 'Pägë',
					'group' => 'page-Pägë',
				],
				[
					'label' => 'Translatable pägë',
					'group' => 'page-Translatable pägë',
				],
			]
		];

		yield [
			'Book',
			10,
			[]
		];
	}
}
