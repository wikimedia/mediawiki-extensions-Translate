<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Diagnostics;

use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageProcessing\StringMatcher;
use MediaWiki\Extension\Translate\Statistics\RebuildMessageGroupStatsJob;
use MediaWiki\Extension\Translate\Utilities\BaseMaintenanceScript;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Language\Language;
use MediaWiki\Language\RawMessage;

/**
 * This maintenance script is responsible for refreshing the cached translation progress stats
 * to fix any outdated entries.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2024.02
 */
class RefreshTranslationProgressStatsMaintenanceScript extends BaseMaintenanceScript {
	private Language $language;

	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Refresh cached message group stats to fix any outdated entries' );

		$this->addOption(
			'group',
			'Which groups to scan. Comma-separated list with wildcard (*) accepted.',
			self::OPTIONAL,
			self::HAS_ARG
		);
		$this->addOption(
			'language',
			'Which languages to scan. Comma-separated list with wildcard (*) accepted.',
			self::OPTIONAL,
			self::HAS_ARG
		);

		$this->addOption(
			'use-job-queue',
			'Use job queue (asynchronous).',
			self::OPTIONAL,
			self::NO_ARG
		);

		$this->requireExtension( 'Translate' );
	}

	/** @inheritDoc */
	public function execute() {
		$this->language = $this->getServiceContainer()->getContentLanguage();

		$groupInput = $this->getOption( 'group', '*' );
		$groupIdPattern = self::commaList2Array( $groupInput );
		$groupIds = MessageGroups::expandWildcards( $groupIdPattern );

		// All language codes are internally lower case in MediaWiki
		$languageInput = strtolower( $this->getOption( 'language', '*' ) );
		$languagePattern = self::commaList2Array( $languageInput );
		$languages = $this->getLanguages( $languagePattern );

		$useJobQueue = $this->hasOption( 'use-job-queue' );
		$jobQueueGroup = $this->getServiceContainer()->getJobQueueGroup();
		$jobCount = count( $groupIds ) * count( $languages );
		$counter = 0;

		$startTime = microtime( true );

		foreach ( $groupIds as $groupId ) {
			$jobs = [];
			foreach ( $languages as $language ) {
				$jobs[] = $job = RebuildMessageGroupStatsJob::newJob( [
					RebuildMessageGroupStatsJob::REFRESH => true,
					RebuildMessageGroupStatsJob::GROUP_ID => $groupId,
					RebuildMessageGroupStatsJob::LANGUAGE_CODE => $language
				] );

				if ( !$useJobQueue ) {
					$job->run();
				}
				$elapsed = microtime( true ) - $startTime;
				$this->output( "\033[0K\r" . $this->cliProgressBar( $jobCount, ++$counter, $elapsed ) );
			}

			if ( $useJobQueue ) {
				$jobQueueGroup->push( $jobs );
			}

		}

		if ( $useJobQueue ) {
			$message = new RawMessage( "\nQueued {{PLURAL:$1|$1 job|$1 jobs}}.", [ $jobCount ] );
			$this->output( $message->text() );
		}
		$this->output( "\n" );
	}

	private function getLanguages( array $patterns ): array {
		$allLanguages = array_keys( Utilities::getLanguageNames( null ) );

		if ( in_array( '*', $patterns, true ) ) {
			return $allLanguages;
		}

		$matchedLanguages = array_intersect( $patterns, $allLanguages );
		$patterns = array_diff( $patterns, $matchedLanguages );
		$remainingLanguages = array_diff( $allLanguages, $matchedLanguages );

		if ( $patterns === [] ) {
			return $matchedLanguages;
		}

		// Slow path for the ones with wildcards
		$matcher = new StringMatcher( '', $patterns );
		foreach ( $remainingLanguages as $id ) {
			if ( $matcher->matches( $id ) ) {
				$matchedLanguages[] = $id;
			}
		}

		return $matchedLanguages;
	}

	private function cliProgressBar( int $total, int $current, float $elapsed ): string {
		$barLength = 50;
		$progress = $current / $total;
		$filledLength = (int)round( $barLength * $progress );
		$bar = str_repeat( '#', $filledLength ) . str_repeat( '-', ( $barLength - $filledLength ) );
		$percent = $current / $total * 100;
		$estimatedRemaining = $this->language->formatDuration( (int)( $elapsed / $progress - $elapsed ) );

		return sprintf( 'Progress: [%s] %.2f%% | %s remaining', $bar, $percent, $estimatedRemaining );
	}
}
