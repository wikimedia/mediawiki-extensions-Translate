<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\MessageGroupProcessing\CachedMessageGroupLoader;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupLoader;
use MediaWiki\Extension\Translate\MessageGroups\AggregateMessageGroup;
use MediaWiki\Extension\Translate\MessageGroups\FileBasedMessageGroup;
use MediaWiki\Extension\Translate\MessageGroups\MediaWikiExtensionMessageGroup;
use MediaWiki\Extension\Translate\MessageGroups\MessageGroup;
use MediaWiki\Extension\Translate\MessageGroups\MessageGroupBase;
use MediaWiki\Extension\Translate\MessageGroups\MessageGroupOld;
use MediaWiki\Extension\Translate\MessageGroups\MessagePrefixMessageGroup;
use MediaWiki\Extension\Translate\MessageGroups\RecentAdditionsMessageGroup;
use MediaWiki\Extension\Translate\MessageGroups\RecentMessageGroup;
use MediaWiki\Extension\Translate\MessageGroups\SandboxMessageGroup;
use MediaWiki\Extension\Translate\MessageGroups\SubsetMessageGroup;
use MediaWiki\Extension\Translate\MessageGroups\WikiMessageGroup;
use MediaWiki\Extension\Translate\MessageGroups\WikiPageMessageGroup;
use MediaWiki\Extension\Translate\MessageGroups\WorkflowStatesMessageGroup;

/**
 * Verifies that the old global class names remain valid after the namespace migration.
 *
 * These tests exist to protect external consumers (CentralNotice, TranslationNotifications,
 * FundraisingTranslateWorkflow, Wikimedia configuration, etc.) that reference the pre-migration
 * global names.
 *
 * @license GPL-2.0-or-later
 * @coversNothing
 */
class MessageGroupsNamespaceCompatibilityTest extends MediaWikiIntegrationTestCase {

	/** @dataProvider provideClassAliases */
	public function testGlobalClassExists( string $globalName ): void {
		$this->assertTrue( class_exists( $globalName ) || interface_exists( $globalName ),
			"Global name '$globalName' must remain available as a class or interface alias" );
	}

	/** @dataProvider provideClassAliases */
	public function testGlobalNameIsAliasOfNamespacedClass( string $globalName, string $namespacedName ): void {
		$this->assertTrue( is_a( $globalName, $namespacedName, true ),
			"'$globalName' must be the same type as '$namespacedName'" );
	}

	public static function provideClassAliases(): array {
		return [
			[ 'AggregateMessageGroup', AggregateMessageGroup::class ],
			[ 'CachedMessageGroupLoader', CachedMessageGroupLoader::class ],
			[ 'FileBasedMessageGroup', FileBasedMessageGroup::class ],
			[ 'MediaWikiExtensionMessageGroup', MediaWikiExtensionMessageGroup::class ],
			[ 'MessageGroup', MessageGroup::class ],
			[ 'MessageGroupBase', MessageGroupBase::class ],
			[ 'MessageGroupLoader', MessageGroupLoader::class ],
			[ 'MessageGroupOld', MessageGroupOld::class ],
			[ 'MessagePrefixMessageGroup', MessagePrefixMessageGroup::class ],
			[ 'RecentAdditionsMessageGroup', RecentAdditionsMessageGroup::class ],
			[ 'RecentMessageGroup', RecentMessageGroup::class ],
			[ 'SandboxMessageGroup', SandboxMessageGroup::class ],
			[ 'SubsetMessageGroup', SubsetMessageGroup::class ],
			[ 'WikiMessageGroup', WikiMessageGroup::class ],
			[ 'WikiPageMessageGroup', WikiPageMessageGroup::class ],
			[ 'WorkflowStatesMessageGroup', WorkflowStatesMessageGroup::class ],
		];
	}

	public function testWikiMessageGroupConstruction(): void {
		$group = new WikiMessageGroup( 'test-id', 'MediaWiki:some-source' );
		$this->assertInstanceOf( WikiMessageGroup::class, $group );
		$this->assertSame( 'test-id', $group->getId() );
	}

	public function testWikiMessageGroupIsInstanceOfGlobalAlias(): void {
		$group = new WikiMessageGroup( 'test-id', 'MediaWiki:some-source' );
		// instanceof check using the global alias — the primary external consumer use case
		$this->assertInstanceOf( 'WikiMessageGroup', $group );
	}

	public function testWikiPageMessageGroupIsInstanceOfGlobalAlias(): void {
		$group = new WikiPageMessageGroup( 'page-test', 'SomePage' );
		$this->assertInstanceOf( 'WikiPageMessageGroup', $group );
	}

	public function testAggregateMessageGroupViaStaticFactory(): void {
		$conf = [
			'BASIC' => [
				'class' => 'AggregateMessageGroup',
				'id' => 'agg-bc-test',
				'namespace' => NS_MEDIAWIKI,
			],
			'GROUPS' => [],
		];
		$group = MessageGroupBase::factory( $conf );
		$this->assertInstanceOf( AggregateMessageGroup::class, $group );
		// Also verify the global alias satisfies instanceof
		$this->assertInstanceOf( 'AggregateMessageGroup', $group );
	}

	public function testExtendsWikiMessageGroupViaGlobalAlias(): void {
		// Simulates CentralNotice's BannerMessageGroup extends WikiMessageGroup
		$group = new class( 'banner-test', 'MediaWiki:some-source' ) extends WikiMessageGroup {
		};
		$this->assertInstanceOf( WikiMessageGroup::class, $group );
		$this->assertInstanceOf( 'WikiMessageGroup', $group );
	}
}
