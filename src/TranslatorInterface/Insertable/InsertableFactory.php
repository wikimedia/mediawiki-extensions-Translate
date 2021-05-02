<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Insertable;

use InvalidArgumentException;

/**
 * A factory class used to instantiate instances of Insertables
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2020.12
 */
class InsertableFactory {
	/**
	 * Takes a InsertableSuggester class name, and returns an instance of that class.
	 * @param string $class
	 * @param array|string|null $params
	 * @throws InvalidArgumentException
	 */
	public static function make( string $class, $params = null ): InsertablesSuggester {
		// FIXME: We should look at using "id / class" similar to what we do with the ValidatorFactory
		$checkedClasses = [];

		// This check is done for custom insertables that might be added for certain groups.
		if ( !class_exists( $class ) ) {
			$checkedClasses[] = $class;
			// Custom class not found, so lets try to load pre-provided Insertables.
			$class = __NAMESPACE__ . '\\' . $class;
		}

		if ( !class_exists( $class ) ) {
			$checkedClasses[] = $class;
			throw new InvalidArgumentException(
				'Could not find InsertableSuggester with class: ' . implode( ', ', $checkedClasses )
			);
		}

		$suggester = new $class( $params );
		if ( !$suggester instanceof InsertablesSuggester ) {
			throw new InvalidArgumentException(
				"$class does not implement the InsertableSuggester interface"
			);
		}

		return $suggester;
	}
}
