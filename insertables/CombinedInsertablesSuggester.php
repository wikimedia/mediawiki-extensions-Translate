<?php

/**
 * A class to combine multiple insertables suggesters.
 */
class CombinedInsertablesSuggester implements InsertablesSuggester {
	protected $suggesters = array();

	/**
	 * CombinedInsertablesSuggester constructor.
	 * @param array $suggesters Array of InsertablesSuggester objects to combine.
	 */
	public function __construct( $suggesters=array() ) {
		$this->suggesters = $suggesters;
	}

	public function getInsertables( $text ) {
		$insertables = array();
		foreach ( $this->suggesters as $suggester ) {
			$new = $suggester->getInsertables( $text );
			$insertables = array_merge( $insertables, $new );
		}

		return $insertables;
	}
}