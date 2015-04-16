<?php
/**
 * Api module for querying message translations.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2011-2013, Niklas Laxström
 * @license GPL-2.0+
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

		$dbr = wfGetDB( DB_SLAVE );

		$res = $dbr->select( 'page',
			array( 'page_namespace', 'page_title' ),
			array(
				'page_namespace' => $namespace,
				'page_title ' . $dbr->buildLike( "$base/", $dbr->anyString() ),
			),
			__METHOD__,
			array(
				'ORDER BY' => 'page_title',
				'USE INDEX' => 'name_title',
			)
		);

		$titles = array();
		foreach ( $res as $row ) {
			$titles[] = $row->page_title;
		}

		if ( $titles === array() ) {
			return array();
		}

		$pageInfo = TranslateUtils::getContents( $titles, $namespace );

		return $pageInfo;
	}

	public function execute() {
		$params = $this->extractRequestParams();

		$title = Title::newFromText( $params['title'] );
		if ( !$title ) {
			$this->dieUsage( 'Invalid title', 'invalidtitle' );
		}

		$handle = new MessageHandle( $title );
		if ( !$handle->isValid() ) {
			$this->dieUsage(
				'Title does not correspond to a translatable message',
				'nomessagefortitle'
			);
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

			$data = array(
				'title' => $tTitle->getPrefixedText(),
				'language' => $tHandle->getCode(),
				'lasttranslator' => $info[1],
			);

			$fuzzy = MessageHandle::hasFuzzyString( $info[0] ) || $tHandle->isFuzzy();

			if ( $fuzzy ) {
				$data['fuzzy'] = 'fuzzy';
			}

			$translation = str_replace( TRANSLATE_FUZZY, '', $info[0] );
			if ( defined( 'ApiResult::META_CONTENT' ) ) {
				ApiResult::setContentValue( $data, 'translation', $translation );
			} else {
				ApiResult::setContent( $data, $translation );
			}

			$fit = $result->addValue( array( 'query', $this->getModuleName() ), null, $data );
			if ( !$fit ) {
				$this->setContinueEnumParameter( 'offset', $count );
				break;
			}
		}

		if ( defined( 'ApiResult::META_CONTENT' ) ) {
			$result->addIndexedTagName( array( 'query', $this->getModuleName() ), 'message' );
		} else {
			$result->setIndexedTagName_internal( array( 'query', $this->getModuleName() ), 'message' );
		}
	}

	public function getAllowedParams() {
		return array(
			'title' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
			'offset' => array(
				ApiBase::PARAM_DFLT => 0,
				ApiBase::PARAM_TYPE => 'integer',
				/** @todo Once support for MediaWiki < 1.25 is dropped, just
				 * use ApiBase::PARAM_HELP_MSG directly
				 */
				defined( 'ApiBase::PARAM_HELP_MSG' ) ? ApiBase::PARAM_HELP_MSG : '' =>
					'api-help-param-continue',
			),
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		return array(
			'title' => 'Full title of a known message',
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return 'Query all translations for a single message';
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	protected function getExamples() {
		return array(
			"api.php?action=query&meta=messagetranslations&mttitle=MediaWiki:January " .
				"List of translations in the wiki for MediaWiki:January",
		);
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=query&meta=messagetranslations&mttitle=MediaWiki:January'
				=> 'apihelp-query+messagetranslations-example-1',
		);
	}
}
