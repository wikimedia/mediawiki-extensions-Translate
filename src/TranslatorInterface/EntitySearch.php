<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

use AggregateMessageGroup;
use Collation;
use MediaWiki\Extension\Translate\MessageBundleTranslation\MessageBundleMessageGroup;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageLoading\MessageIndex;
use MediaWiki\Title\MalformedTitleException;
use MediaWiki\Title\NamespaceInfo;
use MediaWiki\Title\TitleFormatter;
use MediaWiki\Title\TitleParser;
use SplMinHeap;
use Wikimedia\LightweightObjectStore\ExpirationAwareness;
use Wikimedia\ObjectCache\WANObjectCache;
use WikiPageMessageGroup;

/**
 * Service for searching message groups and message keys.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2021.10
 */
class EntitySearch {
	private const FIELD_DELIMITER = "\x7F";
	private const ROW_DELIMITER = "\n";

	private WANObjectCache $cache;
	private Collation $collation;
	private MessageGroups $messageGroupFactory;
	private NamespaceInfo $namespaceInfo;
	private MessageIndex $messageIndex;
	private TitleParser $titleParser;
	private TitleFormatter $titleFormatter;
	private const TYPE_AGGREGATE = AggregateMessageGroup::class;
	private const TYPE_MESSAGE_BUNDLE = MessageBundleMessageGroup::class;
	private const TYPE_WIKIPAGE = WikiPageMessageGroup::class;
	private const TYPE_OTHERS = 'o';
	private const TYPE_MAPPING = [
		self::TYPE_AGGREGATE => [ 'aggregate-groups', 'a' ],
		self::TYPE_MESSAGE_BUNDLE => [ 'message-bundles', 'm' ],
		self::TYPE_WIKIPAGE => [ 'translatable-pages', 'w' ]
	];
	/** @var array<string,string> */
	private array $mappedTypes;

	public function __construct(
		WANObjectCache $cache,
		Collation $collation,
		MessageGroups $messageGroupFactory,
		NamespaceInfo $namespaceInfo,
		MessageIndex $messageIndex,
		TitleParser $titleParser,
		TitleFormatter $titleFormatter
	) {
		$this->cache = $cache;
		$this->collation = $collation;
		$this->messageGroupFactory = $messageGroupFactory;
		$this->namespaceInfo = $namespaceInfo;
		$this->messageIndex = $messageIndex;
		$this->titleParser = $titleParser;
		$this->titleFormatter = $titleFormatter;
		$this->mappedTypes = $this->getGroupTypes();
	}

