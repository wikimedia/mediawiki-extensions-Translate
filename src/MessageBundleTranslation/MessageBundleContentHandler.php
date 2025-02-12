<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use InvalidArgumentException;
use MediaWiki\Content\Content;
use MediaWiki\Content\Renderer\ContentParseParams;
use MediaWiki\Content\TextContentHandler;
use MediaWiki\Content\ValidationParams;
use MediaWiki\Parser\ParserOutput;
use StatusValue;
use const CONTENT_FORMAT_JSON;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2021.05
 */
class MessageBundleContentHandler extends TextContentHandler {
	/** @inheritDoc */
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

	/** @inheritDoc */
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

	/** @inheritDoc */
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

		if ( !$content->isValid() ) {
			$parserOutput->setRawText( null );
			return;
		}

		$label = $content->getMetadata()->getLabel();
		if ( $label !== null ) {
			$parserOutput->setDisplayTitle( $label );
		}

		if ( $cpoParams->getGenerateHtml() ) {
			/** @param $content JsonContent::class */
			$parserOutput->setRawText( $content->rootValueTable( $content->getData()->getValue() ) );
			$parserOutput->addModuleStyles( [ 'mediawiki.content.json' ] );
		} else {
			$parserOutput->setRawText( null );
		}
	}
}
