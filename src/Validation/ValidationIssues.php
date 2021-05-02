<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extension\Translate\Validation;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Mutable collection for validation issues.
 *
 * @newable
 * @since 2020.06
 */
class ValidationIssues implements Countable, IteratorAggregate {
	/** @var ValidationIssue[] */
	private $issues = [];

	/** Add a new validation issue to the collection. */
	public function add( ValidationIssue $issue ) {
		$this->issues[] = $issue;
	}

	/** Merge another collection to this collection. */
	public function merge( ValidationIssues $issues ) {
		$this->issues = array_merge( $this->issues, $issues->issues );
	}

	/**
	 * Check whether this collection is not empty.
	 *
	 * @return bool False if empty, true otherwise
	 */
	public function hasIssues(): bool {
		return $this->issues !== [];
	}

	/** @return Traversable<ValidationIssue> */
	public function getIterator(): Traversable {
		return new ArrayIterator( $this->issues );
	}

	/** @inheritDoc */
	public function count(): int {
		return count( $this->issues );
	}
}
