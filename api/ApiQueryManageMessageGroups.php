<?php
/**
 * API module for querying for changes to message group
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\MessageSync\MessageSourceChange;
use MediaWiki\Extension\Translate\Utilities\StringComparators\SimpleStringComparator;

/**
 * API module for querying message group changes.
 * @since 2019.10
 * @license GPL-2.0-or-later
 * @ingroup API TranslateAPI
 */
class ApiQueryManageMessageGroups extends ApiQueryBase {
	private const RIGHT = 'translate-manage';

	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'mmg' );
	}

	public function execute() {
		$params = $this->extractRequestParams();
		$groupId = $params['groupId'];
		$msgKey = $params['messageKey'];
		$name = $params['changesetName'] ?? MessageChangeStorage::DEFAULT_NAME;

		$user = $this->getUser();
		$allowed = $user->isAllowed( self::RIGHT );

		if ( !$allowed ) {
			$this->dieWithError( 'apierror-permissiondenied-generic', 'permissiondenied' );
		}

		$group = MessageGroups::getGroup( $groupId );
		if ( !$group ) {
			$this->dieWithError( 'apierror-translate-invalidgroup', 'invalidgroup' );
		}

		if ( !MessageChangeStorage::isValidCdbName( $name ) ) {
			$this->dieWithError(
				[ 'apierror-translate-invalid-changeset-name', wfEscapeWikiText( $name ) ],
				'invalidchangeset'
			);
		}
		$cdbPath = MessageChangeStorage::getCdbPath( $name );

		$sourceChanges = MessageChangeStorage::getGroupChanges( $cdbPath, $groupId );

		if ( $sourceChanges->getAllModifications() === [] ) {
			$this->dieWithError( [ 'apierror-translate-smg-nochanges' ] );
		}

		$messages = $this->getPossibleRenames(
			$sourceChanges, $group->getNamespace(), $msgKey, $group->getSourceLanguage()
		);

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
		$deletions = $sourceChanges->getDeletions( $languageCode );
		$targetMsg = $sourceChanges->findMessage(
			$languageCode, $msgKey, [ MessageSourceChange::ADDITION, MessageSourceChange::RENAME ]
		);
		$stringComparator = new SimpleStringComparator();
		$renameList = [];

		// compare deleted messages with the target message and get the similarity.
		foreach ( $deletions as $deletion ) {
			if ( $deletion['content'] === null ) {
				continue;
			}

			$similarity = $stringComparator->getSimilarity(
				$deletion['content'],
				// @phan-suppress-next-line PhanTypeArraySuspiciousNullable
				$targetMsg['content']
			);

			$title = Title::makeTitle(
				$groupNamespace,
				TranslateUtils::title( $deletion['key'], $languageCode, $groupNamespace )
			);

			$renameList[] = [
				'key' => $deletion['key'],
				'content' => $deletion['content'],
				'similarity' => $similarity,
				'link' => $title->getFullURL(),
				'title' => $title->getPrefixedText()
			];
		}

		// sort them based on similarity
		usort( $renameList, static function ( $a, $b ) {
			return -( $a['similarity'] <=> $b['similarity'] );
		} );

		return $renameList;
	}

	protected function getAllowedParams() {
		$params = parent::getAllowedParams();
		$params['groupId'] = [
			ApiBase::PARAM_TYPE => 'string',
			ApiBase::PARAM_REQUIRED => true,
		];

		$params['messageKey'] = [
			ApiBase::PARAM_TYPE => 'string',
			ApiBase::PARAM_REQUIRED => true,
		];

		$params['changesetName'] = [
			ApiBase::PARAM_TYPE => 'string',
			ApiBase::PARAM_DFLT => MessageChangeStorage::DEFAULT_NAME
		];

		return $params;
	}

	protected function getExamplesMessages() {
		return [
			'action=query&meta=managemessagegroup&mmggroupId=hello
				&mmgchangesetName=default&mmgmessageKey=world' => 'apihelp-query+managemessagegroups-example-1',
		];
	}
}
