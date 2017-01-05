<?php

/**
 * A class to combine multiple insertables suggesters.
 */
class CombinedInsertablesSuggester implements InsertablesSuggester {
	protected $suggesters = [];

	/**
	 * CombinedInsertablesSuggester constructor.
	 * @param array $suggesters Array of InsertablesSuggester objects to combine.
	 */
	public function __construct( $suggesters = [] ) {
		$this->suggesters = $suggesters;
	}

	public function getInsertables( $text ) {
		$insertables = [];
		foreach ( $this->suggesters as $suggester ) {
			$new = $suggester->getInsertables( $text );
			$insertables = array_merge( $insertables, $new );
		}

		return array_unique( $insertables, SORT_REGULAR );
	}
}
