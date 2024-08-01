<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use JobQueueGroup;
use MediaWiki\Extension\Translate\MessageLoading\MessageIndex;
use MediaWiki\Extension\Translate\MessageProcessing\MessageGroupMetadata;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePageParser;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePageStatus;
use MediaWiki\Title\Title;
use MediaWikiUnitTestCase;
use Wikimedia\Rdbms\IConnectionProvider;

/**
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatablePageStore
 */
class TranslatablePageStoreTest extends MediaWikiUnitTestCase {
	/** @dataProvider provideUpdateStatus */
	public function testUpdateStatus(
		?int $readyRevisionId,
		?int $markRevisionId,
		int $latestRevisionId,
		?int $expectedStatus
	): void {
		$shouldSetStatusBeCalled = (bool)$expectedStatus;
		$title = $this->getTitleStub( $latestRevisionId );
		$tpPageStore = new TranslatablePageStore(
			$this->createStub( MessageIndex::class ),
			$this->createStub( JobQueueGroup::class ),
			$this->getRevTagStoreStub( $readyRevisionId, $markRevisionId ),
			$this->createStub( IConnectionProvider::class ),
			$this->getTranslatableBundleStatusStoreMock( $shouldSetStatusBeCalled, $title, $expectedStatus ),
			$this->createStub( TranslatablePageParser::class ),
			$this->createStub( MessageGroupMetadata::class )
		);

		$status = $tpPageStore->updateStatus( $title );

		if ( $expectedStatus ) {
			$this->assertEquals( $expectedStatus, $status->getId() );
		} else {
			$this->assertNull( $status );
		}
	}

	public static function provideUpdateStatus() {
		$readyRevisionId = 1;
		$markRevisionId = null;
		$latestRevisionId = 1;
		yield 'Proposed pages' => [
			$readyRevisionId,
			$markRevisionId,
			$latestRevisionId,
			TranslatablePageStatus::PROPOSED
		];

		$readyRevisionId = $markRevisionId = $latestRevisionId = 1;
		yield 'Active pages' => [
			$readyRevisionId,
			$markRevisionId,
			$latestRevisionId,
			TranslatablePageStatus::ACTIVE
		];

		$markRevisionId = 1;
		$readyRevisionId = $latestRevisionId = 2;
		yield 'Outdated pages' => [
			$readyRevisionId,
			$markRevisionId,
			$latestRevisionId,
			TranslatablePageStatus::OUTDATED
		];

		$readyRevisionId = $markRevisionId = 1;
		$latestRevisionId = 2;
		yield 'Broken pages' => [
			$readyRevisionId,
			$markRevisionId,
			$latestRevisionId,
			TranslatablePageStatus::BROKEN
		];

		$readyRevisionId = $markRevisionId = null;
		$latestRevisionId = 1;
		yield 'Not a translatable page' => [
			$readyRevisionId,
			$markRevisionId,
			$latestRevisionId,
			null
		];
	}

	private function getTranslatableBundleStatusStoreMock(
		bool $shouldBeCalled = false,
		?Title $title = null,
		?int $status = null
	) {
		$mock = $this->createMock( TranslatableBundleStatusStore::class );
		if ( $shouldBeCalled ) {
			$mock->expects( $this->once() )
				->method( 'setStatus' )
				->with( $title, new TranslatablePageStatus( $status ) );
		}
		return $mock;
	}

	private function getRevTagStoreStub( ?int $readyRevisionId, ?int $markRevisionId ) {
		$stub = $this->createStub( RevTagStore::class );
		$returnValue = [];
		if ( $readyRevisionId ) {
			$returnValue[RevTagStore::TP_READY_TAG] = $readyRevisionId;
		}

		if ( $markRevisionId ) {
			$returnValue[RevTagStore::TP_MARK_TAG] = $markRevisionId;
		}
		$stub->method( 'getLatestRevisionsForTags' )
			->willReturn( $returnValue );
		return $stub;
	}

	private function getTitleStub( int $latestRevisionId ) {
		$stub = $this->createStub( Title::class );
		$stub->method( 'getLatestRevID' )
			->willReturn( $latestRevisionId );
		$stub->method( 'exists' )
			->willReturn( true );
		$stub->method( 'getId' )
			->willReturn( 1 );
		return $stub;
	}
}
