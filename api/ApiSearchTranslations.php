<?php
/**
 * API module for search translations
 * @since 2015-06-21
 */
class ApiSearchTranslations extends ApiBase {

	public function execute() {
		$params = $this->extractRequestParams();

		$server = TTMServer::primary();
		$searchresult = $server->doSearch(
			$params['query'],
			$params
		);

		$result = $this->getResult();
		$documents = $server->getDocuments( $searchresult );

		foreach ( $documents as $document ) {
			$vals = array(
				'content' => $document['content'],
				'localid' => $document['localid'],
				'uri' => $document['uri'],
				'language' => $document['language'],
				'group' => $document['group']
			);
			$result->addValue( 'search', null, $vals );
		}
	}

	public function getAllowedParams() {

		return array(
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

	// Describe the parameters
	public function getParamDescription() {
		return array(
			'query' => 'The string to search for',
			'language' => 'A language code to search string for',
			'group' => 'A group id to search string in',
			'offset' => 'offset',
			'limit' => 'size of the result',
		);
	}

	// Description
	public function getDescription() {
		return 'Search translations';
	}

	// Get examples
	public function getExamples() {
		return array(
			'api.php?action=searchtranslations&language=fr&query=Help',
		);
	}
}
