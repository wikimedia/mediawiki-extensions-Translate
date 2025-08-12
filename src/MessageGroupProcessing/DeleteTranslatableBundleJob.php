<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use Job;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\Translate\PageTranslation\Hooks;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePage;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;
use Wikimedia\ScopedCallback;

/**
 * Job for deleting translatable bundles and translation pages.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup PageTranslation JobQueue
 */
class DeleteTranslatableBundleJob extends Job {
	public static function newJob(
		Title $target,
		string $base,
		string $bundleType,
		bool $isTranslationPage,
		UserIdentity $performer,
		string $reason,
		?array $userSessionInfo
	): self {
		$params = [
			'translation' => $isTranslationPage,
			'base' => $base,
			'bundleType' => $bundleType,
			'performer' => $performer->getName(),
			'reason' => $reason,
			'session' => $userSessionInfo
		];

		return new self( $target, $params );
	}

	public function __construct( Title $title, array $params = [] ) {
		parent::__construct( 'DeleteTranslatableBundleJob', $title, $params );
	}

	/** @inheritDoc */
	public function run() {
		$title = $this->title;
		$fuzzyBot = FuzzyBot::getUser();
		$summary = $this->getSummary();
		$base = $this->getBase();
		$performer = $this->getPerformer();
		$reason = $this->getReason();

		// Restore the session information if present
		if ( isset( $this->params[ 'session' ] ) ) {
			$scope = RequestContext::importScopedSession( $this->params['session'] );
			$this->addTeardownCallback( static function () use ( &$scope ) {
				ScopedCallback::consume( $scope );
			} );
		}

		$mwInstance = MediaWikiServices::getInstance();

		// Allows regular user to delete pages that are normally protected from direct editing
		Hooks::$allowTargetEdit = true;
		// Skip hook that handles deletion of translation units to avoid recreating translation
		// pages in middle of a delete.
		Hooks::$isDeleteTranslatableBundleJobRunning = true;

		$wikipage = $mwInstance->getWikiPageFactory()->newFromTitle( $title );
		$deletePage = $mwInstance->getDeletePageFactory()->newDeletePage( $wikipage, $fuzzyBot );
		$status = $deletePage->setSuppress( false )
			->forceImmediate( true )
			->deleteUnsafe( "$summary: $reason" );

		$bundleFactory = Services::getInstance()->getTranslatableBundleFactory();
		// Since the page has been removed from cache, create a bundle from the class name.
		$bundle = $bundleFactory->getBundleFromClass( Title::newFromText( $base ), $this->getBundleType() );
		$logger = $bundleFactory->getPageDeleteLogger( $bundle );

		if ( !$status->isGood() ) {
			if ( $this->isTranslation() ) {
				$logger->logPageError( $performer, $reason, $status );
			} else {
				$logger->logBundleError( $performer, $reason, $status );
			}
		}

		Hooks::$allowTargetEdit = false;
		Hooks::$isDeleteTranslatableBundleJobRunning = false;

		$cache = $mwInstance->getMainObjectStash();
		$pageKey = $cache->makeKey( 'pt-base', $base );
		$pages = (array)$cache->get( $pageKey );
		$lastitem = array_pop( $pages );
		if ( $title->getPrefixedText() === $lastitem ) {
			$cache->delete( $pageKey );

			if ( $this->isTranslation() ) {
				$logger->logPageSuccess( $performer, $reason );
			} else {
				$logger->logBundleSuccess( $performer, $reason );
			}

			$title->invalidateCache();
		}

		return true;
	}

	public function getSummary(): string {
		$base = $this->getBase();
		if ( $this->isTranslation() ) {
			$msg = wfMessage( 'pt-deletepage-lang-logreason', $base )->inContentLanguage()->text();
		} else {
			$msg = wfMessage( 'pt-deletepage-full-logreason', $base )->inContentLanguage()->text();
		}

		return $msg;
	}

	public function getReason(): string {
		return $this->params['reason'];
	}

	private function isTranslation(): bool {
		return $this->params['translation'];
	}

	public function getPerformer(): User {
		$userFactory = MediaWikiServices::getInstance()->getUserFactory();
		return $userFactory->newFromName( $this->params['performer'] );
	}

	public function getBase(): string {
		return $this->params['base'];
	}

	private function getBundleType(): string {
		// Default to TranslatablePage if param is not present
		return $this->params['bundleType'] ?? TranslatablePage::class;
	}
}
