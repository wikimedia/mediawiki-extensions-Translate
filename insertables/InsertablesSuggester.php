<?php
/**
 * Interface for InsertableSuggesters.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Insertable is a string that usually does not need translation and is
 * difficult to type manually.
 * @since 2013.09
 */
interface InsertablesSuggester {
	/**
	 * Returns the insertables in the message text.
	 * @param string $text
	 * @return array
	 */
	public function getInsertables( $text );
}
