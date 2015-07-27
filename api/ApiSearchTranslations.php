<?php
/**
 * API module for search translations
 * @since 2015.07
 */
class ApiSearchTranslations extends ApiBase {

	public function execute() {
		global $wgTranslateTranslationServices;
		$params = $this->extractRequestParams();

		$config = $wgTranslateTranslationServices[$params['service']];
		$server = TTMServer::factory( $config );

		$opts = new FormOptions();
		foreach ( $params as $param => $value ) {
			$opts->add( $param, $value );
		}

		$result = $this->getResult();

		if ( $params['filter'] !== '' ) {
			$documents = array();
			$total = $start = 0;
			$offset = $params['offset'];
			$limit = $params['limit'];
			$size = 1000;

			$opts->setValue( 'limit', $size );
			$opts->setValue( 'language', $params['sourcelanguage'] );
			do {
				$opts->setValue( 'offset', $start );
				$searchResults = $server->search(
					$params['query'],
					$opts,
					array( '', '' )
				);

				list( $results, $offsets ) = $this->extractMessages(
					$searchResults,
					$offset,
					$limit
				);
				$offset = $offsets['start'] + $offsets['count'] - $offsets['total'];
				$limit = $limit - $offsets['count'];
				$total = $total + $offsets['total'];

				$documents = array_merge( $documents, $results );
				$start = $start + $size;
			} while (
				$offsets['start'] + $offsets['count'] >= $offsets['total'] &&
				$searchResults->getTotalHits() > $start
			);
		} else {
			$searchResults = $server->search(
				$params['query'],
				$opts,
				array( '', '' )
			);
			$documents = $server->getDocuments( $searchResults );
			$total = $server->getTotalHits( $searchResults );
		}
		$result->addValue( array( 'search', 'metadata' ), 'total', $total );
		$result->addValue( 'search', 'translations', $documents );
	}

	/*
	 * Extract messages from the resultset and build message definitions.
	 * Create a message collection from the definitions in the target language.
	 * Filter the message collection to get filtered messages.
	 * Slice messages according to limit and offset given.
	 */
	protected function extractMessages( $resultset ) {
		global $wgTranslateTranslationServices;
		$params = $this->extractRequestParams();

		$config = $wgTranslateTranslationServices[$params['service']];
		$server = TTMServer::factory( $config );

		$messages = $documents = $ret = array();
		foreach ( $resultset->getResults() as $document ) {
			$data = $document->getData();
			if ( !$server->isLocalSuggestion( $data ) ) {
				continue;
			}

			$title = Title::newFromText( $data['localid'] );
			if ( !$title ) {
				continue;
			}

			$handle = new MessageHandle( $title );
			if ( !$handle->isValid() ) {
				continue;
			}

			$key = $title->getNamespace() . ':' . $title->getDBKey();
			$messages[$key] = $data['content'];
		}

		$language = $this->getLanguage()->getCode();
		$definitions = new MessageDefinitions( $messages );
		$collection = MessageCollection::newFromDefinitions( $definitions, $language );

		$filter = $params['filter'];
		if ( $filter[0] === '!' ) {
			$collection->filter( substr( $filter, 1 ), true );
		} else {
			$collection->filter( $filter, false );
		}

		$total = count( $collection );
		$offset = $collection->slice( $offset, $limit );
		$left = count( $collection );

		$offsets = array(
			'start' => $offset[2],
			'count' => $left,
			'total' => $total,
		);

		$collection->loadTranslations();
		foreach ( $collection->keys() as $mkey => $title ) {
			$documents[$mkey]['definition'] = $collection[$mkey]->definition();
			$documents[$mkey]['translation'] = $collection[$mkey]->translation();
			$output = explode( '/', $title->getPrefixedText() );
			$documents[$mkey]['localid'] = $output[0];
			$handle = new MessageHandle( $title );
			$documents[$mkey]['uri'] = $handle->getTitle()->getCanonicalUrl();
			$ret[] = $documents[$mkey];
		}

		return array( $ret, $offsets );
	}

	public function getAllowedParams() {
		global $wgTranslateTranslationServices, $wgLanguageCode;

		return array(
			'service' => array(
				ApiBase::PARAM_TYPE => array_keys( $wgTranslateTranslationServices ),
				ApiBase::PARAM_DFLT => 'TTMServer',
			),
			'query' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
			'language' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '',
			),
			'group' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '',
			),
			'sourcelanguage' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => $wgLanguageCode,
			),
			'filter' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '',
			),
			'offset' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => 0,
			),
			'limit' => array(
				ApiBase::PARAM_DFLT => 25,
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_MIN => 1,
				ApiBase::PARAM_MAX => ApiBase::LIMIT_SML1,
				ApiBase::PARAM_MAX2 => ApiBase::LIMIT_SML2
			),
		);
	}

	// Get examples
	public function getExamples() {
		return array(
			'api.php?action=searchtranslations&language=fr&query=aide',
			'api.php?action=searchtranslations&language=fr&query=edit&filter=translated',
		);
	}

	// Get examples messages
	protected function getExamplesMessages() {
		return array(
			'action=searchtranslations&language=fr&query=aide'
				=> 'apihelp-searchtranslations-example-1',
			'action=searchtranslations&language=fr&query=edit&filter=translated'
				=> 'apihelp-searchtranslations-example-2',
		);
	}
}
