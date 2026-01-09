<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use Wikimedia\JsonCodec\JsonCodecable;
use Wikimedia\JsonCodec\JsonCodecableTrait;

/**
 * Class encapsulating the response returned by the GroupSynchronizationCache
 * when requested for an update on a group synchronization status.
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2020.06
 */
class GroupSynchronizationResponse implements JsonCodecable {
	use JsonCodecableTrait;

	/**
	 * @param string $groupId
	 * @param MessageUpdateParameter[] $remainingMessages
	 * @param bool $timeout
	 */
	public function __construct(
		private readonly string $groupId,
		private readonly array $remainingMessages,
		private readonly bool $timeout,
	) {
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
	public function toJsonArray(): array {
		return [
			'groupId' => $this->groupId,
			'remainingMessages' => $this->remainingMessages,
			'timeout' => $this->timeout,
		];
	}

	public static function newFromJsonArray( array $params ): self {
		return new static(
			$params['groupId'],
			$params['remainingMessages'],
			$params['timeout']
		);
	}
}
