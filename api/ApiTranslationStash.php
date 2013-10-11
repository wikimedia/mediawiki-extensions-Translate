<?php
/**
 * WebAPI module for stashing translations.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL2+
 */

/**
 * WebAPI module for storing translations for users who are in a sandbox.
 * Access is controlled by hooks in TranslateSandbox class.
 * @since 2013.06
 */
class ApiTranslationStash extends ApiBase {
	public function execute() {
		$params = $this->extractRequestParams();
		$action = $params['subaction'];
		$stash = new TranslationStashStorage( wfGetDB( DB_MASTER ) );
		if ( $action === 'add' ) {
			if ( !isset( $params['title'] ) ) {
				$this->dieUsageMsg( array( 'missingparam', 'title' ) );
			}
			if ( !isset( $params['value'] ) ) {
				$this->dieUsageMsg( array( 'missingparam', 'value' ) );
			}
			$translation = new StashedTranslation(
				$this->getUser(),
				Title::newFromText( $params['title'] ),
				$params['value'],
				FormatJson::decode( $params['metadata'], true )
			);
			$stash->addTranslation( $translation );
		}

		if ( $action === 'query' ) {
			$translations = $stash->getTranslations( $this->getUser() );
			foreach( $translations as $translation ) {
				$translation = array(
					'title' => $translation->getTitle()->getPrefixedDBKey(),
					'value' => $translation->getValue(),
					'metadata' => $translation->getMetadata(),
				);
				$output['translations'][] = $translation;
			}
		}
		// If we got this far, nothing has failed
		$output['result'] = 'ok';
		$this->getResult()->addValue( null, $this->getModuleName(), $output );
	}


	public function isWriteMode() {
		return true;
	}

	public function needsToken() {
		return true;
	}

	public function getTokenSalt() {
		return 'translationstash';
	}

	public static function getToken() {
		$user = RequestContext::getMain()->getUser();

		return $user->getEditToken( 'translationstash' );
	}

	public static function injectTokenFunction( &$list ) {
		$list['translationstash'] = array( __CLASS__, 'getToken' );

		return true;
	}

	public function getAllowedParams() {
		return array(
			'subaction' => array(
				ApiBase::PARAM_TYPE => array( 'add', 'query' ),
				ApiBase::PARAM_REQUIRED => true,
			),
			'title' => array(
				ApiBase::PARAM_TYPE => 'string',
			),
			'value' => array(
				ApiBase::PARAM_TYPE => 'string',
			),
			'metadata' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => null,
			),
			'token' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
		);
	}

	public function getParamDescription() {
		$action = TranslateUtils::getTokenAction( 'edit' );

		return array(
			'subaction' => 'Action',
			'title' => 'Title of the translation unit page',
			'value' => 'Translation',
			'metadata' => 'Json object',
			'token' => 'Sandbox token',
		);
	}

	public function getDescription() {
		return 'Add translations to stash';
	}

	public function getExamples() {
		return array(
			"api.php?action=translationstash&subaction=add&title=MediaWiki:Jan/fi&" .
				"value=tammikuu&metadata={}",
			"api.php?action=translationstash&subaction=query",
		);
	}

	// BC for old MW
	public function getVersion() {
		return __CLASS__ . ': ' . TRANSLATE_VERSION;
	}
}
