<?php
/**
 * Contains code related to web services support.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Value object that represents a HTTP(S) query response.
 * @since 2015.02
 */
class TranslationQueryResponse {
	protected $code;
	protected $reason;
	protected $headers;
	protected $body;
	protected $error;

	/**
	 * @var TranslationQuery
	 */
	protected $query;

	protected function __construct() {
	}

	public static function newFromMultiHttp( array $data, TranslationQuery $query ) {
		$response = $data['response'];
		$obj = new self();
		$obj->code = (int)$response['code'];
		$obj->reason = $response['reason'];
		$obj->headers = $response['headers'];
		$obj->body = $response['body'];
		$obj->error = $response['error'];
		$obj->query = $query;
		return $obj;
	}

	public function getStatusCode() {
		return $this->code;
	}

	public function getStatusMessage() {
		if ( $this->code === 0 ) {
			return $this->error;
		} else {
			return $this->reason;
		}
	}

	public function getBody() {
		return $this->body;
	}

	/**
	 * Get the TranslationQuery that was made for this request.
	 * @return TranslationQuery
	 * @since 2017.04
	 */
	public function getQuery() {
		return $this->query;
	}
}
