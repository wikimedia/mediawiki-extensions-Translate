<?php
/**
 * Web service utility interface.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Interface for classes that want to use QueryAggregator.
 * @since 2015.12
 */
interface QueryAggregatorAware {
	public function setQueryAggregator( QueryAggregator $aggregator );
	public function populateQueries();
}
