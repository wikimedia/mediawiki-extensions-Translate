<?php
/**
 * This file contains an unmanaged message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2013, Niklas Laxström, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * This group supports the %MediaWiki messages.
 * @todo Move to the new interface.
 * @ingroup MessageGroup
 */
class CoreMessageGroup extends MessageGroupOld {
	protected $label = 'MediaWiki';
	protected $id = 'core';
	protected $type = 'mediawiki';
	protected $description = '{{int:translate-group-desc-mediawikicore}}';
	protected $icon;

	public function __construct() {
		parent::__construct();

		global $IP;

		$this->prefix = $IP . '/languages/messages';
		$this->metaDataPrefix = $IP . '/maintenance/language';
	}

	protected $prefix = '';

	public function getPrefix() {
		return $this->prefix;
	}

	public function setPrefix( $value ) {
		$this->prefix = $value;
	}

	protected $metaDataPrefix = '';

	public function getMetaDataPrefix() {
		return $this->metaDataPrefix;
	}

	public function setMetaDataPrefix( $value ) {
		$this->metaDataPrefix = $value;
	}

	public $parentId = null;

	public static function factory( $label, $id ) {
		$group = new CoreMessageGroup;
		$group->setLabel( $label );
		$group->setId( $id );

		return $group;
	}

	/// @see MessageGroup::getIcon
	public function setIcon( $icon ) {
		$this->icon = $icon;
	}

	public function getIcon() {
		return $this->icon;
	}

	public function getUniqueDefinitions() {
		if ( $this->parentId ) {
			$parent = MessageGroups::getGroup( $this->parentId );
			$parentDefs = $parent->getDefinitions();
			$ourDefs = $this->getDefinitions();

			// Filter out shared messages.
			foreach ( array_keys( $parentDefs ) as $key ) {
				unset( $ourDefs[$key] );
			}

			return $ourDefs;
		}

		return $this->getDefinitions();
	}

	public function getMessageFile( $code ) {
		$code = ucfirst( str_replace( '-', '_', $code ) );

		return "Messages$code.php";
	}

	public function getPath() {
		return $this->prefix;
	}

	public function getReader( $code ) {
		return new WikiFormatReader( $this->getMessageFileWithPath( $code ) );
	}

	public function getWriter() {
		return new WikiFormatWriter( $this );
	}

	public function getTags( $type = null ) {
		require( $this->getMetaDataPrefix() . '/messageTypes.inc' );
		$mangler = $this->getMangler();
		$this->optional = $mangler->mangle( $wgOptionalMessages );
		$this->ignored = $mangler->mangle( $wgIgnoredMessages );

		return parent::getTags( $type );
	}

	public function load( $code ) {
		$file = $this->getMessageFileWithPath( $code );
		// Can return null, convert to array.
		$mangler = $this->getMangler();
		$messages = (array) $mangler->mangle(
			PHPVariableLoader::loadVariableFromPHPFile( $file, 'messages' )
		);

		if ( $this->parentId ) {
			if ( !$this->isSourceLanguage( $code ) ) {
				// For branches, load newer compatible messages for missing entries, if any.
				$trunk = MessageGroups::getGroup( $this->parentId );
				$messages += $trunk->getMangler()->mangle( $trunk->load( $code ) );
			}
		}

		return $messages;
	}

	public function getChecker() {
		$checker = new MediaWikiMessageChecker( $this );
		$checker->setChecks( array(
			array( $checker, 'pluralCheck' ),
			array( $checker, 'pluralFormsCheck' ),
			array( $checker, 'wikiParameterCheck' ),
			array( $checker, 'wikiLinksCheck' ),
			array( $checker, 'XhtmlCheck' ),
			array( $checker, 'braceBalanceCheck' ),
			array( $checker, 'pagenameMessagesCheck' ),
			array( $checker, 'miscMWChecks' )
		) );

		return $checker;
	}
}
