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
		$msgKey = $params['msgKey'];
		$name = $params['changesetName'] === '' ?
			MessageChangeStorage::DEFAULT_NAME : $params['changesetName'];
		$changesetModifiedTime = $params['changesetModified'];
		$renameKey = null;

		if ( !self::isValidCdbName( $name ) ) {
			throw new InvalidArgumentException( "Invalid CDB file name passed - '$name'. " );
		}
		$cdbPath = self::getCdbPath( $name );

		if ( !MessageChangeStorage::isLatestVersion( $cdbPath, $changesetModifiedTime ) ) {
			// CDB file has been modified since the time the page was generated.
			$this->dieWithError( [ 'apierror-translate-changeset-modified' ] );
		}

		if ( $op === 'rename' ) {
			if ( !isset( $params['renameMsgKey'] ) ) {
				$this->dieWithError( [ 'apierror-missingparam', 'renameMsgKey' ] );
			}
			$renameKey = $params['renameMsgKey'];
		}

		$sourceChanges = MessageChangeStorage::getGroupChanges( $cdbPath, $groupId );
		if ( $sourceChanges->getModifications() === [] ) {
			$this->dieWithError( [ 'apierror-translate-smg-nochanges' ] );
		}

		$group = MessageGroups::getGroup( $groupId );
		if ( $op === 'rename' ) {
			$this->handleRename( $group, $sourceChanges, $msgKey, $renameKey,
				$group->getSourceLanguage() );
		} elseif ( $op === 'new' ) {
			$this->handleNew( $sourceChanges, $msgKey );
		} else {
			throw new InvalidArgumentException(
				"Invalid operation $op. Valid values - 'new' or 'rename'. "
			);
		}

		// Write the source changes back to file.
		MessageChangeStorage::writeGroupChanges( $sourceChanges, $groupId, $cdbPath );

		$this->getResult()->addValue( null, $this->getModuleName(), [
			'success' => 1
		] );
	}

	/**
	 * Handles rename requests
	 * @param MessageGroup $group
	 * @param MessageSourceChange $sourceChanges
	 * @param string $msgKey
	 * @param string $renameKey
	 * @param string $sourceLanguage
	 * @return void
	 */
	protected function handleRename( MessageGroup $group, MessageSourceChange $sourceChanges,
		$msgKey, $renameKey, $sourceLanguage
	) {
		$msgState = $renameMsgState = null;
		$languages = $sourceChanges->getLanguages();

		foreach ( $languages as $code ) {
			$isSourceLang = $sourceLanguage === $code;
			if ( $isSourceLang ) {
				$this->handleSourceRename( $sourceChanges, $code, $msgKey, $renameKey );
				continue;
			}

			// Check for changes with the new key, then with the old key.
			// If there are no changes, we won't find anything at all, and
			// can skip this languageCode.
			// TODO Rename:: Think & Imporve filtering
			$msg = $sourceChanges->findMessage( $code, $msgKey, [], $msgState );
			$msg = $msg === null ? $sourceChanges->findMessage( $code, $renameKey, [], $msgState ) :
				 $msg;
			if ( $msg === null ) {
				continue;
			}

			// Check for the renamed message in the rename list
			$renameMsg = $sourceChanges->findMessage( $code, $renameKey,
				[ MessageSourceChange::M_RENAME ], $renameMsgState );

			if ( $renameMsg === null ) {
				// Since the rename message is not there in the list of changes
				// we'll have to load it from the database.
				$title = Title::newFromText(
					TranslateUtils::title( $renameKey, $code, $group->getNamespace() ),
					$group->getNamespace()
				);

				$renameContent = TranslateUtils::getContentForTitle( $title, true );

				$renameMsg = [
					'key' => $renameKey,
					'content' => $renameContent
				];
				$renameMsgState = MessageSourceChange::M_NONE;
			}

			if ( $msgState === MessageSourceChange::M_RENAME ) {
				$msgState = $sourceChanges->breakRename( $code, $msg );
			}

			if ( $renameMsgState === MessageSourceChange::M_RENAME ) {
				$renameMsgState = $sourceChanges->breakRename( $code, $renameMsg );
			}

			// Remove previous states
			$sourceChanges->removeBasedOnType( $code, [ $msg['key'] ], $msgState );
			if ( $renameMsgState !== MessageSourceChange::M_NONE ) {
				$sourceChanges->removeBasedOnType( $code, [ $renameKey ], $renameMsgState );
			}
			$msg['key'] = $msgKey;

			// Add as rename
			$stringComparator = new SimpleStringComparator();
			$similarity = $stringComparator->getSimilarity(
				$msg['content'],
				$renameMsg['content']
			);
			$sourceChanges->addRename( $code, $msg, $renameMsg, $similarity );
			$sourceChanges->setRenameState( $code, $msgKey, $msgState );
			$sourceChanges->setRenameState( $code, $renameKey, $renameMsgState );
		}
	}

	protected function handleSourceRename( MessageSourceChange $sourceChanges, $code,
		$msgKey, $renameKey
	) {
		$msgState = $renameMsgState = null;
		$msg = $sourceChanges->findMessage( $code, $msgKey,
				[ MessageSourceChange::M_ADDITION, MessageSourceChange::M_RENAME ], $msgState );
		$renameMsg = $sourceChanges->findMessage( $code, $renameKey,
			[ MessageSourceChange::M_DELETION, MessageSourceChange::M_RENAME ], $renameMsgState );

			if ( !$msg || !$renameMsg ) {
				throw new InvalidArgumentException(
					'Message keys passed for rename were not found in the list of changes ' .
					'for the source language.'
				);
			}

			if ( $msgState === MessageSourceChange::M_RENAME ) {
				$msgState = $sourceChanges->breakRename( $code, $msg );
			}

			if ( $renameMsgState === MessageSourceChange::M_RENAME ) {
				$renameMsgState = $sourceChanges->breakRename( $code, $renameMsg );
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
			$sourceChanges->removeAdditions( $code, [ $msgKey ] );
			$sourceChanges->removeDeletions( $code, [ $renameKey ] );

			// Add as rename
			$stringComparator = new SimpleStringComparator();
			$similarity = $stringComparator->getSimilarity(
				$msg['content'],
				$renameMsg['content']
			);
			$sourceChanges->addRename( $code, $msg, $renameMsg, $similarity );
	}

	/**
	 * Handles add message as new request
	 * @param MessageSourceChange $sourceChanges
	 * @param string $msgKey
	 * @return void
	 */
	protected function handleNew( MessageSourceChange $sourceChanges, $msgKey ) {
		$msgState = null;
		$languages = $sourceChanges->getLanguages();

		foreach ( $languages as $code ) {
			$msg = $sourceChanges->findMessage( $code, $msgKey, [ MessageSourceChange::M_RENAME ],
				$msgState );

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
			$sourceChanges->breakRename( $code, $msg );
		}
	}

	public function getAllowedParams() {
		return [
			'groupId' => [
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
			'changesetName' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false,
				ApiBase::PARAM_DFLT => MessageChangeStorage::DEFAULT_NAME
			],
			// TODO Rename: Add docs
			'changesetModified' => [
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true,
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
