<?php
/**
 * Translation aid provider.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Translation aid which gives the message documentation.
 *
 * @ingroup TranslationAids
 * @since 2013-01-01
 */
class DocumentationAid extends TranslationAid {
	public function getData() {
		global $wgTranslateDocumentationLanguageCode, $wgContLang;
		if ( !$wgTranslateDocumentationLanguageCode ) {
			throw new TranslationHelperException( 'Message documentation is disabled' );
		}

		$page = $this->handle->getKey();
		$ns = $this->handle->getTitle()->getNamespace();

		$info = TranslateUtils::getMessageContent( $page, $wgTranslateDocumentationLanguageCode, $ns );

		$gettext = $this->formatGettextComments();
		if ( $info !== null && $gettext ) {
			$info .= Html::element( 'hr' );
		}
		$info .= $gettext;

		return array(
			'language' => $wgContLang->getCode(),
			'value' => $info,
			'html' => $this->context->getOutput()->parse( $info ),
		);
	}

	protected function formatGettextComments() {
		// We need to get the primary group to get the correct file
		// So $group can be different from $this->group
		$group = $this->handle->getGroup();
		if ( !$group instanceof FileBasedMessageGroup ) {
			return '';
		}

		$ffs = $group->getFFS();
		if ( $ffs instanceof GettextFFS ) {
			global $wgContLang;
			$mykey = $wgContLang->lcfirst( $this->handle->getKey() );
			$mykey = str_replace( ' ', '_', $mykey );
			$data = $ffs->read( $group->getSourceLanguage() );
			$help = $data['TEMPLATE'][$mykey]['comments'];
			// Do not display an empty comment. That's no help and takes up unnecessary space.
			$conf = $group->getConfiguration();
			if ( isset( $conf['BASIC']['codeBrowser'] ) ) {
				$out = '';
				$pattern = $conf['BASIC']['codeBrowser'];
				$pattern = str_replace( '%FILE%', '\1', $pattern );
				$pattern = str_replace( '%LINE%', '\2', $pattern );
				$pattern = "[$pattern \\1:\\2]";
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
				return "$out";
			}
		}

		return '';
	}

}
