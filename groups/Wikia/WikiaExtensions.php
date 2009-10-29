<?php


class PremadeWikiaExtensionGroups extends PremadeMediawikiExtensionGroups {
	protected $useConfigure = false;
	protected $idPrefix = 'wikia-';
	protected $path = null;

	public function __construct() {
		parent::__construct();
		$dir = dirname( __FILE__ );
		$this->definitionFile = $dir . '/extensions.txt';
		$this->path = '/var/www/externals/wikia/';
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

class AllWikiaExtensionsGroup extends AllMediawikiExtensionsGroup {
	protected $description = '{{int:translate-group-desc-wikiaextensions}}';
	protected $label = 'Extensions used by Wikia'; // currently using 1.14.0
	protected $id    = 'ext-0-wikia';
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
			foreach ( $this->wikiaextensions as $key ) {
				$this->classes[$key] = $classes[$key];
			}
		}
	}

	public function wikiaextensions() {
		return $this->wikiaextensions;
	}
}
