<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use MediaWiki\Config\Config;
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
	private Config $options;
	private ILoadBalancer $loadBalancer;

	public function __construct( Config $options, ILoadBalancer $loadBalancer ) {
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

		$res = $dbr->newSelectQueryBuilder()
			->select( [
				'rev_user_text' => 'actor_rev_user.actor_name',
				'lastedit' => 'MAX(rev_timestamp)',
				'count' => 'COUNT(page_id)',
			] )
			->from( 'page' )
			->join( 'revision', null, 'page_id=rev_page' )
			->join( 'actor', 'actor_rev_user', 'actor_rev_user.actor_id = rev_actor' )
			->where( [
				'page_title' . $dbr->buildLike( $dbr->anyString(), '/', $code ),
				'page_namespace' => $this->options->get( 'TranslateMessageNamespaces' ),
			] )
			->groupBy( 'actor_rev_user.actor_name' )
			->orderBy( 'NULL' )
			->caller( __METHOD__ )
			->fetchResultSet();

		$data = [];
		foreach ( $res as $row ) {
			// Warning: user names may be numbers that get cast to ints in array keys
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

		$res = $dbr->newSelectQueryBuilder()
			->select( [
				'rev_user_text' => 'actor_rev_user.actor_name',
				'lang' => 'substring_index(page_title, \'/\', -1)',
				'lastedit' => 'MAX(rev_timestamp)',
				'count' => 'COUNT(page_id)',
			] )
			->from( 'page' )
			->join( 'revision', null, 'page_id=rev_page' )
			->join( 'actor', 'actor_rev_user', 'actor_rev_user.actor_id = rev_actor' )
			->where( [
				'page_title' . $dbr->buildLike( $dbr->anyString(), '/', $dbr->anyString() ),
				'page_namespace' => $this->options->get( 'TranslateMessageNamespaces' ),
			] )
			->groupBy( [ 'lang', 'actor_rev_user.actor_name' ] )
			->orderBy( 'NULL' )
			->caller( __METHOD__ )
			->fetchResultSet();

		$data = [];
		foreach ( $res as $row ) {
			// Warning: user names may be numbers that get cast to ints in array keys
			$data[$row->lang][] = [
				self::USER_NAME => $row->rev_user_text,
				self::USER_TRANSLATIONS => (int)$row->count,
				self::USER_LAST_ACTIVITY => $row->lastedit,
			];
		}

		return $data;
	}
}
