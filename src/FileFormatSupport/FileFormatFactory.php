<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

use FileBasedMessageGroup;
use InvalidArgumentException;
use Wikimedia\ObjectFactory\ObjectFactory;

/**
 * A factory class used to instantiate instances of pre-provided File formats
 *
 * @author Abijeet Patro
 * @author Eugene Wang'ombe
 * @license GPL-2.0-or-later
 * @since 2023.05
 */
class FileFormatFactory {
	/**
	 * @var (string|array)[]
	 * @phpcs-require-sorted-array
	 */
	private const FORMATS = [
		'Amd' => AmdFormat::class,
		'AndroidXml' => AndroidXmlFormat::class,
		'Apple' => AppleFormat::class,
		'Dtd' => DtdFormat::class,
		'FlatPhp' => FlatPhpFormat::class,
		'Gettext' => GettextFormat::class,
		'Ini' => IniFormat::class,
		'Java' => JavaFormat::class,
		'Json' => JsonFormat::class,
		'Yaml' => YamlFormat::class
	];
	private ObjectFactory $objectFactory;

	public function __construct( ObjectFactory $objectFactory ) {
		$this->objectFactory = $objectFactory;
	}

	/**
	 * Returns a FileFormat class instance based on the $format passed
	 *
	 * @param string $format FileFormat identifier
	 * @param FileBasedMessageGroup $group
	 * @return SimpleFormat
	 */
	public function create( string $format, FileBasedMessageGroup $group ): SimpleFormat {
		if ( !isset( self::FORMATS[$format] ) ) {
			throw new InvalidArgumentException(
				"FileFormatSupport: Unknown file format '$format' specified for group '{$group->getId()}'"
			);
		}

		$spec = self::FORMATS[$format];
		if ( is_string( $spec ) ) {
			$spec = [ 'class' => $spec ];
		}

		// Pass the given params as one item, instead of expanding
		$spec['args'][] = $group;

		// Phan seems to misunderstand the param type as callable instead of an array
		// @phan-suppress-next-line PhanTypeInvalidCallableArrayKey
		return $this->objectFactory->createObject( $spec );
	}

	public function loadInstance( string $class, FileBasedMessageGroup $group ): SimpleFormat {
		if ( !class_exists( $class ) ) {
			throw new InvalidArgumentException(
				"Could not find FileFormat class '$class' specified for group '{$group->getId()}'."
			);
		}

		return new $class( $group );
	}

	public function getClassname( string $format ): string {
		if ( !isset( self::FORMATS[$format] ) ) {
			throw new InvalidArgumentException( "Unknown format $format" );
		}

		return self::FORMATS[$format];
	}
}
