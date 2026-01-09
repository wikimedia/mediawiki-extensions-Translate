<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorSandbox;

use MediaWiki\Title\Title;
use MediaWiki\User\User;

/**
 * Value object for stashed translation which you can construct.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2013.06 (namespaced in 2020.11)
 */
class StashedTranslation {

	public function __construct(
		private readonly User $user,
		private readonly Title $title,
		private readonly string $value,
		private readonly ?array $metadata = null,
	) {
	}

	public function getUser(): User {
		return $this->user;
	}

	public function getTitle(): Title {
		return $this->title;
	}

	public function getValue(): string {
		return $this->value;
	}

	public function getMetadata(): ?array {
		return $this->metadata;
	}
}
