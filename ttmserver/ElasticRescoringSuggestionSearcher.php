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
 * Suggestion searcher using a rescore phase to apply levenshtein
 * @since 2018.07
 * @ingroup TTMServer
 */
class ElasticRescoringSuggestionSearcher implements ElasticSuggestionSearcher {

	/**
	 * @var ElasticSearchTTMServer
	 */
	private $server;

	/**
	 * @var array
	 */
	private $params;

	/**
	 * @var WebRequest
	 */
	private $request;

	/**
	 * @param ElasticSearchTTMServer $server
	 * @param array $params
	 * @param WebRequest $request
	 */
	public function __construct(
		ElasticSearchTTMServer $server,
		array $params,
		WebRequest $request
	) {
		$this->server = $server;
		$this->params = $params;
		$this->request = $request;
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
		try {
			$connection->setTimeout( 10 );

			$fuzzyQuery = new FuzzyLikeThis();
			$fuzzyQuery->setLikeText( $text );
			$fuzzyQuery->addFields( [ 'content' ] );

			$boostQuery = new \Elastica\Query\FunctionScore();
			$boostQuery->addFunction( 'levenshtein_distance_score', [
					'text' => $text,
					'field' => 'content'
				] );
			$boostQuery->setBoostMode( \Elastica\Query\FunctionScore::BOOST_MODE_REPLACE );

			// Wrap the fuzzy query so it can be used as a filter.
			// This is slightly faster, as ES can throw away the scores by this query.
			$bool = new \Elastica\Query\BoolQuery();
			$bool->addMust( $fuzzyQuery );

			$languageFilter = new \Elastica\Query\Term();
			$languageFilter->setTerm( 'language', $sourceLanguage );
			$bool->addFilter( $languageFilter );

			// The whole query
			$query = new \Elastica\Query();
			$query->setQuery( $bool );
			$rescoreQuery = new Elastica\Rescore\Query( $boostQuery );
			$rescoreQuery->setQueryWeight( 0 );
			$rescoreQuery->setRescoreQueryWeight( 1 );
			$rescoreQuery->setParam( 'score_mode', 'total' );

			// Max number of results we want to fetch and test php side
			$size = $this->params['retrieval_size'] ?? $this->request
				->getVal( 'elasticTTMRetrievalSize', 500 );
			// Max number of results we want to rescore (here apply levenshtein)
			$rescoreSize = $param['rescore_window'] ?? $this->request
				->getVal( 'elasticTTMRescoreSize', 1000 );

			if ( $size > $rescoreSize ) {
				\MediaWiki\Logger\LoggerFactory::getInstance( 'ElasticSearchTTMServer' )
					->warning( "{class} retrieval_size ({retrieval_size}) cannot be higher "
						. "than rescore_window ({rescore_window}), "
						. "forcing retrieval_size to {rescore_window} ",
						[
							'class' => get_class( $this ),
							'retrieval_size' => $size,
							'rescore_window' => $rescoreSize,
						] );
				$size = $rescoreSize;
			}
			$rescoreQuery->setWindowSize( $rescoreSize );
			$rescoreQuery->setParam( 'score_mode', 'total' );
			$query->setFrom( 0 );
			$query->setSize( $size );
			$query->setParam( '_source', [ 'content' ] );
			$config = $this->server->getConfig();
			$cutoff = isset( $config['cutoff'] ) ? $config['cutoff'] : 0.65;

			/* This query is doing two unrelated things:
			 * 1) Collect the message contents and scores so that they can
			 *    be accessed later for the translations we found.
			 * 2) Build the query string for the query that fetches the translations.
			 */
			$contents = $scores = $terms = [];
			$resultset = $this->server->getType()->search( $query );

			if ( count( $resultset ) === 0 ) {
				return [];
			}

			foreach ( $resultset->getResults() as $result ) {
				$data = $result->getData();
				$score = $result->getScore();
				if ( $score < $cutoff ) {
					break;
				}

				$sourceId = preg_replace( '~/[^/]+$~', '', $result->getId() );
				$contents[$sourceId] = $data['content'];
				$scores[$sourceId] = $score;
				$terms[] = "$sourceId/$targetLanguage";
			}

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

					return ( $a['quality'] < $b['quality'] ) ? 1 : - 1;
				} );
			}
		} finally {
			$connection->setTimeout( $oldTimeout );
		}
		return $suggestions;
	}
}
