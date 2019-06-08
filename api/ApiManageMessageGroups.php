<?php
/**
 * API module for managing message group changes
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */
/**
 * API module for managing message group changes.
 * Marks message as a rename of another message or as a new message.
 * Updates the cdb file.
 * @since 2019.06
 * @license GPL-2.0-or-later
 * @ingroup API TranslateAPI
 */
class ApiManageMessageGroups extends ApiBase {
	const RIGHT = 'translate-manage';

	public function execute() {
		$this->checkUserRightsAny( self::RIGHT );
		$params = $this->extractRequestParams();

		$groupId = $params['groupId'];
		$op = $params['op'];
		$languageCode = $params['languageCode'];
		$msgKey = $params['msgKey'];
		$name = empty( $params['grouptype'] ) ? MessageChangeStorage::DEFAULT_NAME : $params['grouptype'];
		$renameKey = null;

		if ( $op === 'rename' ) {
			if ( !isset( $params['renameMsgKey'] ) ) {
				$this->dieWithError( [ 'apierror-missingparam', 'renameMsgKey' ] );
			}
			$renameKey = $params['renameMsgKey'];
		}

		$sourceChanges = MessageChangeStorage::getGroupChanges( $name, $groupId );
		if ( $sourceChanges->getModifications() === [] ) {
			$this->dieWithError( [ 'apierror-translate-smg-nochanges' ] );
		}

		if ( $op === 'rename' ) {
			$this->handleRename( $sourceChanges, $languageCode, $msgKey, $renameKey );
		} elseif ( $op === 'new' ) {
			$this->handleNew( $sourceChanges, $languageCode, $msgKey );
		} else {
			throw new InvalidArgumentException(
				"Invalid operation $op. Valid values - 'new' or 'rename'. "
			);
		}

		// Write the source changes back to file.
		MessageChangeStorage::writeGroupChanges( $sourceChanges, $groupId, $name );

		$this->getResult()->addValue( null, $this->getModuleName(), [
			'success' => 1
		] );
	}

	/**
	 * Handles rename requests
	 * @param MessageSourceChange $sourceChanges
	 * @param string $languageCode
	 * @param string $msgKey
	 * @param string $renameKey
	 * @return void
	 */
	protected function handleRename( MessageSourceChange $sourceChanges, $languageCode,
		$msgKey, $renameKey ) {
		$msgState = $renameMsgState = null;
		$msg = $sourceChanges->findMessage( $languageCode, $msgKey, $msgState,
			[ MessageSourceChange::M_ADDITION, MessageSourceChange::M_RENAME ] );
		$renameMsg = $sourceChanges->findMessage( $languageCode, $renameKey, $renameMsgState,
			[ MessageSourceChange::M_DELETION, MessageSourceChange::M_RENAME ] );

		if ( !$msg || !$renameMsg ) {
			throw new InvalidArgumentException(
				'Message keys passed for rename were not found in the list of changes.'
			);
		}

		if ( $msgState === MessageSourceChange::M_RENAME ) {
			$msgState = $sourceChanges->breakRename( $languageCode, $msg );
		}

		if ( $renameMsgState === MessageSourceChange::M_RENAME ) {
			$renameMsgState = $sourceChanges->breakRename( $languageCode, $renameMsg );
		}

		// Ensure that one of them is a M_ADDITION, and one is M_DELETION
		if ( $msgState !== MessageSourceChange::M_ADDITION ||
			$renameMsgState !== MessageSourceChange::M_DELETION ) {
			throw new InvalidArgumentException(
				'One of the message passed for rename should be ' .
				'newly added, and the other one deleted. Current states - ' .
				"$msgState and $renameMsgState"
			);
		}

		// Remove previous states
		$sourceChanges->removeAdditions( $languageCode, [ $msgKey ] );
		$sourceChanges->removeDeletions( $languageCode, [ $renameKey ] );

		// Add as rename
		$stringComparator = new SimpleStringComparator();
		$similarity = $stringComparator->getSimilarity(
			$msg['content'],
			$renameMsg['content']
		);
		$sourceChanges->addRename( $languageCode, $msg, $renameMsg, $similarity );
	}

	/**
	 * Handles add message as new request
	 * @param MessageSourceChange $sourceChanges
	 * @param string $languageCode
	 * @param string $msgKey
	 * @return void
	 */
	protected function handleNew( MessageSourceChange $sourceChanges, $languageCode,
		$msgKey ) {
		$msgState = null;
		$msg = $sourceChanges->findMessage( $languageCode, $msgKey, $msgState,
			[ MessageSourceChange::M_RENAME ] );

		if ( !$msg ) {
			throw new InvalidArgumentException(
				'Message key passed for new addition was not found in the list of changes.'
			);
		}

		if ( $msgState !== MessageSourceChange::M_RENAME ) {
			throw new InvalidArgumentException(
				'Only renamed messages can be added as new messages.'
			);
		}

		// breakRename will add the message back to its previous state, nothing more to do
		$msgState = $sourceChanges->breakRename( $languageCode, $msg );

		if ( $msgState !== MessageSourceChange::M_ADDITION ) {
			throw new InvalidArgumentException(
				'Expect the previous state of message to be ' . MessageSourceChange::M_ADDITION .
				". Instead found '$msgState'."
			);
		}
	}

	public function getAllowedParams() {
		return [
			'groupId' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
			'languageCode' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
			'renameMsgKey' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false,
			],
			'msgKey' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
			'op' => [
				ApiBase::PARAM_TYPE => [ 'rename', 'new' ],
				ApiBase::PARAM_ISMULTI => false,
				ApiBase::PARAM_REQUIRED => true,
			],
			'grouptype' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false,
			]
		];
	}

	public function isInternal() {
		return true;
	}

	public function needsToken() {
		return 'csrf';
	}
}
