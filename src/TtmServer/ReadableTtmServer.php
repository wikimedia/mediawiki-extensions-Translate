<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TtmServer;

/**
 * Interface for TtmServer that can be queried (=all of them).
 * @ingroup TTMServer
 */
interface ReadableTtmServer {
	/**
	 * Fetches all relevant suggestions for given text.
	 *
	 * @param string $sourceLanguage language code for the provide text
	 * @param string $targetLanguage language code for the suggestions
	 * @param string $text the text for which to search suggestions
	 * @return array List: unordered suggestions, which each has fields:
	 *   - source: String: the original text of the suggestion
	 *   - target: String: the suggestion
	 *   - context: String: title of the page where the suggestion comes from
	 *   - quality: Float: the quality of suggestion, 1 is perfect match
	 */
	public function query( string $sourceLanguage, string $targetLanguage, string $text ): array;

	/**
	 * Determines if the suggestion returned by this TtmServer comes
	 * from this wiki or any other wiki.
	 */
	public function isLocalSuggestion( array $suggestion ): bool;

	/**
	 * Given suggestion returned by this TtmServer, constructs fully
	 * qualified URL to the location of the translation.
	 * @return string URL
	 */
	public function expandLocation( array $suggestion ): string;
}
