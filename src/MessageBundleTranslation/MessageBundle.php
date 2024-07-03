<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundle;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use Wikimedia\Rdbms\Database;

/**
 * @author Abijeet Patro
 * @author Niklas Laxström
 * @since 2022.04
 * @license GPL-2.0-or-later
 */
class MessageBundle extends TranslatableBundle {
	private Title $title;

	public function __construct( Title $title ) {
		$this->title = $title;
	}

	/** @inheritDoc */
	public function getTitle(): Title {
		return $this->title;
	}

	/** @inheritDoc */
	public function getMessageGroupId(): string {
		return MessageBundleMessageGroup::getGroupId( $this->title->getPrefixedText() );
	}

	/** @inheritDoc */
	public function getTranslationPages(): array {
		// MessageBundle do not have translation pages
		return [];
	}

	/** @inheritDoc */
	public function getTranslationUnitPages( ?string $code = null ): array {
		return $this->getTranslationUnitPagesByTitle( $this->title, $code );
	}

	/** @inheritDoc */
	public function isMoveable(): bool {
		return true;
	}

	/** @inheritDoc */
	public function isDeletable(): bool {
		return true;
	}

	public static function isSourcePage( Title $title ): bool {
		if ( !$title->exists() ) {
			return false;
		}

		$mwServices = MediaWikiServices::getInstance();
		$cache = $mwServices->getMainWANObjectCache();
		$cacheKey = $cache->makeKey( 'messagebundle', 'source' );

		$messageBundleIds = $cache->getWithSetCallback(
			$cacheKey,
			$cache::TTL_HOUR * 2,
			static function ( $oldValue, &$ttl, array &$setOpts ) use ( $mwServices ) {
				$dbr = $mwServices->getDBLoadBalancer()->getConnection( DB_REPLICA );
				$setOpts += Database::getCacheSetOptions( $dbr );

				$ids = RevTagStore::getTranslatableBundleIds( RevTagStore::MB_VALID_TAG );
				// Adding a comma at the end and beginning so that we can check for page Id
				// existence with the "," delimiters
				return ',' . implode( ',', $ids ) . ',';
			},
			[
				'checkKeys' => [ $cacheKey ],
				'pcTTL' => $cache::TTL_PROC_SHORT,
				'pcGroup' => __CLASS__ . ':1',
				'version' => 3,
			]
		);

		return str_contains( $messageBundleIds, ( ',' . $title->getArticleID() . ',' ) );
	}

	public static function clearSourcePageCache(): void {
		$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
		$cache->touchCheckKey( $cache->makeKey( 'messagebundle', 'source' ) );
	}
}
