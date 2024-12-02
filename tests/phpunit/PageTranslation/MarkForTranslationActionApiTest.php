<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Api\ApiMain;
use MediaWiki\Api\ApiQueryTokens;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\Translate\MessageProcessing\MessageGroupMetadata;
use MediaWiki\Page\PageStoreRecord;
use MediaWiki\Request\FauxRequest;
use MediaWiki\Session\SessionManager;
use MediaWiki\Status\Status;
use MediaWiki\Tests\Unit\Permissions\MockAuthorityTrait;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWikiIntegrationTestCase;

/**
 * @author Tim Starling
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\PageTranslation\MarkForTranslationActionApi
 */
class MarkForTranslationActionApiTest extends MediaWikiIntegrationTestCase {
	use MockAuthorityTrait;

	public static function provideExecute() {
		return [
			'firstMark no-op' => [
				[
					'title' => 'Foo',
				],
				[],
				[],
				[],
				true,
				null,
				[],
				[
					'result' => 'Success',
					'firstmark' => true,
					'unitcount' => 42,
				]
			],
			'existing no-op' => [
				[
					'title' => 'Foo',
				],
				[],
				[],
				[],
				false,
				null,
				[
					'enableTransclusion' => false
				],
				[
					'result' => 'Success',
					'firstmark' => false,
					'unitcount' => 42,
				]
			],
			'translatetitle' => [
				[
					'title' => 'Foo',
					'translatetitle' => 'no',
				],
				[],
				[],
				[],
				false,
				null,
				[
					'enableTransclusion' => false,
					'translateTitle' => false
				],
				[
					'result' => 'Success',
					'firstmark' => false,
					'unitcount' => 42,
				]
			],
			'prioritylanguages' => [
				[
					'title' => 'Foo',
					'prioritylanguages' => 'de|fr',
					'forcepriority' => true,
					'priorityreason' => 'Reason',
				],
				[],
				[],
				[],
				false,
				null,
				[
					'enableTransclusion' => false,
					'priorityLanguages' => [ 'de', 'fr' ],
					'forcePriority' => true,
					'priorityReason' => 'Reason',
				],
				[
					'result' => 'Success',
					'firstmark' => false,
					'unitcount' => 42,
				]
			],
			'nofuzzyunits' => [
				[
					'title' => 'Foo',
					'nofuzzyunits' => '1',
				],
				[],
				[],
				[
					[ 'id' => '1' ]
				],
				false,
				null,
				[
					'enableTransclusion' => false,
					'noFuzzyUnits' => [ '1' ],
				],
				[
					'result' => 'Success',
					'firstmark' => false,
					'unitcount' => 42,
				]
			],
			'default nofuzzy unit' => [
				[
					'title' => 'Foo',
				],
				[],
				[],
				[
					[
						'id' => '1',
						'type' => 'changed',
						'text' => 'foo',
						'oldtext' => 'foo',
					]
				],
				false,
				null,
				[
					'enableTransclusion' => false,
					'noFuzzyUnits' => [ '1' ],
				],
				[
					'result' => 'Success',
					'firstmark' => false,
					'unitcount' => 42,
				]
			],
			'fuzzy override of default nofuzzy unit' => [
				[
					'title' => 'Foo',
					'fuzzyunits' => '1',
				],
				[],
				[],
				[
					[
						'id' => '1',
						'type' => 'changed',
						'text' => 'foo',
						'oldtext' => 'foo'
					]
				],
				false,
				null,
				[
					'enableTransclusion' => false,
				],
				[
					'result' => 'Success',
					'firstmark' => false,
					'unitcount' => 42
				]
			],
			'forcelatestsyntaxversion' => [
				[
					'title' => 'Foo',
					'forcelatestsyntaxversion' => '1',
				],
				[],
				[],
				[],
				false,
				null,
				[
					'enableTransclusion' => false,
					'forceLatestSyntaxVersion' => true,
				],
				[
					'result' => 'Success',
					'firstmark' => false,
					'unitcount' => 42
				]
			],
			'new transclusion disabled' => [
				[
					'title' => 'Foo',
					'transclusion' => 'no',
				],
				[],
				[],
				[],
				true,
				null,
				[
					'enableTransclusion' => false,
				],
				[
					'result' => 'Success',
					'firstmark' => true,
					'unitcount' => 42
				]
			],
			'existing transclusion enabled' => [
				[
					'title' => 'Foo',
					'transclusion' => 'yes',
				],
				[],
				[],
				[],
				false,
				null,
				[
					'enableTransclusion' => true
				],
				[
					'result' => 'Success',
					'firstmark' => false,
					'unitcount' => 42
				]
			],
		];
	}

