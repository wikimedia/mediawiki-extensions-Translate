<?php
/**
 * File format support classes.
 *
 * These classes handle parsing and generating various different
 * file formats where translation messages are stored.
 *
 * @file
 * @defgroup FFS File format support
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2013, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Interface for file system support classes.
 * @ingroup FFS
 */
interface FFS {
	public function __construct( FileBasedMessageGroup $group );

	/**
	 * Set the file's location in the system
	 * @param string $target Filesystem path for exported files.
	 */
	public function setWritePath( $target );

	/**
	 * Get the file's location in the system
	 * @return string
	 */
	public function getWritePath();

	/**
	 * Will parse messages, authors, and any custom data from the file
	 * and return it in associative array with keys like \c AUTHORS and
	 * \c MESSAGES.
	 * @param string $code Language code.
	 * @return array|bool Parsed data or false on failure.
	 */
	public function read( $code );

	/**
	 * Same as read(), but takes the data as a parameter. The caller
	 * is supposed to know in what language the translations are.
	 * @param string $data Formatted messages.
	 * @return array Parsed data.
	 */
	public function readFromVariable( $data );

	/**
	 * Writes to the location provided with setWritePath and group specific
	 * directory structure. Exports translations included in the given
	 * collection with any special handling needed.
	 * @param $collection MessageCollection
	 */
	public function write( MessageCollection $collection );

	/**
	 * Quick shortcut for getting the plain exported data.
	 * Same as write(), but returns the output instead of writing it into
	 * a file.
	 * @param MessageCollection $collection
	 * @return string
	 */
	public function writeIntoVariable( MessageCollection $collection );

	/**
	 * Query the capabilities of this FFS. Allowed values are:
	 *  - yes
	 *  - write (ignored on read)
	 *  - no (stripped on write)
	 * @return string
	 * @since 2013-03-05
	 */
	public function supportsFuzzy();

	/**
	 * Return the commonly used file extensions for these formats.
	 * Include the dot.
	 * @return string[]
	 * @since 2013-04
	 */
	public function getFileExtensions();
}
