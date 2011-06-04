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
	public static function newJob( Title $target, $full, /*User*/ $performer ) {
		global $wgTranslateFuzzyBotName;

		$job = new self( $target );
		$job->setUser( $wgTranslateFuzzyBotName );
		$job->setFull( $full );
		$msg = $this->getFull() ? 'pt-deletepage-full-logreason' : 'pt-deletepage-lang-logreason';
		$job->setSummary( wfMsgForContent( 'pt-deletepage-logreason', $target->getPrefixedText() ) );
		$job->setPerformer( $performer );
		$job->lock();
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
		$target  = $this->getTarget();

		PageTranslationHooks::$allowTargetEdit = true;
		$oldUser = $wgUser;
		$wgUser = $user;

		$error = '';
		$ok = new Article( $title, 0 )->doDeleteArticle( $summary, false, 0, true, $error );
		if ( !$ok ) {
			$logger = new LogPage( 'pagetranslation' );
			$params = array(
				'user' => $this->getPerformer(),
				'target' => $target->getPrefixedText(),
				'error' => base64_encode( serialize( $ok ) ), // This is getting ridiculous
			);
			$doer = User::newFromName( $this->getPerformer() );
			$msg = $this->getFull() ? 'deletefnok' : 'deletelnok';
			$logger->addEntry( $msg, $title, null, array( serialize( $params ) ), $doer );
		}

		PageTranslationHooks::$allowTargetEdit = false;

		global $wgMemc;
		$pages = (array) $wgMemc->get( wfMemcKey( 'pt-base', $title->getPrefixedText() ) );
		$last = true;

		foreach ( $pages as $page ) {
			if ( $wgMemc->get( wfMemcKey( 'pt-lock', $page ) ) === true ) {
				$last = false;
				break;
			}
		}

		if ( $last )  {
			$wgMemc->delete( wfMemcKey( 'pt-base',  $title->getPrefixedText() ) );
			$logger = new LogPage( 'pagetranslation' );
			$params = array( 'user' => $this->getPerformer() );
			$doer = User::newFromName( $this->getPerformer() );
			$msg = $this->getFull() ? 'deletefok' : 'deletelok';
			$logger->addEntry( $msg, $title, null, array( serialize( $params ) ), $doer );
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

	/**
	 * Get a user object for doing edits.
	 */
	public function getUser() {
		return User::newFromName( $this->params['user'], false );
	}

	/**
	 * Adapted from wfSuppressWarnings to allow not leaving redirects.
	 */
	public static function forceRedirects( $end = false ) {
		static $suppressCount = 0;
		static $originalLevel = null;

		global $wgGroupPermissions;
		global $wgUser;

		if ( $end ) {
			if ( $suppressCount ) {
				--$suppressCount;
				if ( !$suppressCount ) {
					if ( $originalLevel === null ) {
						unset( $wgGroupPermissions['*']['suppressredirect'] );
					} else {
						$wgGroupPermissions['*']['suppressredirect'] = $originalLevel;
					}
				}
			}
		} else {
			if ( !$suppressCount ) {
				$originalLevel = isset( $wgGroupPermissions['*']['suppressredirect'] ) ? $wgGroupPermissions['*']['suppressredirect'] : null;
				$wgGroupPermissions['*']['suppressredirect'] = true;
			}
			++$suppressCount;
		}
		$wgUser->clearInstanceCache();
	}
}
