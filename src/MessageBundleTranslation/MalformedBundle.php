<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use Exception;
use Throwable;
use Wikimedia\Message\MessageSpecifier;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2021.05
 */
class MalformedBundle extends Exception implements MessageSpecifier {
	private string $key;
	private array $params;

	public function __construct(
		string $key,
		array $params = [],
		?Throwable $previous = null
	) {
		parent::__construct( $key, 0, $previous );
		$this->key = $key;
		$this->params = $params;
	}

	/** @inheritDoc */
	public function getKey(): string {
		return $this->key;
	}

	/** @inheritDoc */
	public function getParams(): array {
		return $this->params;
	}
}
