<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TtmServer;

use Elastica\Document;
use Elastica\ResultSet;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;
use MediaWiki\Extension\Translate\MessageLoading\MessageDefinitions;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Title\Title;

/**
 * Cross Language Translation Search.
 *
 * @license GPL-2.0-or-later
 * @ingroup TTMServer
 */
class CrossLanguageTranslationSearchQuery {
	private SearchableTtmServer $server;
	private array $params;
	private ?ResultSet $resultSet = null;
	private int $total = 0;
	private array $hl = [ '', '' ];

	public function __construct( array $params, SearchableTtmServer $server ) {
		$this->params = $params;
		$this->server = $server;
	}

	public function getDocuments(): array {
		$documents = [];
		$offset = $this->params['offset'];
		$limit = $this->params['limit'];

		$options = $this->params;
		$options['language'] = $this->params['sourcelanguage'];
		// Use a bigger limit that what was requested, since we are likely to throw away many
		// results in the local filtering step at extractMessages
		$options['limit'] = $limit * 10;
		// TODO: the real offset should be communicated to the frontend. It currently assumes
		// next offset is current offset + limit and previous one is current offset - limit.
		// It might be difficult to fix scrolling results backwards. For now we handle offset
		// locally.
		$options['offset'] = 0;

		// @phan-suppress-next-line PhanUndeclaredMethod
		$search = $this->server->createSearch( $this->params['query'], $options, $this->hl );
		$scroll = $search->scroll( '5s' );

		// Used for aggregations. Only the first scroll response has them.
		$this->resultSet = null;

		foreach ( $scroll as $resultSet ) {
			if ( !$this->resultSet ) {
				$this->resultSet = $resultSet;
				$this->total = $resultSet->getTotalHits();
			}

			$results = $this->extractMessages( $resultSet->getDocuments() );
			$documents = array_merge( $documents, $results );

			$count = count( $documents );

			if ( $count >= $offset + $limit ) {
				break;
			}
		}

		if ( !$this->resultSet ) {
			// No hits for documents, just set the result set.
			$this->resultSet = $scroll->current();
			$this->total = $scroll->current()->getTotalHits();
		}

		$scroll->clear();

		return array_slice( $documents, $offset, $limit );
	}

	/**
	 * Extract messages from the documents and build message definitions.
	 * Create a message collection from the definitions in the target language.
	 * Filter the message collection to get filtered messages.
	 * Slice messages according to limit and offset given.
	 * @param Document[] $documents
	 */
	private function extractMessages( array $documents ): array {
		$messages = $ret = [];

		$language = $this->params['language'];
		foreach ( $documents as $document ) {
			$data = $document->getData();

			// @phan-suppress-next-line PhanUndeclaredMethod
			if ( !$this->server->isLocalSuggestion( $data ) ) {
				continue;
			}

			$title = Title::newFromText( $data['localid'] );
			if ( !$title ) {
				continue;
			}

			$handle = new MessageHandle( $title );
			if ( !$handle->isValid() ) {
				continue;
			}

			$key = $title->getNamespace() . ':' . $title->getDBkey();
			$messages[$key] = $data['content'];
		}

		$definitions = new MessageDefinitions( $messages );
		$collection = MessageCollection::newFromDefinitions( $definitions, $language );

		$filter = $this->params['filter'];
		if ( $filter === 'untranslated' ) {
			$collection->filter( MessageCollection::FILTER_HAS_TRANSLATION, MessageCollection::EXCLUDE_MATCHING );
		} elseif ( in_array( $filter, $this->getAvailableFilters() ) ) {
			$collection->filter( $filter, MessageCollection::INCLUDE_MATCHING );
		}

		if ( $filter === 'translated' || $filter === 'fuzzy' ) {
			$collection->loadTranslations();
		}

		foreach ( $collection->keys() as $messageKey => $titleValue ) {
			$title = Title::newFromLinkTarget( $titleValue );

			$result = [];
			$result['content'] = $messages[$messageKey];
			if ( $filter === 'translated' || $filter === 'fuzzy' ) {
				$result['content'] = $collection[$messageKey]->translation();
			}
			$handle = new MessageHandle( $title );
			$result['localid'] = $handle->getTitleForBase()->getPrefixedText();
			$result['language'] = $language;

			$ret[] = $result;
		}

		return $ret;
	}

	public function getAvailableFilters(): array {
		return [
			'translated',
			'fuzzy',
			'untranslated'
		];
	}

	public function getTotalHits(): int {
		return $this->total;
	}

	public function getResultSet(): ResultSet {
		return $this->resultSet;
	}
}
