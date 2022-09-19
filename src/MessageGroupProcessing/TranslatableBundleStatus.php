<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

interface TranslatableBundleStatus {
	public function getId(): int;

	public function isEqual( int $status ): bool;
}
