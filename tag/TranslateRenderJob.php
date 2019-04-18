<?php
/**
 * Job for updating translation pages.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Job for updating translation pages when translation or template changes.
 *
 * @ingroup PageTranslation JobQueue
 */
class TranslateRenderJob extends Job {

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
		global $wgTranslateKeepOutdatedTranslations;

		// Initialization
		$title = $this->title;
		list( , $code ) = TranslateUtils::figureMessage( $title->getPrefixedText() );

		// Return the actual translation page...
		$page = TranslatablePage::isTranslationPage( $title );
		if ( !$page ) {
			throw new MWException( "Cannot render translation page for {$title->getPrefixedText()}!" );
		}

		$group = $page->getMessageGroup();
		$collection = $group->initCollection( $code );

		$text = $page->getParse()->getTranslationPageText(
			$collection,
			$wgTranslateKeepOutdatedTranslations
		);

		// Other stuff
		$user = $this->getUser();
		$summary = $this->getSummary();
		$flags = $this->getFlags();

		$page = WikiPage::factory( $title );

		// @todo FuzzyBot hack
		PageTranslationHooks::$allowTargetEdit = true;
		$content = ContentHandler::makeContent( $text, $page->getTitle() );
		$page->doEditContent( $content, $summary, $flags, false, $user );

		PageTranslationHooks::$allowTargetEdit = false;

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

	/**
	 * @param User|string $user
	 */
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
