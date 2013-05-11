<?php
/**
 * Class for formatting Translate logs.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2013, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
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
				array( 'oldid' => $params[3] )
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
				array( 'language' => $language )
			);

			$params[2] = Message::rawParam( $targetPage );
			$params[3] = TranslateUtils::getLanguageName( $language, $uiLanguage->getCode() );
			$params[5] = $this->formatStateMessage( $params[5] );
			$params[6] = $this->formatStateMessage( $params[6] );
		}

		return $params;
	}

	protected function formatStateMessage( $value ) {
		$message = $this->msg( "translate-workflow-state-$value" );

		return $message->isBlank() ? $value : $message->text();
	}

	protected function makePageLinkWithText( Title $title = null, $text, $parameters = array() ) {
		if ( !$this->plaintext ) {
			$link = Linker::link( $title, htmlspecialchars( $text ), array(), $parameters );
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
