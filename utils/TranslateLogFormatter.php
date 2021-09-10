<?php
/**
 * Class for formatting Translate logs.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2013, Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Class for formatting Translate logs.
 */
class TranslateLogFormatter extends LogFormatter {
	public function getMessageParameters() {
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

			// @phan-suppress-next-line SecurityCheck-DoubleEscaped Mixed plaintext/html mode
			$targetPage = $this->makePageLinkWithText(
				$this->entry->getTarget(),
				$params[4],
				[ 'language' => $language ]
			);

			$params[2] = Message::rawParam( $targetPage );
			$params[3] = TranslateUtils::getLanguageName( $language, $uiLanguage->getCode() );
			$params[5] = $this->formatStateMessage( $params[5] );
			$params[6] = $this->formatStateMessage( $params[6] );
		} elseif ( $type === 'translatorsandbox/rejected' ) {
			// No point linking to the user page which cannot have existed
			$params[2] = $this->entry->getTarget()->getText();
		} elseif ( $type === 'translatorsandbox/promoted' ) {
			// Gender for the target
			$params[3] = User::newFromId( $params[3] )->getName();
		}

		return $params;
	}

	protected function formatStateMessage( $value ) {
		$message = $this->msg( "translate-workflow-state-$value" );

		return $message->isBlank() ? $value : $message->text();
	}

	/**
	 * @param Title|null $title The page
	 * @param string|null $text
	 * @param array $parameters Query parameters
	 * @return string
	 * @return-taint onlysafefor_html
	 */
	protected function makePageLinkWithText(
		?Title $title, $text, array $parameters = []
	) {
		if ( !$this->plaintext ) {
			$link = $this->getLinkRenderer()->makeLink( $title, $text, [], $parameters );
		} else {
			$target = '***';
			if ( $title instanceof Title ) {
				$target = $title->getPrefixedText();
			}
			$link = "[[$target|$text]]";
		}

		return $link;
	}
}
