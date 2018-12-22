<?php
/**
 * Contains code related to web services support.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Mutable objects that represents a HTTP(S) query.
 * NB: Too lazy to make TranslationQueryFactory to make this class immutable.
 * @since 2015.02
 */
class TranslationQuery {
	protected $url;
	protected $timeout = 0;
	protected $method = 'GET';
	protected $params = [];
	protected $body;
	protected $headers = [];

	/**
	 * @var mixed Arbitrary data that is returned with TranslationQueryResponse
	 */
	protected $instructions;

	// URL is mandatory, so using it here
	public static function factory( $url ) {
		$obj = new self();
		$obj->url = $url;
		return $obj;
	}

	/**
	 * Make this a POST request with given data.
	 *
	 * @param string $data
	 * @return $this
	 */
	public function postWithData( $data ) {
		$this->method = 'POST';
		$this->body = $data;
		return $this;
	}

	public function queryParameters( array $params ) {
		$this->params = $params;
		return $this;
	}

	public function queryHeaders( array $headers ) {
		$this->headers = $headers;
		return $this;
	}

	public function timeout( $timeout ) {
		$this->timeout = $timeout;
		return $this;
	}

	/**
	 * Attach arbitrary data that is necessary to process the results.
	 * @param mixed $data
	 * @return self
	 * @since 2017.04
	 */
	public function attachProcessingInstructions( $data ) {
		$this->instructions = $data;
		return $this;
	}

	public function getTimeout() {
		return $this->timeout;
	}

	public function getUrl() {
		return $this->url;
	}

	public function getMethod() {
		return $this->method;
	}

	public function getQueryParameters() {
		return $this->params;
	}

	public function getBody() {
		return $this->body;
	}

	public function getHeaders() {
		return $this->headers;
	}

	/**
	 * Get previously attached result processing instructions.
	 * @return mixed
	 * @since 2017.04
	 */
	public function getProcessingInstructions() {
		return $this->instructions;
	}
}
