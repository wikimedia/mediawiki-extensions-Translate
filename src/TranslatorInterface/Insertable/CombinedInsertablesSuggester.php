<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Insertable;

/**
 * A class to combine multiple insertables suggesters.
 * @author Geoffrey Mon
 * @license GPL-2.0-or-later
 * @since 2020.12
 */
class CombinedInsertablesSuggester implements InsertablesSuggester {

	/** @var InsertablesSuggester[] */
	protected $suggesters = [];

	/** @param InsertablesSuggester[] $suggesters Array of InsertablesSuggester objects to combine. */
	public function __construct( array $suggesters = [] ) {
		$this->suggesters = $suggesters;
	}

	public function getInsertables( string $text ): array {
		$insertables = [];
		foreach ( $this->suggesters as $suggester ) {
			$new = $suggester->getInsertables( $text );
			$insertables = array_merge( $insertables, $new );
		}

		return array_unique( $insertables, SORT_REGULAR );
	}
}
