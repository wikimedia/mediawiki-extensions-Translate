<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Extension\Translate\Jobs\GenericTranslateJob;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageLoading\RebuildMessageIndexJob;
use MediaWiki\Extension\Translate\Statistics\MessageGroupStats;
use MediaWiki\Extension\Translate\Synchronization\UpdateMessageJob;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use RunnableJob;

/**
 * Job for updating translation units and translation pages when
 * a translatable page is marked for translation.
 */
class UpdateTranslatablePageJob extends GenericTranslateJob {
	private const MAX_TRIES = 3;

	/** @inheritDoc */
	public function __construct( Title $title, array $params = [] ) {
		parent::__construct( 'UpdateTranslatablePageJob', $title, $params );
	}

	/**
	 * Create a job that updates a translation page.
	 *
	 * If a list of sections is provided, then the job will also update translation
	 * unit pages.
	 *
	 * @param TranslatablePage $page
	 * @param TranslationUnit[] $sections
	 */
	public static function newFromPage( TranslatablePage $page, array $sections = [] ): self {
		$params = [];
		$params['sections'] = [];
		foreach ( $sections as $section ) {
			$params['sections'][] = $section->serializeToArray();
		}

		return new self( $page->getTitle(), $params );
	}

	public function run(): bool {
		// WARNING: Nothing here must not depend on message index being up to date.
		// For performance reasons, message index rebuild is run a separate job after
		// everything else is updated.

		// START: This section does not care about replication lag
		$this->logInfo( 'Starting UpdateTranslatablePageJob' );

		$sections = $this->params['sections'];
		foreach ( $sections as $index => $section ) {
			// Old jobs stored sections as objects because they were serialized and
			// unserialized transparently. That is no longer supported, so we
			// convert manually to primitive types first (to an PHP array).
			if ( is_array( $section ) ) {
				$sections[$index] = TranslationUnit::unserializeFromArray( $section );
			}
		}

		/**
		 * Units should be updated before the render jobs are run so that the
		 * latest changes can take effect on the translation pages.
		 */
		$page = TranslatablePage::newFromTitle( $this->title );
		$unitJobs = self::getTranslationUnitJobs( $page, $sections );
		foreach ( $unitJobs as $job ) {
			$job->run();
		}

		$this->logInfo(
			'Finished running ' . count( $unitJobs ) . ' MessageUpdate jobs for '
			. count( $sections ) . ' sections'
		);
		// END: This section does not care about replication lag
		$mwServices = MediaWikiServices::getInstance();
		$lb = $mwServices->getDBLoadBalancerFactory();
		if ( !$lb->waitForReplication() ) {
			$this->logWarning( 'Continuing despite replication lag' );
		}

		$attemptsCount = 0;
		do {
			// Ensure we are using the latest group definitions. This is needed so long-running
			// scripts detect the page which was just marked for translation. Otherwise, getMessageGroup
			// in the next line returns null. There is no need to regenerate the global cache.
			MessageGroups::singleton()->clearProcessCache();
			// Ensure fresh definitions for stats

			// Message group may return null due to stale caches, attempt to fetch the group a few
			// times before giving up.
			$messageGroup = $page->getMessageGroup();
			++$attemptsCount;
			if ( $messageGroup ) {
				break;
			}

			// The message group cache regen time on production is around 600ms
			usleep( 500 * 1000 );
		} while ( $attemptsCount <= self::MAX_TRIES );

		if ( $messageGroup ) {
			$messageGroup->clearCaches();
			$this->logInfo(
				'Cleared caches after {attemptsCount} attempt(s)',
				[ 'attemptsCount' => $attemptsCount ]
			);
		} else {
			$this->logWarning(
				'No message group found for page {pageTitle} after {attemptsCount} attempt(s)',
				[
					'pageTitle' => $page->getTitle()->getPrefixedText(),
					'attemptsCount' => self::MAX_TRIES
				]
			);
		}

		// Refresh translations statistics, we want these to be up to date for the
		// RenderJobs, for displaying up to date statistics on the translation pages.
		$id = $page->getMessageGroupId();
		MessageGroupStats::forGroup(
			$id,
			MessageGroupStats::FLAG_NO_CACHE | MessageGroupStats::FLAG_IMMEDIATE_WRITES
		);
		$this->logInfo( 'Updated the message group stats' );

		// Try to avoid stale statistics on the base page
		$wikiPage = $mwServices->getWikiPageFactory()->newFromTitle( $page->getTitle() );
		$wikiPage->doPurge();
		$this->logInfo( 'Finished purging' );

		// These can be run independently and in parallel if possible
		$jobQueueGroup = $mwServices->getJobQueueGroup();
		$renderJobs = self::getRenderJobs( $page );
		$jobQueueGroup->push( $renderJobs );
		$this->logInfo( 'Added ' . count( $renderJobs ) . ' RenderJobs to the queue' );

		// Schedule message index update. Thanks to front caching, it is okay if this takes
		// a while (and on large wikis it does take a while!). Running it as a separate job
		// also allows de-duplication in case multiple translatable pages are being marked
		// for translation in a short period of time.
		$job = RebuildMessageIndexJob::newJob();
		$jobQueueGroup->push( $job );

		$this->logInfo( 'Finished UpdateTranslatablePageJob' );

		return true;
	}

