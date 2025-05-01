<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\Translate\MessageProcessing\MessageGroupMetadata;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;
use MessageGroupTestConfig;
use MessageGroupTestTrait;
use MockWikiMessageGroup;
use stdClass;
use Wikimedia\Rdbms\FakeResultWrapper;

/**
 * @group Database
 * @covers \MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupSubscription
 */
class MessageGroupSubscriptionTest extends MediaWikiIntegrationTestCase {
	use MessageGroupTestTrait;

	private MessageGroupSubscription $subscription;
	private MockEventCreator $mockEventCreator;
	private MessageGroupSubscriptionStore $subscriptionStoreMock;

	protected function setUp(): void {
		parent::setUp();
		$this->setupTestData();
		$this->overrideConfigValues( [
			'TranslateEnableMessageGroupSubscription' => true,
			'JobClasses' => [
				'MessageGroupSubscriptionNotificationJob' => MessageGroupSubscriptionNotificationJob::class
			]
		] );

		$config = new MessageGroupTestConfig();
		$config->groups = $this->getTestGroups();
		$this->setupGroupTestEnvironmentWithConfig( $this, $config );
	}

	/** @dataProvider provideTestSendNotifications */
	public function testSendNotifications( array $info, array $expectedValues, array $expectedGroupIds ): void {
		$groupCount = $this->exactly( count( $expectedGroupIds ) );

		$consecutiveValues = [];
		foreach ( $expectedValues as $value ) {
			$consecutiveValues[] = [ $this->equalTo( $value ) ];
		}

		$this->mockEventCreator
			->expects( $groupCount )
			->method( 'create' )
			->withConsecutive( ...$consecutiveValues );
		$this->subscriptionStoreMock
			->expects( $this->once() )
			->method( 'getSubscriptions' )
			->with( $this->callback( static function ( $actualGroupIds ) use ( $expectedGroupIds ) {
				return count( $actualGroupIds ) === count( $expectedGroupIds ) &&
					empty( array_diff( $actualGroupIds, $expectedGroupIds ) );
			} ) )
			->willReturn( $this->getFakeSubscribers( $expectedGroupIds ) );
		$this->subscription->sendNotifications( $info );
	}

