<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use Collation;
use InvalidArgumentException;
use MediaWiki\Extension\Translate\MessageBundleTranslation\MessageBundle;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePage;
use Title;
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
	/** @var IDatabase */
	private $database;
	/** @var Collation */
	private $collation;
	/** @var IMaintainableDatabase */
	private $dbMaintainance;
	/** @var ?bool */
	private $tableExists = null;

	public function __construct(
		IDatabase $database,
		Collation $collation,
		IMaintainableDatabase $dbMaintainance
	) {
		$this->database = $database;
		$this->collation = $collation;
		$this->dbMaintainance = $dbMaintainance;
	}

	public function setStatus( Title $title, TranslatableBundleStatus $status, string $bundleType ): void {
		if ( !$this->doesTableExist() ) {
			return;
		}

		$sortKey = substr( $this->collation->getSortKey( $title->getPrefixedDBkey() ), 0, 255 );
		$bundleTypeId = $this->getBundleTypeId( $bundleType );
		$this->database->replace(
			self::TABLE_NAME,
			[ 'ttb_page_id' ],
			[
				'ttb_page_id' => $title->getArticleID(),
				'ttb_type' => $bundleTypeId,
				'ttb_status' => $status->getId(),
				'ttb_sortkey' => $sortKey
			],
			__METHOD__
		);
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
		if ( $this->tableExists !== null ) {
			return $this->tableExists;
		}

		$this->tableExists = $this->dbMaintainance->tableExists( self::TABLE_NAME, __METHOD__ );
		return $this->tableExists;
	}
}
