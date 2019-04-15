<?php
use MediaWiki\Extensions\Translate\MessageValidator\ValidationResult;

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
		$validationResult = $this->validateTranslation( $handle, $translation );

		if ( $validationResult ) {
			// Added for backward compatibility with previous MessageChecker framework.
			// TODO: MV - Remove in the future.
			$validationResult->setWarnings( array_merge( $checkResults,
				$validationResult->getWarnings() ) );

			$this->getResult()->addValue( null, 'validation', [
				'errors' => $validationResult->getDescriptiveErrors( $this->getContext() ),
				'warnings' => $validationResult->getDescriptiveWarnings( $this->getContext() ),
			] );
		} else {
			// To maintain backward compatibility with previous MessageChecker framework.
			// TODO: MV - Remove in the future.
			$this->getResult()->addValue( null, 'validation', [
				'errors' => [],
				'warnings' => ValidationResult::expandMessages( $this->getContext(), $checkResults )
			] );
		}
	}

	public function validateTranslation( MessageHandle $handle, $translation ) {
		if ( $translation === '' ) {
			return null;
		}

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
