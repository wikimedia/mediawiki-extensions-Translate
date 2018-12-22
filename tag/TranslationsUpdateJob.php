<?php
/**
 * Job for updating translation units and translation pages when
 * a translatable page is marked for translation.
 *
 * @note MessageUpdateJobs from getTranslationUnitJobs() should be run
 * before the TranslateRenderJobs are run so that the latest changes can
 * take effect on the translation pages.
 *
 * @since 2016.03
 */
class TranslationsUpdateJob extends Job {
	/**
	 * @inheritDoc
	 */
	public function __construct( Title $title, $params = [] ) {
		parent::__construct( __CLASS__, $title, $params );
	}

	/**
	 * Create a job that updates a translation page.
	 *
	 * If a list of sections is provided, then the job will also update translation
	 * unit pages.
	 *
	 * @param TranslatablePage $page
	 * @param TPSection[] $sections
	 * @return TranslationsUpdateJob
	 * @since 2018.07
	 */
	public static function newFromPage( TranslatablePage $page, array $sections = [] ) {
		$params = [];
		$params[ 'sections' ] = [];
		foreach ( $sections as $section ) {
			$params[ 'sections' ][] = $section->serializeToArray();
		}

		return new self( $page->getTitle(), $params );
	}

	public function run() {
		$page = TranslatablePage::newFromTitle( $this->title );
		$sections = $this->params[ 'sections' ];
		foreach ( $sections as $index => $section ) {
			// Old jobs stored sections as objects because they were serialized and
			// unserialized transparently. That is no longer supported, so we
			// convert manually to primitive types first (to an PHP array).
			if ( is_array( $section ) ) {
				$sections[ $index ] = TPSection::unserializeFromArray( $section );
			}
		}

		// Units should be updated before the render jobs are run
		$unitJobs = self::getTranslationUnitJobs( $page, $sections );
		foreach ( $unitJobs as $job ) {
			$job->run();
		}

		// Ensure we are using the latest group definitions. This is needed so
		// that in long running scripts we do see the page which was just
		// marked for translation. Otherwise getMessageGroup in the next line
		// returns null. There is no need to regenerate the global cache.
		MessageGroups::singleton()->clearProcessCache();
		// Ensure fresh definitions for MessageIndex and stats
		$page->getMessageGroup()->clearCaches();

		MessageIndex::singleton()->rebuild();

		// Refresh translations statistics
		$id = $page->getMessageGroupId();
		MessageGroupStats::forGroup( $id, MessageGroupStats::FLAG_NO_CACHE );

		$wikiPage = WikiPage::factory( $page->getTitle() );
		$wikiPage->doPurge();

		$renderJobs = self::getRenderJobs( $page );
		JobQueueGroup::singleton()->push( $renderJobs );
		return true;
	}

	/**
	 * Creates jobs needed to create or update all translation page definitions.
	 * @param TranslatablePage $page
	 * @param TPSection[] $sections
	 * @return Job[]
	 * @since 2013-01-28
	 */
	public static function getTranslationUnitJobs( TranslatablePage $page, array $sections ) {
		$jobs = [];

		$code = $page->getSourceLanguageCode();
		$prefix = $page->getTitle()->getPrefixedText();

		foreach ( $sections as $s ) {
			$unit = $s->name;
			$title = Title::makeTitle( NS_TRANSLATIONS, "$prefix/$unit/$code" );

			$fuzzy = $s->type === 'changed';
			$jobs[] = MessageUpdateJob::newJob( $title, $s->getTextWithVariables(), $fuzzy );
		}

		return $jobs;
	}

	/**
	 * Creates jobs needed to create or update all translation pages.
	 * @param TranslatablePage $page
	 * @return Job[]
	 * @since 2013-01-28
	 */
	public static function getRenderJobs( TranslatablePage $page ) {
		$jobs = [];

		$jobTitles = $page->getTranslationPages();
		// $jobTitles may have the source language title already but duplicate TranslateRenderJobs
		// are not executed so it's not run twice for the source language page present. This is
		// added to ensure that we create the source language page from the very beginning.
		$sourceLangTitle = $page->getTitle()->getSubpage( $page->getSourceLanguageCode() );
		$jobTitles[] = $sourceLangTitle;
		foreach ( $jobTitles as $t ) {
			$jobs[] = TranslateRenderJob::newJob( $t );
		}

		return $jobs;
	}

}
