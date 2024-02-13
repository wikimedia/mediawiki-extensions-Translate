<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Diagnostics;

use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageProcessing\StringMatcher;
use MediaWiki\Extension\Translate\Statistics\RebuildMessageGroupStatsJob;
use MediaWiki\Extension\Translate\Utilities\BaseMaintenanceScript;
use MediaWiki\Language\RawMessage;
use MediaWiki\MediaWikiServices;
use TranslateUtils;

/**
 * This maintenance script is responsible for refreshing the cached translation progress stats
 * to fix any outdated entries.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2024.02
 */
class RefreshTranslationProgressStats extends BaseMaintenanceScript {
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
		$groupInput = $this->getOption( 'group', '*' );
		$groupIdPattern = self::commaList2Array( $groupInput );
		$groupIds = MessageGroups::expandWildcards( $groupIdPattern );
		$groups = MessageGroups::getGroupsById( $groupIds );

		// All language codes are internally lower case in MediaWiki
		$languageInput = strtolower( $this->getOption( 'language', '*' ) );
		$languagePattern = self::commaList2Array( $languageInput );
		$languages = $this->getLanguages( $languagePattern );

		$useJobQueue = $this->hasOption( 'use-job-queue' );
		$jobs = [];

		foreach ( $groups as $groupId => $group ) {
			foreach ( $languages as $language ) {
				$jobs[] = RebuildMessageGroupStatsJob::newJob( [
					RebuildMessageGroupStatsJob::REFRESH => true,
					RebuildMessageGroupStatsJob::GROUP_ID => $groupId,
					RebuildMessageGroupStatsJob::LANGUAGE_CODE => $language
				] );
			}
		}

		$jobCount = count( $jobs );
		if ( $useJobQueue ) {
			MediaWikiServices::getInstance()->getJobQueueGroup()->push( $jobs );
			$message = new RawMessage( "Queued {{PLURAL:$1|$1 job|$1 jobs}}.\n", [ $jobCount ] );
			$this->output( $message->text() );
		} else {
			foreach ( $jobs as $i => $job ) {
				$this->output( "\033[0K\r" . $this->cliProgressBar( $jobCount, $i + 1 ) );
				$job->run();
			}
			$this->output( "\n" );
		}
	}

	private static function commaList2Array( string $list ): array {
		return array_map( 'trim', explode( ',', $list ) );
	}

	private function getLanguages( array $patterns ): array {
		$allLanguages = array_keys( TranslateUtils::getLanguageNames( null ) );

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

	private function cliProgressBar( int $total, int $current ): string {
		$barLength = 50;
		$filledLength = (int)round( $barLength * $current / $total );
		$percent = $current / $total * 100;
		$bar = str_repeat( '#', $filledLength ) . str_repeat( '-', ( $barLength - $filledLength ) );

		return sprintf( 'Progress: [%s] %.2f%%', $bar, $percent );
	}
}
