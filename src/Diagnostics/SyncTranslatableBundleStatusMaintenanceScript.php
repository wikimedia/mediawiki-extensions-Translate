<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Diagnostics;

use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundle;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleFactory;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleStatus;
use MediaWiki\Extension\Translate\PageTranslation\PageTranslationSpecialPage;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePage;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePageStatus;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Maintenance\LoggedUpdateMaintenance;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use RuntimeException;

/**
 * Script to identify the status of the translatable bundles in the rev_tag table
 * and update them in the translatable_bundles page.
 */
class SyncTranslatableBundleStatusMaintenanceScript extends LoggedUpdateMaintenance {
	private const INDENT_SPACER = '  ';
	private const STATUS_NAME_MAPPING = [
		TranslatablePageStatus::PROPOSED => 'Proposed',
		TranslatablePageStatus::ACTIVE => 'Active',
		TranslatablePageStatus::OUTDATED => 'Outdated',
		TranslatablePageStatus::BROKEN => 'Broken'
	];
	private const SYNC_BATCH_STATUS = 15;
	private const SCRIPT_VERSION = 1;

	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Sync translatable bundle status with values from the rev_tag table' );
		$this->requireExtension( 'Translate' );
	}

	/** @inheritDoc */
	protected function getUpdateKey(): string {
		return __CLASS__ . '_v' . self::SCRIPT_VERSION;
	}

	/** @inheritDoc */
	protected function doDBUpdates(): bool {
		$this->output( "... Syncing translatable bundle status ...\n\n" );

		$this->output( "Fetching translatable bundles and their statues\n\n" );
		$translatableBundles = $this->fetchTranslatableBundles();
		$translatableBundleStatuses = Services::getInstance()
			->getTranslatableBundleStatusStore()
			->getAllWithStatus();

		$differences = $this->identifyDifferences( $translatableBundles, $translatableBundleStatuses );

		$this->outputDifferences( $differences['missing'], 'Missing' );
		$this->outputDifferences( $differences['incorrect'], 'Incorrect' );
		$this->outputDifferences( $differences['extra'], 'Extra' );

		$this->output( "\nSynchronizing...\n\n" );

		$this->syncStatus( $differences['missing'], 'Missing' );
		$this->syncStatus( $differences['incorrect'], 'Incorrect' );
		$this->removeStatus( $differences['extra'] );

		$this->output( "\n...Done syncing translatable status...\n" );

		return true;
	}

	private function fetchTranslatableBundles(): array {
		// Fetch the translatable pages
		$resultWrapper = PageTranslationSpecialPage::loadPagesFromDB();
		return PageTranslationSpecialPage::buildPageArray( $resultWrapper );

		// TODO: Fetch message bundles
	}

	/**
	 * This function compares the bundles and bundles statuses to identify,
	 * - Missing bundles in translatable statuses
	 * - Extra bundles in translatable statuses
	 * - Incorrect statuses in translatable statuses
	 * The data from the rev_tag table is treated as the source of truth.
	 */
	private function identifyDifferences(
		array $translatableBundles,
		array $translatableBundleStatuses
	): array {
		$result = [
			'missing' => [],
			'extra' => [],
			'incorrect' => []
		];

		$bundleFactory = Services::getInstance()->getTranslatableBundleFactory();
		foreach ( $translatableBundles as $bundleId => $bundleInfo ) {
			$title = $bundleInfo['title'];
			$bundle = $this->getTranslatableBundle( $bundleFactory, $title );
			$bundleStatus = $this->determineStatus( $bundle, $bundleInfo );

			if ( !$bundleStatus ) {
				// Ignore pages for which status could not be determined.
				continue;
			}

			if ( !isset( $translatableBundleStatuses[$bundleId] ) ) {
				// Identify missing records in translatable_bundles
				$response = [
					'title' => $title,
					'status' => $bundleStatus,
					'page_id' => $bundleId
				];
				$result['missing'][] = $response;
			} elseif ( !$bundleStatus->isEqual( $translatableBundleStatuses[$bundleId] ) ) {
				// Identify incorrect records in translatable_bundles
				$response = [
					'title' => $title,
					'status' => $bundleStatus,
					'page_id' => $bundleId
				];
				$result['incorrect'][] = $response;
			}
		}

		// Identify extra records in translatable_bundles
		$extraStatusBundleIds = array_diff_key( $translatableBundleStatuses, $translatableBundles );
		foreach ( $extraStatusBundleIds as $extraBundleId => $statusId ) {
			$title = Title::newFromID( $extraBundleId );
			$response = [
				'title' => $title,
				// TODO: This should be determined dynamically when we start supporting MessageBundles
				'status' => new TranslatablePageStatus( $statusId ),
				'page_id' => $extraBundleId
			];

			$result['extra'][] = $response;
		}

		return $result;
	}

	private function determineStatus(
		TranslatableBundle $bundle,
		array $bundleInfo
	): ?TranslatableBundleStatus {
		if ( $bundle instanceof TranslatablePage ) {
			return $bundle::determineStatus(
				$bundleInfo[RevTagStore::TP_READY_TAG] ?? null,
				$bundleInfo[RevTagStore::TP_MARK_TAG] ?? null,
				$bundleInfo['latest']
			);
		} else {
			// TODO: Add determineStatus as a function to TranslatableBundle abstract class and then
			// implement it in MessageBundle. It may not take the same set of parameters though.
			throw new RuntimeException( 'Method determineStatus not implemented for MessageBundle' );
		}
	}

	private function getTranslatableBundle(
		TranslatableBundleFactory $tbFactory,
		Title $title
	): TranslatableBundle {
		$bundle = $tbFactory->getBundle( $title );
		if ( $bundle ) {
			return $bundle;
		}

		// This page has a revision tag, lets assume that this is a translatable page
		// Broken pages for example will not be in the cache
		// TODO: Is there a better way to handle this?
		return TranslatablePage::newFromTitle( $title );
	}

	private function syncStatus( array $bundlesWithDifference, string $differenceType ): void {
		if ( !$bundlesWithDifference ) {
			$this->output( "No \"$differenceType\" bundle statuses\n" );
			return;
		}

		$this->output( "Syncing \"$differenceType\" bundle statuses\n" );

		$bundleFactory = Services::getInstance()->getTranslatableBundleFactory();
		$tpStore = Services::getInstance()->getTranslatablePageStore();
		$lbFactory = MediaWikiServices::getInstance()->getDBLoadBalancerFactory();

		$bundleCountProcessed = 0;
		foreach ( $bundlesWithDifference as $bundleInfo ) {
			$pageId = $bundleInfo['page_id'];
			$bundleTitle = $bundleInfo['title'] ?? null;
			if ( !$bundleTitle instanceof Title ) {
				$this->fatalError( "No title for page with id: $pageId \n" );
			}

			$bundle = $this->getTranslatableBundle( $bundleFactory, $bundleTitle );
			if ( $bundle instanceof TranslatablePage ) {
				// TODO: Eventually we want to add this method to the TranslatableBundleStore
				// and then call updateStatus on it. After that we won't have to check for the
				// type of the translatable bundle.
				$tpStore->updateStatus( $bundleTitle );
			}

			if ( $bundleCountProcessed % self::SYNC_BATCH_STATUS === 0 ) {
				$lbFactory->waitForReplication();
			}

			++$bundleCountProcessed;
		}

		$this->output( "Completed sync for \"$differenceType\" bundle statuses\n" );
	}

	private function removeStatus( array $extraBundleInfo ): void {
		if ( !$extraBundleInfo ) {
			$this->output( "No \"extra\" bundle statuses\n" );
			return;
		}
		$this->output( "Removing \"extra\" bundle statuses\n" );
		$pageIds = [];
		foreach ( $extraBundleInfo as $bundleInfo ) {
			$pageIds[] = $bundleInfo['page_id'];
		}

		$tbStatusStore = Services::getInstance()->getTranslatableBundleStatusStore();
		$tbStatusStore->removeStatus( ...$pageIds );
		$this->output( "Removed \"extra\" bundle statuses\n" );
	}

	private function outputDifferences( array $bundlesWithDifference, string $differenceType ): void {
		if ( $bundlesWithDifference ) {
			$this->output( "$differenceType translatable bundles statuses:\n" );
			foreach ( $bundlesWithDifference as $bundle ) {
				$this->outputBundleInfo( $bundle );
			}
		} else {
			$this->output( "No \"$differenceType\" translatable bundle statuses found!\n" );
		}
	}

	private function outputBundleInfo( array $bundle ): void {
		$titlePrefixedDbKey = $bundle['title'] instanceof Title ?
			$bundle['title']->getPrefixedDBkey() : '<Title not available>';
		$id = str_pad( (string)$bundle['page_id'], 7, ' ', STR_PAD_LEFT );
		$status = self::STATUS_NAME_MAPPING[$bundle['status']->getId()];
		$this->output( self::INDENT_SPACER . "* [Id: $id] $titlePrefixedDbKey: $status\n" );
	}
}
