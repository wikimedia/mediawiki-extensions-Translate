<?php
/**
 * Job for updating translation pages.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2010, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Job for updating translation pages when translation or template changes.
 */
 * @ingroup PageTranslation JobQueue
 */
class RenderJob extends Job {
	public static function newJob( Title $target ) {
		global $wgTranslateFuzzyBotName;

		$job = new self( $target );
		$job->setUser( $wgTranslateFuzzyBotName );
		$job->setFlags( EDIT_FORCE_BOT );
		$job->setSummary( wfMsgForContent( 'tpt-render-summary' ) );

		return $job;
	}

	function __construct( $title, $params = array(), $id = 0 ) {
		parent::__construct( __CLASS__, $title, $params, $id );
		$this->params = $params;
		$this->removeDuplicates = true;
	}

	function run() {
		// Initialization
		$title = $this->title;
		list( $key, $code ) = TranslateUtils::figureMessage( $title->getPrefixedText() );

		// Return the actual translation page...
		$page = TranslatablePage::isTranslationPage( $title );
		if ( !$page ) {
			var_dump( $this->params );
			var_dump( $title );
			throw new MWException( "Oops, this should not happen!" );
		}

		$group = MessageGroups::getGroup( "page|$key" );
		$collection = $group->initCollection( $code );

		$text = $page->getParse()->getTranslationPageText( $collection );

		// Other stuff
		$user    = $this->getUser();
		$summary = $this->getSummary();
		$flags   = $this->getFlags();

		$article = new Article( $title, 0 );

		// @todo Fuzzybot hack
		PageTranslationHooks::$allowTargetEdit = true;

		// User hack
		global $wgUser;

		$oldUser = $wgUser;
		$wgUser = $user;

		// Do the edit
		$status = $article->doEdit( $text, $summary, $flags );
		SpecialPageTranslation::superDebug( __METHOD__, 'edit', $user, $title, $flags, $status );

		// User hack
		$wgUser = $oldUser;

		PageTranslationHooks::$allowTargetEdit = false;

		// purge cache
		$page->getTranslationPercentages( true );

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

	public function setUser( $user ) {
		if ( $user instanceof User ) {
			$this->params['user'] = $user->getName();
		} else {
			$this->params['user'] = $user;
		}
	}

	/**
	 * Get a user object for doing edits.
	 */
	public function getUser() {
		return User::newFromName( $this->params['user'], false );
	}
}
