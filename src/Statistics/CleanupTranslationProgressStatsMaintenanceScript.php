<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Language\RawMessage;
use MediaWiki\Maintenance\Maintenance;
use MediaWiki\MediaWikiServices;
use const DB_PRIMARY;

/**
 * @since 2021.03
 * @license GPL-2.0-or-later
 * @author Niklas Laxström
 */
class CleanupTranslationProgressStatsMaintenanceScript extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Remove obsolete entries from translate_groupstats table' );
		$this->requireExtension( 'Translate' );
	}

	public function execute() {
		$services = MediaWikiServices::getInstance();
		$db = $services->getDBLoadBalancer()->getConnection( DB_PRIMARY );

		$dbGroupIds = $db->newSelectQueryBuilder()
			->select( 'tgs_group' )
			->distinct()
			->from( 'translate_groupstats' )
			->caller( __METHOD__ )
			->fetchFieldValues();
		$knownGroupIds = array_map(
			[ MessageGroupStats::class, 'getDatabaseIdForGroupId' ],
			array_keys( MessageGroups::singleton()->getGroups() )
		);
		$unknownGroupIds = array_diff( $dbGroupIds, $knownGroupIds );

		if ( $unknownGroupIds !== [] ) {
			$msg = ( new RawMessage( "Removing rows for $1 unknown group{{PLURAL:$1||s}}:\n" ) )
				->numParams( count( $unknownGroupIds ) )
				->inLanguage( 'en' )
				->text();
			$this->output( $msg );
			foreach ( $unknownGroupIds as $id ) {
				$this->output( "* $id\n" );
			}
			$db->newDeleteQueryBuilder()
				->deleteFrom( 'translate_groupstats' )
				->where( [ 'tgs_group' => $unknownGroupIds ] )
				->caller( __METHOD__ )
				->execute();
		}

		$dbLanguages = $db->newSelectQueryBuilder()
			->select( 'tgs_lang' )
			->distinct()
			->from( 'translate_groupstats' )
			->caller( __METHOD__ )
			->fetchFieldValues();
		$knownLanguages = array_keys( Utilities::getLanguageNames( 'en' ) );
		$unknownLanguages = array_diff( $dbLanguages, $knownLanguages );

		if ( $unknownLanguages !== [] ) {
			$msg = ( new RawMessage( "Removing rows for $1 unknown language{{PLURAL:$1||s}}:\n" ) )
				->numParams( count( $unknownLanguages ) )
				->inLanguage( 'en' )
				->text();
			$this->output( $msg );
			foreach ( $unknownLanguages as $languageCode ) {
				$this->output( "* $languageCode\n" );
			}
			$db->newDeleteQueryBuilder()
				->deleteFrom( 'translate_groupstats' )
				->where( [ 'tgs_lang' => $unknownLanguages ] )
				->caller( __METHOD__ )
				->execute();
		}

		$this->output( "Done.\n" );
	}
}