	/** @dataProvider provideExecute */
	public function testExecute(
		array $params,
		array $meta,
		array $pageInfo,
		array $units,
		bool $firstMark,
		?string $unitValidationError,
		array $expectedSettings,
		array $expectedResult
	): void {
		$authority = $this->mockRegisteredUltimateAuthority();
		$user = $this->getServiceContainer()->getUserFactory()
			->newFromAuthority( $authority );

		if ( $unitValidationError ) {
			$unitValidationStatus = Status::newFatal( $unitValidationError );
		} else {
			$unitValidationStatus = Status::newGood();
		}

		$pageRecord = new PageStoreRecord(
			(object)[
				'page_id' => 1,
				'page_namespace' => 0,
				'page_title' => 'Foo',
				'page_is_redirect' => 0,
				'page_is_new' => 0,
				'page_latest' => 1,
				'page_touched' => '',
			],
			false
		);
		$title = $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->onlyMethods( [ 'toPageRecord' ] )
			->getMock();
		$title->method( 'toPageRecord' )
			->willReturn( $pageRecord );

		$page = $this->getMockBuilder( 'TranslatablePage' )
			->disableOriginalConstructor()
			->onlyMethods( [ 'hasPageDisplayTitle', 'getMessageGroupId', 'supportsTransclusion' ] )
			->getMock();
		$page->method( 'hasPageDisplayTitle' )
			->willReturn( $pageInfo['hasPageDisplayTitle'] ?? true );
		$page->method( 'getMessageGroupId' )
			->willReturn( 'foo' );
		$page->method( 'supportsTransclusion' )
			->willReturn( $pageInfo['supportsTransclusion'] ?? null );

		$unitObjects = [];
		foreach ( $units as $unit ) {
			$unitObjects[] = new TranslationUnit(
				$unit['text'] ?? '',
				$unit['id'],
				$unit['type'] ?? 'new',
				$unit['oldtext'] ?? ''
			);
		}

		$operation = new TranslatablePageMarkOperation(
			$page,
			new ParserOutput( '', [], [] ),
			$unitObjects,
			[],
			$firstMark,
			$unitValidationStatus
		);

		$settings = new TranslatablePageSettings(
			$expectedSettings['priorityLanguages'] ?? [],
			$expectedSettings['forcePriority'] ?? false,
			$expectedSettings['priorityReason'] ?? '',
			$expectedSettings['noFuzzyUnits'] ?? [],
			$expectedSettings['translateTitle'] ?? true,
			$expectedSettings['forceLatestSyntaxVersion'] ?? false,
			$expectedSettings['enableTransclusion'] ?? true
		);

		$sessionObj = SessionManager::singleton()->getEmptySession();
		$params['token'] = ApiQueryTokens::getToken(
			$user,
			$sessionObj,
			ApiQueryTokens::getTokenTypeSalts()['csrf']
		)->toString();

		$context = new RequestContext;
		$context->setRequest( new FauxRequest( $params ) );
		$context->setAuthority( $authority );

		$apiMain = new ApiMain( $context );

		$marker = $this->getMockBuilder( TranslatablePageMarker::class )
			->disableOriginalConstructor()
			->onlyMethods( [ 'getMarkOperation', 'markForTranslation' ] )
			->getMock();
		$marker->expects( $this->once() )
			->method( 'getMarkOperation' )
			->willReturn( $operation );
		$marker->expects( $this->once() )
			->method( 'markForTranslation' )
			->with(
				$this->identicalTo( $operation ),
				$settings,
				RequestContext::getMain(),
				$this->isInstanceOf( User::class )
			)
			->willReturn( 42 );

		$messageGroupMetadata = $this->getMockBuilder( MessageGroupMetadata::class )
			->disableOriginalConstructor()
			->onlyMethods( [ 'get' ] )
			->getMock();
		$messageGroupMetadata
			->method( 'get' )
			->willReturnCallback( static function ( $group, $key ) use ( $meta ) {
				return $meta[$key] ?? false;
			} );
		$this->setService( 'Translate:MessageGroupMetadata', $messageGroupMetadata );

		$module = new class(
			$apiMain,
			'markfortranslation',
			$marker,
			$messageGroupMetadata,
			$title
		) extends MarkForTranslationActionApi {
			private $title;

			public function __construct(
				ApiMain $mainModule,
						$moduleName,
				TranslatablePageMarker $translatablePageMarker,
				MessageGroupMetadata $messageGroupMetadata,
				Title $title
			) {
				parent::__construct( $mainModule, $moduleName, $translatablePageMarker, $messageGroupMetadata );
				$this->title = $title;
			}

			public function getTitleFromTitleOrPageId( $params ) {
				return $this->title;
			}
		};

		$module->execute();
		$result = $module->getResult()->getResultData();

		$this->assertSame(
			$expectedResult,
			$result['markfortranslation'] ?? $result['error']
		);
	}
}
