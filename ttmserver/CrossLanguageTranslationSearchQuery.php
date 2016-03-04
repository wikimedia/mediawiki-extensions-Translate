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

	protected $hl = array( '', '' );

	public function __construct( array $params, SearchableTTMServer $server ) {
		$this->params = $params;
		$this->server = $server;
	}

	public function getDocuments() {
		$documents = array();
		$total = $start = 0;
		$queryString = $this->params['query'];
		$offset = $this->params['offset'];
		$limit = $this->params['limit'];
		$size = 1000;

		$options = $this->params;
		$options['limit'] = $size;
		$options['language'] = $this->params['sourcelanguage'];
		do {
			$options['offset'] = $start;
			$this->resultset = $this->server->search( $queryString, $options, $this->hl );

			list( $results, $offsets ) = $this->extractMessages(
				$this->resultset,
				$offset,
				$limit
			);
			$offset = $offsets['start'] + $offsets['left'] - $offsets['total'];
			$limit = $limit - $offsets['left'];
			$total = $total + $offsets['total'];

			$documents = array_merge( $documents, $results );
			$start = $start + $size;
		} while (
			$offsets['start'] + $offsets['left'] >= $offsets['total'] &&
			$this->resultset->getTotalHits() > $start
		);
		$this->total = $total;

		return $documents;
	}

	/*
	 * Extract messages from the resultset and build message definitions.
	 * Create a message collection from the definitions in the target language.
	 * Filter the message collection to get filtered messages.
	 * Slice messages according to limit and offset given.
	 */
	protected function extractMessages( $resultset, $offset, $limit ) {
		$messages = $documents = $ret = array();

		$language = $this->params['language'];
		foreach ( $resultset->getResults() as $document ) {
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

		$total = count( $collection );
		$offset = $collection->slice( $offset, $limit );
		$left = count( $collection );

		$offsets = array(
			'start' => $offset[2],
			'left' => $left,
			'total' => $total,
		);

		if ( $filter === 'translated' || $filter === 'fuzzy' ) {
			$collection->loadTranslations();
		}

		foreach ( $collection->keys() as $mkey => $title ) {
			$documents[$mkey]['content'] = $messages[$mkey];
			if ( $filter === 'translated' || $filter === 'fuzzy' ) {
				$documents[$mkey]['content'] = $collection[$mkey]->translation();
			}
			$handle = new MessageHandle( $title );
			$documents[$mkey]['localid'] = $handle->getTitleForBase()->getPrefixedText();
			$documents[$mkey]['language'] = $language;
			$ret[] = $documents[$mkey];
		}

		return array( $ret, $offsets );
	}

	/**
	 * @return array
	 */
	public function getAvailableFilters() {
		return array(
			'translated',
			'fuzzy',
			'untranslated'
		);
	}

	public function getTotalHits() {
		return $this->total;
	}

	public function getResultSet() {
		return $this->resultset;
	}
}
