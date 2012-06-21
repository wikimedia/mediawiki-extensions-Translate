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
		$groups = MessageGroups::getGroupsByType( 'FileBasedMessageGroup' );
		foreach ( $groups as $id => $group ) {
			$this->output( "Processing $id\n" );
			$this->processMessageGroup( $group );
		}
		if ( count( $this->changes ) ) {
			$this->writeChanges();
		} else {
			$this->output( "No changes found\n" );
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
			$this->addMessageUpdateChanges( $group, $code, $reason, $cache );

			if ( !isset( $this->changes[$group->getId()][$code] ) ) {
				/* Update the cache immediately if file and wiki state match.
				 * Otherwise the cache will get outdated compared to file state
				 * and will give false positive conflicts later. */
				$cache->create();
			}
		}
		wfProfileOut( __METHOD__ );
	}

	/**
	 * This is the detective roman. We have three sources of information:
	 * - current message state in the file
	 * - current message state in the wiki
	 * - cached message state since cache was last build
	 *   (usually after export from wiki)
	 * Now we must try to guess what in earth has driven
	 * the file state and wiki state out of sync. Then we
	 * must compile list of events that would bring those
	 * to sync. Types of events are addition, deletion,
	 * (content) change and possible rename in the future.
	 * After that the list of events are stored for later
	 * processing of a translation administrator, who can
	 * decide what actions to take on those events to bring
	 * the state more or less in sync.
	 */
	protected function addMessageUpdateChanges( FileBasedMessageGroup $group, $code, $reason, $cache ) {
		wfProfileIn( __METHOD__ );
		/* This throws a warning if message definitions are not yet
		 * cached and will read the file for definitions. */
		$wiki = $group->initCollection( $code );
		$wiki->filter( 'hastranslation', false );
		$wiki->loadTranslations();
		$wikiKeys = $wiki->getMessageKeys();

		// By-pass cached message definitions
		$file = $group->getFFS()->read( $code );
		if ( !isset( $file['MESSAGES'] ) ) {
			error_log( "{$group->getId()} has an FFS - the FFS didn't return cake for $code" );
		}
		$fileKeys = array_keys( $file['MESSAGES'] );

		$common = array_intersect( $fileKeys, $wikiKeys );

		foreach ( $common as $key ) {
			$sourceContent = $file['MESSAGES'][$key];
			$wikiContent = $wiki[$key]->translation();

			if ( !self::compareContent( $sourceContent, $wikiContent ) ) {
				if ( $reason !== MessageGroupCache::NO_CACHE ) {
					$cacheContent = $cache->get( $key );
				  if ( self::compareContent( $sourceContent, $cacheContent ) ) {
						/* This message has only changed in the wiki, which means
						 * we can ignore the difference and have it exported on
						 * next export. */
						continue;
					}
				}
				$this->addChange( 'change', $group, $code, $key, $sourceContent );
			}
		}

		$added = array_diff( $fileKeys, $wikiKeys );
		foreach ( $added as $key ) {
			$sourceContent = $file['MESSAGES'][$key];
			if ( trim( $sourceContent ) === '' ) continue;
			$this->addChange( 'addition', $group, $code, $key, $sourceContent );
		}

		/* Should the cache not exist, don't consider the messages
		 * missing from the file as deleted - they probably aren't
		 * yet exported. For example new language translations are
		 * exported the first time. */
		if ( $reason !== MessageGroupCache::NO_CACHE ) {
			$deleted = array_diff( $wikiKeys, $fileKeys );
			foreach ( $deleted as $key ) {
				if ( $cache->get( $key ) === false ) {
					/* This message has never existed in the cache, so it
					 * must be a newly made in the wiki. */
					continue;
				}
				$this->addChange( 'deletion', $group, $code, $key, null );
			}
		}

		wfProfileOut( __METHOD__ );
	}

	protected function addChange( $type, $group, $language, $key, $content ) {
		$this->changes[$group->getId()][$language][$type][] = array(
			'key' => $key,
			'content' => $content,
		);
	}

	/**
	 * Compares two strings ignoring fuzzy markers.
	 * @since 2012-05-08
	 * @return bool
	 */
	protected static function compareContent( $a, $b ) {
		$a = str_replace( TRANSLATE_FUZZY, '', $a );
		$b = str_replace( TRANSLATE_FUZZY, '', $b );
		return  $a === $b;
	}
}

$maintClass = 'ProcessMessageChanges';
require_once( RUN_MAINTENANCE_IF_MAIN );
