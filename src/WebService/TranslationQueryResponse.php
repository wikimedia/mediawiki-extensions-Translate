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
	private int $code;
	private string $reason;
	private array $headers;
	private string $body;
	private string $error;
	/** @var TranslationQuery */
	private $query;

	public function __construct( array $data, TranslationQuery $query ) {
		$response = $data['response'];

		$this->code = (int)$response['code'];
		$this->reason = $response['reason'];
		$this->headers = $response['headers'];
		$this->body = $response['body'];
		$this->error = $response['error'];
		$this->query = $query;
	}

	public function getStatusCode(): int {
		return $this->code;
	}

	public function getStatusMessage(): string {
		if ( $this->code === 0 ) {
			return $this->error;
		} else {
			return $this->reason;
		}
	}

	public function getBody(): string {
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
