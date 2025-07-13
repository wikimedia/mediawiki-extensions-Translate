<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Cache\LinkBatchFactory;
use MediaWiki\Extension\Translate\MessageProcessing\MessageGroupMetadata;
use MediaWiki\Html\Html;
use MediaWiki\Html\TemplateParser;
use MediaWiki\SpecialPage\SpecialPage;

/**
 * Contains logic for special page Special:AggregateGroups.
 *
 * @author Santhosh Thottingal
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @author Kunal Grover
 * @license GPL-2.0-or-later
 */
class AggregateGroupsSpecialPage extends SpecialPage {
	private LinkBatchFactory $linkBatchFactory;
	private MessageGroupMetadata $messageGroupMetadata;
	private AggregateGroupManager $aggregateGroupManager;
	private TemplateParser $templateParser;

	public function __construct(
		LinkBatchFactory $linkBatchFactory,
		MessageGroupMetadata $messageGroupMetadata,
		AggregateGroupManager $aggregateGroupManager
	) {
		parent::__construct( 'AggregateGroups', 'translate-manage' );
		$this->linkBatchFactory = $linkBatchFactory;
		$this->messageGroupMetadata = $messageGroupMetadata;
		$this->aggregateGroupManager = $aggregateGroupManager;
		$this->templateParser = new TemplateParser( __DIR__ . '/templates' );
	}

	protected function getGroupName(): string {
		return 'translation';
	}

	/** @inheritDoc */
	public function execute( $parameters ): void {
		$this->setHeaders();
		$this->addHelpLink( 'Help:Extension:Translate/Page translation administration' );

		// Check permissions
		$hasPermission = $this->getUser()->isAllowed( 'translate-manage' );

		$out = $this->getOutput();

		$out->addModuleStyles( 'ext.translate.specialpages.styles' );
		$out->addModules( 'ext.translate.special.aggregategroups' );

		$out->addHTML(
			Html::element(
				'div',
				[
					'id' => 'ext-translate-aggregategroups',
					'data-haspermission' => $hasPermission ? 'true' : null,
				]
			)
		);

		$aggregateGroups = $this->aggregateGroupManager->getAll();
		if ( $aggregateGroups === [] ) {
			$out->addHTML(
				Html::noticeBox(
					$this->msg( 'tpt-aggregategroup-no-groups' )->escaped(),
					'tpt-aggregategroup-nogroups'
				)
			);
		}

		$msgCache = [
			'editLabel' => $this->msg( 'tpt-aggregategroup-edit' )->text(),
			'deleteLabel' => $this->msg( 'tpt-aggregategroup-delete' )->text(),
			'removeItemLabel' => $this->msg( 'tpt-aggregategroup-remove-item' )->text(),
		];
		foreach ( $aggregateGroups as $aggregateGroup ) {
			$subGroupIds = $this->messageGroupMetadata->getSubgroups( $aggregateGroup->getId() );

			$html = $this->templateParser->processTemplate( 'AggregateGroupTemplate', [
				'id' => $aggregateGroup->getId(),
				'name' => $aggregateGroup->getLabel(),
				'description' => $aggregateGroup->getDescription(),
				'subGroups' => $this->getSubGroupInfoForTemplate( $subGroupIds ),
				'shouldExpand' => count( $subGroupIds ) <= 3,
				'editLabel' => $msgCache['editLabel'],
				'deleteLabel' => $msgCache['deleteLabel'],
				'removeItemLabel' => $msgCache['removeItemLabel'],
				'hasManageRights' => $hasPermission,
			] );

			$this->getOutput()->addHTML( $html );
		}
	}

	private function getSubGroupInfoForTemplate( array $subGroupIds ): array {
		$subGroups = MessageGroups::getGroupsById( $subGroupIds );
		uasort( $subGroups, [ MessageGroups::class, 'groupLabelSort' ] );

		$groupCache = $this->getGroupCache( $subGroups );

		$subGroupInfo = [];
		foreach ( $subGroupIds as $id ) {
			$group = $subGroups[$id] ?? null;
			if ( $group ) {
				$text = $this->getLinkRenderer()->makeKnownLink( $groupCache[$group->getId()], $group->getLabel() );
				$note = htmlspecialchars( MessageGroups::getPriority( $id ) );
			} else {
				$text = htmlspecialchars( $id );
				$note = $this->msg( 'tpt-aggregategroup-invalid-group' )->escaped();
			}
			$subGroupInfo[] = [
				'id' => $id,
				'labelHtml' => $text,
				'note' => $note,
			];
		}

		return $subGroupInfo;
	}

	/**
	 * Adds titles of subgroups into the link cache and execute it to add them to the LinkCache
	 * to avoid potentially thousands of separate database queries from LinkRenderer::makeKnownLink
	 */
	private function getGroupCache( array $subGroups ): array {
		$groupCache = [];
		$lb = $this->linkBatchFactory->newLinkBatch();
		foreach ( $subGroups as $group ) {
			$subGroupId = $group->getId();
			$groupCache[$subGroupId] = $this->aggregateGroupManager->getTargetTitleByGroupId( $subGroupId );
			$lb->addObj( $groupCache[$subGroupId] );
		}
		$lb->setCaller( __METHOD__ );
		$lb->execute();

		return $groupCache;
	}
}
