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

		$searchResults = $server->search(
			$params['query'],
			$opts,
			array( '', '' )
		);

		$result = $this->getResult();

		if ( $params['filter'] === 'translated' ) {
			$sourceLanguage = $params['sourcelanguage'];
			$language = $params['language'];
			if ( $language === '' ) {
				$language = $this->getLanguage()->getCode();
			}

			$documents = $this->extractMessages( $searchResults );
			$result->addValue(
				'search',
				'translations from ' . $sourceLanguage . ' to ' . $language,
				$documents
			);
		} else {
			$documents = $server->getDocuments( $searchResults );
			$result->addValue( 'search', 'translations', $documents );
		}
	}

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

		$definitions = new MessageDefinitions( $messages );
		$language = $params['language'];
		if ( $language === '' ) {
			$language = $this->getLanguage()->getCode();
		}
		$collection = MessageCollection::newFromDefinitions( $definitions, $language );
		$collection->filter( 'translated', false );
		$offset = $collection->slice(
			$params['offset'],
			$params['limit']
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

		return $ret;
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
		);
	}

	// Get examples messages
	protected function getExamplesMessages() {
		return array(
			'action=searchtranslations&language=fr&query=aide'
				=> 'apihelp-searchtranslations-example-1',
		);
	}
}
