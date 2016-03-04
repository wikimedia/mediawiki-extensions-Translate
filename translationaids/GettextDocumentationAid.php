<?php
/**
 * Translation aid provider.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2013, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Translation aid which gives Gettext documentation.
 *
 * @ingroup TranslationAids
 * @since 2013-01-01
 */
class GettextDocumentationAid extends TranslationAid {
	public function getData() {

		// We need to get the primary group to get the correct file
		// So $group can be different from $this->group
		$group = $this->handle->getGroup();
		if ( !$group instanceof FileBasedMessageGroup ) {
			throw new TranslationHelperException( 'Not a Gettext group' );
		}

		$ffs = $group->getFFS();
		if ( !$ffs instanceof GettextFFS ) {
			throw new TranslationHelperException( 'Not a Gettext group' );
		}

		global $wgContLang;
		$mykey = $wgContLang->lcfirst( $this->handle->getKey() );
		$mykey = str_replace( ' ', '_', $mykey );
		$data = $ffs->read( $group->getSourceLanguage() );
		$help = $data['TEMPLATE'][$mykey]['comments'];

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

		return array(
			'language' => $wgContLang->getCode(),
			// @todo Provide raw data when possible
			// 'value' => $help,
			'html' => $this->context->getOutput()->parse( $out ),
		);
	}
}
