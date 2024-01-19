<?php

namespace MediaWiki\Extension\Notifications\Model;

/** Stub of Echo's Event class for phan */
class Event {
	/** @return self|false */
	public static function create( array $eventDetails ) {
		return false;
	}

	public function getExtra(): array {
		return [];
	}

	/** @return string */
	public function getTimestamp() {
		return wfTimestampNow();
	}

	/** @return mixed */
	public function getExtraParam( string $key ) {
	}

	/** @return string */
	public function getType() {
		return 'hello';
	}
}
