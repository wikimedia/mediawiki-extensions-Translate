<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use Wikimedia\JsonCodec\JsonCodecable;
use Wikimedia\JsonCodec\JsonCodecableTrait;

/**
 * Store params for UpdateMessageJob.
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2020.06
 */
class MessageUpdateParameter implements JsonCodecable {
	use JsonCodecableTrait;

	/** @var string */
	private $pageName;
	/** @var bool */
	private $rename;
	/** @var bool */
	private $fuzzy;
	/** @var string */
	private $content;
	/** @var string */
	private $target;
	/** @var string */
	private $replacement;
	/** @var array|null */
	private $otherLangs;

	public function __construct( array $params ) {
		$this->assignPropsFromArray( $params );
	}

	public function getPageName(): string {
		return $this->pageName;
	}

	public function isRename(): bool {
		return $this->rename;
	}

	public function getReplacementValue(): string {
		return $this->replacement;
	}

	public function getTargetValue(): string {
		return $this->target;
	}

	public function getContent(): string {
		return $this->content;
	}

	public function isFuzzy(): bool {
		return $this->fuzzy;
	}

	public function getOtherLangs(): ?array {
		return $this->otherLangs;
	}

	public static function newFromJsonArray( array $params ): self {
		return new static( $params );
	}

	/** @return mixed[] */
	public function toJsonArray(): array {
		// Keep this in sync with `assignPropsFromArray` which is used
		// in the constructor.
		return [
			'pageName' => $this->pageName,
			'rename' => $this->rename,
			'fuzzy' => $this->fuzzy,
			'content' => $this->content,
			'target' => $this->target,
			'replacement' => $this->replacement,
			'otherLangs' => $this->otherLangs,
		];
	}

	private function assignPropsFromArray( array $params ) {
		// We are using "rename" as value for $params['rename']
		// at some places otherwise this could be simplified to
		// $params['rename'] ?? false
		$this->rename = isset( $params['rename'] ) && $params['rename'];
		$this->fuzzy = $params['fuzzy'];
		$this->content = $params['content'];
		$this->pageName = $params['title'] ?? $params['pageName'];

		if ( $this->rename ) {
			$this->target = $params['target'];
			$this->replacement = $params['replacement'];
			$this->otherLangs = $params['otherLangs'] ?? [];
		}
	}

	/** Create a new instance of the class from UpdateMessageJob */
	public static function createFromJob( UpdateMessageJob $job ): self {
		$jobParams = $job->getParams();
		$jobParams['title'] = $job->getTitle()->getPrefixedDBkey();
		return new self( $jobParams );
	}
}