	public static function provideTestSendNotifications() {
		yield 'notification for an aggregate group and another subgroup' => [
			'changes to process' => [
				'agg-group-id' => [
					MessageGroupSubscription::STATE_ADDED => [ 'msg1' ],
					MessageGroupSubscription::STATE_UPDATED => [ 'msg2' ]
				],
				'agg-group-id-tp-1' => [
					MessageGroupSubscription::STATE_ADDED => [ 'tp-msg1' ],
					MessageGroupSubscription::STATE_UPDATED => [ 'tp-msg2' ]
				]
			],
			'arguments for Event::create' => [
				[
					'type' => 'translate-mgs-message-added',
					'extra' => [
						'groupId' => 'agg-group-id',
						'groupLabel' => 'aggregate group',
						'changes' => [
							MessageGroupSubscription::STATE_ADDED => [ 'msg1', 'tp-msg1' ],
							MessageGroupSubscription::STATE_UPDATED => [ 'msg2', 'tp-msg2' ]
						],
					]
				],
				[
					'type' => 'translate-mgs-message-added',
					'extra' => [
						'groupId' => 'agg-group-id-tp-1',
						'groupLabel' => 'none',
						'changes' => [
							MessageGroupSubscription::STATE_ADDED => [ 'tp-msg1' ],
							MessageGroupSubscription::STATE_UPDATED => [ 'tp-msg2' ]
						],
					]
				],
				[
					'type' => 'translate-mgs-message-added',
					'extra'	=> [
						'groupId' => 'parent-agg-group-id',
						'groupLabel' => 'parent aggregate group',
						'changes' => [
							MessageGroupSubscription::STATE_ADDED => [ 'msg1', 'tp-msg1' ],
							MessageGroupSubscription::STATE_UPDATED => [ 'msg2', 'tp-msg2' ]
						],
						'sourceGroupIds' => [ 'agg-group-id', 'agg-group-id-tp-1' ]
					]
				]
			],
			'input for getSubscriber method' => [ 'agg-group-id', 'agg-group-id-tp-1', 'parent-agg-group-id' ],
		];

		yield 'notification for an aggregate subgroup and a normal group' => [
			'changes to process' => [
				'agg-group-id-tp-1' => [
					MessageGroupSubscription::STATE_ADDED => [ 'tp-msg1' ],
					MessageGroupSubscription::STATE_UPDATED => [ 'tp-msg2' ]
				],
				'bar' => [
					MessageGroupSubscription::STATE_ADDED => [ 'bar-msg1' ],
					MessageGroupSubscription::STATE_UPDATED => [ 'bar-msg2' ]
				]
			],
			'arguments for Event::create' => [
				[
					'type' => 'translate-mgs-message-added',
					'extra' => [
						'groupId' => 'agg-group-id-tp-1',
						'groupLabel' => 'none',
						'changes' => [
							MessageGroupSubscription::STATE_ADDED => [ 'tp-msg1' ],
							MessageGroupSubscription::STATE_UPDATED => [ 'tp-msg2' ]
						],
					]
				],
				[
					'type' => 'translate-mgs-message-added',
					'extra' => [
						'groupId' => 'bar',
						'groupLabel' => 'none',
						'changes' => [
							MessageGroupSubscription::STATE_ADDED => [ 'bar-msg1' ],
							MessageGroupSubscription::STATE_UPDATED => [ 'bar-msg2' ]
						]
					]
				],
				[
					'type' => 'translate-mgs-message-added',
					'extra' => [
						'groupId' => 'agg-group-id',
						'groupLabel' => 'aggregate group',
						'changes' => [
							MessageGroupSubscription::STATE_ADDED => [ 'tp-msg1' ],
							MessageGroupSubscription::STATE_UPDATED => [ 'tp-msg2' ]
						],
						'sourceGroupIds' => [ 'agg-group-id-tp-1' ]
					]
				],
				[
					'type' => 'translate-mgs-message-added',
					'extra'	=> [
						'groupId' => 'parent-agg-group-id',
						'groupLabel' => 'parent aggregate group',
						'changes' => [
							MessageGroupSubscription::STATE_ADDED => [ 'tp-msg1' ],
							MessageGroupSubscription::STATE_UPDATED => [ 'tp-msg2' ]
						],
						'sourceGroupIds' => [ 'agg-group-id-tp-1' ]
					]
				]
			],
			'input for getSubscriber method' => [ 'agg-group-id', 'bar', 'agg-group-id-tp-1', 'parent-agg-group-id' ]
		];

		yield 'notification for a normal group' => [
			'changes tro process' => [
				'bar' => [
					MessageGroupSubscription::STATE_ADDED => [ 'bar-msg1' ],
					MessageGroupSubscription::STATE_UPDATED => [ 'bar-msg2' ]
				]
			],
			'arguments for Event::create' => [
				[
					'type' => 'translate-mgs-message-added',
					'extra' => [
						'groupId' => 'bar',
						'groupLabel' => 'none',
						'changes' => [
							MessageGroupSubscription::STATE_ADDED => [ 'bar-msg1' ],
							MessageGroupSubscription::STATE_UPDATED => [ 'bar-msg2' ]
						]
					]
				]
			],
			'input for getSubscriber method' => [ 'bar' ]
		];
	}

