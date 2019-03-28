<?php
/**
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

/**
 * A generic regex validator and insertable that can be reused by other classes.
 * @since 2019.03
 */
class InsertableGenericRegexValidator extends \Validator implements \InsertablesSuggester {
	public function __construct( array $config ) {
		parent::__construct( $config );

		// TODO: MV Pre / Post, use specific params like regex rather than just params.
		if ( !is_string( $this->params ) ) {
			throw new \RuntimeException( "Regex Validator expects the regex to be passed as 'params'." );
		}
	}

	public function validate( $messages, $code, &$warnings, &$errors ) {
		if ( $this->isEnforced() ) {
			$this->parameterCheck( $messages, $code, $errors, $this->params );
		} else {
			$this->parameterCheck( $messages, $code, $warnings, $this->params );
		}
	}

	public function getInsertables( $text ) {
		// TODO: MV Pre / Post
		$insertables = [];

		$matches = [];
		preg_match_all(
			$this->params,
			$text,
			$matches,
			PREG_SET_ORDER
		);
		$new = array_map( function ( $match ) {
			// TODO: MV Pre / Post
			return new Insertable( $match[0], $match[0] );
		}, $matches );
		$insertables = array_merge( $insertables, $new );

		return $insertables;
	}
}
