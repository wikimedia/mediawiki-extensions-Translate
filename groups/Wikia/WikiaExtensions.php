<?php
/**
 * Classes for Wikia extension translation.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2010, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * @todo Needs documentation.
 */
class PremadeWikiaExtensionGroups extends PremadeMediawikiExtensionGroups {
	protected $useConfigure = false;
	protected $idPrefix = 'wikia-';
	protected $path = null;

	public function __construct() {
		global $wgTranslateGroupRoot;

		parent::__construct();
		$dir = dirname( __FILE__ );
		$this->definitionFile = $dir . '/extensions.txt';
		$this->path = "$wgTranslateGroupRoot/wikia/";
	}

	protected function addAllMeta() {
		global $wgTranslateAC, $wgTranslateEC;

		$meta = array(
			'wikia-0-all' => 'AllWikiaExtensionsGroup',
		);

		foreach ( $meta as $id => $g ) {
			$wgTranslateAC[$id] = $g;
			$wgTranslateEC[] = $id;
		}
	}
}

/**
 * Adds a message group containing all supported Wikia extensions in the
 * Wikia Subversion repository and the Wikimedia Subversion.
 *
 * @todo Needs documentation.
 */
class AllWikiaExtensionsGroup extends AllMediawikiExtensionsGroup {
	protected $description = '{{int:translate-group-desc-wikiaextensions}}';
	protected $label = 'Extensions used by Wikia'; // currently using 1.14.0
	protected $id    = 'wikia-0-all';
	protected $meta  = true;
	protected $type  = 'wikia';
	protected $classes = null;

	protected $wikiaextensions = array(
		'ext-antibot',
		'ext-categorytree',
		'ext-charinsert',
		'ext-checkuser',
		'ext-cite',
		'ext-confirmedit',
		'ext-dismissablesitenotice',
		'ext-dplforum',
		'ext-editcount',
		'ext-findspam',
		'ext-googlemaps',
		'ext-imagemap',
		'ext-importfreeimages',
		'ext-inputbox',
		'ext-lookupuser',
		'ext-multiupload',
		'ext-parserfunctions',
		'ext-poem',
		'ext-randomimage',
		'ext-spamblacklist',
		'ext-stringfunctions',
		'ext-timeline',
		'ext-torblock',
		'ext-wikihiero',
	);

	protected function init() {
		if ( $this->classes === null ) {
			$this->classes = array();

			$classes = MessageGroups::singleton()->getGroups();

			// Add regular MediaWiki extensions
			foreach ( $this->wikiaextensions as $key ) {
				$this->classes[$key] = $classes[$key];
			}

			// Add extensions that have a wikia- prefix
			foreach ( $classes as $index => $class ) {
				if ( ( strpos( $index, 'wikia-' ) === 0 ) && !$class->isMeta() && $class->exists() ) {
					$this->classes[$index] = $classes[$index];
					$this->wikiaextensions[] = $index;
				}
			}
		}
	}

	public function wikiaextensions() {
		return $this->wikiaextensions;
	}
}