	public function searchStaticMessageGroups( string $query, int $maxResults, array $types = [] ): array {
		$cache = $this->cache;
		// None of the static groups currently use language-dependent labels. This
		// may need revisiting later and splitting the cache by language.
		$key = $cache->makeKey( 'Translate', 'EntitySearch', 'static-groups', '-v2' );
		$haystack = $cache->getWithSetCallback(
			$key,
			ExpirationAwareness::TTL_WEEK,
			function (): string {
				return $this->getStaticMessageGroupsHaystack();
			},
			[
				// Calling touchCheckKey() on this key purges the cache
				'checkKeys' => [ $this->messageGroupFactory->getCacheKey() ],
				// Avoid querying cache servers multiple times in a web request
				'pcTTL' => ExpirationAwareness::TTL_PROC_LONG
			]
		);

		// Algorithm: Construct one big string with one entity per line. Then run
		// preg_match_all twice over it, first to collect prefix match (to show them
		// first), then to match words if more results are needed.
		$results = [];

		$delimiter = self::FIELD_DELIMITER;
		$anything = "[^$delimiter\n]";
		$query = preg_quote( $query, '/' );

		$groupTypeFilter = [];
		foreach ( $types as $filter ) {
			$groupTypeFilter[] = $this->mappedTypes[$filter] ?? '';
		}
		$groupTypeFilter = count( $groupTypeFilter )
			? ( '[' . implode( $groupTypeFilter ) . ']' )
			: $anything;

		// Prefix match
		$pattern = "/^$groupTypeFilter$delimiter($query$anything*)$delimiter($anything+)$/miu";
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
		$pattern = "/^$groupTypeFilter$delimiter($anything*\b$query$anything*)$delimiter($anything+)$/miu";
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

	/** Search message prefixes. Results are collapsed into prefix patterns when possible. */
	public function searchMessages( string $query, int $maxResults ): array {
		// Optimized based on requirements:
		// * "Natural" sorting of results
		// * No need to show which message group things belong to
		// * Match at any point in the message
		// * Return full keys of prefixes that match multiple messages

		// Algorithm: Construct one big string (haystack) with one entity per line.
		// Then run preg_match_all over it. Because we will have many more matches
		// than search results, this may be more efficient than calling preg_match
		// repeatedly in a loop.  On the other hand, it can use a lot of memory to
		// construct the array for all the matches.
		$haystack = $this->getMessagesHaystack();
		$rowDelimiter = self::ROW_DELIMITER;
		$anything = "[^$rowDelimiter]";
		$query = preg_quote( $query, '/' );

		// Word match
		$pattern = "/^($anything*\b$query)$anything*$/miu";
		preg_match_all( $pattern, $haystack, $matches, PREG_SET_ORDER );

		$results = [];
		$previousPrefixMatch = null;
		foreach ( $matches as [ $full, $prefixMatch ] ) {
			// This is a bit tricky. If we are at the maximum results, continue processing
			// until the prefix changes, to get an accurate count
			if ( count( $results ) >= $maxResults && $previousPrefixMatch !== $prefixMatch ) {
				break;
			}

			if ( $full === $prefixMatch ) {
				$results[$full] = [ $full, 1, true, $full ];
			} else {
				if ( !isset( $results["$prefixMatch*"] ) ) {
					$results["$prefixMatch*"] = [ "$prefixMatch*", 0, false, $full ];
				}
				$results["$prefixMatch*"][1]++;
			}
			$previousPrefixMatch = $prefixMatch;
		}

		// Convert partial matches with single results to full match
		foreach ( $results as $index => [ $label, $count, $isExact, $full ] ) {
			if ( $count === 1 && !$isExact ) {
				$results[$index][0] = $full;
			}
		}

		// Drop unnecessary fields, pretty format title
		foreach ( $results as &$value ) {
			try {
				$title = $this->titleParser->parseTitle( $value[0] );
				$label = $this->titleFormatter->getPrefixedText( $title );
			} catch ( MalformedTitleException $e ) {
				$label = $value[0];
			}
			$value = [
				'pattern' => $label,
				'count' => $value[1]
			];
		}

		return array_values( $results );
	}

	/** Match messages matching a pattern. '*' is the wildcard for anything. */
	public function matchMessages( string $query ): array {
		$haystack = $this->getMessagesHaystack();
		$rowDelimiter = self::ROW_DELIMITER;
		$anything = "[^$rowDelimiter]*";

		// Need something that's not affected by preg_quote and cannot be guessed
		$placeholder = bin2hex( random_bytes( 16 ) );
		$query = str_replace( '*', $placeholder, $query );
		$query = preg_quote( $query, '/' );
		$query = str_replace( $placeholder, $anything, $query );

		$pattern = "/^$query/miu";
		preg_match_all( $pattern, $haystack, $matches, PREG_SET_ORDER );
		return array_column( $matches, 0 );
	}

	private function getStaticMessageGroupsHaystack(): string {
		$groups = $this->messageGroupFactory->getGroups();
		$data = new SplMinHeap();
		foreach ( $groups as $group ) {
			$label = $group->getLabel();
			// Ensure there are no special chars that will break matching
			$label = strtr( $label, [ self::FIELD_DELIMITER => '', self::ROW_DELIMITER => '' ] );
			$sortKey = $this->collation->getSortKey( $label );
			// It is unlikely that different groups have the same label (or sort key),
			// but it's possible, so cannot use a hashmap here.
			$groupType = get_class( $group );
			$type = self::TYPE_MAPPING[$groupType][1] ?? self::TYPE_OTHERS;
			$data->insert( [ $sortKey, $label, $group->getId(), $type ] );
		}

		$haystack = '';
		foreach ( $data as [ , $label, $groupId, $type ] ) {
			$haystack
				.= $type
				. self::FIELD_DELIMITER
				. $label
				. self::FIELD_DELIMITER
				. $groupId
				. self::ROW_DELIMITER;
		}
		return $haystack;
	}

	private function getMessageHaystackUncached(): string {
		$namespaceMap = [];
		$data = new SplMinHeap();
		$keys = $this->messageIndex->getKeys();
		foreach ( $keys as $key ) {
			// Normalize "_" to " " so that \b in regexp matches words separated by underscores
			$key = strtr( $key, [ '_' => ' ' ] );

			[ $namespaceId, $label ] = explode( ':', $key, 2 );
			if ( !isset( $namespaceMap[$namespaceId] ) ) {
				$namespaceMap[$namespaceId] = $this->namespaceInfo->getCanonicalName( (int)$namespaceId );
			}
			$label = $namespaceMap[$namespaceId] . ":$label";

			// Ensure there are no special chars that will break matching
			$label = strtr( $label, [ self::ROW_DELIMITER => '' ] );
			$sortKey = $this->collation->getSortKey( $label );
			$data->insert( [ $sortKey, $label ] );
		}

		$haystack = '';
		foreach ( $data as [ , $label ] ) {
			$haystack .= $label . self::ROW_DELIMITER;
		}

		return $haystack;
	}

	private function getMessagesHaystack(): string {
		$cache = $this->cache;
		$key = $cache->makeKey( 'Translate', 'EntitySearch', 'messages' );
		return $cache->getWithSetCallback(
			$key,
			ExpirationAwareness::TTL_WEEK,
			function (): string {
				// This can get rather large. On translatewiki.net it is multiple megabytes
				// uncompressed. With compression (assumed to happen implicitly in the
				// caching layer) it's under a megabyte.
				return $this->getMessageHaystackUncached();
			},
			[
				// Calling touchCheckKey() on this key purges the cache
				'checkKeys' => [ $this->messageIndex->getStatusCacheKey() ],
				// Avoid querying cache servers multiple times in a web request
				'pcTTL' => ExpirationAwareness::TTL_PROC_LONG
			]
		);
	}

	/** @return array<string,string> */
	public function getGroupTypes(): array {
		return array_column( self::TYPE_MAPPING, 1, 0 );
	}
}
