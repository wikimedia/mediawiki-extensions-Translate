<?php
/**
 * Contains code related to web services support.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
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
	protected $params = array();
	protected $body;

	// URL is mandatory, so using it here
	public static function factory( $url ) {
		$obj = new TranslationQuery();
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

	public function queryParamaters( array $params ) {
		$this->params = $params;
		return $this;
	}

	public function timeout( $timeout ) {
		$this->timeout = $timeout;
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
}
