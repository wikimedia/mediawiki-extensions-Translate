<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use IContextSource;
use MediaWiki\Linker\LinkRenderer;
use Wikimedia\Rdbms\ILoadBalancer;

/**
 * Factory class for MessageGroupStatsTable
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2023.02
 */
class MessageGroupStatsTableFactory {
	private ProgressStatsTableFactory $progressStatsTableFactory;
	private ILoadBalancer $loadBalancer;
	private LinkRenderer $linkRenderer;
	private bool $haveTranslateWorkflowStates;

	public function __construct(
		ProgressStatsTableFactory $progressStatsTableFactory,
		ILoadBalancer $loadBalancer,
		LinkRenderer $linkRenderer,
		bool $haveTranslateWorkflowStates
	) {
		$this->progressStatsTableFactory = $progressStatsTableFactory;
		$this->loadBalancer = $loadBalancer;
		$this->linkRenderer = $linkRenderer;
		$this->haveTranslateWorkflowStates = $haveTranslateWorkflowStates;
	}

	public function newFromContext( IContextSource $contextSource ): MessageGroupStatsTable {
		return new MessageGroupStatsTable(
			$this->progressStatsTableFactory->newFromContext( $contextSource ),
			$this->loadBalancer,
			$this->linkRenderer,
			$contextSource,
			$contextSource->getLanguage(),
			$this->haveTranslateWorkflowStates
		);
	}
}
