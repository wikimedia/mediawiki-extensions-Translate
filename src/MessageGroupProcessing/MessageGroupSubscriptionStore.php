<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use Wikimedia\Rdbms\IMaintainableDatabase;

/**
 * Store service for looking up and storing user subscriptions to message group
 * @since 2024.04
 * @license GPL-2.0-or-later
 * @author Abijeet Patro
 */
class MessageGroupSubscriptionStore {
	private const TABLE_NAME = 'translate_message_group_subscriptions';
	private ?bool $tableExists = null;
	private IMaintainableDatabase $dbMaintenance;

	public function __construct( IMaintainableDatabase $dbMaintenance ) {
		$this->dbMaintenance = $dbMaintenance;
	}

	public function doesTableExist(): bool {
		$this->tableExists ??= $this->dbMaintenance->tableExists( self::TABLE_NAME, __METHOD__ );
		return $this->tableExists;
	}
}
