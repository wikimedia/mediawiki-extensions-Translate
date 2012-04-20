<?php
/**
 * Script for processing message changes in file based message groups.
 *
 * @author Niklas Laxstrom
 *
 * @copyright Copyright © 2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @file
 */

// Standard boilerplate to define $IP
if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$dir = dirname( __FILE__ ); $IP = "$dir/../../..";
}
require_once( "$IP/maintenance/Maintenance.php" );

/// * Script for processing message changes in file based message groups.
class ProcessMessageChanges extends Maintenance {
	protected $changes = array();

	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Script for processing message changes in file based message groups';
	}

	public function execute() {
		$groups = self::getGroupsOfType( 'FileBasedMessageGroup' );
		foreach ( $groups as $group ) {
			$this->processMessageGroup( $group );
		}
		$this->writeChanges();
	}

	protected function writeChanges() {
		// This method is almost identical with MessageIndex::store
		wfProfileIn( __METHOD__ );
		$array = $this->changes;
		$file = TranslateUtils::cacheFile( SpecialManageGroups::CHANGEFILE );
		$cache = CdbWriter::open( $file );
		$keys = array_keys( $array );
		$cache->set( '#keys', serialize( $keys ) );

		foreach ( $array as $key => $value ) {
			$value = serialize( $value );
			$cache->set( $key, $value );
		}
		$cache->close();
		wfProfileOut( __METHOD__ );
	}

	protected function processMessageGroup( FileBasedMessageGroup $group ) {
		$languages = Language::getLanguageNames( false );
		$sourceLanguage = $group->getSourceLanguage();
		unset( $languages[$sourceLanguage] );
		$languages = array_keys( $languages );
		$languages = array( 'de' );

		$this->processLanguage( $group, $sourceLanguage );
		foreach ( $languages as $code ) {
			$this->processLanguage( $group, $code );
		}
	}

	protected function processLanguage( FileBasedMessageGroup $group, $code ) {
		wfProfileIn( __METHOD__ );
		$cache = new MessageGroupCache( $group, $code );
		$reason = 0;
		if ( !$cache->isValid( $reason ) ) {
			$this->addMessageUpdateChanges( $group, $code, $reason );
		}
		wfProfileOut( __METHOD__ );
	}

	protected function addMessageUpdateChanges( FileBasedMessageGroup $group, $code, $reason ) {
		wfProfileIn( __METHOD__ );
		$wiki = $group->initCollection( $code );
		$wiki->filter( 'hastranslation', false );
		$wiki->loadTranslations();

		// By-pass cached messages
		$file = $group->getFFS()->read( $code );

		$fileKeys = array_keys( $file['MESSAGES'] );
		$wikiKeys = $wiki->getMessageKeys();
		$common = array_intersect( $fileKeys, $wikiKeys );

		foreach ( $common as $key ) {
			$sourceContent = $file['MESSAGES'][$key];
			$wikiContent = $wiki[$key]->translation();

			if ( $sourceContent !== str_replace( TRANSLATE_FUZZY, '', $wikiContent ) ) {
				// TODO: Check whether the cached content is the
				// same as the source and if so skip
				$this->addChange( 'change', $group, $code, $key, $sourceContent );
			}
		}

		$added = array_diff( $fileKeys, $wikiKeys );
		foreach ( $added as $key ) {
			$sourceContent = $file['MESSAGES'][$key];
			if ( trim( $sourceContent ) === '' ) continue;
			$this->addChange( 'addition', $group, $code, $key, $sourceContent );
		}

		$deleted = array_diff( $wikiKeys, $fileKeys );
		foreach ( $deleted as $key ) {
			if ( $reason === MessageGroupCache::NO_CACHE ) continue;
			$this->addChange( 'deletion', $group, $code, $key, null );
		}

		wfProfileOut( __METHOD__ );
	}

	protected function addChange( $type, $group, $language, $key, $content ) {
		$this->changes[$group->getId()][$language][$type][] = array(
			'key' => $key,
			'content' => $content,
		);
	}


	protected static function getGroupsOfType( $type ) {
		return array( MessageGroups::getGroup( 'out-waymarked-trails' ) );
		wfProfileIn( __METHOD__ );
		$groups = MessageGroups::getAllGroups();
		foreach ( $groups as $id => $group ) {
			if ( !$group instanceof $type ) {
				unset( $groups[$id] );
			}
		}
		wfProfileOut( __METHOD__ );
		return $groups;
	}

}

$maintClass = 'ProcessMessageChanges';
require_once( RUN_MAINTENANCE_IF_MAIN );
