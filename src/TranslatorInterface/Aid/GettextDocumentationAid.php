<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Aid;

use FileBasedMessageGroup;
use GettextFFS;
use MediaWiki\Extension\Translate\TranslatorInterface\TranslationHelperException;
use MediaWiki\MediaWikiServices;

/**
 * Translation aid that provides Gettext documentation.
 * @ingroup TranslationAids
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2013-01-01
 */
class GettextDocumentationAid extends TranslationAid {
	public function getData(): array {
		// We need to get the primary group to get the correct file
		// So $group can be different from $this->group
		$group = $this->handle->getGroup();
		if ( !$group instanceof FileBasedMessageGroup ) {
			throw new TranslationHelperException( 'Not a FileBasedMessageGroup group' );
		}

		$ffs = $group->getFFS();
		if ( !$ffs instanceof GettextFFS ) {
			throw new TranslationHelperException( 'Group is not using GettextFFS' );
		}

		$cache = $group->getMessageGroupCache( $group->getSourceLanguage() );
		if ( !$cache->exists() ) {
			throw new TranslationHelperException( 'Definitions are not cached' );
		}

		$extra = $cache->getExtra();
		$contLang = MediaWikiServices::getInstance()->getContentLanguage();
		$messageKey = $contLang->lcfirst( $this->handle->getKey() );
		$messageKey = str_replace( ' ', '_', $messageKey );

		$help = $extra['TEMPLATE'][$messageKey]['comments'] ?? null;
		if ( !$help ) {
			throw new TranslationHelperException( "No comments found for key '$messageKey'" );
		}

		$conf = $group->getConfiguration();
		if ( isset( $conf['BASIC']['codeBrowser'] ) ) {
			$pattern = $conf['BASIC']['codeBrowser'];
			$pattern = str_replace( '%FILE%', '\1', $pattern );
			$pattern = str_replace( '%LINE%', '\2', $pattern );
			$pattern = "[$pattern \\1:\\2]";
		} else {
			$pattern = "\\1:\\2";
		}

		$out = '';
		foreach ( $help as $type => $lines ) {
			if ( $type === ':' ) {
				$files = '';
				foreach ( $lines as $line ) {
					$files .= ' ' . preg_replace( '/([^ :]+):(\d+)/', $pattern, $line );
				}
				$out .= "<nowiki>#:</nowiki> $files<br />";
			} else {
				foreach ( $lines as $line ) {
					$out .= "<nowiki>#$type</nowiki> $line<br />";
				}
			}
		}

		$html = $this->context->getOutput()->parseAsContent( $out );

		return [
			'language' => $contLang->getCode(),
			// @todo Provide raw data when possible
			// 'value' => $help,
			'html' => $html,
		];
	}
}
