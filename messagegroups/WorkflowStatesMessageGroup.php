<?php
/**
 * This file contains an unmanaged message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2013, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0-or-later
 */

use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Content\ContentHandler;
use MediaWiki\Context\IContextSource;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;

/** @ingroup MessageGroup */
class WorkflowStatesMessageGroup extends WikiMessageGroup {
	// id and source are not needed
	public function __construct() {
	}

	/** @inheritDoc */
	public function getId() {
		return 'translate-workflow-states';
	}

	/** @inheritDoc */
	public function getLabel( ?IContextSource $context = null ) {
		$msg = wfMessage( 'translate-workflowgroup-label' );
		$msg = self::addContext( $msg, $context );

		return $msg->plain();
	}

	/** @inheritDoc */
	public function getDescription( ?IContextSource $context = null ) {
		$msg = wfMessage( 'translate-workflowgroup-desc' );
		$msg = self::addContext( $msg, $context );

		return $msg->plain();
	}

	/** @inheritDoc */
	public function getDefinitions() {
		$groups = MessageGroups::getAllGroups();
		$keys = [];

		/** @var $g MessageGroup */
		foreach ( $groups as $g ) {
			$states = $g->getMessageGroupStates()->getStates();
			if ( $states !== null ) {
				foreach ( $states as $state => $_ ) {
					$keys["Translate-workflow-state-$state"] = $state;
				}
			}
		}

		if ( !$keys ) {
			return [];
		}

		$defs = Utilities::getContents( array_keys( $keys ), $this->getNamespace() );
		$wikiPageFactory = MediaWikiServices::getInstance()->getWikiPageFactory();
		foreach ( $keys as $key => $state ) {
			if ( !isset( $defs[$key] ) ) {
				// @todo Use jobqueue
				$title = Title::makeTitleSafe( $this->getNamespace(), $key );
				$page = $wikiPageFactory->newFromTitle( $title );
				$content = ContentHandler::makeContent( $state, $title );
				$fuzzyBotUser = FuzzyBot::getUser();

				$updater = $page->newPageUpdater( $fuzzyBotUser )
					->setContent( SlotRecord::MAIN, $content );
				if ( $fuzzyBotUser->authorizeWrite( 'autopatrol', $title ) ) {
						$updater->setRcPatrolStatus( RecentChange::PRC_AUTOPATROLLED );
				}

				$summary = wfMessage( 'translate-workflow-autocreated-summary', $state )->inContentLanguage()->text();
				$updater->saveRevision(
					CommentStoreComment::newUnsavedComment( $summary ),
					EDIT_FORCE_BOT
				);
			} else {
				// Use the wiki translation as definition if available.
				// getContents returns array( content, last author )
				[ $content, ] = $defs[$key];
				$keys[$key] = $content;
			}
		}

		return $keys;
	}
}
