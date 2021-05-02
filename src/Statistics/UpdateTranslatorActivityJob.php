<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extension\Translate\Statistics;

use GenericParameterJob;
use MediaWiki\Extension\Translate\Jobs\GenericTranslateJob;
use MediaWiki\Extension\Translate\Services;

/** @since 2020.04 */
class UpdateTranslatorActivityJob extends GenericTranslateJob implements GenericParameterJob {
	public function __construct( array $params ) {
		parent::__construct( 'UpdateTranslatorActivity', $params );
		$this->removeDuplicates = true;
	}

	public static function newJobForLanguage( string $language ): self {
		return new self( [ 'language' => $language ] );
	}

	public function run() {
		$activity = Services::getInstance()->getTranslatorActivity();

		try {
			$activity->updateLanguage( $this->getParams()['language'] );
		} catch ( StatisticsUnavailable $e ) {
			$this->logInfo( $e->getMessage() );
			// The job will be retried according to JobQueue configuration
			return false;
		}

		return true;
	}
}
