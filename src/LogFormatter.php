<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate;

use LogFormatter as CoreLogFormatter;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Title\Title;

/**
 * Class for formatting Translate logs.
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2013, Niklas Laxström
 * @license GPL-2.0-or-later
 */
class LogFormatter extends CoreLogFormatter {
	public function getMessageParameters(): array {
		$params = parent::getMessageParameters();

		$type = $this->entry->getFullType();

		if ( $type === 'translationreview/message' ) {
			$targetPage = $this->makePageLink(
				$this->entry->getTarget(),
				[ 'oldid' => $params[3] ]
			);

			$params[2] = Message::rawParam( $targetPage );
		} elseif ( $type === 'translationreview/group' ) {
			/*
			 * - 3: language code
			 * - 4: label of the message group
			 * - 5: old state
			 * - 6: new state
			 */

			$uiLanguage = $this->context->getLanguage();
			$language = $params[3];

			$targetPage = $this->makePageLinkWithText(
				$this->entry->getTarget(),
				$params[4],
				[ 'language' => $language ]
			);

			$params[2] = Message::rawParam( $targetPage );
			$params[3] = Utilities::getLanguageName( $language, $uiLanguage->getCode() );
			$params[5] = $this->formatStateMessage( $params[5] );
			$params[6] = $this->formatStateMessage( $params[6] );
		} elseif ( $type === 'translatorsandbox/rejected' ) {
			// No point linking to the user page which cannot have existed
			$params[2] = $this->entry->getTarget()->getText();
		} elseif ( $type === 'translatorsandbox/promoted' ) {
			// Gender for the target
			$params[3] = MediaWikiServices::getInstance()->getUserFactory()
				->newFromId( $params[3] )
				->getName();
		}

		return $params;
	}

	/** @param string|bool $value */
	private function formatStateMessage( $value ): string {
		$message = $this->msg( "translate-workflow-state-$value" );

		return $message->isBlank() ? $value : $message->text();
	}

	/** @return-taint onlysafefor_html */
	private function makePageLinkWithText(
		Title $pageTitle, ?string $text, array $queryParameters = []
	): string {
		if ( !$this->plaintext ) {
			$link = $this->getLinkRenderer()->makeLink( $pageTitle, $text, [], $queryParameters );
		} else {
			$target = $pageTitle->getPrefixedText();
			$link = "[[$target|$text]]";
		}

		return $link;
	}
}
