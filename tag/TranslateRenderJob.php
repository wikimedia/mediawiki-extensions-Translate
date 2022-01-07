<?php
/**
 * Job for updating translation pages.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\Jobs\GenericTranslateJob;
use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\MediaWikiServices;
use MediaWiki\Permissions\Authority;
use MediaWiki\User\UserIdentity;

/**
 * Job for updating translation pages when translation or template changes.
 *
 * @ingroup PageTranslation JobQueue
 */
class TranslateRenderJob extends GenericTranslateJob {

	/**
	 * @param Title $target
	 * @return self
	 */
	public static function newJob( Title $target ) {
		$job = new self( $target );
		$job->setUser( FuzzyBot::getUser() );
		$job->setFlags( EDIT_FORCE_BOT );
		$job->setSummary( wfMessage( 'tpt-render-summary' )->inContentLanguage()->text() );

		return $job;
	}

	/**
	 * @param Title $title
	 * @param array $params
	 */
	public function __construct( $title, $params = [] ) {
		parent::__construct( __CLASS__, $title, $params );
		$this->removeDuplicates = true;
	}

	public function run(): bool {
		$this->logInfo( 'Starting TranslateRenderJob' );

		// We may be doing double wait here if this job was spawned by TranslationUpdateJob
		$lb = MediaWikiServices::getInstance()->getDBLoadBalancerFactory();
		if ( !$lb->waitForReplication() ) {
			$this->logWarning( 'Continuing despite replication lag' );
		}

		// Initialization
		$title = $this->title;

		$tpPage = TranslatablePage::getTranslationPageFromTitle( $title );
		if ( !$tpPage ) {
			$this->logError( 'Cannot render translation page!' );
			return false;
		}

		// Other stuff
		$user = $this->getUser();
		$summary = $this->getSummary();
		$flags = $this->getFlags();

		$page = WikiPage::factory( $title );

		// @todo FuzzyBot hack
		PageTranslationHooks::$allowTargetEdit = true;
		$content = $tpPage->getPageContent();
		$editStatus = $page->doUserEditContent(
			$content,
			$user,
			$summary,
			$flags
		);
		if ( !$editStatus->isOK() ) {
			$this->logError(
				'Error while editing content in page.',
				[
					'content' => $content,
					'errors' => $editStatus->getErrors()
				]
			);
		}

		$this->logInfo( 'Finished page edit operation' );
		PageTranslationHooks::$allowTargetEdit = false;

		$this->logInfo( 'Finished TranslateRenderJob' );
		return true;
	}

	/** @param int $flags */
	public function setFlags( $flags ) {
		$this->params['flags'] = $flags;
	}

	/** @return int */
	private function getFlags() {
		return $this->params['flags'];
	}

	/** @param string $summary */
	public function setSummary( $summary ) {
		$this->params['summary'] = $summary;
	}

	/** @return string */
	private function getSummary() {
		return $this->params['summary'];
	}

	/** @param UserIdentity|string $user */
	public function setUser( $user ) {
		if ( $user instanceof UserIdentity ) {
			$this->params['user'] = $user->getName();
		} else {
			$this->params['user'] = $user;
		}
	}

	/**
	 * Get a user object for doing edits.
	 *
	 * @return Authority
	 */
	private function getUser() {
		return User::newFromName( $this->params['user'], false );
	}
}
