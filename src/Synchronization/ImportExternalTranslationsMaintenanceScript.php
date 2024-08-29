<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use Exception;
use FileBasedMessageGroup;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageSync\MessageSourceChange;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Utilities\BaseMaintenanceScript;
use MediaWiki\MediaWikiServices;
use MediaWiki\SpecialPage\SpecialPage;
use MessageGroup;

/** Script for processing message changes in file based message groups. */
class ImportExternalTranslationsMaintenanceScript extends BaseMaintenanceScript {
	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Script for processing message changes in file based message groups' );
		$this->addOption(
			'group',
			'(optional) Comma separated list of group IDs to process (can use * as wildcard). ' .
				'Default: "*"',
			self::OPTIONAL,
			self::HAS_ARG
		);
		$this->addOption(
			'skipgroup',
			'(optional) Comma separated list of group IDs to not process (can use * ' .
				'as wildcard). Overrides --group parameter.',
			self::OPTIONAL,
			self::HAS_ARG
		);
		$this->addOption(
			'name',
			'(optional) Unique name to avoid conflicts with multiple invocations of this script.',
			self::OPTIONAL,
			self::HAS_ARG
		);
		$this->addOption(
			'safe-import',
			'(optional) Import "safe" changes: message additions when no other kind of changes.'
		);
		$this->addOption(
			'skip-group-sync-check',
			'(optional) Skip importing group if synchronization is still in progress or if there ' .
				'was an error during synchronization. See: ' .
				'https://www.mediawiki.org/wiki/Help:Extension:Translate/Group_management#Strong_synchronization'
		);
		$this->addOption(
			'import-non-renames',
			'(optional) Import non renames: if a language in a group has only additions and changes to existing ' .
			' strings, then the additions are imported'
		);
		$this->requireExtension( 'Translate' );
	}

	public function execute() {
		$name = $this->getOption( 'name', MessageChangeStorage::DEFAULT_NAME );
		if ( !MessageChangeStorage::isValidCdbName( $name ) ) {
			$this->fatalError( 'Invalid name' );
		}

		$groups = $this->getGroups();
		$changes = [];
		$comparator = Services::getInstance()->getExternalMessageSourceStateComparator();

		$importStrategy = $this->getImportStrategy();
		$skipGroupSyncCache = $this->hasOption( 'skip-group-sync-check' );

		$services = Services::getInstance();
		$importer = $services->getExternalMessageSourceStateImporter();

		$statusFormatter = MediaWikiServices::getInstance()
			->getFormatterFactory()
			->getStatusFormatter( RequestContext::getMain() );

		foreach ( $groups as $id => $group ) {
			$status = $importer->canImportGroup( $group, $skipGroupSyncCache );
			if ( !$status->isOK() ) {
				$this->error( $statusFormatter->getMessage( $status )->plain() );
				continue;
			}

			try {
				$changes[$id] = $comparator->processGroup( $group );
			} catch ( Exception $e ) {
				$errorMsg = "Exception occurred while processing group: $id.\nException: $e";
				$this->error( $errorMsg );
				error_log( $errorMsg );
			}
		}

		// Remove all groups without changes
		$changes = array_filter( $changes, static function ( MessageSourceChange $change ) {
			return $change->getAllModifications() !== [];
		} );

		if ( $changes === [] ) {
			if ( $importStrategy === ExternalMessageSourceStateImporter::IMPORT_NONE ) {
				$this->output( "No changes found\n" );
			}

			return;
		}

		$info = $importer->import( $changes, $name, $importStrategy );
		$this->printChangeInfo( $info );
	}

	/**
	 * Gets list of message groups filtered by user input.
	 * @return FileBasedMessageGroup[]
	 */
	private function getGroups(): array {
		$groups = MessageGroups::getGroupsByType( FileBasedMessageGroup::class );

		// Include all if option not given
		$include = $this->getOption( 'group', '*' );
		$include = explode( ',', $include );
		$include = array_map( 'trim', $include );
		$include = MessageGroups::expandWildcards( $include );

		// Exclude nothing if option not given
		$exclude = $this->getOption( 'skipgroup', '' );
		$exclude = explode( ',', $exclude );
		$exclude = array_map( 'trim', $exclude );
		$exclude = MessageGroups::expandWildcards( $exclude );

		// Flip to allow isset
		$include = array_flip( $include );
		$exclude = array_flip( $exclude );

		return array_filter( $groups,
			static function ( MessageGroup $group ) use ( $include, $exclude ) {
				$id = $group->getId();

				return isset( $include[$id] ) && !isset( $exclude[$id] );
			}
		);
	}

	private function printChangeInfo( array $info ): void {
		foreach ( $info['processed'] as $group => $languages ) {
			$newMessageCount = array_sum( $languages );
			if ( $newMessageCount ) {
				$this->output( "Imported $newMessageCount new messages or translations for $group.\n" );
			}
		}

		if ( $info['skipped'] !== [] ) {
			$skipped = implode( ', ', array_keys( $info['skipped'] ) );
			$this->output( "There are changes to check for groups $skipped.\n" );
			$url = SpecialPage::getTitleFor( 'ManageMessageGroups', $info['name'] )->getFullURL();
			$this->output( "You can process them at $url\n" );
		}
	}

	private function getImportStrategy(): int {
		$importStrategy = ExternalMessageSourceStateImporter::IMPORT_NONE;
		if ( $this->hasOption( 'safe-import' ) ) {
			$importStrategy = ExternalMessageSourceStateImporter::IMPORT_SAFE;
		}

		if ( $this->hasOption( 'import-non-renames' ) ) {
			$importStrategy = ExternalMessageSourceStateImporter::IMPORT_NON_RENAMES;
		}

		return $importStrategy;
	}
}
