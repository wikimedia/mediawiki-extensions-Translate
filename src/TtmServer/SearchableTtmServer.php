<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TtmServer;

/**
 * Interface for TtmServer that can act as backend for translation search.
 * @ingroup TTMServer
 */
interface SearchableTtmServer {
	/**
	 * Performs a search in the translation database.
	 *
	 * @param string $queryString String to search for.
	 * @param array $opts Query options like language.
	 * @param array $highlight Tags for highlighting.
	 * @return mixed Result set
	 */
	public function search( string $queryString, array $opts, array $highlight );

	/**
	 * @param mixed $resultset
	 * @return array[]
	 */
	public function getFacets( $resultset ): array;

	/** @param mixed $resultset */
	public function getTotalHits( $resultset ): int;

	/**
	 * @param mixed $resultset
	 * @return array[]
	 */
	public function getDocuments( $resultset ): array;
}
