<?php
/**
 * WebAPI module for stashing translations.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
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
			foreach ( $translations as $translation ) {
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
			'title' => $title->getPrefixedText(),
			'definition' => $definition,
			'translation' => $translation->getValue(),
			'comparison' => $comparison,
			'metadata' => $translation->getMetadata(),
		);
	}

	public function isWriteMode() {
		return true;
	}

	public function needsToken() {
		return 'csrf';
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
			'translation' => array(
				ApiBase::PARAM_TYPE => 'string',
			),
			'metadata' => array(
				ApiBase::PARAM_TYPE => 'string',
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

	protected function getExamplesMessages() {
		return array(
			'action=translationstash&subaction=add&title=MediaWiki:Jan/fi&translation=tammikuu&metadata={}'
				=> 'apihelp-translationstash-example-1',
			'action=translationstash&subaction=query'
				=> 'apihelp-translationstash-example-2',
		);
	}
}
