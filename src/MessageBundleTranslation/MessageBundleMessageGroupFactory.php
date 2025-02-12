<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use MainConfigDependency;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\Translate\MessageGroupProcessing\CachedMessageGroupFactory;
use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\MessageProcessing\MessageGroupMetadata;
use MediaWiki\Title\Title;
use Wikimedia\Rdbms\IReadableDatabase;

/**
 * @since 2024.05
 * @author Niklas Laxström
 * @copyright GPL-2.0-or-later
 */
class MessageBundleMessageGroupFactory implements CachedMessageGroupFactory {
	public const SERVICE_OPTIONS = [
		'TranslateEnableMessageBundleIntegration'
	];
	private MessageGroupMetadata $messageGroupMetadata;
	private bool $enableIntegration;

	public function __construct(
		MessageGroupMetadata $messageGroupMetadata,
		ServiceOptions $options
	) {
		$this->messageGroupMetadata = $messageGroupMetadata;
		$options->assertRequiredOptions( self::SERVICE_OPTIONS );
		$this->enableIntegration = $options->get( 'TranslateEnableMessageBundleIntegration' );
	}

	public function getCacheKey(): string {
		return 'message-bundles';
	}

	public function getCacheVersion(): int {
		return 1;
	}

	public function getDependencies(): array {
		return [ new MainConfigDependency( 'TranslateEnableMessageBundleIntegration' ) ];
	}

	/** @inheritDoc */
	public function getData( IReadableDatabase $db ) {
		if ( !$this->enableIntegration ) {
			return [];
		}

		$cacheData = [];
		$res = $db->newSelectQueryBuilder()
			->select( [ 'page_id', 'page_namespace', 'page_title', 'rt_revision' => 'MAX(rt_revision)' ] )
			->from( 'page' )
			->join( 'revtag', null, [ 'page_id=rt_page', 'rt_type' => RevTagStore::MB_VALID_TAG ] )
			->groupBy( [ 'page_id', 'page_namespace', 'page_title' ] )
			->caller( __METHOD__ )
			->fetchResultSet();

		foreach ( $res as $r ) {
			$title = Title::newFromRow( $r );
			$cacheData[] = [
				$title->getPrefixedText(),
				(int)$r->page_id,
				(int)$r->rt_revision,
			];
		}

		return $cacheData;
	}

	/** @inheritDoc */
	public function createGroups( $data ): array {
		$groups = [];
		$groupIds = [];

		// First get all the group ids
		foreach ( $data as $conf ) {
			$groupIds[] = MessageBundleMessageGroup::getGroupId( $conf[0] );
		}

		// Preload all the metadata
		$this->messageGroupMetadata->preloadGroups( $groupIds, __METHOD__ );

		// Loop over all the group ids and create the MessageBundleMessageGroup
		foreach ( $groupIds as $index => $groupId ) {
			$conf = $data[$index];
			$description = $this->messageGroupMetadata->getWithDefaultValue( $groupId, 'description', null );
			$label = $this->messageGroupMetadata->getWithDefaultValue( $groupId, 'label', null );
			$groups[$groupId] = new MessageBundleMessageGroup(
				$groupId,
				$conf[0],
				$conf[1],
				$conf[2],
				$description,
				$label
			);
		}

		return $groups;
	}
}
