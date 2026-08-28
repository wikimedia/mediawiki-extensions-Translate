<?php

namespace MediaWiki\Extension\Translate\Synchronization;

use MediaWiki\Context\RequestContext;
use MediaWiki\Page\PageReference;
use MediaWiki\Page\PageReferenceValue;
use MediaWiki\Request\FauxRequest;
use MediaWiki\Session\Token;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;
use MessageGroupTestTrait;
use MockWikiMessageGroup;

/**
 * @covers \MediaWiki\Extension\Translate\Synchronization\MessageWebImporter
 * @group Database
 */
class MessageWebImporterTest extends MediaWikiIntegrationTestCase {
	use MessageGroupTestTrait;

	private const PAGE_NAMESPACE = NS_MEDIAWIKI;
	private const PAGE_DBKEY = __CLASS__ . '_translated';
	private const GROUP = 'test-group';

	protected function setUp(): void {
		parent::setUp();
		$this->setupGroupTestEnvironmentWithGroups( $this, $this->getTestGroups() );
		$this->setGroupPermissions( 'translationadmin', 'translate-manage', true );
	}

	private function getImporter( string $langCode, array $postParams ): MessageWebImporter {
		$user = $this->getTestUser( 'translationadmin' )->getUser();
		$fauxRequest = new FauxRequest(
			$postParams + [ 'token' => ( new Token( 'verysecrettoken', '' ) )->toString() ],
			true,
			[ 'wsTokenSecrets' => [ 'default' => 'verysecrettoken' ] ],
		);
		$fauxRequest->getSession()->setUser( $user );
		$context = RequestContext::getMain();
		$context->setUser( $user );
		$context->setRequest( $fauxRequest );
		return new MessageWebImporter(
			Title::makeTitle( self::PAGE_NAMESPACE, self::PAGE_DBKEY ),
			$context,
			self::GROUP,
			$langCode,
		);
	}

	public function getTestGroups() {
		// MockWikiMessageGroup implies namespace = NS_MEDIAWIKI
		$list[self::GROUP] = new MockWikiMessageGroup( self::GROUP, [
			self::PAGE_DBKEY => 'bunny',
		] );
		return $list;
	}

	/**
	 * @dataProvider provideTestExecute
	 *
	 * @param string $langCode
	 * @param array<string,string> $postParams
	 * @param array<string,string> $messages
	 * @param bool $expectedResult
	 * @param array{en:string,fi:string} $expectedContents Language code to content serialization map
	 */
	public function testExecute(
		string $langCode,
		array $postParams,
		array $messages,
		bool $expectedResult,
		array $expectedContents
	): void {
		$this->assertStatusGood(
			$this->editPage( $this->getTranslationPage( 'en' ), 'English Original' ),
			'Sanity: Must create English original translation'
		);
		$this->assertStatusGood(
			$this->editPage( $this->getTranslationPage( 'fi' ), 'Finnish Original' ),
			'Sanity: Must create Finnish original translation'
		);

		$importer = $this->getImporter( $langCode, $postParams );
		$actualResult = $importer->execute( $messages );
		$this->assertSame( $expectedResult, $actualResult );

		foreach ( $expectedContents as $lang => $expectedContent ) {
			$actualContent = $this->getServiceContainer()->getWikiPageFactory()
				->newFromTitle( $this->getTranslationPage( $lang ) )
				->getContent()
				->serialize();
			$this->assertSame( $expectedContent, $actualContent );
		}
	}

	private function getTranslationPage( string $langCode ): PageReference {
		return PageReferenceValue::localReference(
			self::PAGE_NAMESPACE,
			self::PAGE_DBKEY . "/$langCode"
		);
	}

