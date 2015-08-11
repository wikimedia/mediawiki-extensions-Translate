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

	public function __construct( $params, $server ) {
		$this->params = $params;
		$this->server = $server;
	}

	/*
	 * Change an array to FormOptions
	 * return FormOptions
	 */
	protected function makeOptions( $params ) {
		$opts = new FormOptions();
		foreach ( $params as $param => $value ) {
			$opts->add( $param, $value );
		}
		return $opts;
	}

	public function getDocuments() {
		$documents = array();
		$total = $start = 0;
		$queryString = $this->params['query'];
		$offset = $this->params['offset'];
		$limit = $this->params['limit'];
		$size = 1000;

		$options = $this->makeOptions( $this->params );
		$options->setValue( 'limit', $size );
		$options->setValue( 'language', $this->params['sourcelanguage'] );
		do {
			$options->setValue( 'offset', $start );
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

		$language = RequestContext::getMain()->getLanguage()->getCode();
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

			$key = $title->getNamespace() . ':' . $title->getDBKey();
			$messages[$key] = $data['content'];
		}

		$definitions = new MessageDefinitions( $messages );
		$collection = MessageCollection::newFromDefinitions( $definitions, $language );
		$collection->filter( 'hastranslation', true );

		$total = count( $collection );
		$offset = $collection->slice( $offset, $limit );
		$left = count( $collection );

		$offsets = array(
			'start' => $offset[2],
			'left' => $left,
			'total' => $total,
		);

		foreach ( $collection->keys() as $mkey => $title ) {
			$documents[$mkey]['content'] = $messages[$mkey];
			$handle = new MessageHandle( $title );
			$documents[$mkey]['localid'] = $handle->getTitleForBase()->getPrefixedText();
			$documents[$mkey]['language'] = $language;
			$ret[] = $documents[$mkey];
		}

		return array( $ret, $offsets );
	}

	public function getTotalHits() {
		return $this->total;
	}

	public function getResultSet() {
		return $this->resultset;
	}
}
