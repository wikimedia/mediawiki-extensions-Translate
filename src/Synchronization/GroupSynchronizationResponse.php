<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

/**
 * Class encapsulating the response returned by the GroupSynchronizationCache
 * when requested for an update on a group synchronization status.
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2020.06
 */
class GroupSynchronizationResponse {
	/** @var MessageUpdateParameter[] */
	private $remainingMessages;
	/** @var string */
	private $groupId;
	/** @var bool */
	private $timeout;

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
}

class_alias( GroupSynchronizationResponse::class, '\MediaWiki\Extensions\Translate\GroupSynchronizationResponse' );
