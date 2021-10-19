<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

use Generator;
use HashBagOStuff;
use HashMessageIndex;
use MediaWikiIntegrationTestCase;
use MessageGroup;
use MessageGroups;
use MessageIndex;
use WANObjectCache;

/**
 * @covers \MediaWiki\Extension\Translate\TranslatorInterface\EntitySearch
 * @license GPL-2.0-or-later
 */
class EntitySearchTest extends MediaWikiIntegrationTestCase {
	public function getMessageGroupFactoryStub(): MessageGroups {
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
		$data = explode( "\n", $data );
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
		$mediaWikiServices = $this->getServiceContainer();
		$entitySearch = new EntitySearch(
			new WANObjectCache( [ 'cache' => new HashBagOStuff() ] ),
			$mediaWikiServices->getCollationFactory()->makeCollation( 'uca-default-u-kn' ),
			$this->getMessageGroupFactoryStub(),
			$mediaWikiServices->getNamespaceInfo(),
			new HashMessageIndex(),
			$mediaWikiServices->getTitleFormatter(),
			$mediaWikiServices->getTitleParser()
		);

		$actual = $entitySearch->searchStaticMessageGroups( $query, $maxResults );
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

	public function getMessageIndexStub(): MessageIndex {
		$data = <<<EOF
8:title
8:page title
8:CAPITAL TITLE
8:big_bunny
8:prefix
9:prefix-1
9:prefix-2
9:prefix-3
EOF;
		$data = explode( "\n", $data );
		$stub = $this->createStub( MessageIndex::class );
		$stub->method( 'getKeys' )->willReturn( $data );
		return $stub;
	}

	/** @dataProvider provideTestSearchMessages */
	public function testSearchMessages( string $query, int $maxResults, array $expected ) {
		$mediaWikiServices = $this->getServiceContainer();
		$entitySearch = new EntitySearch(
			new WANObjectCache( [ 'cache' => new HashBagOStuff() ] ),
			$mediaWikiServices->getCollationFactory()->makeCollation( 'uca-default-u-kn' ),
			$this->createStub( MessageGroups::class ),
			$mediaWikiServices->getNamespaceInfo(),
			$this->getMessageIndexStub(),
			$mediaWikiServices->getTitleFormatter(),
			$mediaWikiServices->getTitleParser()
		);

		$actual = $entitySearch->searchMessages( $query, $maxResults );
		$this->assertEquals( $expected, $actual );
	}

	public function provideTestSearchMessages(): Generator {
		yield 'prefix and infix case-insensitive matching at word boundaries' => [
			'title',
			10,
			[
				[
					'pattern' => 'MediaWiki:CAPITAL TITLE',
					'count' => 1,
				],
				[
					'pattern' => 'MediaWiki:Page title',
					'count' => 1,
				],
				[
					'pattern' => 'MediaWiki:Title',
					'count' => 1,
				],
			]
		];

		yield 'matching at underscore boundaries' => [
			'bunny',
			1,
			[

				[
					'pattern' => 'MediaWiki:Big bunny',
					'count' => 1,
				],
			]
		];

		yield 'prefix collapsing' => [
			'prefix',
			2,
			[
				[
					'pattern' => 'MediaWiki talk:Prefix*',
					'count' => 3,
				],
				[
					'pattern' => 'MediaWiki:Prefix',
					'count' => 1,
				],
			]
		];

		yield [
			'No match',
			10,
			[]
		];
	}
}
