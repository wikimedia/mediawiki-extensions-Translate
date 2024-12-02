<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

use MediaWiki\Api\ApiBase;
use MediaWiki\Api\ApiMain;
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\ParamValidator\TypeDef\NumericDef;

/**
 * Action API module for searching message groups and message keys.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
class TranslationEntitySearchActionApi extends ApiBase {
	private EntitySearch $entitySearch;
	private const GROUPS = 'groups';
	private const MESSAGES = 'messages';

	public function __construct(
		ApiMain $mainModule,
		string $moduleName,
		EntitySearch $entitySearch
	) {
		parent::__construct( $mainModule, $moduleName );
		$this->entitySearch = $entitySearch;
	}

	public function execute() {
		$query = $this->getParameter( 'query' );
		$maxResults = $this->getParameter( 'limit' );
		$entityTypes = $this->getParameter( 'entitytype' );
		$groupTypeFilter = $this->getParameter( 'grouptypes' );

		$searchResults = [];
		$remainingResults = $maxResults;

		if ( in_array( self::GROUPS, $entityTypes ) ) {
			$searchResults[ self::GROUPS ] = $this->entitySearch
				->searchStaticMessageGroups( $query, $maxResults, $groupTypeFilter );
			$remainingResults = $maxResults - count( $searchResults[ self::GROUPS ] );
		}

		if ( in_array( self::MESSAGES, $entityTypes ) && $remainingResults > 0 ) {
			$searchResults[ self::MESSAGES ] = $this->entitySearch
				->searchMessages( $query, $remainingResults );
		}

		$this->getResult()->addValue( null, $this->getModuleName(), $searchResults );
	}

	protected function getAllowedParams(): array {
		return [
			'entitytype' => [
				ParamValidator::PARAM_TYPE => [ self::GROUPS, self::MESSAGES ],
				ParamValidator::PARAM_ISMULTI => true,
				ParamValidator::PARAM_DEFAULT => implode( '|', [ self::GROUPS, self::MESSAGES ] )
			],
			'query' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_DEFAULT => ''
			],
			'limit' => [
				ParamValidator::PARAM_TYPE => 'limit',
				ParamValidator::PARAM_DEFAULT => 10,
				NumericDef::PARAM_MAX => ApiBase::LIMIT_SML1
			],
			'grouptypes' => [
				ParamValidator::PARAM_ISMULTI => true,
				ParamValidator::PARAM_DEFAULT => [],
				ParamValidator::PARAM_TYPE => array_keys( $this->entitySearch->getGroupTypes() )
			]
		];
	}

	public function isInternal(): bool {
		// Temporarily until stable
		return true;
	}
}