	public function testQueueMessage(): void {
		$messageTitle = Title::makeTitle( NS_MEDIAWIKI, 'translated' );
		$message2Title = Title::makeTitle( NS_MEDIAWIKI, 'changedtranslated_1' );
		$this->subscription->queueMessage(
			$messageTitle,
			MessageGroupSubscription::STATE_ADDED,
			'agg-group-id-tp-1'
		);
		$this->subscription->queueMessage(
			$message2Title,
			MessageGroupSubscription::STATE_UPDATED,
			'bar'
		);

		$this->subscription->queueNotificationJob();

		$jobQueueGroup = $this->getServiceContainer()->getJobQueueGroup();
		$job = $jobQueueGroup->pop( 'MessageGroupSubscriptionNotificationJob' );
		$this->assertInstanceOf( MessageGroupSubscriptionNotificationJob::class, $job );

		$changes = $job->getParams()['changes'];
		$this->assertEqualsCanonicalizing(
			[ 'agg-group-id-tp-1', 'bar' ],
			array_keys( $changes ),
			'all expected groups have notifications'
		);
		$this->assertEquals(
			$changes['agg-group-id-tp-1'][MessageGroupSubscription::STATE_ADDED],
			[ $messageTitle->getPrefixedDBkey() ],
			'changes for each group match expected outcome'
		);
		$this->assertEquals(
			$changes['bar'][MessageGroupSubscription::STATE_UPDATED],
			[ $message2Title->getPrefixedDBkey() ],
			'changes for each group match expected outcome'
		);

		$this->subscription->queueNotificationJob();
		$job = $jobQueueGroup->pop( 'MessageGroupSubscriptionNotificationJob' );
		$this->assertFalse( $job, 'job is not created if there are no changes' );
	}

	private function setupTestData(): void {
		$this->subscriptionStoreMock = $this->getMockBuilder( MessageGroupSubscriptionStore::class )
			->disableOriginalConstructor()
			->getMock();

		$serviceContainer = $this->getServiceContainer();
		$this->subscription = new MessageGroupSubscription(
			$this->subscriptionStoreMock,
			$serviceContainer->getJobQueueGroup(),
			$serviceContainer->getUserIdentityLookup(),
			LoggerFactory::getInstance( 'test.translate' ),
			new ServiceOptions(
				MessageGroupSubscription::CONSTRUCTOR_OPTIONS,
				[
					'TranslateEnableMessageGroupSubscription' => true
				]
			)
		);

		$this->mockEventCreator = $this->getMockBuilder( MockEventCreator::class )
			->disableOriginalConstructor()
			->onlyMethods( [ 'create' ] )
			->getMock();

		$this->subscription->setMockEventCreator( $this->mockEventCreator );
	}

	private function getTestGroups(): array {
		$testGroups = [];

		$messageGroupMetadata = $this->createStub( MessageGroupMetadata::class );
		$factory = new AggregateGroupMessageGroupFactory( $messageGroupMetadata );
		$aggGroup = [
			'agg-group-id' => [
				'BASIC' => [
					'id' => 'agg-group-id',
					'label' => 'aggregate group',
					'description' => 'my-description',
				],
				'GROUPS' => [ 'agg-group-id-tp-1', 'agg-group-id-tp-2' ],
			],
			'parent-agg-group-id' => [
				'BASIC' => [
					'id' => 'parent-agg-group-id',
					'label' => 'parent aggregate group',
					'description' => 'parent my-description',
				],
				'GROUPS' => [ 'agg-group-id', 'agg-group-id-tp-1' ],
			]
		];

		$aggregateGroups = $factory->createGroups( $aggGroup );
		$testGroups[ 'agg-group-id' ] = $aggregateGroups['agg-group-id'];
		$testGroups[ 'parent-agg-group-id' ] = $aggregateGroups['parent-agg-group-id'];

		$messages = [
			'translated' => 'bunny',
			'untranslated' => 'fanny'
		];
		$messages2 = [
			'newtranslated' => 'new',
			'oldtranslated' => 'old'
		];
		$messages3 = [
			'changedtranslated_1' => 'bunny',
			'changedtranslated_2' => 'fanny'
		];

		$testGroups['agg-group-id-tp-1'] = new MockWikiMessageGroup( 'agg-group-id-tp-1', $messages );
		$testGroups['agg-group-id-tp-2'] = new MockWikiMessageGroup( 'agg-group-id-tp-2', $messages2 );
		$testGroups['bar'] = new MockWikiMessageGroup( 'bar', $messages3 );

		return $testGroups;
	}

	private function getFakeSubscribers( array $groupIds ): FakeResultWrapper {
		$subscribers = [];
		foreach ( $groupIds as $groupId ) {
			$subscriber = new stdClass();
			$subscriber->tmgs_group = $groupId;
			$subscriber->tmgs_user_id = 1;
			$subscribers[] = $subscriber;
		}

		return new FakeResultWrapper( $subscribers );
	}
}
