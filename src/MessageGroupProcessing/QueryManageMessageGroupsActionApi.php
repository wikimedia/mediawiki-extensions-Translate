<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Api\ApiQuery;
use MediaWiki\Api\ApiQueryBase;
use MediaWiki\Extension\Translate\MessageSync\MessageSourceChange;
use MediaWiki\Extension\Translate\Synchronization\MessageChangeStorage;
use MediaWiki\Extension\Translate\Utilities\StringComparators\EditDistanceStringComparator;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Title\Title;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * API module for querying message group changes.
 * @author Abijeet Patro
 * @since 2019.10
 * @license GPL-2.0-or-later
 * @ingroup API TranslateAPI
 */
class QueryManageMessageGroupsActionApi extends ApiQueryBase {
	private const RIGHT = 'translate-manage';

	public function __construct( ApiQuery $query, string $moduleName ) {
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

	/** Fetches the messages that can be used as possible renames for a given message. */
	protected function getPossibleRenames(
		MessageSourceChange $sourceChanges,
		int $groupNamespace,
		string $msgKey,
		string $languageCode
	): array {
		$deletions = $sourceChanges->getDeletions( $languageCode );
		$targetMsg = $sourceChanges->findMessage(
			$languageCode, $msgKey, [ MessageSourceChange::ADDITION, MessageSourceChange::RENAME ]
		);
		$stringComparator = new EditDistanceStringComparator();
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
				Utilities::title( $deletion['key'], $languageCode, $groupNamespace )
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

	protected function getAllowedParams(): array {
		$params = parent::getAllowedParams();
		$params['groupId'] = [
			ParamValidator::PARAM_TYPE => 'string',
			ParamValidator::PARAM_REQUIRED => true,
		];

		$params['messageKey'] = [
			ParamValidator::PARAM_TYPE => 'string',
			ParamValidator::PARAM_REQUIRED => true,
		];

		$params['changesetName'] = [
			ParamValidator::PARAM_TYPE => 'string',
			ParamValidator::PARAM_DEFAULT => MessageChangeStorage::DEFAULT_NAME
		];

		return $params;
	}

	protected function getExamplesMessages(): array {
		return [
			'action=query&meta=managemessagegroup&mmggroupId=hello
				&mmgchangesetName=default&mmgmessageKey=world' => 'apihelp-query+managemessagegroups-example-1',
		];
	}
}
