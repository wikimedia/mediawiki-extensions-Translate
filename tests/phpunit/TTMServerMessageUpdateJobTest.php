<?php
/**
 * @file
 * @author David Causse
 * @license GPL-2.0-or-later
 */

/**
 * Mostly test mirroring and failure modes.
 */
class TTMServerMessageUpdateJobTest extends MediaWikiTestCase {
	/**
	 * @var WritableTTMServer[] used to link our mocks with TestableTTMServer built by the
	 * factory
	 */
	public static $mockups = [];

	public function setUp() {
		parent::setUp();
		self::$mockups = [];
		$this->setMwGlobals( [
			'wgTranslateTranslationServices' => [
				'primary' => [
					'class' => TestableTTMServer::class,
					// will be used as the key in static::$mockups to attach the
					// mock to the newly created TestableTTMServer instance
					'name' => 'primary',
					'mirrors' => [ 'secondary' ],
				],
				'secondary' => [
					'class' => TestableTTMServer::class,
					'name' => 'secondary',
				]
			],
			'wgTranslateTranslationDefaultService' => 'primary'
		] );
	}

	public function tearDown() {
		parent::tearDown();
		self::$mockups = [];
	}

	/**
	 * Normal mode, we ensure that update is called on primary and its mirror without any resent
	 * jobs
	 */
	public function testReplication() {
		$mock = $this->getMockBuilder( WritableTTMServer::class )
			->getMock();
		$mock->expects( $this->atLeastOnce() )
			->method( 'update' );
		static::$mockups['primary'] = $mock;
		$mock = $this->getMockBuilder( WritableTTMServer::class )
			->getMock();
		$mock->expects( $this->atLeastOnce() )
			->method( 'update' );
		static::$mockups['secondary'] = $mock;

		$job = new TestableTTMServerMessageUpdateJob(
			Title::makeTitle( NS_MAIN, 'Main Page' ),
			[ 'command' => 'refresh' ],
			$this->getMockBuilder( MessageHandle::class )
				->disableOriginalConstructor()
				->getMock()
		);
		$job->run();
		$this->assertEmpty( $job->getResentJobs() );
	}

	/**
	 * The mirror failed, we ensure that we resend a job
	 * with the appropriate params.
	 */
	public function testReplicationError() {
		$mock = $this->getMockBuilder( WritableTTMServer::class )
			->getMock();
		$mock->expects( $this->atLeastOnce() )
			->method( 'update' );
		static::$mockups['primary'] = $mock;
		$mock = $this->getMockBuilder( WritableTTMServer::class )
			->getMock();
		$mock->expects( $this->atLeastOnce() )
			->method( 'update' )
			->will( $this->throwException( new TTMServerException ) );
		static::$mockups['secondary'] = $mock;

		$job = new TestableTTMServerMessageUpdateJob(
			Title::makeTitle( NS_MAIN, 'Main Page' ),
			[ 'command' => 'refresh' ],
			$this->getMockBuilder( MessageHandle::class )
				->disableOriginalConstructor()
				->getMock()
		);
		$job->run();
		$this->assertEquals( 1, count( $job->getResentJobs() ) );
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
		$mock = $this->getMockBuilder( WritableTTMServer::class )
			->getMock();
		$mock->expects( $this->atLeastOnce() )
			->method( 'update' )
			->will( $this->throwException( new TTMServerException ) );
		static::$mockups['primary'] = $mock;
		$mock = $this->getMockBuilder( WritableTTMServer::class )
			->getMock();
		$mock->expects( $this->atLeastOnce() )
			->method( 'update' )
			->will( $this->throwException( new TTMServerException ) );
		static::$mockups['secondary'] = $mock;

		$job = new TestableTTMServerMessageUpdateJob(
			Title::makeTitle( NS_MAIN, 'Main Page' ),
			[ 'command' => 'refresh' ],
			$this->getMockBuilder( MessageHandle::class )
				->disableOriginalConstructor()
				->getMock()
		);
		$job->run();
		$this->assertEquals( 2, count( $job->getResentJobs() ) );
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
		$mock = $this->getMockBuilder( WritableTTMServer::class )
			->getMock();
		$mock->expects( $this->atLeastOnce() )
			->method( 'update' );
		static::$mockups['primary'] = $mock;
		$mock = $this->getMockBuilder( WritableTTMServer::class )
			->getMock();
		$mock->expects( $this->never() )
			->method( 'update' );
		static::$mockups['secondary'] = $mock;

		$job = new TestableTTMServerMessageUpdateJob(
			Title::makeTitle( NS_MAIN, 'Main Page' ),
			[
				'errorCount' => 1,
				'service' => 'primary',
				'command' => 'refresh'
			],
			$this->getMockBuilder( MessageHandle::class )
				->disableOriginalConstructor()
				->getMock()
		);
		$job->run();
		$this->assertEmpty( $job->getResentJobs() );
	}

