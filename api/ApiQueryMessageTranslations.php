<?php
/**
 * Api module for querying message translations.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Api module for querying message translations.
 *
 * @ingroup API TranslateAPI
 */
class ApiQueryMessageTranslations extends ApiQueryBase {
	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'mt' );
	}

	public function getCacheMode( $params ) {
		return 'public';
	}

	/**
	 * Returns all translations of a given message.
	 * @param MessageHandle $handle Language code is ignored.
	 * @return array[]
	 * @since 2012-12-18
	 */
	public static function getTranslations( MessageHandle $handle ) {
		$namespace = $handle->getTitle()->getNamespace();
		$base = $handle->getKey();

		$dbr = wfGetDB( DB_REPLICA );

		$res = $dbr->select( 'page',
			[ 'page_namespace', 'page_title' ],
			[
				'page_namespace' => $namespace,
				'page_title ' . $dbr->buildLike( "$base/", $dbr->anyString() ),
			],
			__METHOD__,
			[
				'ORDER BY' => 'page_title',
				'USE INDEX' => 'name_title',
			]
		);

		$titles = [];
		foreach ( $res as $row ) {
			$titles[] = $row->page_title;
		}

		if ( $titles === [] ) {
			return [];
		}

		$pageInfo = TranslateUtils::getContents( $titles, $namespace );

		return $pageInfo;
	}

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

		$namespace = $title->getNamespace();
		$pageInfo = self::getTranslations( $handle );

		$result = $this->getResult();
		$count = 0;

		foreach ( $pageInfo as $key => $info ) {
			if ( ++$count <= $params['offset'] ) {
				continue;
			}

			$tTitle = Title::makeTitle( $namespace, $key );
			$tHandle = new MessageHandle( $tTitle );

			$data = [
				'title' => $tTitle->getPrefixedText(),
				'language' => $tHandle->getCode(),
				'lasttranslator' => $info[1],
			];

			$fuzzy = MessageHandle::hasFuzzyString( $info[0] ) || $tHandle->isFuzzy();

			if ( $fuzzy ) {
				$data['fuzzy'] = 'fuzzy';
			}

			$translation = str_replace( TRANSLATE_FUZZY, '', $info[0] );
			ApiResult::setContentValue( $data, 'translation', $translation );

			$fit = $result->addValue( [ 'query', $this->getModuleName() ], null, $data );
			if ( !$fit ) {
				$this->setContinueEnumParameter( 'offset', $count );
				break;
			}
		}

		$result->addIndexedTagName( [ 'query', $this->getModuleName() ], 'message' );
	}

	public function getAllowedParams() {
		return [
			'title' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
			'offset' => [
				ApiBase::PARAM_DFLT => 0,
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_HELP_MSG => 'api-help-param-continue',
			],
		];
	}

	protected function getExamplesMessages() {
		return [
			'action=query&meta=messagetranslations&mttitle=MediaWiki:January'
				=> 'apihelp-query+messagetranslations-example-1',
		];
	}
}
