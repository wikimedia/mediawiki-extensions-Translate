<?php
/**
 * Job for moving translation pages.
 *
 * @ingroup Extensions
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2010, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class MoveJob extends Job {
	public static function newJob( Title $source, Title $target, $base, User $performer ) {
		global $wgTranslateFuzzyBotName;

		$job = new self( $source );
		$job->setUser( $wgTranslateFuzzyBotName );
		$job->setTarget( $target->getPrefixedText() );
		$job->setSummary( wfMsgForContent( 'pt-movepage-logreason', $target->getPrefixedText() ) );
		$job->setBase( $base );
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

		// Don't check perms, don't leave a redirect
		$ok = $source->moveTo( $target, false, $summary, false );
		if ( $ok ) {
			$logger = new LogPage( 'pagetranslation' );
			$params = array(
				'user' => $this->getPerformer(),
				'target' => $target->getPrefixedText(),
				'error' => base64_encode( $ok ),
			);
			$logger->addEntry( 'moveok', $title, null, array( serialize( $params ) ) );
		}

		$wgUser = $oldUser;
		PageTranslationHooks::$allowTargetEdit = false;

		$this->unlock();

		global $wgMemc;
		$pages = $wgMemc->get( 'pt-base', $base );
		foreach ( $pages as $page ) {
			if ( $wgMemc->get( 'pt-lock', $page ) === true ) {
				return true;
			}
		}

		$logger = new LogPage( 'pagetranslation' );
		$params = array(
			'user' => $this->getPerformer(),
			'target' => $target->getPrefixedText(),
		);
		$logger->addEntry( 'moveok', $title, null, array( serialize( $params ) ) );


		return true;
	}

	public function setSummary( $summary ) {
		$this->params['summary'] = $summary;
	}

	public function getSummary() {
		return $this->params['summary'];
	}

	public function setBase( $base ) {
		$this->params['base'] = $base;
	}

	public function getBase() {
		return $this->params['base'];
	}

	public function setPerformer( $performer ) {
		if ( $performer instanceof Performer ) {
			$this->params['performer'] = $performer->getName();
		} else {
			$this->params['performer'] = $performer;
		}
	}

	public function getPerformer() {
		return $this->params['performer'];
	}

	public function setTarget( $target ) {
		if ( $target instanceof Title ) {
			$this->params['target'] = $target->getPrefixedText();
		} else {
			$this->params['target'] = $target;
		}
	}

	public function getTarget() {
		return Title::newFromText( $this->params['summary'] );
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

	public function lock() {
		global $wgMemc;
		$wgMemc->set( wfMemcKey( 'pt-lock', $title->getPrefixedText() ), true, 60*60*6 );
		$wgMemc->set( wfMemcKey( 'pt-lock', $this->getTarget()->getPrefixedText() ), true, 60*60*6 );
	}

	public function unlock() {
		global $wgMemc;
		$wgMemc->delete( wfMemcKey( 'pt-lock', $title->getPrefixedText() ) );
		$wgMemc->delete( wfMemcKey( 'pt-lock', $this->getTarget()->getPrefixedText() ) );
	}

}
