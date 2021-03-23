<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

/**
 * This class represents one translation variable in a translation unit.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2021.03
 */
class TranslationVariable {
	/** @var string */
	private $definition;
	/** @var string */
	private $name;
	/** @var string */
	private $value;

	public function __construct( string $definition, string $name, string $value ) {
		$this->definition = $definition;
		$this->name = $name;
		$this->value = $value;
	}

	public function getDefinition(): string {
		return $this->definition;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getValue(): string {
		return $this->value;
	}
}
