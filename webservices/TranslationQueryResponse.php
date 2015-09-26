<?php
/**
 * Contains code related to web services support.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
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

	protected function __construct() {
	}

	public static function newFromMultiHttp( array $data ) {
		$response = $data['response'];
		$obj = new TranslationQueryResponse();
		$obj->code = (int) $response['code'];
		$obj->reason = $response['reason'];
		$obj->headers = $response['headers'];
		$obj->body = $response['body'];
		$obj->error = $response['error'];
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
}
