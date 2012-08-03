<?php
/**
 * This file contains a unmanaged message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2012, Niklas Laxström, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * This group supports messages of %MediaWiki extensions using the standard
 * format.
 * @todo Move to the new interface.
 * @ingroup MessageGroup
 */
class ExtensionMessageGroup extends MessageGroupOld {
	protected $magicFile, $aliasFile;

	/**
	 * Name of the array where all messages are stored, if applicable.
	 */
	protected $arrName = 'messages';

	/**
	 * Name of the array where all special page aliases are stored, if applicable.
	 * Only used in class SpecialPageAliasesCM
	 */
	protected $arrAlias = 'specialPageAliases';

	protected $path = null;

	public function getVariableName() { return $this->arrName; }
	public function setVariableName( $value ) { $this->arrName = $value; }

	public function getVariableNameAlias() { return $this->arrAlias; }
	public function setVariableNameAlias( $value ) { $this->arrAlias = $value; }

	/**
	 * Path to the file where array or function is defined, relative to extensions
	 * root directory defined by $wgTranslateExtensionDirectory.
	 */
	protected $messageFile  = null;
	public function getMessageFile( $code ) { return $this->messageFile; }
	public function setMessageFile( $value ) { $this->messageFile = $value; }

	public function getDescription() {
		if ( $this->description === null ) {
			// Load the messages only when needed.
			$this->setDescriptionMsgReal( $this->descriptionKey, $this->descriptionUrl );
		}
		return parent::getDescription();
	}

	/**
	 * Holders for lazy loading.
	 */
	private $descriptionKey, $descriptionUrl;

	/**
	 * Extensions have almost always a localised description message and
	 * address to extension homepage.
	 * @param $key
	 * @param $url
	 */
	public function setDescriptionMsg( $key, $url ) {
		$this->descriptionKey = $key;
		$this->descriptionUrl = $url;
	}

	/**
	 * @param $key
	 * @param $url
	 */
	protected function setDescriptionMsgReal( $key, $url ) {
		$this->description = '';

		$desc = null;

		$msg = wfMessage( $key );
		if ( !$msg->isDisabled() ) {
			$desc = $msg->plain();
		}

		if ( $desc === null ) {
			$desc = $this->getMessage( $key, $this->getSourceLanguage() );
		}

		if ( $desc !== null ) {
			$this->description = $desc;
		}

		if ( $url ) {
			$this->description .= wfMessage( 'translate-ext-url', $url )->plain();
		}

		if ( $this->description === '' ) {
			$this->description = wfMessage( 'translate-group-desc-nodesc' )->plain();
		}
	}

	/**
	 * @param $label
	 * @param $id
	 * @return ExtensionMessageGroup
	 */
	public static function factory( $label, $id ) {
		$group = new ExtensionMessageGroup;
		$group->setLabel( $label );
		$group->setId( $id );

		return $group;
	}

	/**
	 * This function loads messages for given language for further use.
	 *
	 * @param $code \string Language code
	 * @throws MWException If loading fails.
	 * @return \array List of messages.
	 */
	public function load( $code ) {
		$reader = $this->getReader( $code );
		$cache = $reader->parseMessages( $this->mangler );

		if ( $cache === null ) {
			throw new MWException( "Unable to load messages for $code in {$this->label}" );
		}

		if ( isset( $cache[$code] ) ) {
			return $cache[$code];
		} else {
			return array();
		}
	}

	public function getPath() {
		if ( $this->path === null ) {
			global $wgTranslateExtensionDirectory;
			return $wgTranslateExtensionDirectory; // BC
		}
		return $this->path;
	}

	public function setPath( $path ) {
		$this->path = $path;
	}

	public function getReader( $code ) {
		$reader = new WikiExtensionFormatReader( $this->getMessageFileWithPath( $code ) );
		$reader->variableName = $this->getVariableName();

		return $reader;
	}

	public function getWriter() {
		$writer = new WikiExtensionFormatWriter( $this );
		$writer->variableName = $this->getVariableName();

		return $writer;
	}

	public function exists() {
		return is_readable( $this->getMessageFileWithPath( $this->getSourceLanguage() ) );
	}

	/**
	 * @return MediaWikiMessageChecker
	 */
	public function getChecker() {
		$checker = new MediaWikiMessageChecker( $this );
		$checker->setChecks( array(
			array( $checker, 'pluralCheck' ),
			array( $checker, 'wikiParameterCheck' ),
			array( $checker, 'wikiLinksCheck' ),
			array( $checker, 'XhtmlCheck' ),
			array( $checker, 'braceBalanceCheck' ),
			array( $checker, 'pagenameMessagesCheck' ),
			array( $checker, 'miscMWChecks' )
		) );

		return $checker;
	}

	public function getAliasFile() { return $this->aliasFile; }
	public function setAliasFile( $file ) { $this->aliasFile = $file; }

	public function getMagicFile() { return $this->magicFile; }
	public function setMagicFile( $file ) { $this->magicFile = $file; }
}
