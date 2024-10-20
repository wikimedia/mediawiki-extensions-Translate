<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use InvalidArgumentException;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;
use MediaWiki\Extension\Translate\MessageLoading\MessageDefinitions;
use MediaWiki\Title\TitleParser;

/**
 * This class abstracts MessagePrefix statistics calculation and storing.
 * @author Abijeet Patro
 * @since 2023.02
 * @license GPL-2.0-or-later
 */
class MessagePrefixStats {
	private TitleParser $titleParser;
	private ?array $allStats;

	public function __construct( TitleParser $titleParser ) {
		$this->titleParser = $titleParser;
	}

	/**
	 * Returns statistics for the message keys provided. Assumes that the message keys
	 * belong to the same namespace.
	 * @param string ...$prefixedMessagesKeys
	 * @return array
	 */
	public function forAll( string ...$prefixedMessagesKeys ): array {
		$languages = MessageGroupStats::getLanguages();
		$stats = [];

		if ( !$prefixedMessagesKeys ) {
			throw new InvalidArgumentException( 'Empty prefixed message keys passed as argument' );
		}

		$messagesForDefinition = [];
		foreach ( $prefixedMessagesKeys as $key ) {
			$messageTitle = $this->titleParser->parseTitle( $key );
			$messageNamespace = $messageTitle->getNamespace();
			$messagesForDefinition["$messageNamespace:{$messageTitle->getDBkey()}"] = null;
		}

		$messageDefinitions = new MessageDefinitions( $messagesForDefinition, false );

		foreach ( $languages as $code ) {
			if ( $this->isLanguageUnused( $code ) ) {
				$collection = MessageCollection::newFromDefinitions( $messageDefinitions, $code );
				$stats[ $code ] = MessageGroupStats::getStatsForCollection( $collection );
			} else {
				$stats[ $code ] = MessageGroupStats::getEmptyStats();
				$stats[ $code ][ MessageGroupStats::TOTAL ] = count( $prefixedMessagesKeys );
			}
		}

		return $stats;
	}

	/** Check if there are any stats for the language */
	private function isLanguageUnused( string $languageCode ): bool {
		$allStats = $this->getAllLanguageStats();
		$languageStats = $allStats[ $languageCode ] ?? [];
		$translatedStats = $languageStats[ MessageGroupStats::TRANSLATED ];
		$fuzzyStats = $languageStats[ MessageGroupStats::FUZZY ];

		return $translatedStats !== 0 || $fuzzyStats !== 0;
	}

	private function getAllLanguageStats(): array {
		$this->allStats ??= MessageGroupStats::getApproximateLanguageStats();
		return $this->allStats;
	}
}
