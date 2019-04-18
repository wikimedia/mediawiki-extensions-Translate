<?php
/**
 * Cross Language Translation Search.
 * @since 2015.08
 */
class CrossLanguageTranslationSearchQuery {
	/** @var TTMServer */
	protected $server;

	/** @var array */
	protected $params;

	/** @var ResultSet */
	protected $resultset;

	/** @var int */
	protected $total = 0;

	protected $hl = [ '', '' ];

	public function __construct( array $params, SearchableTTMServer $server ) {
		$this->params = $params;
		$this->server = $server;
	}

	public function getDocuments() {
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

		$search = $this->server->createSearch( $this->params['query'], $options, $this->hl );
		$scroll = $search->scroll( '5s' );

		// Used for aggregations. Only the first scroll response has them.
		$this->resultset = null;

		foreach ( $scroll as $resultSet ) {
			if ( !$this->resultset ) {
				$this->resultset = $resultSet;
				$this->total = $resultSet->getTotalHits();
			}

			$results = $this->extractMessages( $resultSet->getDocuments() );
			$documents = array_merge( $documents, $results );

			$count = count( $documents );

			if ( $count >= $offset + $limit ) {
				break;
			}
		}

		// clear was introduced in Elastica 5.3.1, but Elastica extension uses 5.3.0
		if ( is_callable( [ $scroll, 'clear' ] ) ) {
			$scroll->clear();
		}
		$documents = array_slice( $documents, $offset, $limit );

		return $documents;
	}

	/**
	 * Extract messages from the documents and build message definitions.
	 * Create a message collection from the definitions in the target language.
	 * Filter the message collection to get filtered messages.
	 * Slice messages according to limit and offset given.
	 * @param \Elastica\Document[] $documents
	 * @return array[]
	 */
	protected function extractMessages( $documents ) {
		$messages = $ret = [];

		$language = $this->params['language'];
		foreach ( $documents as $document ) {
			$data = $document->getData();

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
			$collection->filter( 'hastranslation', true );
		} elseif ( in_array( $filter, $this->getAvailableFilters() ) ) {
			$collection->filter( $filter, false );
		}

		if ( $filter === 'translated' || $filter === 'fuzzy' ) {
			$collection->loadTranslations();
		}

		foreach ( $collection->keys() as $mkey => $title ) {
			$result = [];
			$result['content'] = $messages[$mkey];
			if ( $filter === 'translated' || $filter === 'fuzzy' ) {
				$result['content'] = $collection[$mkey]->translation();
			}
			$handle = new MessageHandle( $title );
			$result['localid'] = $handle->getTitleForBase()->getPrefixedText();
			$result['language'] = $language;

			$ret[] = $result;
		}

		return $ret;
	}

	/**
	 * @return array
	 */
	public function getAvailableFilters() {
		return [
			'translated',
			'fuzzy',
			'untranslated'
		];
	}

	public function getTotalHits() {
		return $this->total;
	}

	public function getResultSet() {
		return $this->resultset;
	}
}
