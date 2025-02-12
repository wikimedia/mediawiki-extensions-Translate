<?php
declare( strict_types = 1 );

use MediaWiki\Context\IContextSource;

/**
 * Contains an unmanaged message group for fetching stats using message prefixes
 * @author Abijeet Patro
 * @since 2023.02
 * @license GPL-2.0-or-later
 * @ingroup MessageGroup
 */
class MessagePrefixMessageGroup extends WikiMessageGroup {
	/** @var string */
	protected $language;

	public function __construct() {
	}

	public function setLanguage( string $code ) {
		$this->language = $code;
	}

	/** @inheritDoc */
	public function getId() {
		return '!prefix';
	}

	/** @inheritDoc */
	public function getLabel( ?IContextSource $context = null ) {
		return 'Message prefixes';
	}

	/** @inheritDoc */
	public function getDescription( ?IContextSource $context = null ) {
		return '"Messages in this message group are dynamically added based on ' .
			'selecting of message prefix on Special:MessageGroupStats';
	}

	/** @inheritDoc */
	public function getDefinitions() {
		return [];
	}

	/** @inheritDoc */
	public function getValidator() {
		return null;
	}
}
