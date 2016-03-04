<?php
/**
 * Contains class with job for moving translation pages.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2010, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Contains class with job for moving translation pages. Used together with
 * PageTranslationMovePage class.
 *
 * @ingroup PageTranslation JobQueue
 */
class TranslateMoveJob extends Job {
	/**
	 * @param $source Title
	 * @param $target Title
	 * @param $params array, should include base-source and base-target
	 * @param $performer
	 * @return TranslateMoveJob
	 */
	public static function newJob( Title $source, Title $target, array $params,
		/*User*/$performer
	) {
		$job = new self( $source );
		$job->setUser( FuzzyBot::getUser() );
		$job->setTarget( $target->getPrefixedText() );
		$summary = wfMessage( 'pt-movepage-logreason', $params['base-source'] );
		$summary = $summary->inContentLanguage()->text();
		$job->setSummary( $summary );
		$job->setParams( $params );
		$job->setPerformer( $performer );
		$job->lock();

		return $job;
	}

	/**
	 * @param Title $title
	 * @param array $params
	 * @param int $id
	 */
	public function __construct( $title, $params = array(), $id = 0 ) {
		parent::__construct( __CLASS__, $title, $params, $id );
	}

	public function run() {
		// Unfortunately the global is needed until bug is fixed:
		// https://phabricator.wikimedia.org/T51086
		// Once MW >= 1.24 is supported, can use MovePage class.
		global $wgUser;

		// Initialization
		$title = $this->title;
		// Other stuff
		$user = $this->getUser();
		$summary = $this->getSummary();
		$target = $this->getTarget();
		$base = $this->params['base-source'];
		$doer = User::newFromName( $this->getPerformer() );

		PageTranslationHooks::$allowTargetEdit = true;
		PageTranslationHooks::$jobQueueRunning = true;
		$oldUser = $wgUser;
		$wgUser = $user;
		self::forceRedirects( false );

		// Don't check perms, don't leave a redirect
		$ok = $title->moveTo( $target, false, $summary, false );
		if ( !$ok ) {
			$params = array(
				'target' => $target->getPrefixedText(),
				'error' => $ok,
			);

			$entry = new ManualLogEntry( 'pagetranslation', 'movenok' );
			$entry->setPerformer( $doer );
			$entry->setTarget( $title );
			$entry->setParameters( $params );
			$logid = $entry->insert();
			$entry->publish( $logid );
		}

		self::forceRedirects( true );
		PageTranslationHooks::$allowTargetEdit = false;

		$this->unlock();

		$cache = wfGetCache( CACHE_ANYTHING );
		$key = wfMemcKey( 'translate-pt-move', $base );

		$count = $cache->decr( $key );
		$last = (string)$count === '0';

		if ( $last ) {
			$cache->delete( $key );

			$params = array(
				'target' => $this->params['base-target'],
			);

			$entry = new ManualLogEntry( 'pagetranslation', 'moveok' );
			$entry->setPerformer( $doer );
			$entry->setParameters( $params );
			$entry->setTarget( Title::newFromText( $base ) );
			$logid = $entry->insert();
			$entry->publish( $logid );

			PageTranslationHooks::$jobQueueRunning = false;
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

	public function setTarget( $target ) {
		if ( $target instanceof Title ) {
			$this->params['target'] = $target->getPrefixedText();
		} else {
			$this->params['target'] = $target;
		}
	}

	public function getTarget() {
		return Title::newFromText( $this->params['target'] );
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
	 * @return User
	 */
	public function getUser() {
		return User::newFromName( $this->params['user'], false );
	}

	public function setParams( array $params ) {
		foreach ( $params as $k => $v ) {
			$this->params[$k] = $v;
		}
	}

	public function lock() {
		$cache = wfGetCache( CACHE_ANYTHING );
		$cache->set( wfMemcKey( 'pt-lock', sha1( $this->title->getPrefixedText() ) ), true );
		$cache->set( wfMemcKey( 'pt-lock', sha1( $this->getTarget()->getPrefixedText() ) ), true );
	}

	public function unlock() {
		$cache = wfGetCache( CACHE_ANYTHING );
		$cache->delete( wfMemcKey( 'pt-lock', sha1( $this->title->getPrefixedText() ) ) );
		$cache->delete( wfMemcKey( 'pt-lock', sha1( $this->getTarget()->getPrefixedText() ) ) );
	}

	/**
	 * Adapted from wfSuppressWarnings to allow not leaving redirects.
	 * @param $end bool
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
				$originalLevel = isset( $wgGroupPermissions['*']['suppressredirect'] ) ?
					$wgGroupPermissions['*']['suppressredirect'] :
					null;
				$wgGroupPermissions['*']['suppressredirect'] = true;
			}
			++$suppressCount;
		}
		$wgUser->clearInstanceCache();
	}
}
