<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorSandbox;

use Title;
use User;

/**
 * Value object for stashed translation which you can construct.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2013.06 (namespaced in 2020.11)
 */
class StashedTranslation {
	/** @var User */
	protected $user;
	/** @var Title */
	protected $title;
	/** @var string */
	protected $value;
	/** @var array|null */
	protected $metadata;

	public function __construct( User $user, Title $title, string $value, array $metadata = null ) {
		$this->user = $user;
		$this->title = $title;
		$this->value = $value;
		$this->metadata = $metadata;
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
