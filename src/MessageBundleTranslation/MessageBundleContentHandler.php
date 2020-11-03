<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use TextContentHandler;
use const CONTENT_FORMAT_JSON;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2021.05
 */
class MessageBundleContentHandler extends TextContentHandler {
	public function __construct( $modelId = MessageBundleContent::CONTENT_MODEL_ID ) {
		parent::__construct( $modelId, [ CONTENT_FORMAT_JSON ] );
	}

	protected function getContentClass() {
		return MessageBundleContent::class;
	}

	public function makeEmptyContent() {
		$class = $this->getContentClass();
		return new $class( '{}' );
	}
}
