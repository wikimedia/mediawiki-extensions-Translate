<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Insertable;

/**
 * Interface for InsertablesSuggesters. Insertable is a string that usually does
 * not need translation and is difficult to type manually.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2020.12
 */
interface InsertablesSuggester {
	/**
	 * Returns the insertables in the message text.
	 * @return Insertable[]
	 */
	public function getInsertables( string $text ): array;
}
