<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use Exception;
use MediaWiki\Api\ApiBase;
use MediaWiki\Extension\Translate\MessageSync\MessageSourceChange;
use MediaWiki\Extension\Translate\Synchronization\MessageChangeStorage;
use MediaWiki\Extension\Translate\Utilities\StringComparators\SimpleStringComparator;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Json\FormatJson;
use MediaWiki\Title\Title;
use MessageGroup;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * API module for managing message group changes.
 * Marks message as a rename of another message or as a new message.
 * Updates the cdb file.
 * @author Abijeet Patro
 * @since 2019.10
 * @license GPL-2.0-or-later
 * @ingroup API TranslateAPI
 */
class ManageMessageGroupsActionApi extends ApiBase {
	private const RIGHT = 'translate-manage';

	public function execute(): void {
		$this->checkUserRightsAny( self::RIGHT );
		$params = $this->extractRequestParams();

		$groupId = $params['groupId'];
		$op = $params['operation'];
		$msgKey = $params['messageKey'];
		$name = $params['changesetName'] ?? MessageChangeStorage::DEFAULT_NAME;
		$changesetModifiedTime = $params['changesetModified'];
		$keyToRename = null;

		if ( !MessageChangeStorage::isValidCdbName( $name ) ) {
			$this->dieWithError(
				[ 'apierror-translate-invalid-changeset-name', wfEscapeWikiText( $name ) ],
				'invalidchangeset'
			);
		}
		$cdbPath = MessageChangeStorage::getCdbPath( $name );

		if ( !MessageChangeStorage::isModifiedSince( $cdbPath, $changesetModifiedTime ) ) {
			// Changeset file has been modified since the time the page was generated.
			$this->dieWithError( [ 'apierror-translate-changeset-modified' ] );
		}

		if ( $op === 'rename' ) {
			if ( !isset( $params['renameMessageKey'] ) ) {
				$this->dieWithError( [ 'apierror-missingparam', 'renameMessageKey' ] );
			}
			$keyToRename = $params['renameMessageKey'];
		}

		$sourceChanges = MessageChangeStorage::getGroupChanges( $cdbPath, $groupId );
		if ( $sourceChanges->getAllModifications() === [] ) {
			$this->dieWithError( [ 'apierror-translate-smg-nochanges' ] );
		}

		$group = MessageGroups::getGroup( $groupId );
		if ( $group === null ) {
			$this->dieWithError( 'apierror-translate-invalidgroup', 'invalidgroup' );
		}

		try {
			if ( $op === 'rename' ) {
				$this->handleRename(
					$group, $sourceChanges, $msgKey, $keyToRename, $group->getSourceLanguage()
				);
			} elseif ( $op === 'new' ) {
				$this->handleNew( $sourceChanges, $msgKey, $group->getSourceLanguage() );
			} else {
				$this->dieWithError(
					[ 'apierror-translate-invalid-operation', wfEscapeWikiText( $op ),
						wfEscapeWikiText( implode( '/', [ 'new', 'rename' ] ) ) ],
					'invalidoperation'
				);
			}
		} catch ( Exception $ex ) {
			// Log necessary parameters and rethrow.
			$data = [
				'op' => $op,
				'newMsgKey' => $msgKey,
				'msgKey' => $keyToRename,
				'groupId' => $group->getId(),
				'group' => $group->getLabel(),
				'groupSourceLang' => $group->getSourceLanguage(),
				'exception' => $ex
			];

			error_log(
				"Error while running: ManageMessageGroupsActionApi::execute. Inputs: \n" .
				FormatJson::encode( $data, true )
			);

			throw $ex;
		}

		// Write the source changes back to file.
		MessageChangeStorage::writeGroupChanges( $sourceChanges, $groupId, $cdbPath );

		$this->getResult()->addValue( null, $this->getModuleName(), [
			'success' => 1
		] );
	}

	/** Handles rename requests */
	protected function handleRename(
		MessageGroup $group,
		MessageSourceChange $sourceChanges,
		string $msgKey,
		string $keyToRename,
		string $sourceLanguage
	): void {
		$languages = $sourceChanges->getLanguages();

		foreach ( $languages as $code ) {
			$msgState = $renameMsgState = null;

			$isSourceLang = $sourceLanguage === $code;
			if ( $isSourceLang ) {
				$this->handleSourceRename( $sourceChanges, $code, $msgKey, $keyToRename );
				continue;
			}

			// Check for changes with the new key, then with the old key.
			// If there are no changes, we won't find anything at all, and
			// can skip this languageCode.
			$msg = $sourceChanges->findMessage( $code, $msgKey, [
				MessageSourceChange::ADDITION,
				MessageSourceChange::RENAME
			], $msgState );

			// This case will arise if the message key has been changed in the source
			// language, but has not been modified in this language code.
			// NOTE: We are also searching under deletions. This means that if the source
			// language key is renamed, but one of the non source language keys is removed,
			// renaming it will not remove the translation, but only rename it. This
			// scenario is highly unlikely though.
			$msg ??= $sourceChanges->findMessage( $code, $keyToRename, [
				MessageSourceChange::DELETION,
				MessageSourceChange::CHANGE,
				MessageSourceChange::RENAME
			], $msgState );

			if ( $msg === null ) {
				continue;
			}

			// Check for the renamed message in the rename list, and deleted list.
			$renameMsg = $sourceChanges->findMessage(
				$code,
				$keyToRename,
				[ MessageSourceChange::RENAME, MessageSourceChange::DELETION ],
				$renameMsgState
			);

			// content / msg will not be present if the message was deleted from the wiki or
			// was for some reason unavailable during processing incoming changes. We're going
			// to try and load it here again from the database. Very rare chance of this happening.
			if ( $renameMsg === null || !isset( $renameMsg['content'] ) ) {
				$title = Title::newFromText(
					Utilities::title( $keyToRename, $code, $group->getNamespace() ),
					$group->getNamespace()
				);

				$renameContent = Utilities::getContentForTitle( $title, true ) ?? '';

				$renameMsg = [
					'key' => $keyToRename,
					'content' => $renameContent
				];

				// If the message was found in changes, this will be set, otherwise set it
				// to none
				$renameMsgState ??= MessageSourceChange::NONE;
			}

			// Remove previous states
			if ( $msgState === MessageSourceChange::RENAME ) {
				$msgState = $sourceChanges->breakRename( $code, $msg['key'] );
			} else {
				$sourceChanges->removeBasedOnType( $code, [ $msg['key'] ], $msgState );
			}

			if ( $renameMsgState === MessageSourceChange::RENAME ) {
				$renameMsgState = $sourceChanges->breakRename( $code, $renameMsg['key'] );
			} elseif ( $renameMsgState !== MessageSourceChange::NONE ) {
				$sourceChanges->removeBasedOnType( $code, [ $keyToRename ], $renameMsgState );
			}

			// This is done in case the key has not been renamed in the non-source language.
			$msg['key'] = $msgKey;

			// Add as rename
			$stringComparator = new SimpleStringComparator();
			$similarity = $stringComparator->getSimilarity(
				$msg['content'],
				$renameMsg['content']
			);
			$sourceChanges->addRename( $code, $msg, $renameMsg, $similarity );
			$sourceChanges->setRenameState( $code, $msgKey, $msgState );
			$sourceChanges->setRenameState( $code, $keyToRename, $renameMsgState );
		}
	}

