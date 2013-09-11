<?php
/**
 * Interface for InsertableSuggesters.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Insertable is a string that usually does not need translation and is
 * difficult to type manually.
 * @since 2013.09
 */
interface InsertablesSuggester {
	public function getInsertables( $text );
}
