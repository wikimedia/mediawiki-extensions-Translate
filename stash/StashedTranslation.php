<?php
/**
 * Value object for stashed translation.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Value object for stashed translation which you can construct.
 * @since 2013.06
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

	/**
	 * @param User $user
	 * @param Title $title
	 * @param string $value
	 * @param array|null $metadata
	 */
	public function __construct( User $user, Title $title, $value, array $metadata = null ) {
		$this->user = $user;
		$this->title = $title;
		$this->value = $value;
		$this->metadata = $metadata;
	}

	/**
	 * @return User
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * @return Title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @return array|null
	 */
	public function getMetadata() {
		return $this->metadata;
	}
}