	protected function handleSourceRename(
		MessageSourceChange $sourceChanges,
		string $code,
		string $msgKey,
		string $keyToRename
	): void {
		$msgState = $renameMsgState = null;

		$msg = $sourceChanges->findMessage(
			$code, $msgKey, [ MessageSourceChange::ADDITION, MessageSourceChange::RENAME ], $msgState
		);

		$renameMsg = $sourceChanges->findMessage(
			$code,
			$keyToRename,
			[ MessageSourceChange::DELETION, MessageSourceChange::RENAME ],
			$renameMsgState
		);

		if ( $msg === null || $renameMsg === null ) {
			$this->dieWithError( 'apierror-translate-rename-key-invalid' );
		}

		if ( $msgState === MessageSourceChange::RENAME ) {
			// @phan-suppress-next-line PhanTypeArraySuspiciousNullable T240141
			$msgState = $sourceChanges->breakRename( $code, $msg['key'] );
		}

		if ( $renameMsgState === MessageSourceChange::RENAME ) {
			// @phan-suppress-next-line PhanTypeArraySuspiciousNullable T240141
			$renameMsgState = $sourceChanges->breakRename( $code, $renameMsg['key'] );
		}

		// Ensure that one of them is an ADDITION, and one is DELETION
		if ( $msgState !== MessageSourceChange::ADDITION ||
			$renameMsgState !== MessageSourceChange::DELETION ) {
			$this->dieWithError( [
				'apierror-translate-rename-state-invalid',
				wfEscapeWikiText( $msgState ), wfEscapeWikiText( $renameMsgState )
			] );
		}

		// Remove previous states
		$sourceChanges->removeAdditions( $code, [ $msgKey ] );
		$sourceChanges->removeDeletions( $code, [ $keyToRename ] );

		// Add as rename
		$stringComparator = new SimpleStringComparator();
		$similarity = $stringComparator->getSimilarity(
			// @phan-suppress-next-line PhanTypeArraySuspiciousNullable T240141
			$msg['content'],
			// @phan-suppress-next-line PhanTypeArraySuspiciousNullable T240141
			$renameMsg['content']
		);
		$sourceChanges->addRename( $code, $msg, $renameMsg, $similarity );
	}

	/** Handles add message as new request */
	protected function handleNew(
		MessageSourceChange $sourceChanges,
		string $msgKey,
		string $sourceLang
	): void {
		$msgState = null;
		$languages = $sourceChanges->getLanguages();

		foreach ( $languages as $code ) {
			$msg = $sourceChanges->findMessage(
				$code, $msgKey, [ MessageSourceChange::RENAME ], $msgState
			);

			if ( $code === $sourceLang && $msg === null ) {
				$this->dieWithError( 'apierror-translate-addition-key-invalid' );
			}

			if ( $code === $sourceLang && $msgState !== MessageSourceChange::RENAME ) {
				$this->dieWithError( 'apierror-translate-rename-msg-new' );
			}

			// For any other language, it's possible for the message to be not found.
			if ( $msg === null ) {
				continue;
			}

			// breakRename will add the message back to its previous state, nothing more to do
			$sourceChanges->breakRename( $code, $msg['key'] );
		}
	}

	protected function getAllowedParams(): array {
		return [
			'groupId' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
			'renameMessageKey' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
			],
			'messageKey' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
			'operation' => [
				ParamValidator::PARAM_TYPE => [ 'rename', 'new' ],
				ParamValidator::PARAM_ISMULTI => false,
				ParamValidator::PARAM_REQUIRED => true,
			],
			'changesetName' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_DEFAULT => MessageChangeStorage::DEFAULT_NAME
			],
			'changesetModified' => [
				ParamValidator::PARAM_TYPE => 'integer',
				ParamValidator::PARAM_REQUIRED => true,
			]
		];
	}

	public function isInternal(): bool {
		return true;
	}

	public function needsToken(): string {
		return 'csrf';
	}
}
