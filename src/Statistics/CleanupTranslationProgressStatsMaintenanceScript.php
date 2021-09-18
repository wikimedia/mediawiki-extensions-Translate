<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use Maintenance;
use MediaWiki\MediaWikiServices;
use MessageGroups;
use RawMessage;
use TranslateUtils;
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
		$db = $services->getDBLoadBalancer()->getConnectionRef( DB_PRIMARY );

		$dbGroupIds = $db->selectFieldValues(
			'translate_groupstats',
			'DISTINCT(tgs_group)',
			'*',
			__METHOD__
		);
		$knownGroupIds = array_map(
			'MessageGroupStats::getDatabaseIdForGroupId',
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
			$db->delete(
				'translate_groupstats',
				[ 'tgs_group' => $unknownGroupIds ],
				__METHOD__
			);
		}

		$dbLanguages = $db->selectFieldValues(
			'translate_groupstats',
			'DISTINCT(tgs_lang)',
			'*',
			__METHOD__
		);
		$knownLanguages = array_keys( TranslateUtils::getLanguageNames( 'en' ) );
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
			$db->delete(
				'translate_groupstats',
				[ 'tgs_lang' => $unknownLanguages ],
				__METHOD__
			);
		}

		$this->output( "Done.\n" );
	}
}
