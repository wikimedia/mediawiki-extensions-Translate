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
	private const GROUPS = 'groups';
	private const MESSAGES = 'messages';

	public function __construct( ApiMain $mainModule, $moduleName, EntitySearch $entitySearch ) {
		parent::__construct( $mainModule, $moduleName );
		$this->entitySearch = $entitySearch;
	}

	public function execute() {
		$query = $this->getParameter( 'query' );
		$maxResults = $this->getParameter( 'limit' );
		$entityTypes = $this->getParameter( 'entitytype' );

		$searchResults = [];
		$remainingResults = $maxResults;

		if ( in_array( self::GROUPS, $entityTypes ) ) {
			$searchResults[ self::GROUPS ] = $this->entitySearch
				->searchStaticMessageGroups( $query, $maxResults );
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
				ParamValidator::PARAM_REQUIRED => true
			],
			'limit' => [
				ParamValidator::PARAM_TYPE => 'limit',
				ParamValidator::PARAM_DEFAULT => 10,
				NumericDef::PARAM_MAX => ApiBase::LIMIT_SML1
			],
		];
	}

	public function isInternal(): bool {
		// Temporarily until stable
		return true;
	}
}
