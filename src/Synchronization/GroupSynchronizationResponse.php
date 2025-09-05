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
