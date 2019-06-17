<?php
/**
 * API module for querying for changes to message group
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

/**
 * API module for querying message group changes.
 * @since 2019.06
 * @license GPL-2.0-or-later
 * @ingroup API TranslateAPI
 */
class ApiQueryManageMessageGroups extends ApiQueryBase {
	const RIGHT = 'translate-manage';

	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'mmg' );
	}

	public function execute() {
		$params = $this->extractRequestParams();
		$groupId = $params['group'];
		$code = $params['language'];
		$msgKey = $params['key'];
		$name = empty( $params['groupname'] ) ?
			MessageChangeStorage::DEFAULT_NAME : $params['groupname'];

		$user = $this->getUser();
		$allowed = $user->isAllowed( self::RIGHT );

		if ( !$allowed ) {
			$this->dieWithError( 'apierror-permissiondenied-generic', 'permissiondenied' );
		}

		$group = MessageGroups::getGroup( $groupId );
		$sourceChanges = MessageChangeStorage::getGroupChanges( $name, $groupId );
		if ( $sourceChanges->getModifications() === [] ) {
			$this->dieWithError( [ 'apierror-translate-smg-nochanges' ] );
		}

		$messages = $this->getPossibleRenames( $sourceChanges, $group->getNamespace(),
			$msgKey, $code );

		$result = $this->getResult();
		$result->addValue( [ 'query', $this->getModuleName() ], null, $messages );
	}

	/**
	 * Fetches the messages that can be used as possible renames for a given message.
	 * @param MessageSourceChange $sourceChanges
	 * @param int $groupNamespace Group namespace
	 * @param string $msgKey
	 * @param string $languageCode Language code
	 * @return array
	 */
	protected function getPossibleRenames( MessageSourceChange $sourceChanges, $groupNamespace,
		$msgKey, $languageCode
	) {
		global $wgContLang;

		$deletions = $sourceChanges->getDeletions( $languageCode );
		$targetMsg = $sourceChanges->findMessage( $languageCode,  $msgKey,
			[ MessageSourceChange::M_ADDITION, MessageSourceChange::M_RENAME ] );
		$stringComparator = new SimpleStringComparator();
		$renameList = [];

		// compare deleted messages with the target message and get the similarity.
		foreach ( $deletions as $deletion ) {
			if ( $deletion['content'] === null ) {
				continue;
			}

			$similarity = $stringComparator->getSimilarity(
				$deletion['content'],
				$targetMsg['content']
			);

			$deletionTitleKey = $deletion['key'];
			if ( MWNamespace::isCapitalized( $groupNamespace ) ) {
				$deletionTitleKey = $wgContLang->ucfirst( $deletionTitleKey );
			}

			$title = Title::makeTitle( $groupNamespace, $deletionTitleKey . "/$languageCode" );

			$renameList[] = [
				'key' => $deletion['key'],
				'content' => $deletion['content'],
				'similarity' => $similarity,
				'link' => $title->getFullURL(),
				'title' => $title->getPrefixedText()
			];
		}

		// sort them based on similarity
		usort( $renameList, function ( $a, $b ) {
			if ( $a['similarity'] === $b['similarity'] ) {
				return 0;
			}

			return $a['similarity'] > $b['similarity'] ? -1 : 1;
		} );

		return $renameList;
	}

	public function getAllowedParams() {
		$params = parent::getAllowedParams();
		$params['group'] = [
			ApiBase::PARAM_TYPE => 'string',
			ApiBase::PARAM_REQUIRED => true,
		];

		$params['language'] = [
			ApiBase::PARAM_TYPE => 'string',
			ApiBase::PARAM_REQUIRED => true,
		];

		$params['key'] = [
			ApiBase::PARAM_TYPE => 'string',
			ApiBase::PARAM_REQUIRED => true,
		];

		$params['groupname'] = [
			ApiBase::PARAM_TYPE => 'string',
			ApiBase::PARAM_REQUIRED => false,
			ApiBase::PARAM_DFLT => MessageChangeStorage::DEFAULT_NAME
		];

		return $params;
	}

	protected function getExamplesMessages() {
		return [
			'action=query&meta=managemessagegroup&mmggroup=hello&mmglanguage=en
				&mmggroupname=default&mmgkey=world' => 'apihelp-managemessagegroup-example-1',
		];
	}
}
