<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

use FileBasedMessageGroup;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;

/**
 * Interface for file format support classes. These classes handle parsing and generating
 * various different file formats where translation messages are stored.
 *
 * @ingroup FileFormatSupport
 * @defgroup FileFormatSupport File format support
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 */
interface FileFormatSupport {
	public function __construct( FileBasedMessageGroup $group );

	/**
	 * Set the file's location in the system
	 * @param string $target Filesystem path for exported files.
	 */
	public function setWritePath( string $target );

	/** Get the file's location in the system */
	public function getWritePath(): string;

	/**
	 * Will parse messages, authors, and any custom data from the file
	 * and return it in associative array with keys like \c AUTHORS and
	 * \c MESSAGES.
	 * @param string $code Language code.
	 * @return array|bool Parsed data or false on failure.
	 */
	public function read( string $code );

	/**
	 * Same as read(), but takes the data as a parameter. The caller
	 * is supposed to know in what language the translations are.
	 * @param string $data Formatted messages.
	 * @return array Parsed data.
	 */
	public function readFromVariable( string $data ): array;

	/**
	 * Writes to the location provided with setWritePath and group specific
	 * directory structure. Exports translations included in the given
	 * collection with any special handling needed.
	 */
	public function write( MessageCollection $collection );

	/**
	 * Quick shortcut for getting the plain exported data.
	 * Same as write(), but returns the output instead of writing it into
	 * a file.
	 */
	public function writeIntoVariable( MessageCollection $collection ): string;

	/**
	 * Query the capabilities of this FFS. Allowed values are:
	 *  - yes
	 *  - write (ignored on read)
	 *  - no (stripped on write)
	 */
	public function supportsFuzzy(): string;

	/**
	 * Checks whether two strings are equal. Sometimes same content might
	 * have multiple representations. The main case are inline plurals,
	 * which in some formats require expansion at export time.
	 */
	public function isContentEqual( ?string $a, ?string $b ): bool;

	/**
	 * Return the commonly used file extensions for these formats. Include the dot.
	 * @return string[]
	 */
	public function getFileExtensions(): array;

	/**
	 * Allows to skip writing the export output into a file. This is useful
	 * to skip updates that would only update irrelevant parts, such as the
	 * timestamp of the export.
	 *
	 * @param string $a The existing content.
	 * @param string $b The new export content.
	 */
	public function shouldOverwrite( string $a, string $b ): bool;
}
