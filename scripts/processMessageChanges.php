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

/**
 * Script for processing message changes in file based message groups.
 *
 * We used to process changes during web request, but that was too slow. With
 * this command line script we can do all the work needed even if it takes
 * some time.
 *
 * @since 2012-04-23
 */
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
		if ( count( $this->changes ) ) {
			$this->writeChanges();
		} else {
			$this->output( 'No changes found' );
		}
	}

	protected function writeChanges() {
		// This method is almost identical with MessageIndex::store
		wfProfileIn( __METHOD__ );
		$array = $this->changes;
		/* This will overwrite the previous cache file if any. Once the cache
		 * file is processed with Special:ManageMessageGroups, it is
		 * renamed so that it wont be processed again. */
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

		// Process the source language before others
		$sourceLanguage = $group->getSourceLanguage();
		unset( $languages[$sourceLanguage] );
		$languages = array_keys( $languages );
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
		/* This throws a warning if message definitions are not yet
		 * cached and will read the file for definitions. */
		$wiki = $group->initCollection( $code );
		$wiki->filter( 'hastranslation', false );
		$wiki->loadTranslations();
		$wikiKeys = $wiki->getMessageKeys();

		// By-pass cached message definitions
		$file = $group->getFFS()->read( $code );
		$fileKeys = array_keys( $file['MESSAGES'] );

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
			/* Should the cache not exist, don't consider the messages
			 * missing from the file as deleted - they probably aren't
			 * yet exported. For example new language translations are
			 * exported the first time. */
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
