<?php
declare( strict_types = 1 );

namespace MediaWiki\Extensions\Translate\Synchronization;

use FormatJson;
use MessageUpdateJob;
use Serializable;

/**
 * Store params for MessageUpdateJob.
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2020.06
 */
class MessageUpdateParameter implements Serializable {
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

	/** @var array */
	private $otherLangs;

	public function __construct( array $params ) {
		$this->assignPropsFromArray( $params );
	}

	public function getPageName(): string {
		return $this->pageName;
	}

	public function isRename(): bool {
		return boolval( $this->rename );
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

	public function getOtherLangs(): array {
		return $this->otherLangs;
	}

	public function serialize(): string {
		$return = FormatJson::encode( get_object_vars( $this ), false, FormatJson::ALL_OK );
		return $return;
	}

	public function unserialize( $deserialized ) {
		$params = FormatJson::decode( $deserialized, true );
		$this->assignPropsFromArray( $params );
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

	/** Create a new instance of the class from MessageUpdateJob */
	public static function createFromJob( MessageUpdateJob $job ): self {
		$jobParams = $job->getParams();
		$jobParams['title'] = $job->getTitle()->getPrefixedDBkey();
		return new self( $jobParams );
	}
}
