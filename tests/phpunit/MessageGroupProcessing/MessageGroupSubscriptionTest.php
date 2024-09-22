<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\Translate\MessageProcessing\MessageGroupMetadata;
use MediaWiki\Logger\LoggerFactory;
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

		$config = new MessageGroupTestConfig();
		$config->groups = $this->getTestGroups();
		$config->skipMessageIndexRebuild = true;
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

	public function provideTestSendNotifications() {
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
				]
			],
			'input for getSubscriber method' => [ 'agg-group-id', 'agg-group-id-tp-1' ],
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
				]
			],
			'input for getSubscriber method' => [ 'agg-group-id', 'bar', 'agg-group-id-tp-1' ]
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
		$data = [
			'agg-group-id' => [
				'BASIC' => [
					'id' => 'agg-group-id',
					'label' => 'aggregate group',
					'description' => 'my-description',
				],
				'GROUPS' => [ 'agg-group-id-tp-1', 'agg-group-id-tp-2' ],
			]
		];

		$testGroups[ 'agg-group-id' ] = $factory->createGroups( $data )['agg-group-id'];

		$messages = [
			'translated' => 'bunny',
			'untranslated' => 'fanny',
			'changedtranslated_1' => 'bunny',
			'changedtranslated_2' => 'fanny'
		];

		$testGroups['agg-group-id-tp-1'] = new MockWikiMessageGroup( 'agg-group-id-tp-1', $messages );
		$testGroups['agg-group-id-tp-2'] = new MockWikiMessageGroup( 'agg-group-id-tp-2', $messages );
		$testGroups['bar'] = new MockWikiMessageGroup( 'bar', $messages );
		$testGroups['foo'] = new MockWikiMessageGroup( 'foo', $messages );

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
