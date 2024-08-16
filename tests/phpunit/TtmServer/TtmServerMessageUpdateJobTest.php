<?php
declare( strict_types = 1 );
/**
 * @author David Causse
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extension\Translate\TtmServer;

use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;

/** @covers \MediaWiki\Extension\Translate\TtmServer\TtmServerMessageUpdateJob */
class TtmServerMessageUpdateJobTest extends MediaWikiIntegrationTestCase {
	/** @var WritableTtmServer[] used to link our mocks with TestableTTMServer built by the factory */
	public static array $mockups = [];

	protected function setUp(): void {
		parent::setUp();
		self::$mockups = [];
		$this->overrideConfigValue( 'TranslateTranslationServices', [
			'primary' => [
				'class' => TestableTTMServer::class,
				// will be used as the key in static::$mockups to attach the
				// mock to the newly created TestableTTMServer instance
				'name' => 'primary',
				'type' => 'ttmserver',
				'writable' => true
			],
			'secondary' => [
				'class' => TestableTTMServer::class,
				'name' => 'secondary',
				'type' => 'ttmserver',
				'writable' => true
			]
		] );
	}

	protected function tearDown(): void {
		parent::tearDown();
		self::$mockups = [];
	}

	/**
	 * Normal mode, we ensure that update is called on primary and its mirror without any resent
	 * jobs
	 */
	public function testReplication() {
		$mock = $this->createMock( WritableTtmServer::class );
		$mock->expects( $this->atLeastOnce() )
			->method( 'update' );
		static::$mockups['primary'] = $mock;
		$mock = $this->createMock( WritableTtmServer::class );
		$mock->expects( $this->atLeastOnce() )
			->method( 'update' );
		static::$mockups['secondary'] = $mock;

		$title = Title::makeTitle( NS_MAIN, 'Main Page' );
		$job = new TestableTtmServerMessageUpdateJob(
			$title, [ 'command' => 'refresh' ], $this->createMessageHandleMock( $title )
		);
		$job->run();
		$this->assertSame( [], $job->getResentJobs() );
	}

	/**
	 * The mirror failed, we ensure that we resend a job
	 * with the appropriate params.
	 */
	public function testReplicationError() {
		$mock = $this->createMock( WritableTtmServer::class );
		$mock->expects( $this->atLeastOnce() )
			->method( 'update' );
		static::$mockups['primary'] = $mock;
		$mock = $this->createMock( WritableTtmServer::class );
		$mock->expects( $this->atLeastOnce() )
			->method( 'update' )
			->willThrowException( new TtmServerException );
		static::$mockups['secondary'] = $mock;

		$title = Title::makeTitle( NS_MAIN, 'Main Page' );
		$job = new TestableTtmServerMessageUpdateJob(
			$title, [ 'command' => 'refresh' ], $this->createMessageHandleMock( $title )
		);
		$job->run();
		$this->assertCount( 1, $job->getResentJobs() );
		$expectedParams = [
			'errorCount' => 1,
			'service' => 'secondary',
			'command' => 'refresh'
		];
		$actualParams = array_intersect_key(
			$job->getResentJobs()[0]->getParams(),
			$expectedParams
		);
		$this->assertEquals( $expectedParams, $actualParams );
	}

	/**
	 * All services failed, we ensure that we resend 2 jobs for
	 * each services
	 */
	public function testAllServicesInError() {
		$mock = $this->createMock( WritableTtmServer::class );
		$mock->expects( $this->atLeastOnce() )
			->method( 'update' )
			->willThrowException( new TtmServerException );
		static::$mockups['primary'] = $mock;
		$mock = $this->createMock( WritableTtmServer::class );
		$mock->expects( $this->atLeastOnce() )
			->method( 'update' )
			->willThrowException( new TtmServerException );
		static::$mockups['secondary'] = $mock;

		$title = Title::makeTitle( NS_MAIN, 'Main Page' );
		$job = new TestableTtmServerMessageUpdateJob(
			$title, [ 'command' => 'refresh' ], $this->createMessageHandleMock( $title )
		);
		$job->run();
		$this->assertCount( 2, $job->getResentJobs() );
		$expectedParams = [
			'errorCount' => 1,
			'service' => 'primary',
			'command' => 'refresh'
		];
		$actualParams = array_intersect_key(
			$job->getResentJobs()[0]->getParams(),
			$expectedParams
		);
		$this->assertEquals( $expectedParams, $actualParams );

		$expectedParams = [
			'errorCount' => 1,
			'service' => 'secondary',
			'command' => 'refresh'
		];
		$actualParams = array_intersect_key(
			$job->getResentJobs()[1]->getParams(),
			$expectedParams
		);
		$this->assertEquals( $expectedParams, $actualParams );
	}

