<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extension\Translate\Statistics;

use ActorMigration;
use Config;
use MediaWiki\Config\ServiceOptions;
use Wikimedia\Rdbms\ILoadBalancer;

/**
 * Gathers translator activity from the database.
 *
 * @since 2020.04
 */
class TranslatorActivityQuery {
	public const USER_TRANSLATIONS = 0;
	public const USER_LAST_ACTIVITY = 1;
	private $options;
	private $loadBalancer;

	/**
	 * @param Config|ServiceOptions $options
	 * @param ILoadBalancer $loadBalancer
	 */
	public function __construct( $options, ILoadBalancer $loadBalancer ) {
		$this->options = $options;
		$this->loadBalancer = $loadBalancer;
	}

	/**
	 * Fetch the translators for a language
	 *
	 * @param string $code Language tag
	 * @return array<string,array<int|string>> Map of user name to translation stats
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
			$data[$row->rev_user_text] = [
				self::USER_TRANSLATIONS => $row->count,
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
	 * @return array<string,array<string,array<int|string>>> Map of language tags to user name to
	 * translation stats
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
			$data[$row->lang][$row->rev_user_text] = [
				self::USER_TRANSLATIONS => $row->count,
				self::USER_LAST_ACTIVITY => $row->lastedit,
			];
		}

		return $data;
	}
}

class_alias( TranslatorActivityQuery::class, '\MediaWiki\Extensions\Translate\TranslatorActivityQuery' );
