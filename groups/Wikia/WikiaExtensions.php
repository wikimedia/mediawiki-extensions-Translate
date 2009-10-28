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
		// Nothing for now
	}
}