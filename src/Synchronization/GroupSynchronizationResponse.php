<?php

declare( strict_types = 1 );

namespace MediaWiki\Extensions\Translate\Synchronization;

/**
 * Class encapsulating the response returned by the GroupSynchronizationCache
 * when requested for an update on a group synchronization status.
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2020.06
 */
class GroupSynchronizationResponse {
	/** @var array */
	private $remainingMessageKeys;

	/** @var string */
	private $groupId;

	/** @var bool */
	private $timeout;

	public function __construct(
		string $groupId, array $remainingMessageKeys, bool $hasTimedOut
	) {
		$this->groupId = $groupId;
		$this->remainingMessageKeys = $remainingMessageKeys;
		$this->timeout = $hasTimedOut;
	}

	public function isDone(): bool {
		return $this->remainingMessageKeys === [];
	}

	public function getRemainingMessages(): array {
		return $this->remainingMessageKeys;
	}

	public function getGroupId(): string {
		return $this->groupId;
	}

	public function hasTimedOut(): bool {
		return $this->timeout;
	}
}
