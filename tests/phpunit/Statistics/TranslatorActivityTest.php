<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use EmptyBagOStuff;
use HashBagOStuff;
use InvalidArgumentException;
use JobQueueGroup;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWikiIntegrationTestCase;
use Wikimedia\Timestamp\ConvertibleTimestamp;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Statistics\TranslatorActivity
 */
class TranslatorActivityTest extends MediaWikiIntegrationTestCase {
	public function testInvalidLanguage() {
		$cache = $this->createMock( EmptyBagOStuff::class );
		$query = $this->createMock( TranslatorActivityQuery::class );
		$jobQueue = $this->createMock( JobQueueGroup::class );
		$languageValidator = $this->createMock( LanguageNameUtils::class );
		$languageValidator->method( 'isKnownLanguageTag' )->willReturn( false );

		$service = new TranslatorActivity( $cache, $query, $jobQueue, $languageValidator );

		$this->expectException( InvalidArgumentException::class );
		$service->inLanguage( 'not-a-valid-language-code' );
	}

	public function testInLanguage() {
		$language = 'en';
		$translators = $this->getExampleData();
		$cacheKey = 'cache:en';
		$fakeTime1 = 1;
		$expected = [
			'users' => $translators,
			'asOfTime' => $fakeTime1,
		];

		$cache = $this->createMock( HashBagOStuff::class );
		$cache->method( 'makeKey' )->willReturn( $cacheKey );
		$cache->expects( $this->once() )->method( 'set' )->with( $cacheKey, $expected );
		$query = $this->createMock( TranslatorActivityQuery::class );
		$query
			->method( 'inLanguage' )
			->willReturn( $translators )
			->with( $language );
		$jobQueue = $this->createMock( JobQueueGroup::class );
		$jobQueue->expects( $this->never() )->method( 'push' );
		$languageValidator = $this->createMock( LanguageNameUtils::class );
		$languageValidator->method( 'isKnownLanguageTag' )->willReturn( true );

		$service = new TranslatorActivity( $cache, $query, $jobQueue, $languageValidator );

		ConvertibleTimestamp::setFakeTime( $fakeTime1 );
		$actual = $service->inLanguage( $language );

		$this->assertEquals( $expected, $actual, 'Correct value is returned' );
	}

	public function testInLanguageStale() {
		$language = 'en';
		$translators = $this->getExampleData();
		$fakeTime1 = 1;
		$fakeTime2 = $fakeTime1 + TranslatorActivity::CACHE_STALE;
		$expected = [
			'users' => $translators,
			'asOfTime' => $fakeTime1,
		];

		$cache = new HashBagOStuff();
		$query = $this->createMock( TranslatorActivityQuery::class );
		$query
			->method( 'inLanguage' )
			->willReturn( $translators )
			->with( $language );
		$jobQueue = $this->createMock( JobQueueGroup::class );
		$jobQueue->expects( $this->once() )->method( 'push' );
		$languageValidator = $this->createMock( LanguageNameUtils::class );
		$languageValidator->method( 'isKnownLanguageTag' )->willReturn( true );

		$service = new TranslatorActivity( $cache, $query, $jobQueue, $languageValidator );

		ConvertibleTimestamp::setFakeTime( $fakeTime1 );
		$cache->setMockTime( $fakeTime1 );
		$service->inLanguage( $language );

		ConvertibleTimestamp::setFakeTime( $fakeTime2 );
		$cache->setMockTime( $fakeTime2 );
		$actual = $service->inLanguage( $language );

		$this->assertEquals( $expected, $actual, 'Correct value is returned' );
	}

	public function testInLanguageExpired() {
		$language = 'en';
		$translators = $this->getExampleData();
		$fakeTime1 = 1;
		$fakeTime2 = $fakeTime1 + TranslatorActivity::CACHE_TIME;
		$expected = [
			'users' => $translators,
			'asOfTime' => $fakeTime2,
		];

		$cache = new HashBagOStuff();
		$query = $this->createMock( TranslatorActivityQuery::class );
		$query
			->method( 'inLanguage' )
			->willReturn( $translators )
			->with( $language );
		$jobQueue = $this->createMock( JobQueueGroup::class );
		$jobQueue->expects( $this->never() )->method( 'push' );
		$languageValidator = $this->createMock( LanguageNameUtils::class );
		$languageValidator->method( 'isKnownLanguageTag' )->willReturn( true );

		$service = new TranslatorActivity( $cache, $query, $jobQueue, $languageValidator );

		ConvertibleTimestamp::setFakeTime( $fakeTime1 );
		$cache->setMockTime( $fakeTime1 );
		$service->inLanguage( $language );

		ConvertibleTimestamp::setFakeTime( $fakeTime2 );
		$cache->setMockTime( $fakeTime2 );
		$actual = $service->inLanguage( $language );

		$this->assertEquals( $expected, $actual, 'Correct value is returned' );
	}

	private function getExampleData(): array {
		$translators = [
			[
				TranslatorActivityQuery::USER_NAME => 'Hunter',
				TranslatorActivityQuery::USER_TRANSLATIONS => 1234,
				TranslatorActivityQuery::USER_LAST_ACTIVITY => 10,
			],
			[
				TranslatorActivityQuery::USER_NAME => 'Farmer',
				TranslatorActivityQuery::USER_TRANSLATIONS => 2,
				TranslatorActivityQuery::USER_LAST_ACTIVITY => 20,
			],
		];

		return $translators;
	}
}
