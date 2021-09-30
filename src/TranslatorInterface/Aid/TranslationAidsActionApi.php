<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Aid;

use ApiBase;
use MediaWiki\Extension\Translate\TranslatorInterface\TranslationHelperException;
use MediaWiki\Logger\LoggerFactory;
use MessageGroups;
use MessageHandle;
use QueryAggregator;
use QueryAggregatorAware;
use Title;

/**
 * Api module for querying message aids.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup API TranslateAPI
 */
class TranslationAidsActionApi extends ApiBase {
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
		$types = TranslationAid::getTypes();
		$props = array_intersect( $props, array_keys( $types ) );

		$result = $this->getResult();

		// Create list of aids, populate web services queries
		/** @var TranslationAid[] $aids */
		$aids = [];

		$dataProvider = new TranslationAidDataProvider( $handle );

		// Message definition should not be empty, but sometimes is.
		// See: https://phabricator.wikimedia.org/T285830
		// Identify and log.
		if ( !$dataProvider->hasDefinition() ) {
			LoggerFactory::getInstance( 'Translate' )->warning(
				'Message definition is empty! Title: {title}, group: {group}, key: {key}',
				[
					'title' => $handle->getTitle()->getPrefixedText(),
					'group' => $group->getId(),
					'key' => $handle->getKey()
				]
			);
		}

		foreach ( $props as $type ) {
			// Do not proceed if translation aid is not supported for this message group
			if ( !isset( $types[$type] ) ) {
				$types[$type] = UnsupportedTranslationAid::class;
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

	protected function getAllowedParams(): array {
		$props = array_keys( TranslationAid::getTypes() );

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
