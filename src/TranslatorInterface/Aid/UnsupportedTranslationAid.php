<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Aid;

use MediaWiki\Extension\Translate\TranslatorInterface\TranslationHelperException;

/**
 * Dummy translation aid that always errors
 * @author Harry Burt
 * @license GPL-2.0-or-later
 * @since 2013-03-29
 * @ingroup TranslationAids
 * @phan-file-suppress PhanPluginNeverReturnMethod
 */
class UnsupportedTranslationAid extends TranslationAid {
	public function getData(): array {
		throw new TranslationHelperException( 'This translation aid is disabled' );
	}
}
