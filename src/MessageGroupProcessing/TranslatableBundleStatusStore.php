<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use Collation;
use InvalidArgumentException;
use MediaWiki\Extension\Translate\MessageBundleTranslation\MessageBundle;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePage;
use MediaWiki\Title\Title;
use Wikimedia\Rdbms\IDatabase;
use Wikimedia\Rdbms\IMaintainableDatabase;

/**
 * Store service for looking up and storing status for translatable bundle status.
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2022.09
 */
class TranslatableBundleStatusStore {
	private const TABLE_NAME = 'translate_translatable_bundles';
	private IDatabase $database;
	private Collation $collation;
	private IMaintainableDatabase $dbMaintenance;
	private ?bool $tableExists = null;

	public function __construct(
		IDatabase $database,
		Collation $collation,
		IMaintainableDatabase $dbMaintenance
	) {
		$this->database = $database;
		$this->collation = $collation;
		$this->dbMaintenance = $dbMaintenance;
	}

	public function setStatus( Title $title, TranslatableBundleStatus $status, string $bundleType ): void {
		if ( !$this->doesTableExist() ) {
			return;
		}

		$sortKey = substr( $this->collation->getSortKey( $title->getPrefixedDBkey() ), 0, 255 );
		$bundleTypeId = $this->getBundleTypeId( $bundleType );
		$this->database->newReplaceQueryBuilder()
			->replaceInto( self::TABLE_NAME )
			->uniqueIndexFields( [ 'ttb_page_id' ] )
			->row( [
				'ttb_page_id' => $title->getArticleID(),
				'ttb_type' => $bundleTypeId,
				'ttb_status' => $status->getId(),
				'ttb_sortkey' => $sortKey
			] )
			->caller( __METHOD__ )
			->execute();
	}

	/** Return all bundles in an array with key being page id, value being status */
	public function getAllWithStatus(): array {
		if ( !$this->doesTableExist() ) {
			return [];
		}

		$resultSet = $this->database->newSelectQueryBuilder()
			->select( [ 'ttb_page_id', 'ttb_status' ] )
			->from( self::TABLE_NAME )
			->caller( __METHOD__ )
			->fetchResultSet();

		$result = [];
		foreach ( $resultSet as $row ) {
			$result[$row->ttb_page_id] = (int)$row->ttb_status;
		}

		return $result;
	}

	public function removeStatus( int ...$pageIds ): void {
		if ( !$this->doesTableExist() ) {
			return;
		}

		$this->database->newDeleteQueryBuilder()
			->deleteFrom( self::TABLE_NAME )
			->where( [ 'ttb_page_id' => $pageIds ] )
			->caller( __METHOD__ )
			->execute();
	}

	private function getBundleTypeId( string $bundle ): int {
		if ( $bundle === TranslatablePage::class ) {
			return 1;
		} elseif ( $bundle === MessageBundle::class ) {
			return 2;
		}

		throw new InvalidArgumentException( "Unknown translatable bundle type: $bundle" );
	}

	/** TODO: Remove this check once table is available on Wikimedia sites that use Translate */
	private function doesTableExist(): bool {
		if ( $this->tableExists === null ) {
			$this->tableExists = $this->dbMaintenance->tableExists( self::TABLE_NAME, __METHOD__ );
		}

		return $this->tableExists;
	}
}
