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

		// The user we are operating on, not necessarly the user making the request
		$user = $this->getUser();

		if ( isset( $params['username'] ) ){
			if ( $this->getUser()->isAllowed( 'translate-sandboxmanage' ) ) {
				$user = User::newFromName( $params['username'] );
				if ( !$user ) {
					$this->dieUsageMsg( array( 'invalidparam', 'username' ) );
				}
			} else {
				$this->dieUsageMsg( array( 'invalidparam', 'username' ) );
			}
		}

		$stash = new TranslationStashStorage( wfGetDB( DB_MASTER ) );
		$action = $params['subaction'];

		if ( $action === 'add' ) {
			if ( !isset( $params['title'] ) ) {
				$this->dieUsageMsg( array( 'missingparam', 'title' ) );
			}
			if ( !isset( $params['translation'] ) ) {
				$this->dieUsageMsg( array( 'missingparam', 'translation' ) );
			}

			// @todo: Return value of Title::newFromText not checked
			$translation = new StashedTranslation(
				$user,
				Title::newFromText( $params['title'] ),
				$params['translation'],
				FormatJson::decode( $params['metadata'], true )
			);
			$stash->addTranslation( $translation );
		}

		if ( $action === 'query' ) {
			$output['translations'] = array();

			$translations = $stash->getTranslations( $user );
			foreach( $translations as $translation ) {
				$output['translations'][] = $this->formatTranslation( $translation );
			}
		}

		// If we got this far, nothing has failed
		$output['result'] = 'ok';
		$this->getResult()->addValue( null, $this->getModuleName(), $output );
	}

	protected function formatTranslation( StashedTranslation $translation ) {
		$title = $translation->getTitle();
		$handle = new MessageHandle( $title );

		// Prepare for the worst
		$definition = '';
		$comparison = '';
		if ( $handle->isValid() ) {
			$groupId = MessageIndex::getPrimaryGroupId( $handle );
			$group = MessageGroups::getGroup( $groupId );

			$key = $handle->getKey();

			$definition = $group->getMessage( $key, $group->getSourceLanguage() );
			$comparison = $group->getMessage( $key, $handle->getCode() );
		}

		return array(
			'title' => $title->getPrefixedDBKey(),
			'definition' => $definition,
			'translation' => $translation->getValue(),
			'comparison' => $comparison,
			'metadata' => $translation->getMetadata(),
		);
	}


	public function isWriteMode() {
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
			'username' => array(
				ApiBase::PARAM_TYPE => 'string',
			),
		);
	}

	public function getParamDescription() {
		$action = TranslateUtils::getTokenAction( 'edit' );

		return array(
			'subaction' => 'Action',
			'title' => 'Title of the translation unit page',
			'translation' => 'Translation made by the user',
			'metadata' => 'Json object',
			'token' => 'Sandbox token',
			'username' => 'Optionally the user whose stash to get. '
				. 'Only priviledged users can do this',
		);
	}

	public function getDescription() {
		return 'Add translations to stash';
	}

	public function getExamples() {
		return array(
			"api.php?action=translationstash&subaction=add&title=MediaWiki:Jan/fi&" .
				"translation=tammikuu&metadata={}",
			"api.php?action=translationstash&subaction=query",
		);
	}

	// BC for old MW
	public function getVersion() {
		return __CLASS__ . ': ' . TRANSLATE_VERSION;
	}
}
