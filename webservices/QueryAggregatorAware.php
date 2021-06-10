<?php
declare( strict_types = 1 );

/**
 * Interface for classes that want to use QueryAggregator.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2015.12
 */
interface QueryAggregatorAware {
	public function setQueryAggregator( QueryAggregator $aggregator ): void;

	public function populateQueries(): void;
}
