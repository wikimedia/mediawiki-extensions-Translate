<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use IContextSource;
use MediaWiki\Extension\Translate\Utilities\ConfigHelper;
use MediaWiki\Linker\LinkRenderer;
use StatsTable;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2021.10
 */
class ProgressStatsTableFactory {
	/** @var LinkRenderer */
	private $linkRenderer;
	/** @var ConfigHelper */
	private $configHelper;

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
