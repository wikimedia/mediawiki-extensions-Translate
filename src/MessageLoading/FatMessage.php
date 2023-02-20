<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageLoading;

/**
 * Message object where you can directly set the translation.
 * Hence the name fat. Authors are not supported.
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2010, Niklas Laxström
 * @license GPL-2.0-or-later
 */
class FatMessage extends Message {
	/** Stored translation. */
	protected ?string $translation = null;

	/** Set the current translation of this message. */
	public function setTranslation( ?string $text ): void {
		$this->translation = $text;
	}

	public function translation(): ?string {
		if ( $this->translation === null ) {
			return $this->infile;
		}

		return $this->translation;
	}
}
