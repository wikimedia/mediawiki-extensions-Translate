<?php

use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;

/**
 * Job for deleting translatable and translation pages.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup PageTranslation JobQueue
 */
class TranslateDeleteJob extends Job {
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

	/**
	 * @param Title $title
	 * @param array $params
	 */
	public function __construct( $title, $params = [] ) {
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

		if ( !$status->isGood() ) {
			$params = [
				'target' => $base,
				'errors' => $status->getErrorsArray(),
			];

			$type = $this->isTranslatablePage() ? 'deletefnok' : 'deletelnok';
			$entry = new ManualLogEntry( 'pagetranslation', $type );
			$entry->setPerformer( $performer );
			$entry->setComment( $reason );
			$entry->setTarget( $title );
			$entry->setParameters( $params );
			$logid = $entry->insert();
			$entry->publish( $logid );
		}

		PageTranslationHooks::$allowTargetEdit = false;

		$cache = ObjectCache::getInstance( CACHE_DB );
		$pageKey = $cache->makeKey( 'pt-base', $base );
		$pages = (array)$cache->get( $pageKey );
		$lastitem = array_pop( $pages );
		if ( $title->getPrefixedText() === $lastitem ) {
			$cache->delete( $pageKey );

			$type = $this->isTranslatablePage() ? 'deletefok' : 'deletelok';
			$entry = new ManualLogEntry( 'pagetranslation', $type );
			$entry->setPerformer( $performer );
			$entry->setComment( $reason );
			$entry->setTarget( Title::newFromText( $base ) );
			$logid = $entry->insert();
			$entry->publish( $logid );

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

	/** @return bool True if this job is for a translatable page, false if for a translation page */
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
