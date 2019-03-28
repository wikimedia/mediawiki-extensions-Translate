<?php
/**
 * @since 2017.10
 * @license GPL-2.0-or-later
 */
class ApiTranslationCheck extends ApiBase {
	public function execute() {
		$params = $this->extractRequestParams();

		$title = Title::newFromText( $params[ 'title' ] );
		if ( !$title ) {
			$this->dieWithError( [ 'apierror-invalidtitle', wfEscapeWikiText( $params['title'] ) ] );
		}
		$handle = new MessageHandle( $title );
		$translation = $params[ 'translation' ];

		$checkResults = $this->getWarnings( $handle, $translation );
		$validationOutput = $this->getValidationOutput( $handle, $translation );

		// To maintain backward compatibility with previous MessageChecker framework.
		$checkResults = array_merge( $checkResults, $validationOutput['warnings'] );

		foreach ( $checkResults as $item ) {
			$key = array_shift( $item );
			$msg = $this->getContext()->msg( $key, $item )->parse();
			$this->getResult()->addValue( 'warnings', null, $msg );
		}

		foreach ( $validationOutput['errors'] as $item ) {
			$key = array_shift( $item );
			$msg = $this->getContext()->msg( $key, $item )->parse();
			$this->getResult()->addValue( 'errors', null, $msg );
		}
	}

	public function getValidationOutput( MessageHandle $handle, $translation ) {
		if ( $translation === '' ) {
			return [];
		}

		if ( $handle->isDoc() || !$handle->isValid() ) {
			return [];
		}

		$validator = $handle->getGroup()->getValidator();
		if ( !$validator ) {
			return [];
		}

		$definition = $this->getDefinition( $handle );
		$message = new FatMessage( $handle->getKey(), $definition );
		$message->setTranslation( $translation );

		$validationOutput = $validator->validateMessage( $message, $handle->getCode() );

		return $validationOutput;
	}

	public function getWarnings( MessageHandle $handle, $translation ) {
		if ( $translation === '' ) {
			return [];
		}

		if ( $handle->isDoc() || !$handle->isValid() ) {
			return [];
		}

		$checker = $handle->getGroup()->getChecker();
		if ( !$checker ) {
			return [];
		}

		$definition = $this->getDefinition( $handle );
		$message = new FatMessage( $handle->getKey(), $definition );
		$message->setTranslation( $translation );

		$checks = $checker->checkMessage( $message, $handle->getCode() );
		if ( $checks === [] ) {
			return [];
		}

		return $checks;
	}

	private function getDefinition( MessageHandle $handle ) {
		$group = $handle->getGroup();
		if ( method_exists( $group, 'getMessageContent' ) ) {
			return $group->getMessageContent( $handle );
		} else {
			return $group->getMessage( $handle->getKey(), $group->getSourceLanguage() );
		}
	}

	public function getAllowedParams() {
		return [
			'title' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
			'translation' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
		];
	}

	public function isInternal() {
		return true;
	}
}
