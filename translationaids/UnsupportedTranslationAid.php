<?php
declare( strict_types = 1 );

/**
 * Dummy translation aid that always errors
 * @author Harry Burt
 * @license GPL-2.0-or-later
 * @since 2013-03-29
 * @ingroup TranslationAids
 */
class UnsupportedTranslationAid extends TranslationAid {
	public function getData(): array {
		throw new TranslationHelperException( 'This translation aid is disabled' );
	}
}
