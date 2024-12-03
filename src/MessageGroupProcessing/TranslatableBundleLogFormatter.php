<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use LogFormatter;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Title\Title;

/**
 * Class for formatting translatable bundle logs.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
class TranslatableBundleLogFormatter extends LogFormatter {
	protected function getMessageParameters(): array {
		$params = parent::getMessageParameters();
		$legacy = $this->entry->getParameters();

		$type = $this->entry->getFullType();
		switch ( $type ) {
			case 'pagetranslation/mark':
				$revision = $legacy['revision'];

				$targetPage = $this->makePageLink(
					$this->entry->getTarget(),
					[ 'oldid' => $revision ]
				);

				$params[2] = Message::rawParam( $targetPage );
				break;

			case 'pagetranslation/moveok':
			case 'pagetranslation/movenok':
			case 'pagetranslation/deletefnok':
			case 'pagetranslation/deletelnok':
			case 'messagebundle/moveok':
			case 'messagebundle/movenok':
				$target = $legacy['target'] ?? 'Special:Badtitle';
				$moveTarget = $this->makePageLink( Title::newFromText( $target ) );
				$params[3] = Message::rawParam( $moveTarget );
				break;

			case 'pagetranslation/prioritylanguages':
				$params[3] = $legacy['force'];
				$languages = $legacy['languages'];
				if ( $languages !== false ) {
					$lang = $this->context->getLanguage();
					$inLanguage = $lang->getCode();

					$languages = array_map(
						static function ( string $code ) use ( $inLanguage ): string {
							return Utilities::getLanguageName( trim( $code ), $inLanguage );
						},
						preg_split( '/,/', $languages, -1, PREG_SPLIT_NO_EMPTY )
					);

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

	public function getComment(): string {
		$legacy = $this->entry->getParameters();
		if ( isset( $legacy['reason'] ) ) {
			$commentFormatter = MediaWikiServices::getInstance()->getCommentFormatter();
			$comment = $commentFormatter->formatBlock( (string)$legacy['reason'] );

			// No hard coded spaces thanx
			return ltrim( $comment );
		}

		return parent::getComment();
	}

	protected function getMessageKey(): string {
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
