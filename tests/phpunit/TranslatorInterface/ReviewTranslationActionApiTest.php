<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

use InvalidArgumentException;
use MediaWiki\Api\ApiUsageException;
use MediaWiki\Content\ContentHandler;
use MediaWiki\Tests\Api\ApiTestCase;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MessageGroupTestTrait;
use MockWikiMessageGroup;

/**
 * @group Database
 * @group medium
 * @covers \MediaWiki\Extension\Translate\TranslatorInterface\ReviewTranslationActionApi
 */
class ReviewTranslationActionApiTest extends ApiTestCase {
	use MessageGroupTestTrait;

	/** @var User[] */
	private static $testUsers = [];

	public function addDBDataOnce() {
		self::$testUsers[ 'plainUser' ] = $this->getMutableTestUser()->getUser();
		self::$testUsers[ 'superUser1' ] = $this->getMutableTestUser( [ 'sysop' ] )->getUser();
		self::$testUsers[ 'superUser2' ] = $this->getMutableTestUser( [ 'sysop' ] )->getUser();
	}

	protected function setUp(): void {
		parent::setUp();
		$this->setGroupPermissions( [
			'sysop' => [
				'translate-messagereview' => true,
			],
			'user' => [
				'translate-messagereview' => false,
			],
			'*' => [
				'read' => true,
			]
		] );

		$this->setupGroupTestEnvironmentWithGroups( $this, $this->getTestGroups() );
	}

	public function getTestGroups(): array {
		$messages = [
			'ugakey1' => 'value1',
			'ugakey2' => 'value2',
		];

		$list['testgroup'] = new MockWikiMessageGroup( 'testgroup', $messages );

		return $list;
	}

	/** @dataProvider provideTestGetReviewBlockers */
	public function testGetReviewBlockers(
		string $exceptionMessage,
		string $reviewerName,
		string $editorName,
		string $titleString,
		string $content
	): void {
		$title = Title::makeTitle( NS_MEDIAWIKI, $titleString );
		$content = ContentHandler::makeContent( $content, $title );

		$editStatus = $this->editPage( $title, $content, __METHOD__, NS_MAIN, $this->getUser( $editorName ) );
		$this->assertStatusOK( $editStatus );

		$revRecord = $this->getServiceContainer()
			->getRevisionLookup()
			->getRevisionByTitle( $title );
		$this->assertNotNull( $revRecord );

		if ( $exceptionMessage ) {
			$this->expectException( ApiUsageException::class );
			$this->expectExceptionMessageMatches( '/' . $exceptionMessage . '/i' );
		}

		$result = $this->doApiRequestWithToken( [
			'action' => 'translationreview',
			'revision' => $revRecord->getId()
		], null, $this->getUser( $reviewerName ) );

		if ( !$exceptionMessage ) {
			$this->assertArrayHasKey( 'translationreview', $result[0] );
		}
	}

	public static function provideTestGetReviewBlockers() {
		yield [
			"don't have permission",
			'plainUser',
			'superUser1',
			'Ugakey1/fi',
			'trans1',
			'Unpriviledged user is not allowed to change state'
		];

		yield [
			'own translations',
			'superUser1',
			'superUser1',
			'Ugakey1/fi',
			'trans1',
			'Cannot approve own translation'
		];

		yield [
			'Cannot review fuzzy',
			'superUser1',
			'superUser2',
			'Ugakey2/fi',
			'!!FUZZY!!trans2',
			'Cannot approve fuzzy translation'
		];

		yield [
			'Unknown message',
			'superUser1',
			'superUser2',
			'Ugakey3/fi',
			'unknown message',
			'Cannot approve unknown translation'
		];

		yield [
			'',
			'superUser2',
			'superUser1',
			'Ugakey1/fi',
			'trans1',
			'Can approve non-fuzzy known non-own translation'
		];
	}

	private function getUser( string $name ): User {
		if ( isset( self::$testUsers[ $name ] ) ) {
			return self::$testUsers[ $name ];
		}

		throw new InvalidArgumentException( "Unknown user: $name" );
	}
}