	/**
	 * We simulate a job that failed multiple times and we fail again, we encure that we adandon
	 * the job by not resending it to queue
	 */
	public function testAbandonedJob() {
		$mock = $this->getMockBuilder( WritableTTMServer::class )
			->getMock();
		$mock->expects( $this->atLeastOnce() )
			->method( 'update' )
			->will( $this->throwException( new TTMServerException ) );
		static::$mockups['primary'] = $mock;
		$mock = $this->getMockBuilder( WritableTTMServer::class )
			->getMock();
		$mock->expects( $this->never() )
			->method( 'update' );
		static::$mockups['secondary'] = $mock;

		$job = new TestableTTMServerMessageUpdateJob(
			Title::makeTitle( NS_MAIN, 'Main Page' ),
			[
				'errorCount' => 4,
				'service' => 'primary',
				'command' => 'refresh'
			],
			$this->getMockBuilder( MessageHandle::class )
				->disableOriginalConstructor()
				->getMock()
		);
		$job->run();
		$this->assertEmpty( $job->getResentJobs() );
	}

	/**
	 * One service is frozen
	 */
	public function testOneServiceFrozen() {
		$mock = $this->getMockBuilder( WritableTTMServer::class )
			->getMock();
		$mock->expects( $this->atLeastOnce() )
			->method( 'update' );
		static::$mockups['primary'] = $mock;
		$mock = $this->getMockBuilder( WritableTTMServer::class )
			->getMock();
		$mock->expects( $this->never() )
			->method( 'update' );
		$mock->expects( $this->atLeastOnce() )
			->method( 'isFrozen' )
			->willReturn( true );
		static::$mockups['secondary'] = $mock;

		$now = time();
		$job = new TestableTTMServerMessageUpdateJob(
			Title::makeTitle( NS_MAIN, 'Main Page' ),
			[
				'command' => 'refresh',
				'createdAt' => $now
			],
			$this->getMockBuilder( MessageHandle::class )
				->disableOriginalConstructor()
				->getMock()
		);
		$job->run();
		$this->assertEquals( 1, count( $job->getResentJobs() ) );
		$expectedParams = [
			'errorCount' => 0,
			'retryCount' => 1,
			'createdAt' => $now,
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
	 * One is broken
	 * One is frozen
	 */
	public function testOneBrokenOneFrozen() {
		$mock = $this->getMockBuilder( WritableTTMServer::class )
			->getMock();
		$mock->expects( $this->atLeastOnce() )
			->method( 'update' )
			->will( $this->throwException( new TTMServerException ) );
		static::$mockups['primary'] = $mock;
		$mock = $this->getMockBuilder( WritableTTMServer::class )
			->getMock();
		$mock->expects( $this->never() )
			->method( 'update' );
		$mock->expects( $this->atLeastOnce() )
			->method( 'isFrozen' )
			->willReturn( true );
		static::$mockups['secondary'] = $mock;

		$now = time();
		$job = new TestableTTMServerMessageUpdateJob(
			Title::makeTitle( NS_MAIN, 'Main Page' ),
			[
				'command' => 'refresh',
				'createdAt' => $now
			],
			$this->getMockBuilder( MessageHandle::class )
				->disableOriginalConstructor()
				->getMock()
		);
		$job->run();
		$this->assertEquals( 2, count( $job->getResentJobs() ) );
		$expectedParams = [
			'errorCount' => 1,
			'retryCount' => 0,
			'createdAt' => $now,
			'service' => 'primary',
			'command' => 'refresh'
		];
		$actualParams = array_intersect_key(
			$job->getResentJobs()[0]->getParams(),
			$expectedParams
		);
		$this->assertEquals( $expectedParams, $actualParams );

		$expectedParams = [
			'errorCount' => 0,
			'retryCount' => 1,
			'createdAt' => $now,
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
	 * Old jobs are abandoned
	 */
	public function testAbandonedOldJob() {
		$mock = $this->getMockBuilder( WritableTTMServer::class )
			->getMock();
		$mock->expects( $this->never() )
			->method( 'update' );
		$mock->expects( $this->never() )
			->method( 'isFrozen' );
		static::$mockups['primary'] = $mock;
		$mock = $this->getMockBuilder( WritableTTMServer::class )
			->getMock();
		$mock->expects( $this->never() )
			->method( 'update' );
		$mock->expects( $this->atLeastOnce() )
			->method( 'isFrozen' )
			->willReturn( true );
		static::$mockups['secondary'] = $mock;

		$job = new TestableTTMServerMessageUpdateJob(
			Title::makeTitle( NS_MAIN, 'Main Page' ),
			[
				'command' => 'refresh',
				'retryCount' => 10,
				'service' => 'secondary',
				'createdAt' => time() - TTMServerMessageUpdateJob::DROP_DELAYED_JOBS_AFTER - 1,
			],
			$this->getMockBuilder( MessageHandle::class )
				->disableOriginalConstructor()
				->getMock()
		);
		$job->run();
		$this->assertEquals( 0, count( $job->getResentJobs() ) );
	}
}

/**
 * Test subclass to override methods that we are not able to mock
 * easily.
 * For the context of the test we can only test the 'refresh' command
 * because other ones would need to have a more complex context to prepare
 */
class TestableTTMServerMessageUpdateJob extends TTMServerMessageUpdateJob {
	private $resentJobs = [];
	private $handleMock;
	public function __construct( Title $title, $params, $handleMock ) {
		parent::__construct( $title, $params );
		$this->handleMock = $handleMock;
	}
	public function resend( TTMServerMessageUpdateJob $job ) {
		$this->resentJobs[] = $job;
	}

	protected function getHandle() {
		return $this->handleMock;
	}

	protected function getTranslation( MessageHandle $handle ) {
		return 'random text';
	}

	public function getResentJobs() {
		return $this->resentJobs;
	}
}

/**
 * This "testable" TTMServer implementation allows to:
 * - test TTMServer specific methods
 * - attach our mocks to the Test static context, this is needed because
 *   the factory always creates a new instance of the service
 */
class TestableTTMServer extends TTMServer implements WritableTTMServer {
	private $delegate;
	public function __construct( array $config ) {
		parent::__construct( $config );
		$this->delegate = TTMServerMessageUpdateJobTest::$mockups[$config['name']];
	}

	public function update( MessageHandle $handle, $targetText ) {
		$this->delegate->update( $handle, $targetText );
	}

	public function beginBootstrap() {
		$this->delegate->beginBootstrap();
	}

	public function beginBatch() {
		$this->delegate->beginBatch();
	}

	public function batchInsertDefinitions( array $batch ) {
		$this->delegate->batchInsertDefinitions( $batch );
	}

	public function batchInsertTranslations( array $batch ) {
		$this->delegate->batchInsertTranslations( $batch );
	}

	public function endBatch() {
		$this->delegate->endBatch();
	}

	public function endBootstrap() {
		$this->delegate->endBootstrap();
	}

	public function isFrozen() {
		return $this->delegate->isFrozen();
	}
}
