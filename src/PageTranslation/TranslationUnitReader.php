<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

/**
 * @license GPL-2.0-or-later
 * @author Niklas Laxström
 * @since 2021.05
 */
interface TranslationUnitReader {
	/** @return array<string,TranslationUnit> */
	public function getUnits(): array;

	/** @return string[] */
	public function getNames(): array;
}
