<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\WebService;

/**
 * Mutable objects that represents a HTTP(S) query.
 * NB: Too lazy to make TranslationQueryFactory to make this class immutable.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2015.02
 */
class TranslationQuery {
	private $url;
	private $timeout = 0;
	private $method = 'GET';
	private $params = [];
	private $body;
	private $headers = [];
	/** @var mixed Arbitrary data that is returned with TranslationQueryResponse */
	private $instructions;

	// URL is mandatory, so using it here
	public static function factory( string $url ): TranslationQuery {
		$obj = new self();
		$obj->url = $url;
		return $obj;
	}

	/** Make this a POST request with given data. */
	public function postWithData( string $data ): TranslationQuery {
		$this->method = 'POST';
		$this->body = $data;
		return $this;
	}

	public function queryParameters( array $params ): TranslationQuery {
		$this->params = $params;
		return $this;
	}

	public function queryHeaders( array $headers ): TranslationQuery {
		$this->headers = $headers;
		return $this;
	}

	public function timeout( int $timeout ): TranslationQuery {
		$this->timeout = $timeout;
		return $this;
	}

	/**
	 * Attach arbitrary data that is necessary to process the results.
	 * @param mixed $data
	 * @since 2017.04
	 */
	public function attachProcessingInstructions( $data ): TranslationQuery {
		$this->instructions = $data;
		return $this;
	}

	public function getTimeout(): int {
		return $this->timeout;
	}

	public function getUrl(): string {
		return $this->url;
	}

	public function getMethod(): string {
		return $this->method;
	}

	public function getQueryParameters(): array {
		return $this->params;
	}

	public function getBody(): ?string {
		return $this->body;
	}

	public function getHeaders(): array {
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
