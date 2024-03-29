<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use IContextSource;
use MediaWiki\Extension\Translate\Utilities\ConfigHelper;
use MediaWiki\Linker\LinkRenderer;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2021.10
 */
class ProgressStatsTableFactory {
	private LinkRenderer $linkRenderer;
	private ConfigHelper $configHelper;

	public function __construct(
		LinkRenderer $linkRenderer,
		ConfigHelper $configHelper
	) {
		$this->linkRenderer = $linkRenderer;
		$this->configHelper = $configHelper;
	}

	public function newFromContext( IContextSource $contextSource ): StatsTable {
		return new StatsTable(
			$this->linkRenderer,
			$this->configHelper,
			$contextSource,
			$contextSource->getLanguage()
		);
	}

}