	/**
	 * Creates jobs needed to create or update all translation unit definition pages.
	 * @param TranslatablePage $page
	 * @param TranslationUnit[] $units
	 * @return RunnableJob[]
	 */
	private static function getTranslationUnitJobs( TranslatablePage $page, array $units ): array {
		$jobs = [];

		$code = $page->getSourceLanguageCode();
		$prefix = $page->getTitle()->getPrefixedText();

		foreach ( $units as $unit ) {
			$unitName = $unit->id;
			$title = Title::makeTitle( NS_TRANSLATIONS, "$prefix/$unitName/$code" );

			$fuzzy = $unit->type === 'changed';
			$jobs[] = UpdateMessageJob::newJob( $title, $unit->getTextWithVariables(), $fuzzy );
		}

		return $jobs;
	}

	/**
	 * Creates jobs needed to create or update all translation pages.
	 * @return RunnableJob[]
	 */
	public static function getRenderJobs( TranslatablePage $page, bool $nonPrioritizedJobs = false ): array {
		$documentationLanguageCode = MediaWikiServices::getInstance()
			->getMainConfig()
			->get( 'TranslateDocumentationLanguageCode' );

		$jobs = [];

		$jobTitles = $page->getTranslationPages();
		// Ensure that we create the source language page when page is marked for translation.
		$jobTitles[] = $page->getTitle()->getSubpage( $page->getSourceLanguageCode() );
		// In some cases translation page may be missing even though translations exist. One such case
		// is when FuzzyBot makes edits, which suppresses render jobs. There may also be bugs with the
		// render jobs failing. Add jobs based on message group stats to create self-healing process.
		$stats = MessageGroupStats::forGroup( $page->getMessageGroupId() );
		foreach ( $stats as $languageCode => $languageStats ) {
			if ( $languageStats[MessageGroupStats::TRANSLATED] > 0 && $languageCode !== $documentationLanguageCode ) {
				$jobTitles[] = $page->getTitle()->getSubpage( $languageCode );
			}
		}

		// These jobs can be deduplicated by the job queue as well, but it's simple to do it here ourselves.
		// Titles have __toString method that returns the prefixed text so array_unique should work.
		$jobTitles = array_unique( $jobTitles );
		foreach ( $jobTitles as $t ) {
			if ( $nonPrioritizedJobs ) {
				$jobs[] = RenderTranslationPageJob::newNonPrioritizedJob( $t );
			} else {
				$jobs[] = RenderTranslationPageJob::newJob( $t );
			}

		}

		return $jobs;
	}
}
