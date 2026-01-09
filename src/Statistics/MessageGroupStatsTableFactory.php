<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use MediaWiki\Context\IContextSource;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupReviewStore;
use MediaWiki\Extension\Translate\MessageProcessing\MessageGroupMetadata;
use MediaWiki\Linker\LinkRenderer;

/**
 * Factory class for MessageGroupStatsTable
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2023.02
 */
class MessageGroupStatsTableFactory {

	public function __construct(
		private readonly ProgressStatsTableFactory $progressStatsTableFactory,
		private readonly LinkRenderer $linkRenderer,
		private readonly MessageGroupReviewStore $groupReviewStore,
		private readonly MessageGroupMetadata $messageGroupMetadata,
		private readonly bool $haveTranslateWorkflowStates,
	) {
	}

	public function newFromContext( IContextSource $contextSource ): MessageGroupStatsTable {
		return new MessageGroupStatsTable(
			$this->progressStatsTableFactory->newFromContext( $contextSource ),
			$this->linkRenderer,
			$contextSource,
			$contextSource->getLanguage(),
			$this->groupReviewStore,
			$this->messageGroupMetadata,
			$this->haveTranslateWorkflowStates
		);
	}
}
