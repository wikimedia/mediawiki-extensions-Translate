<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\MessageGroupProcessing\PageDeleteLogger;
use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\MediaWikiServices;

/**
 * Job for deleting translatable bundles and translation pages.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup PageTranslation JobQueue
 */
class TranslatableBundleDeleteJob extends Job {
	public static function newJob(
		Title $target,
		string $base,
		bool $isTranslatablePage,
		User $performer,
		string $reason
	): self {
		$params = [
			'full' => $isTranslatablePage,
			'base' => $base,
			'performer' => $performer->getName(),
			'reason' => $reason
		];

		return new self( $target, $params );
	}

	public function __construct( Title $title, array $params = [] ) {
		parent::__construct( __CLASS__, $title, $params );
	}

	public function run() {
		$title = $this->title;
		$user = FuzzyBot::getUser();
		$summary = $this->getSummary();
		$base = $this->getBase();
		$performer = $this->getPerformer();
		$reason = $this->getReason();

		PageTranslationHooks::$allowTargetEdit = true;
		PageTranslationHooks::$jobQueueRunning = true;

		$error = '';
		$wikipage = new WikiPage( $title );

		$status = $wikipage->doDeleteArticleReal(
			"{$summary}: $reason",
			$user,
			false,
			null,
			$error,
			null,
			[],
			'delete',
			true
		);

		$logger = new PageDeleteLogger( $title, 'pagetranslation' );
		if ( !$status->isGood() ) {
			if ( $this->isTranslatablePage() ) {
				$logger->logBundleError( $performer, $reason, $status );
			} else {
				$logger->logPageError( $performer, $reason, $status );
			}
		}

		PageTranslationHooks::$allowTargetEdit = false;

		$cache = MediaWikiServices::getInstance()->getMainObjectStash();
		$pageKey = $cache->makeKey( 'pt-base', $base );
		$pages = (array)$cache->get( $pageKey );
		$lastitem = array_pop( $pages );
		if ( $title->getPrefixedText() === $lastitem ) {
			$cache->delete( $pageKey );

			if ( $this->isTranslatablePage() ) {
				$logger->logBundleSuccess( $performer, $reason );
			} else {
				$logger->logPageSuccess( $performer, $reason );
			}

			$tpage = TranslatablePage::newFromTitle( $title );
			$tpage->getTranslationPercentages();
			foreach ( $tpage->getTranslationPages() as $page ) {
				$page->invalidateCache();
			}
			$title->invalidateCache();
			PageTranslationHooks::$jobQueueRunning = false;
		}

		return true;
	}

	public function getSummary(): string {
		$base = $this->getBase();
		if ( $this->isTranslatablePage() ) {
			$msg = wfMessage( 'pt-deletepage-full-logreason', $base )->inContentLanguage()->text();
		} else {
			$msg = wfMessage( 'pt-deletepage-lang-logreason', $base )->inContentLanguage()->text();
		}

		return $msg;
	}

	public function getReason(): string {
		return $this->params['reason'];
	}

	public function isTranslatablePage(): bool {
		return $this->params['full'];
	}

	public function getPerformer(): User {
		return User::newFromName( $this->params['performer'] );
	}

	public function getBase(): string {
		return $this->params['base'];
	}
}
