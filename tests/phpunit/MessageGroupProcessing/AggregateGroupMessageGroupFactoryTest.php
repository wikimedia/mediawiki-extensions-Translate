<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use AggregateMessageGroup;
use MediaWiki\Extension\Translate\MessageProcessing\MessageGroupMetadata;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\MessageGroupProcessing\AggregateGroupMessageGroupFactory
 */
class AggregateGroupMessageGroupFactoryTest extends \MediaWikiIntegrationTestCase {
	public function testCreateGroups() {
		$messageGroupMetadata = $this->createStub( MessageGroupMetadata::class );

		$factory = new AggregateGroupMessageGroupFactory( $messageGroupMetadata );

		$data = [
			'my-group-id' => [
				'BASIC' => [
					'id' => 'my-group-id',
					'description' => 'my-description',
				],
				'GROUPS' => [ 'foo', 'bar' ],
			]
		];

		[ 'my-group-id' => $group ] = $factory->createGroups( $data );

		static::assertInstanceOf( AggregateMessageGroup::class, $group );
		static::assertSame( 'my-group-id', $group->getId() );
		static::assertSame( 'my-description', $group->getDescription() );
	}
}
