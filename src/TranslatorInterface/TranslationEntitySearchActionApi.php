<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

use ApiBase;
use ApiMain;
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\ParamValidator\TypeDef\NumericDef;

/**
 * Action API module for searching message groups and message keys.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
class TranslationEntitySearchActionApi extends ApiBase {
	/** @var EntitySearch */
	private $entitySearch;

	public function __construct( ApiMain $mainModule, $moduleName, EntitySearch $entitySearch ) {
		parent::__construct( $mainModule, $moduleName );
		$this->entitySearch = $entitySearch;
	}

	public function execute() {
		$query = $this->getParameter( 'query' );
		$maxResults = $this->getParameter( 'limit' );

		$searchResults = [];
		$searchResults[ 'groups' ] = $this->entitySearch->searchStaticMessageGroups( $query, $maxResults );

		$remainingResults = count( $searchResults[ 'groups' ] ) - $maxResults;
		if ( $remainingResults > 0 ) {
			$searchResults[ 'messages' ] = $this->entitySearch->searchMessages( $query, $remainingResults );
		}

		$this->getResult()->addValue( null, $this->getModuleName(), $searchResults );
	}

	protected function getAllowedParams(): array {
		return [
			'query' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true
			],
			'limit' => [
				ParamValidator::PARAM_TYPE => 'limit',
				ParamValidator::PARAM_DEFAULT => 10,
				NumericDef::PARAM_MAX => ApiBase::LIMIT_SML1,
				ParamValidator::PARAM_REQUIRED => false,
			],
		];
	}

	public function isInternal(): bool {
		// Temporarily until stable
		return true;
	}
}
