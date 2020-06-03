<?php
/**
 * Api module for querying message aids.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Api module for querying message aids.
 *
 * @ingroup API TranslateAPI
 */
class ApiTranslationAids extends ApiBase {
	public function execute() {
		$params = $this->extractRequestParams();

		$title = Title::newFromText( $params['title'] );
		if ( !$title ) {
			$this->dieWithError( [ 'apierror-invalidtitle', wfEscapeWikiText( $params['title'] ) ] );
		}

		$handle = new MessageHandle( $title );
		if ( !$handle->isValid() ) {
			$this->dieWithError( 'apierror-translate-nomessagefortitle', 'nomessagefortitle' );
		}

		if ( (string)$params['group'] !== '' ) {
			$group = MessageGroups::getGroup( $params['group'] );
		} else {
			$group = $handle->getGroup();
		}

		if ( !$group ) {
			$this->dieWithError( 'apierror-translate-invalidgroup', 'invalidgroup' );
		}

		$data = [];
		$times = [];

		$props = $params['prop'];
		$aggregator = new QueryAggregator();

		// Figure out the intersection of supported and requested aids
		$types = $group->getTranslationAids();
		$props = array_intersect( $props, array_keys( $types ) );

		$result = $this->getResult();

		// Create list of aids, populate web services queries
		$aids = [];

		$dataProvider = new TranslationAidDataProvider( $handle );
		foreach ( $props as $type ) {
			// Do not proceed if translation aid is not supported for this message group
			if ( !isset( $types[$type] ) ) {
				$types[$type] = 'UnsupportedTranslationAid';
			}

			$class = $types[$type];
			$obj = new $class( $group, $handle, $this, $dataProvider );

			if ( $obj instanceof QueryAggregatorAware ) {
				$obj->setQueryAggregator( $aggregator );
				try {
					$obj->populateQueries();
				} catch ( TranslationHelperException $e ) {
					$data[$type] = [ 'error' => $e->getMessage() ];
					// Prevent processing this aids and thus overwriting our error
					continue;
				}
			}

			$aids[$type] = $obj;
		}

		// Execute all web service queries asynchronously to save time
		$start = microtime( true );
		$aggregator->run();
		$times['query_aggregator'] = round( microtime( true ) - $start, 3 );

		// Construct the result data structure
		foreach ( $aids as $type => $obj ) {
			$start = microtime( true );

			try {
				$aid = $obj->getData();
			} catch ( TranslationHelperException $e ) {
				$aid = [ 'error' => $e->getMessage() ];
			}

			if ( isset( $aid['**'] ) ) {
				$result->setIndexedTagName( $aid, $aid['**'] );
				unset( $aid['**'] );
			}

			$data[$type] = $aid;
			$times[$type] = round( microtime( true ) - $start, 3 );
		}

		$result->addValue( null, 'helpers', $data );
		$result->addValue( null, 'times', $times );
	}

	public function getAllowedParams() {
		$props = array_keys( TranslationAid::getTypes() );
		Hooks::run( 'TranslateTranslationAids', [ &$props ] );

		return [
			'title' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
			'group' => [
				ApiBase::PARAM_TYPE => 'string',
			],
			'prop' => [
				ApiBase::PARAM_DFLT => implode( '|', $props ),
				ApiBase::PARAM_TYPE => $props,
				ApiBase::PARAM_ISMULTI => true,
			],
		];
	}

	protected function getExamplesMessages() {
		return [
			'action=translationaids&title=MediaWiki:January/fi'
				=> 'apihelp-translationaids-example-1',
		];
	}
}