	public static function provideTestExecute(): iterable {
		// Importing English (original)
		yield 'English, no change' => [
			'langCode' => 'en',
			'postParams' => [
				'process' => '1',
			],
			'messages' => [
				self::PAGE_DBKEY => 'English Original',
			],
			'expectedResult' => true,
			'expectedContents' => [
				'en' => 'English Original',
				'fi' => 'Finnish Original',
			],
		];
		yield 'English, changed but no decision' => [
			'langCode' => 'en',
			'postParams' => [
				'process' => '1',
			],
			'messages' => [
				self::PAGE_DBKEY => 'English Changed',
			],
			'expectedResult' => false,
			'expectedContents' => [
				'en' => 'English Original',
				'fi' => 'Finnish Original',
			],
		];
		yield 'English, import without fuzzying' => [
			'langCode' => 'en',
			'postParams' => [
				'process' => '1',
				'action-changed-' . self::PAGE_DBKEY => 'import',
			],
			'messages' => [
				self::PAGE_DBKEY => 'English Changed',
			],
			'expectedResult' => true,
			'expectedContents' => [
				'en' => 'English Changed',
				'fi' => 'Finnish Original',
			],
		];
		yield 'English, import and fuzzy translations' => [
			'langCode' => 'en',
			'postParams' => [
				'process' => '1',
				'action-changed-' . self::PAGE_DBKEY => 'fuzzy',
			],
			'messages' => [
				self::PAGE_DBKEY => 'English Changed',
			],
			'expectedResult' => true,
			'expectedContents' => [
				'en' => 'English Changed',
				'fi' => TRANSLATE_FUZZY . 'Finnish Original',
			],
		];
		yield 'English, ignore' => [
			'langCode' => 'en',
			'postParams' => [
				'process' => '1',
				'action-changed-' . self::PAGE_DBKEY => 'ignore',
			],
			'messages' => [
				self::PAGE_DBKEY => 'English Changed',
			],
			'expectedResult' => true,
			'expectedContents' => [
				'en' => 'English Original',
				'fi' => 'Finnish Original',
			],
		];

		// Importing Finnish (translation)
		yield 'Finnish, no change' => [
			'langCode' => 'fi',
			'postParams' => [
				'process' => '1',
			],
			'messages' => [
				self::PAGE_DBKEY => 'Finnish Original',
			],
			'expectedResult' => true,
			'expectedContents' => [
				'en' => 'English Original',
				'fi' => 'Finnish Original',
			],
		];
		yield 'Finnish, changed but no decision' => [
			'langCode' => 'fi',
			'postParams' => [
				'process' => '1',
			],
			'messages' => [
				self::PAGE_DBKEY => 'Finnish Changed',
			],
			'expectedResult' => false,
			'expectedContents' => [
				'en' => 'English Original',
				'fi' => 'Finnish Original',
			],
		];
		yield 'Finnish, import without fuzzying' => [
			'langCode' => 'fi',
			'postParams' => [
				'process' => '1',
				'action-changed-' . self::PAGE_DBKEY => 'import',
			],
			'messages' => [
				self::PAGE_DBKEY => 'Finnish Changed',
			],
			'expectedResult' => true,
			'expectedContents' => [
				'en' => 'English Original',
				'fi' => 'Finnish Changed',
			],
		];
		yield 'Finnish, import and fuzzy' => [
			'langCode' => 'fi',
			'postParams' => [
				'process' => '1',
				'action-changed-' . self::PAGE_DBKEY => 'conflict',
			],
			'messages' => [
				self::PAGE_DBKEY => 'Finnish Changed',
			],
			'expectedResult' => true,
			'expectedContents' => [
				'en' => 'English Original',
				'fi' => TRANSLATE_FUZZY . 'Finnish Changed',
			],
		];
		yield 'Finnish, ignore' => [
			'langCode' => 'fi',
			'postParams' => [
				'process' => '1',
				'action-changed-' . self::PAGE_DBKEY => 'ignore',
			],
			'messages' => [
				self::PAGE_DBKEY => 'Finnish Changed',
			],
			'expectedResult' => true,
			'expectedContents' => [
				'en' => 'English Original',
				'fi' => 'Finnish Original',
			],
		];
	}
}
