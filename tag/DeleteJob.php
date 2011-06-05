<?php
/**
 * Contains class with job for deleting translatable and translation pages.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2011, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Contains class with job for deleting translatable and translation pages.
 *
 * @ingroup PageTranslation JobQueue
 * @todo Get rid of direct reference to $wgMemc.
 */
class DeleteJob extends Job {
	public static function newJob( Title $target, $base, $full, /*User*/ $performer ) {
		global $wgTranslateFuzzyBotName;

		$job = new self( $target );
		$job->setUser( $wgTranslateFuzzyBotName );
		$job->setFull( $full );
		$job->setBase( $base );
		$msg = $job->getFull() ? 'pt-deletepage-full-logreason' : 'pt-deletepage-lang-logreason';
		$job->setSummary( wfMsgForContent( $msg, $target->getPrefixedText() ) );
		$job->setPerformer( $performer );
		return $job;
	}

	function __construct( $title, $params = array(), $id = 0 ) {
		parent::__construct( __CLASS__, $title, $params, $id );
	}

	function run() {
		global $wgUser;

		// Initialization
		$title = $this->title;
		// Other stuff
		$user    = $this->getUser();
		$summary = $this->getSummary();
		$base    = $this->getBase();

		PageTranslationHooks::$allowTargetEdit = true;
		$oldUser = $wgUser;
		$wgUser = $user;

		$error = '';
		$article = new Article( $title, 0 );
		$ok = $article->doDeleteArticle( $summary, false, 0, true, $error );
		if ( !$ok ) {
			$logger = new LogPage( 'pagetranslation' );
			$params = array(
				'user' => $this->getPerformer(),
				'target' => $title->getPrefixedText(),
				'error' => base64_encode( serialize( $ok ) ), // This is getting ridiculous
			);
			$doer = User::newFromName( $this->getPerformer() );
			$msg = $this->getFull() ? 'deletefnok' : 'deletelnok';
			$logger->addEntry( $msg, $title, null, array( serialize( $params ) ), $doer );
		}

		PageTranslationHooks::$allowTargetEdit = false;

		$cache = wfGetCache( CACHE_DB );
		$pages = (array) $cache->get( wfMemcKey( 'pt-base', $base ) );
		$lastitem = array_pop( $pages );
		if ( $title->getPrefixedText() === $lastitem ) {
			$cache->delete( wfMemcKey( 'pt-base', $base ) );
			$logger = new LogPage( 'pagetranslation' );
			$params = array( 'user' => $this->getPerformer() );
			$doer = User::newFromName( $this->getPerformer() );
			$msg = $this->getFull() ? 'deletefok' : 'deletelok';
			$logger->addEntry( $msg, Title::newFromText( $base ), null, array( serialize( $params ) ), $doer );

			$tpage = TranslatablePage::newFromTitle( $title );
			$tpage->getTranslationPercentages( true );
			foreach ( $tpage->getTranslationPages() as $page ) {
				$page->invalidateCache();
			}
			$title->invalidateCache();
		}

		$wgUser = $oldUser;

		return true;
	}

	public function setSummary( $summary ) {
		$this->params['summary'] = $summary;
	}

	public function getSummary() {
		return $this->params['summary'];
	}

	public function setFull( $full ) {
		$this->params['full'] = $full;
	}

	public function getFull() {
		return $this->params['full'];
	}

	public function setPerformer( $performer ) {
		if ( is_object( $performer ) ) {
			$this->params['performer'] = $performer->getName();
		} else {
			$this->params['performer'] = $performer;
		}
	}

	public function getPerformer() {
		return $this->params['performer'];
	}

	public function setUser( $user ) {
		if ( is_object( $user ) ) {
			$this->params['user'] = $user->getName();
		} else {
			$this->params['user'] = $user;
		}
	}

	public function setBase( $base ) {
		$this->params['base'] = $base;
	}

	public function getBase() {
		return $this->params['base'];
	}

	/**
	 * Get a user object for doing edits.
	 */
	public function getUser() {
		return User::newFromName( $this->params['user'], false );
	}

}
