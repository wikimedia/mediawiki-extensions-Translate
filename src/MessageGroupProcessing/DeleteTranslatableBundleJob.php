<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use Job;
use MediaWiki\Extension\Translate\PageTranslation\Hooks;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePage;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\MediaWikiServices;
use Title;
use User;

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
		bool $isTranslatableBundle,
		User $performer,
		string $reason
	): self {
		$params = [
			'translation' => $isTranslatableBundle,
			'base' => $base,
			'bundleType' => $bundleType,
			'performer' => $performer->getName(),
			'reason' => $reason
		];

		return new self( $target, $params );
	}

	public function __construct( Title $title, array $params = [] ) {
		parent::__construct( 'DeleteTranslatableBundleJob', $title, $params );
	}

	public function run() {
		$title = $this->title;
		$fuzzyBot = FuzzyBot::getUser();
		$summary = $this->getSummary();
		$base = $this->getBase();
		$performer = $this->getPerformer();
		$reason = $this->getReason();
		$mwInstance = MediaWikiServices::getInstance();

		Hooks::$allowTargetEdit = true;
		Hooks::$jobQueueRunning = true;

		$wikipage = $mwInstance->getWikiPageFactory()->newFromTitle( $title );
		$deletePage = $mwInstance->getDeletePageFactory()->newDeletePage( $wikipage, $fuzzyBot );
		$status = $deletePage->setSuppress( false )
			->forceImmediate( true )
			->deleteUnsafe( "{$summary}: $reason" );

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
			Hooks::$jobQueueRunning = false;
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
		// Use 'full' property if 'translation' is missing. This will happen
		// if the job is added before param 'full' was changed to 'translation'
		// Remove after MLEB 2022.07
		return $this->params['translation'] ?? !$this->params['full'];
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
