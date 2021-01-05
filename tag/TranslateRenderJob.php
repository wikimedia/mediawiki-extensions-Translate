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

	public function run() {
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

		$text = $tpPage->generateSource();

		// Other stuff
		$user = $this->getUser();
		$summary = $this->getSummary();
		$flags = $this->getFlags();

		$page = WikiPage::factory( $title );
		$model = $page->getTitle()->getContentModel();

		// @todo FuzzyBot hack
		PageTranslationHooks::$allowTargetEdit = true;
		$content = ContentHandler::makeContent( $text, $page->getTitle(), $model );
		$editStatus = $page->doEditContent( $content, $summary, $flags, false, $user );
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

	public function setFlags( $flags ) {
		$this->params['flags'] = $flags;
	}

	public function getFlags() {
		return $this->params['flags'];
	}

	public function setSummary( $summary ) {
		$this->params['summary'] = $summary;
	}

	public function getSummary() {
		return $this->params['summary'];
	}

	/** @param User|string $user */
	public function setUser( $user ) {
		if ( $user instanceof User ) {
			$this->params['user'] = $user->getName();
		} else {
			$this->params['user'] = $user;
		}
	}

	/**
	 * Get a user object for doing edits.
	 *
	 * @return User
	 */
	public function getUser() {
		return User::newFromName( $this->params['user'], false );
	}
}
