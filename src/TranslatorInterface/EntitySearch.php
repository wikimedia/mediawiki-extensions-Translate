<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

use Collation;
use MessageGroups;
use SplMinHeap;
use WANObjectCache;
use Wikimedia\LightweightObjectStore\ExpirationAwareness;

/**
 * Service for searching message groups and message keys.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
class EntitySearch {
	private const FIELD_DELIMITER = "\x7F";
	/** @var WANObjectCache */
	private $cache;
	/** @var Collation */
	private $collation;
	/** @var MessageGroups */
	private $messageGroupFactory;

	public function __construct( WANObjectCache $cache, Collation $collation, MessageGroups $messageGroupFactory ) {
		$this->cache = $cache;
		$this->collation = $collation;
		$this->messageGroupFactory = $messageGroupFactory;
	}

	public function searchStaticMessageGroups( string $query, int $maxResults ): array {
		$cache = $this->cache;
		// None of the static groups currently use language-dependent labels. This
		// may need revisiting later and splitting the cache by language.
		$key = $cache->makeKey( 'Translate', 'EntitySearch', 'static-groups' );
		$haystack = $cache->getWithSetCallback(
			$key,
			ExpirationAwareness::TTL_HOUR,
			function (): string {
				return $this->getStaticMessageGroupsHaystack();
			},
			[
				// Calling touchCheckKey() on this key purges the cache
				'checkKeys' => [ $this->messageGroupFactory->getCacheKey() ],
				// Avoid querying cache servers multiple times in a web request
				'pcTTL' => $cache::TTL_PROC_LONG
			]
		);

		// Algorithm: Construct one big string with one entity per line. Then run
		// preg_match_all twice over it, first to collect prefix match (to show them
		// first), then to match words if more results are needed.
		$results = [];

		$delimiter = self::FIELD_DELIMITER;
		$anything = "[^$delimiter\n]";
		$query = preg_quote( $query, '/' );
		// Prefix match
		$pattern = "/^($query$anything*)$delimiter($anything+)$/miu";
		preg_match_all( $pattern, $haystack, $matches, PREG_SET_ORDER );
		foreach ( $matches as [ , $label, $groupId ] ) {
			// Index by $groupId to avoid duplicates from the prefix match and the word match
			$results[$groupId] = [
				'label' => $label,
				'group' => $groupId,
			];

			if ( count( $results ) >= $maxResults ) {
				return array_values( $results );
			}
		}

		// Word match
		$pattern = "/^($anything*\b$query$anything*)$delimiter($anything+)$/miu";
		preg_match_all( $pattern, $haystack, $matches, PREG_SET_ORDER );
		foreach ( $matches as [ , $label, $groupId ] ) {
			$results[$groupId] = [
				'label' => $label,
				'group' => $groupId,
			];

			if ( count( $results ) >= $maxResults ) {
				return array_values( $results );
			}
		}

		return array_values( $results );
	}

	private function getStaticMessageGroupsHaystack(): string {
		$groups = $this->messageGroupFactory->getGroups();
		$data = new SplMinHeap();
		foreach ( $groups as $group ) {
			$label = $group->getLabel();
			// Ensure there are no special chars that will break matching
			$label = strtr( $label, [ self::FIELD_DELIMITER => '', "\n" => '' ] );
			$sortKey = $this->collation->getSortKey( $label );
			// It is unlikely that different groups have the same label (or sort key),
			// but it's possible.
			$data->insert( [ $sortKey, $label, $group->getId() ] );
		}

		$haystack = '';
		foreach ( $data as [ , $label, $groupId ] ) {
			$haystack .= $label . self::FIELD_DELIMITER . $groupId . "\n";
		}

		return $haystack;
	}
}
