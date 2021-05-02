<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use ActorMigration;
use Config;
use MediaWiki\Config\ServiceOptions;
use Wikimedia\Rdbms\ILoadBalancer;

/**
 * Gathers translator activity from the database.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2020.04
 */
class TranslatorActivityQuery {
	public const USER_NAME = 0;
	public const USER_TRANSLATIONS = 1;
	public const USER_LAST_ACTIVITY = 2;
	/** @var Config|ServiceOptions */
	private $options;
	/** @var ILoadBalancer */
	private $loadBalancer;

	public function __construct( $options, ILoadBalancer $loadBalancer ) {
		$this->options = $options;
		$this->loadBalancer = $loadBalancer;
	}

	/**
	 * Fetch the translators for a language
	 *
	 * @param string $code Language tag
	 * @return array<int,array<string|int|string>> Translation stats per user
	 */
	public function inLanguage( string $code ): array {
		$dbr = $this->loadBalancer->getConnection( DB_REPLICA, 'vslow' );

		$actorQuery = ActorMigration::newMigration()->getJoin( 'rev_user' );

		$tables = [ 'page', 'revision' ] + $actorQuery['tables'];
		$fields = [
			'rev_user_text' => $actorQuery['fields']['rev_user_text'],
			'MAX(rev_timestamp) as lastedit',
			'count(page_id) as count',
		];
		$conds = [
			'page_title' . $dbr->buildLike( $dbr->anyString(), '/', $code ),
			'page_namespace' => $this->options->get( 'TranslateMessageNamespaces' ),
		];
		$options = [
			'GROUP BY' => $actorQuery['fields']['rev_user_text'],
			'ORDER BY' => 'NULL',
		];
		$joins = [
				'revision' => [ 'JOIN', 'page_id=rev_page' ],
			] + $actorQuery['joins'];

		$res = $dbr->select( $tables, $fields, $conds, __METHOD__, $options, $joins );

		$data = [];
		foreach ( $res as $row ) {
			// Warning: user names may be numbers that get casted to ints in array keys
			$data[] = [
				self::USER_NAME => $row->rev_user_text,
				self::USER_TRANSLATIONS => (int)$row->count,
				self::USER_LAST_ACTIVITY => $row->lastedit,
			];
		}

		return $data;
	}

	/**
	 * Fetch the translators for all languages.
	 *
	 * This is faster than doing each language separately.
	 *
	 * @return array<string,array<int,array<string|int|string>>> Map of language tags to
	 * translation stats per user
	 */
	public function inAllLanguages(): array {
		$dbr = $this->loadBalancer->getConnection( DB_REPLICA, 'vslow' );

		$actorQuery = ActorMigration::newMigration()->getJoin( 'rev_user' );

		$tables = [ 'page', 'revision' ] + $actorQuery['tables'];
		$fields = [
			'rev_user_text' => $actorQuery['fields']['rev_user_text'],
			'substring_index(page_title, \'/\', -1) as lang',
			'MAX(rev_timestamp) as lastedit',
			'count(page_id) as count',
		];
		$conds = [
			'page_title' . $dbr->buildLike( $dbr->anyString(), '/', $dbr->anyString() ),
			'page_namespace' => $this->options->get( 'TranslateMessageNamespaces' ),
		];
		$options = [
			'GROUP BY' => [ 'lang', $actorQuery['fields']['rev_user_text'] ],
			'ORDER BY' => 'NULL',
		];

		$joins = [
				'revision' => [ 'JOIN', 'page_id=rev_page' ],
			] + $actorQuery['joins'];

		$res = $dbr->select( $tables, $fields, $conds, __METHOD__, $options, $joins );

		$data = [];
		foreach ( $res as $row ) {
			// Warning: user names may be numbers that get casted to ints in array keys
			$data[$row->lang][] = [
				self::USER_NAME => $row->rev_user_text,
				self::USER_TRANSLATIONS => (int)$row->count,
				self::USER_LAST_ACTIVITY => $row->lastedit,
			];
		}

		return $data;
	}
}
