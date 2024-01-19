<?php

namespace MediaWiki\Extension\Notifications\Formatters;

use MediaWiki\Extension\Notifications\Model\Event;
use Message;
use MessageLocalizer;

/** Stub of Echo's EchoEventPresentationModel class for phan */
class EchoEventPresentationModel implements MessageLocalizer {
	public Event $event;

	/** @inheritDoc */
	public function msg( $key, ...$params ): Message {
		return new Message( $key, $params );
	}

	public function isBundled(): bool {
		return false;
	}

	public function getBundleCount(): int {
		return 5;
	}

	/** @return string */
	public function getCompactHeaderMessageKey() {
		return "notification-compact-header-stub";
	}

	/** @return Message */
	public function getCompactHeaderMessage() {
	}
}
