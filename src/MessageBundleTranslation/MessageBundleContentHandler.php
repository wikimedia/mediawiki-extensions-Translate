<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use Content;
use MediaWiki\Content\ValidationParams;
use StatusValue;
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

	protected function getContentClass(): string {
		return MessageBundleContent::class;
	}

	public function makeEmptyContent(): Content {
		$class = $this->getContentClass();
		return new $class( '{}' );
	}

	public function validateSave(
		Content $content,
		ValidationParams $validationParams
	) {
		'@phan-var MessageBundleContent $content';
		// This will give an informative error message when trying to change the content model
		try {
			$content->getMessages();
			return StatusValue::newGood();
		} catch ( MalformedBundle $e ) {
			// XXX: We have no context source nor is there Message::messageParam :(
			return StatusValue::newFatal( 'translate-messagebundle-validation-error', wfMessage( $e ) );
		}
	}
}
