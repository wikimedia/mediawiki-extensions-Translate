<?php

/**
 * @covers ElasticSuggestionSearcherFactory
 */
class ElasticSuggestionSearcherFactoryTest extends MediaWikiTestCase {

	public function provideData() {
		return [
			'empty' => [
				[],
				ElasticClassicSuggestionSearcher::class
			],
			'explicit' => [
				[ 'suggestion_searcher_profile' => 'classic' ],
				ElasticClassicSuggestionSearcher::class
			],
			'named profile' => [
				[
					'suggestion_searcher_profile' => 'classic2',
					'suggestion_searcher_profiles' => [
						'classic2' => [
							'type' => 'classic',
						]
					],
				],
				ElasticClassicSuggestionSearcher::class
			],
			'explicit rescoring' => [
				[ 'suggestion_searcher_profile' => 'rescoring' ],
				ElasticRescoringSuggestionSearcher::class
			],
			'named rescoring profile' => [
				[
					'suggestion_searcher_profile' => 'my_profile',
					'suggestion_searcher_profiles' => [
						'my_profile' => [
							'type' => 'rescoring',
							'params' => [
								'retrieval_size' => 1000,
								'rescore_window' => 1000,
							]
						]
					]
				],
				ElasticRescoringSuggestionSearcher::class
			],
			'bad explicit type' => [
				[
					'suggestion_searcher_profile' => 'unknown',
				],
				RuntimeException::class
			],
			'bad type in profile' => [
				[
					'suggestion_searcher_profile' => 'broken_profile',
					'suggestion_searcher_profiles' => [
						'broken_profile' => [
							'type' => 'unknown'
						]
					]
				],
				RuntimeException::class
			],
			'uri override' => [
				[
					'suggestion_searcher_profiles' => [
						'experimental' => [
							'type' => 'rescoring'
						]
					]
				],
				ElasticRescoringSuggestionSearcher::class,
				'experimental'
			],
			'bad uri override' => [
				[
					'suggestion_searcher_profiles' => [
						'experimental' => [
							'type' => 'rescoring'
						]
					]
				],
				RuntimeException::class,
				'experimental2'
			],
		];
	}

	/**
	 * @param $config
	 * @param $expectedClass
	 * @dataProvider provideData
	 * @throws MWEXception
	 */
	public function test( array $config, $expectedClass, $uriParam = null ) {
		$config += [ 'class' => ElasticSearchTTMServer::class ];
		$server = TTMServer::factory( $config );
		$this->assertInstanceOf( ElasticSearchTTMServer::class, $server );

		$request = null;
		if ( $uriParam !== null ) {
			$request = new FauxRequest(
				[ ElasticSuggestionSearcherFactory::URI_PARAM_OVERRIDE => $uriParam ] );
		}
		/**
		 * @var ElasticSearchTTMServer $server
		 */

		if ( $expectedClass !== RuntimeException::class ) {
			$searcher = ElasticSuggestionSearcherFactory::getSuggestionSearcher( $server, $request );
			$this->assertInstanceOf( $expectedClass, $searcher );
		} else {
			try {
				ElasticSuggestionSearcherFactory::getSuggestionSearcher( $server, $request );
				$this->fail( "$expectedClass was expected" );
			} catch ( Exception $e ) {
				$this->assertInstanceOf( $expectedClass, $e );
			}
		}
	}
}
