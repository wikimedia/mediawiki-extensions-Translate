<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use Content;
use InvalidArgumentException;
use MediaWiki\Content\Renderer\ContentParseParams;
use MediaWiki\Content\ValidationParams;
use ParserOutput;
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

	public function validateSave( Content $content, ValidationParams $validationParams ) {
		// This will give an informative error message when trying to change the content model
		try {
			if ( $content instanceof MessageBundleContent ) {
				$content->validate();
			}
			return StatusValue::newGood();
		} catch ( MalformedBundle $e ) {
			// XXX: We have no context source nor is there Message::messageParam :(
			return StatusValue::newFatal( 'translate-messagebundle-validation-error', wfMessage( $e ) );
		}
	}

	/**
	 * Set the HTML and add the appropriate styles.
	 * @since 1.38
	 * @param Content $content
	 * @param ContentParseParams $cpoParams
	 * @param ParserOutput &$parserOutput The output object to fill (reference).
	 */
	protected function fillParserOutput(
		Content $content,
		ContentParseParams $cpoParams,
		ParserOutput &$parserOutput
	) {
		if ( !$content instanceof MessageBundleContent ) {
			throw new InvalidArgumentException(
				'Expected $content to be MessageBundleContent; got: ' . get_class( $content )
			);
		}

		if ( $cpoParams->getGenerateHtml() && $content->isValid() ) {
			$parserOutput->setText( $content->rootValueTable( $content->getData()->getValue() ) );
			$parserOutput->addModuleStyles( [ 'mediawiki.content.json' ] );
		} else {
			$parserOutput->setText( null );
		}
	}
}
