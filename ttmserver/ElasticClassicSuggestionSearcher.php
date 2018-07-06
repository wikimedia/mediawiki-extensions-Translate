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
 * Original suggestion searcher
 * @since 2018.07
 * @ingroup TTMServer
 */
class ElasticClassicSuggestionSearcher implements ElasticSuggestionSearcher {

	/**
	 * @var ElasticSearchTTMServer
	 */
	private $server;

	/**
	 * @param ElasticSearchTTMServer $server
	 */
	public function __construct( ElasticSearchTTMServer $server ) {
		$this->server = $server;
	}

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
	public function getSuggestions( $sourceLanguage, $targetLanguage, $text ) {
		if ( !$this->server->useWikimediaExtraPlugin() ) {
			// ElasticTTM is currently not compatible with elasticsearch 2.x/5.x
			// It needs FuzzyLikeThis ported via the wmf extra plugin
			throw new \RuntimeException( 'The wikimedia extra plugin is mandatory.' );
		}
		/* Two query system:
		 * 1) Find all strings in source language that match text
		 * 2) Do another query for translations for those strings
		 */
		$connection = $this->server->getClient()->getConnection();
		$oldTimeout = $connection->getTimeout();
		$connection->setTimeout( 10 );

		$fuzzyQuery = new FuzzyLikeThis();
		$fuzzyQuery->setLikeText( $text );
		$fuzzyQuery->addFields( [ 'content' ] );

		$boostQuery = new \Elastica\Query\FunctionScore();
		if ( $this->server->useWikimediaExtraPlugin() ) {
			$boostQuery->addFunction(
				'levenshtein_distance_score',
				[
					'text' => $text,
					'field' => 'content'
				]
			);
		} else {
			// TODO: should we remove this code block the extra
			// plugin is now mandatory and we will never use the
			// groovy script.
			if ( $this->server->isElastica5() ) {
				$scriptClass = \Elastica\Script\Script::class;
			} else {
				$scriptClass = \Elastica\Script::class;
			}

			$groovyScript =
				<<<GROOVY
import org.apache.lucene.search.spell.*
new LevensteinDistance().getDistance(srctxt, _source['content'])
GROOVY;
			$script = new $scriptClass(
				$groovyScript,
				[ 'srctxt' => $text ],
				$scriptClass::LANG_GROOVY
			);
			$boostQuery->addScriptScoreFunction( $script );
		}
		$boostQuery->setBoostMode( \Elastica\Query\FunctionScore::BOOST_MODE_REPLACE );

		// Wrap the fuzzy query so it can be used as a filter.
		// This is slightly faster, as ES can throw away the scores by this query.
		$bool = new \Elastica\Query\BoolQuery();
		$bool->addFilter( $fuzzyQuery );
		$bool->addMust( $boostQuery );

		$languageFilter = new \Elastica\Query\Term();
		$languageFilter->setTerm( 'language', $sourceLanguage );
		$bool->addFilter( $languageFilter );

		// The whole query
		$query = new \Elastica\Query();
		$query->setQuery( $bool );

		// The interface usually displays three best candidates. These might
		// come from more than three source things, if the translations are
		// the same. In other words suggestions are grouped by the suggested
		// translation. This algorithm might not find all suggestions, if the
		// top N best matching source texts don't have equivalent translations
		// in the target language, but worse matches which we did not fetch do.
		// This code tries to balance between doing too many or too big queries
		// and not fetching enough results to show all possible suggestions.
		$sizeFirst = 100;
		$sizeSecond = $sizeFirst * 5;

		$query->setFrom( 0 );
		$query->setSize( $sizeFirst );
		$query->setParam( '_source', [ 'content' ] );
		$config = $this->server->getConfig();
		$cutoff = isset( $config['cutoff'] ) ? $config['cutoff'] : 0.65;
		$query->setParam( 'min_score', $cutoff );
		$query->setSort( [ '_score', '_uid' ] );

		/* This query is doing two unrelated things:
		 * 1) Collect the message contents and scores so that they can
		 *    be accessed later for the translations we found.
		 * 2) Build the query string for the query that fetches the translations.
		 */
		$contents = $scores = $terms = [];
		do {
			$resultset = $this->server->getType()->search( $query );

			if ( count( $resultset ) === 0 ) {
				break;
			}

			foreach ( $resultset->getResults() as $result ) {
				$data = $result->getData();
				$score = $result->getScore();

				$sourceId = preg_replace( '~/[^/]+$~', '', $result->getId() );
				$contents[$sourceId] = $data['content'];
				$scores[$sourceId] = $score;
				$terms[] = "$sourceId/$targetLanguage";
			}

			// Check if it looks like that we are hitting the long tail already.
			// Otherwise, we'll do a query to fetch some more to reach a "sane"
			// breaking point, i.e. include all suggestions with same content
			// for reliable used X times statistics.
			if ( count( array_unique( $scores ) ) > 5 ) {
				break;
			}

			// Okay, We are now in second iteration of the loop. We already got
			// lots of suggestions. We will give up for now even if it means we
			// return in some sense incomplete results.
			if ( count( $resultset ) === $sizeSecond ) {
				break;
			}

			// After the first query, the smallest score is the new threshold.
			$query->setParam( 'min_score', $score );
			$query->setFrom( $query->getParam( 'size' ) + $query->getParam( 'from' ) );
			$query->setSize( $sizeSecond );

			// Break if we already got all hits
		} while ( $resultset->getTotalHits() > count( $contents ) );

		$suggestions = [];

		// Skip second query if first query found nothing. Keeping only one return
		// statement in this method to avoid forgetting to reset connection timeout
		if ( $terms !== [] ) {
			$idQuery = new \Elastica\Query\Terms();
			$idQuery->setTerms( '_id', $terms );

			$query = new \Elastica\Query( $idQuery );
			$query->setSize( 25 );
			$query->setParam( '_source', [ 'wiki', 'uri', 'content', 'localid' ] );
			$resultset = $this->server->getType()->search( $query );

			foreach ( $resultset->getResults() as $result ) {
				$data = $result->getData();

				// Construct the matching source id
				$sourceId = preg_replace( '~/[^/]+$~', '', $result->getId() );

				$suggestions[] = [
					'source' => $contents[$sourceId],
					'target' => $data['content'],
					'context' => $data['localid'],
					'quality' => $scores[$sourceId],
					'wiki' => $data['wiki'],
					'location' => $data['localid'] . '/' . $targetLanguage,
					'uri' => $data['uri'],
				];
			}

			// Ensure reults are in quality order
			uasort( $suggestions, function ( $a, $b ) {
				if ( $a['quality'] === $b['quality'] ) {
					return 0;
				}

				return ( $a['quality'] < $b['quality'] ) ? 1 : -1;
			} );
		}

		$connection->setTimeout( $oldTimeout );

		return $suggestions;
	}
}
