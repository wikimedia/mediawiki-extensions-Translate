<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use JsonSerializable;
use MediaWiki\Json\JsonDeserializable;
use MediaWiki\Json\JsonDeserializableTrait;
use MediaWiki\Json\JsonDeserializer;

/**
 * Class encapsulating the response returned by the GroupSynchronizationCache
 * when requested for an update on a group synchronization status.
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2020.06
 */
class GroupSynchronizationResponse implements JsonSerializable, JsonDeserializable {
	use JsonDeserializableTrait;

	/** @var MessageUpdateParameter[] */
	private array $remainingMessages;
	private string $groupId;
	private bool $timeout;

	public function __construct(
		string $groupId, array $remainingMessages, bool $hasTimedOut
	) {
		$this->groupId = $groupId;
		$this->remainingMessages = $remainingMessages;
		$this->timeout = $hasTimedOut;
	}

	public function isDone(): bool {
		return $this->remainingMessages === [];
	}

	/** @return MessageUpdateParameter[] */
	public function getRemainingMessages(): array {
		return $this->remainingMessages;
	}

	public function getGroupId(): string {
		return $this->groupId;
	}

	public function hasTimedOut(): bool {
		return $this->timeout;
	}

	/** @return mixed[] */
	protected function toJsonArray(): array {
		return get_object_vars( $this );
	}

	public static function newFromJsonArray( JsonDeserializer $deserializer, array $params ): self {
		return new self(
			$params['groupId'],
			$params['remainingMessages'],
			$params['timeout']
		);
	}
}
