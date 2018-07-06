<?php
/**
 * TTMServer - The Translate extension translation memory interface
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup TTMServer
 */

/**
 * A searcher class that works on top of elasticsearch with an ElasticTTMServer
 * to search and display suggestion of translations recorded by this same TTM
 * service.
 * @ingroup TTMServer
 */
interface ElasticSuggestionSearcher {

	/**
	 * @param string $sourceLanguage language code for the provide text
	 * @param string $targetLanguage language code for the suggestions
	 * @param string $text the text for which to search suggestions
	 * @return array List: unordered suggestions, which each has fields:
	 *   - source: String: the original text of the suggestion
	 *   - target: String: the suggestion
	 *   - context: String: title of the page where the suggestion comes from
	 *   - quality: Float: the quality of suggestion, 1 is perfect match
	 */
	public function getSuggestions( $sourceLanguage, $targetLanguage, $text );
}
