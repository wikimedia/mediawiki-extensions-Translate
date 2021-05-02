<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Utilities;

use MediaWiki\Widget\TagMultiselectWidget;

/**
 * Widget to select multiple languages.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2020.07
 */
class LanguagesMultiselectWidget extends TagMultiselectWidget {
	/** @var array */
	private $languages;

	public function __construct( array $config = [] ) {
		parent::__construct( $config );
		$this->languages = $config['languages'];
	}

	protected function getJavaScriptClassName() {
		return 'LanguagesMultiselectWidget';
	}

	public function getConfig( &$config ) {
		$config['languages'] = $this->languages;

		return parent::getConfig( $config );
	}
}
