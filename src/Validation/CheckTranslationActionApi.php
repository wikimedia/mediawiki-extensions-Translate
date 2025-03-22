<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation;

use MediaWiki\Api\ApiBase;
use MediaWiki\Extension\Translate\MessageLoading\FatMessage;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Title\Title;
use Wikimedia\ParamValidator\ParamValidator;

/** @license GPL-2.0-or-later */
class CheckTranslationActionApi extends ApiBase {
	public function execute(): void {
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

	private function validateTranslation( MessageHandle $handle, string $translation ): ?ValidationResult {
		if ( $handle->isDoc() || !$handle->isValid() ) {
			return null;
		}

		$messageValidator = $handle->getGroup()->getValidator();
		if ( !$messageValidator ) {
			return null;
		}

		$definition = $this->getDefinition( $handle );
		if ( $definition === null ) {
			// Very unlikely to happen since the handle is already found to be valid
			return null;
		}

		$message = new FatMessage( $handle->getKey(), $definition );
		$message->setTranslation( $translation );

		$validationResult = $messageValidator->validateMessage( $message, $handle->getCode() );

		return $validationResult;
	}

	private function getDefinition( MessageHandle $handle ): ?string {
		$group = $handle->getGroup();
		if ( !$group ) {
			return null;
		}
		if ( method_exists( $group, 'getMessageContent' ) ) {
			// @phan-suppress-next-line PhanUndeclaredMethod
			return $group->getMessageContent( $handle );
		} else {
			return $group->getMessage( $handle->getKey(), $group->getSourceLanguage() );
		}
	}

	protected function getAllowedParams(): array {
		return [
			'title' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
			'translation' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
		];
	}

	public function isInternal(): bool {
		return true;
	}
}
