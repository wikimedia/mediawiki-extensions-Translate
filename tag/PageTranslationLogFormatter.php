<?php
/**
 * Class for formatting Translate page translation logs.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Class for formatting Translate page translation logs.
 */
class PageTranslationLogFormatter extends LogFormatter {
	public function getMessageParameters() {
		$params = parent::getMessageParameters();
		$legacy = $this->entry->getParameters();

		$type = $this->entry->getFullType();
		switch ( $type ) {
			case 'pagetranslation/mark':
				$revision = $legacy['revision'];

				$targetPage = $this->makePageLink(
					$this->entry->getTarget(),
					array( 'oldid' => $revision )
				);

				$params[2] = Message::rawParam( $targetPage );
				break;

			case 'pagetranslation/moveok':
			case 'pagetranslation/movenok':
			case 'pagetranslation/deletefnok':
			case 'pagetranslation/deletelnok':
				$target = $legacy['target'];

				$moveTarget = $this->makePageLink( Title::newFromText( $target ) );
				$params[3] = Message::rawParam( $moveTarget );
				break;

			case 'pagetranslation/prioritylanguages':
				$params[3] = $legacy['force'];
				$languages = $legacy['languages'];
				if ( $languages !== false ) {
					$lang = $this->context->getLanguage();

					$languages = array_map(
						'trim',
						preg_split( '/,/', $languages, -1, PREG_SPLIT_NO_EMPTY )
					);
					$languages = array_map( function ( $code ) use ( $lang ) {
						return TranslateUtils::getLanguageName( $code, $lang->getCode() );
					}, $languages );

					$params[4] = $lang->listToText( $languages );
				}
				break;

			case 'pagetranslation/associate':
			case 'pagetranslation/dissociate':
				$params[3] = $legacy['aggregategroup'];
				break;
		}

		return $params;
	}

	public function getComment() {
		$legacy = $this->entry->getParameters();
		if ( isset( $legacy['reason'] ) ) {
			$comment = Linker::commentBlock( $legacy['reason'] );

			// No hard coded spaces thanx
			return ltrim( $comment );
		}

		return parent::getComment();
	}

	protected function getMessageKey() {
		$key = parent::getMessageKey();
		$type = $this->entry->getFullType();

		// logentry-pagetranslation-prioritylanguages-unset
		// logentry-pagetranslation-prioritylanguages-force
		if ( $type === 'pagetranslation/prioritylanguages' ) {
			$params = $this->getMessageParameters();
			if ( !isset( $params[4] ) ) {
				$key .= '-unset';
			} elseif ( $params['3'] === 'on' ) {
				$key .= '-force';
			}
		}

		return $key;
	}
}
