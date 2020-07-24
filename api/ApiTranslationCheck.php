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

		$validationResult = $this->validateTranslation( $handle, $translation );

		$validationOutput = [ 'errors' => [], 'warnings' => [] ];
		if ( $validationResult ) {
			$validationOutput['errors'] =
				$validationResult->getDescriptiveErrors( $this->getContext() );
			$validationOutput['warnings'] =
				$validationResult->getDescriptiveWarnings( $this->getContext() );
		}

		$this->getResult()->addValue( null, 'validation', $validationOutput );
	}

	private function validateTranslation( MessageHandle $handle, $translation ) {
		if ( $handle->isDoc() || !$handle->isValid() ) {
			return null;
		}

		$messageValidator = $handle->getGroup()->getValidator();
		if ( !$messageValidator ) {
			return null;
		}

		$definition = $this->getDefinition( $handle );
		$message = new FatMessage( $handle->getKey(), $definition );
		$message->setTranslation( $translation );

		$validationResult = $messageValidator->validateMessage( $message, $handle->getCode() );

		return $validationResult;
	}

	private function getDefinition( MessageHandle $handle ) {
		$group = $handle->getGroup();
		if ( is_callable( [ $group, 'getMessageContent' ] ) ) {
			// @phan-suppress-next-line PhanUndeclaredMethod
			return $group->getMessageContent( $handle );
		} else {
			return $group->getMessage( $handle->getKey(), $group->getSourceLanguage() );
		}
	}

	protected function getAllowedParams() {
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
