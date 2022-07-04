<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

use ApiTestCase;
use ApiUsageException;
use ContentHandler;
use HashBagOStuff;
use HashMessageIndex;
use InvalidArgumentException;
use MediaWiki\MediaWikiServices;
use MessageGroups;
use MessageIndex;
use MockWikiMessageGroup;
use Title;
use User;
use WANObjectCache;

/**
 * @group Database
 * @group medium
 */
class ReviewTranslationActionApiTest extends ApiTestCase {
	protected function setUp(): void {
		parent::setUp();

		$this->setMwGlobals( [
			'wgGroupPermissions' => [
				'sysop' => [
					'translate-messagereview' => true,
				],
				'*' => [
					'read' => true,
					'writeapi' => true
				]
			],
			'wgTranslateMessageNamespaces' => [ NS_MEDIAWIKI ],
		] );
		$this->setTemporaryHook( 'TranslatePostInitGroups', [ $this, 'getTestGroups' ] );

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => new HashBagOStuff() ] ) );
		$mg->recache();

		MessageIndex::setInstance( new HashMessageIndex() );
		MessageIndex::singleton()->rebuild();
	}

	public function getTestGroups( &$list ): bool {
		$messages = [
			'ugakey1' => 'value1',
			'ugakey2' => 'value2',
		];

		$list['testgroup'] = new MockWikiMessageGroup( 'testgroup', $messages );

		return false;
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

		$this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $title )->doUserEditContent(
			$content,
			$this->getUser( $editorName ),
			__METHOD__
		);

		$revRecord = MediaWikiServices::getInstance()
			->getRevisionLookup()
			->getRevisionByTitle( $title );

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

	public function provideTestGetReviewBlockers() {
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

	private static $testUsers = [];

	private function getUser( string $name ): User {
		if ( self::$testUsers === [] ) {
			self::$testUsers[ 'plainUser' ] = $this->getMutableTestUser()->getUser();
			self::$testUsers[ 'superUser1' ] = $this->getMutableTestUser( [ 'sysop', 'bureaucrat' ] )->getUser();
			self::$testUsers[ 'superUser2' ] = $this->getMutableTestUser( [ 'sysop', 'bureaucrat' ] )->getUser();
		}

		if ( isset( self::$testUsers[ $name ] ) ) {
			return self::$testUsers[ $name ];
		}

		throw new InvalidArgumentException( "Unknown user: $name" );
	}
}
