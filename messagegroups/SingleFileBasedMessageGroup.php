<?php
/**
 * This file a contains a message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * SingleFileBasedMessageGroup is a special case of FileBasedMessageGroup.
 * It should be used for all file based message groups that store all
 * language translations in single file.
 *
 * It triggers special handling when importing external changes, since we
 * actually need to parse the file to look what languages are present instead
 * of just checking whether files with specific names exist.
 *
 * The message group should go together with FFS implementation that handles
 * multiple languages in one file efficiently. See MediaWikiExtensionFFS for
 * high quality example implementation.
 *
 * @ingroup MessageGroup
 */
class SingleFileBasedMessageGroup extends FileBasedMessageGroup {
}