	/**
	 * We simulate a resent job after a failure, this job is directed to a specific service, we
	 * ensure that we do not replicate the write to its mirror
	 */
	public function testJobOnSingleService() {
		$mock = $this->createMock( WritableTtmServer::class );
		$mock->expects( $this->atLeastOnce() )
			->method( 'update' );
		static::$mockups['primary'] = $mock;
		$mock = $this->createMock( WritableTtmServer::class );
		$mock->expects( $this->never() )
			->method( 'update' );
		static::$mockups['secondary'] = $mock;

		$title = Title::makeTitle( NS_MAIN, 'Main Page' );
		$job = new TestableTtmServerMessageUpdateJob(
			$title,
			[
				'errorCount' => 1,
				'service' => 'primary',
				'command' => 'refresh'
			],
			$this->createMessageHandleMock( $title )
		);
		$job->run();
		$this->assertSame( [], $job->getResentJobs() );
	}

	/**
	 * We simulate a job that failed multiple times and we fail again, we encure that we adandon
	 * the job by not resending it to queue
	 */
	public function testAbandonedJob() {
		$mock = $this->createMock( WritableTtmServer::class );
		$mock->expects( $this->atLeastOnce() )
			->method( 'update' )
			->willThrowException( new TtmServerException );
		static::$mockups['primary'] = $mock;
		$mock = $this->createMock( WritableTtmServer::class );
		$mock->expects( $this->never() )
			->method( 'update' );
		static::$mockups['secondary'] = $mock;

		$title = Title::makeTitle( NS_MAIN, 'Main Page' );
		$job = new TestableTtmServerMessageUpdateJob(
			$title,
			[
				'errorCount' => 4,
				'service' => 'primary',
				'command' => 'refresh'
			],
			$this->createMessageHandleMock( $title )
		);
		$job->run();
		$this->assertSame( [], $job->getResentJobs() );
	}

	private function createMessageHandleMock( Title $title ) {
		$mock = $this->createMock( MessageHandle::class );
		$mock->method( 'getTitle' )->willReturn( $title );
		return $mock;
	}

}

/**
 * Test subclass to override methods that we are not able to mock
 * easily.
 * For the context of the test we can only test the 'refresh' command
 * because other ones would need to have a more complex context to prepare
 */
class TestableTtmServerMessageUpdateJob extends TtmServerMessageUpdateJob {
	private array $resentJobs = [];
	private MessageHandle $handleMock;

	public function __construct( Title $title, $params, MessageHandle $handleMock ) {
		parent::__construct( $title, $params );
		$this->handleMock = $handleMock;
	}

	protected function resend( TtmServerMessageUpdateJob $job ): void {
		$this->resentJobs[] = $job;
	}

	protected function getHandle(): MessageHandle {
		return $this->handleMock;
	}

	protected function getTranslation( MessageHandle $handle ): string {
		return 'random text';
	}

	public function getResentJobs() {
		return $this->resentJobs;
	}
}

/**
 * This "testable" TtmServer implementation allows to:
 * - test TtmServer specific methods
 * - attach our mocks to the Test static context, this is needed because
 *   the factory always creates a new instance of the service
 */
class TestableTtmServer extends TtmServer implements WritableTtmServer {
	private WritableTtmServer $delegate;

	public function __construct( array $config ) {
		parent::__construct( $config );
		$this->delegate = TTMServerMessageUpdateJobTest::$mockups[$config['name']];
	}

	public function update( MessageHandle $handle, ?string $targetText ): bool {
		$this->delegate->update( $handle, $targetText );
		return true;
	}

	public function beginBootstrap(): void {
		$this->delegate->beginBootstrap();
	}

	public function beginBatch(): void {
		$this->delegate->beginBatch();
	}

	public function batchInsertDefinitions( array $batch ): void {
		$this->delegate->batchInsertDefinitions( $batch );
	}

	public function batchInsertTranslations( array $batch ): void {
		$this->delegate->batchInsertTranslations( $batch );
	}

	public function endBatch(): void {
		$this->delegate->endBatch();
	}

	public function endBootstrap(): void {
		$this->delegate->endBootstrap();
	}

	public function setDoReIndex(): void {
		$this->delegate->setDoReIndex();
	}
}
