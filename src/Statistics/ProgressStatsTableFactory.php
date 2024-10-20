<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use MediaWiki\Context\IContextSource;
use MediaWiki\Extension\Translate\MessageProcessing\MessageGroupMetadata;
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
	private MessageGroupMetadata $messageGroupMetadata;

	public function __construct(
		LinkRenderer $linkRenderer,
		ConfigHelper $configHelper,
		MessageGroupMetadata $messageGroupMetadata
	) {
		$this->linkRenderer = $linkRenderer;
		$this->configHelper = $configHelper;
		$this->messageGroupMetadata = $messageGroupMetadata;
	}

	public function newFromContext( IContextSource $contextSource ): StatsTable {
		return new StatsTable(
			$this->linkRenderer,
			$this->configHelper,
			$contextSource,
			$contextSource->getLanguage(),
			$this->messageGroupMetadata
		);
	}

}
