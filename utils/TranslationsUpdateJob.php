<?php
/**
 * Job for updating translation units and translation pages when
 * a page is marked for translation.
 *
 * @note MessageUpdateJobs should be run before the TranslateRenderJobs
 * so that the latest changes can take effect on the translatable pages.
 */
class TranslationsUpdateJob extends Job {
	/**
	 * @param Title $title
	 * @param array $params
	 * @param int $id
	 */
	public function __construct( Title $title, array $params, $id = 0 ) {
		parent::__construct( __CLASS__, $title, $params, $id );

		$this->page = $params['page'];
		$this->sections = $params['sections'];
	}

	public function run() {
		// Units should be updated before the render jobs are run
		$unitJobs = SpecialPageTranslation::getTranslationUnitJobs( $this->page, $this->sections );
		foreach ( $unitJobs as $job ) {
			$job->run();
		}

		$renderJobs = SpecialPageTranslation::getRenderJobs( $this->page );
		JobQueueGroup::singleton()->push( $renderJobs );
		return true;
	}
}
