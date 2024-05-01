<?php
declare ( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageLoading;

use MediaWiki\MediaWikiServices;
use Wikimedia\Rdbms\ILoadBalancer;

/**
 * Storage on the database itself.
 *
 * This is likely to be the slowest backend. However, it scales okay
 * and provides random access. It also doesn't need any special setup,
 * the database table is added with update.php together with other tables,
 * which is the reason this is the default backend. It also works well
 * on multi-server setup without needing for shared file storage.
 */
class DatabaseMessageIndex extends MessageIndex {
	private ?array $index = null;
	private ILoadBalancer $loadBalancer;

	public function __construct() {
		parent::__construct();
		$this->loadBalancer = MediaWikiServices::getInstance()->getDBLoadBalancer();
	}

	protected function lock(): bool {
		$dbw = $this->loadBalancer->getConnection( DB_PRIMARY );

		// Any transaction should be flushed after getting the lock to avoid
		// stale pre-lock REPEATABLE-READ snapshot data.
		$ok = $dbw->lock( 'translate-messageindex', __METHOD__, 5 );
		if ( $ok ) {
			$dbw->commit( __METHOD__, 'flush' );
		}

		return $ok;
	}

	protected function unlock(): bool {
		$fname = __METHOD__;
		$dbw = $this->loadBalancer->getConnection( DB_PRIMARY );
		// Unlock once the rows are actually unlocked to avoid deadlocks
		if ( !$dbw->trxLevel() ) {
			$dbw->unlock( 'translate-messageindex', $fname );
		} else {
			$dbw->onTransactionResolution( static function () use ( $dbw, $fname ) {
				$dbw->unlock( 'translate-messageindex', $fname );
			}, $fname );
		}

		return true;
	}

	public function retrieve( bool $readLatest = false ): array {
		if ( $this->index !== null && !$readLatest ) {
			return $this->index;
		}

		$dbr = $this->loadBalancer->getConnection( $readLatest ? DB_PRIMARY : DB_REPLICA );
		$res = $dbr->newSelectQueryBuilder()
			->select( '*' )
			->from( 'translate_messageindex' )
			->caller( __METHOD__ )
			->fetchResultSet();
		$this->index = [];
		foreach ( $res as $row ) {
			$this->index[$row->tmi_key] = $this->unserialize( $row->tmi_value );
		}

		return $this->index;
	}

	/** @inheritDoc */
	protected function get( $key ) {
		$dbr = $this->loadBalancer->getConnection( DB_REPLICA );
		$value = $dbr->newSelectQueryBuilder()
			->select( 'tmi_value' )
			->from( 'translate_messageindex' )
			->where( [ 'tmi_key' => $key ] )
			->caller( __METHOD__ )
			->fetchField();

		return is_string( $value ) ? $this->unserialize( $value ) : null;
	}

	protected function store( array $array, array $diff ): void {
		$updates = [];

		foreach ( [ $diff['add'], $diff['mod'] ] as $changes ) {
			foreach ( $changes as $key => $data ) {
				[ , $new ] = $data;
				$updates[] = [
					'tmi_key' => $key,
					'tmi_value' => $this->serialize( $new ),
				];
			}
		}

		$index = [ 'tmi_key' ];
		$deletions = array_keys( $diff['del'] );

		$dbw = $this->loadBalancer->getConnection( DB_PRIMARY );
		$dbw->startAtomic( __METHOD__ );

		if ( $updates !== [] ) {
			$dbw->replace( 'translate_messageindex', [ $index ], $updates, __METHOD__ );
		}

		if ( $deletions !== [] ) {
			$dbw->delete( 'translate_messageindex', [ 'tmi_key' => $deletions ], __METHOD__ );
		}

		$dbw->endAtomic( __METHOD__ );

		$this->index = $array;
	}
}
