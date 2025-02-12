<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageLoading;

use MediaWiki\Api\ApiBase;
use MediaWiki\Api\ApiQuery;
use MediaWiki\Api\ApiQueryBase;
use MediaWiki\Api\ApiResult;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Title\Title;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * Api module for querying message translations.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup API TranslateAPI
 */
class QueryMessageTranslationsActionApi extends ApiQueryBase {
	public function __construct( ApiQuery $query, string $moduleName ) {
		parent::__construct( $query, $moduleName, 'mt' );
	}

	/** @inheritDoc */
	public function getCacheMode( $params ) {
		return 'public';
	}

	public function execute(): void {
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
		$pageInfo = Utilities::getTranslations( $handle );

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

	protected function getAllowedParams(): array {
		return [
			'title' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
			'offset' => [
				ParamValidator::PARAM_DEFAULT => 0,
				ParamValidator::PARAM_TYPE => 'integer',
				ApiBase::PARAM_HELP_MSG => 'api-help-param-continue',
			],
		];
	}

	protected function getExamplesMessages(): array {
		return [
			'action=query&meta=messagetranslations&mttitle=MediaWiki:January'
				=> 'apihelp-query+messagetranslations-example-1',
		];
	}
}
