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
	 * @param Title $title
	 * @param array $params
	 * @param int $id
	 */
	public function __construct( Title $title, array $params, $id = 0 ) {
		parent::__construct( __CLASS__, $title, $params, $id );

		$this->page = TranslatablePage::newFromTitle( $title );
		$this->sections = $params['sections'];
	}

	public function run() {
		// Units should be updated before the render jobs are run
		$unitJobs = self::getTranslationUnitJobs( $this->page, $this->sections );
		foreach ( $unitJobs as $job ) {
			$job->run();
		}

		$renderJobs = self::getRenderJobs( $this->page );
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
		$jobs = array();

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
		$jobs = array();

		$titles = $page->getTranslationPages();
		foreach ( $titles as $t ) {
			$jobs[] = TranslateRenderJob::newJob( $t );
		}

		return $jobs;
	}

}
