<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\WebService;

/**
 * Value object that represents a HTTP(S) query response.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2015.02
 */
class TranslationQueryResponse {
	private $code;
	private $reason;
	private $headers;
	private $body;
	private $error;
	/** @var TranslationQuery */
	private $query;

	protected function __construct() {
	}

	public static function newFromMultiHttp( array $data, TranslationQuery $query ): self {
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

	public function getStatusCode(): int {
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
	 * @since 2017.04
	 */
	public function getQuery(): TranslationQuery {
		return $this->query;
	}
}
