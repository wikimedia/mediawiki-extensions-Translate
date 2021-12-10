<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use AggregateMessageGroup;
use Html;
use MediaWiki\Cache\LinkBatchFactory;
use MessageGroups;
use SpecialPage;
use TranslateMetadata;
use WikiPageMessageGroup;
use Xml;

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
	/** @var bool */
	private $hasPermission = false;
	/** @var LinkBatchFactory */
	private $linkBatchFactory;

	public function __construct( LinkBatchFactory $linkBatchFactory ) {
		parent::__construct( 'AggregateGroups', 'translate-manage' );
		$this->linkBatchFactory = $linkBatchFactory;
	}

	protected function getGroupName(): string {
		return 'translation';
	}

	public function execute( $parameters ) {
		$this->setHeaders();
		$this->addHelpLink( 'Help:Extension:Translate/Page translation administration' );

		$out = $this->getOutput();
		$out->addModuleStyles( 'ext.translate.specialpages.styles' );

		// Check permissions
		if ( $this->getUser()->isAllowed( 'translate-manage' ) ) {
			$this->hasPermission = true;
		}

		$groupsPreload = MessageGroups::getGroupsByType( AggregateMessageGroup::class );
		TranslateMetadata::preloadGroups( array_keys( $groupsPreload ), __METHOD__ );

		$groups = MessageGroups::getAllGroups();
		uasort( $groups, [ MessageGroups::class, 'groupLabelSort' ] );
		$aggregates = [];
		$pages = [];
		foreach ( $groups as $group ) {
			if ( $group instanceof WikiPageMessageGroup ) {
				$pages[] = $group;
			} elseif ( $group instanceof AggregateMessageGroup ) {
				// Filter out AggregateGroups configured in YAML
				$subgroups = TranslateMetadata::getSubgroups( $group->getId() );
				if ( $subgroups !== null ) {
					$aggregates[] = $group;
				}
			}
		}

		if ( !$pages ) {
			// @todo Use different message
			$out->addWikiMsg( 'tpt-list-nopages' );

			return;
		}

		$this->showAggregateGroups( $aggregates );
	}

	protected function showAggregateGroup( AggregateMessageGroup $group ): string {
		$id = $group->getId();
		$label = $group->getLabel();
		$desc = $group->getDescription( $this->getContext() );

		$edit = '';
		$remove = '';
		$editGroup = '';
		$select = '';
		$addButton = '';

		// Add divs for editing Aggregate Groups
		if ( $this->hasPermission ) {
			// Group edit and remove buttons
			$edit = Html::element( 'span', [ 'class' => 'tp-aggregate-edit-ag-button' ] );
			$remove = Html::element( 'span', [ 'class' => 'tp-aggregate-remove-ag-button' ] );

			// Edit group div
			$editGroupNameLabel = $this->msg( 'tpt-aggregategroup-edit-name' )->escaped();
			$editGroupName = Html::input(
				'tp-agg-name',
				$label,
				'text',
				[ 'class' => 'tp-aggregategroup-edit-name', 'maxlength' => '200' ]
			);
			$editGroupDescriptionLabel = $this->msg( 'tpt-aggregategroup-edit-description' )->escaped();
			$editGroupDescription = Html::input(
				'tp-agg-desc',
				$desc,
				'text',
				[ 'class' => 'tp-aggregategroup-edit-description' ]
			);
			$saveButton = Xml::submitButton(
				$this->msg( 'tpt-aggregategroup-update' )->text(),
				[ 'class' => 'tp-aggregategroup-update' ]
			);
			$cancelButton = Xml::submitButton(
				$this->msg( 'tpt-aggregategroup-update-cancel' )->text(),
				[ 'class' => 'tp-aggregategroup-update-cancel' ]
			);
			$editGroup = Html::rawElement(
				'div',
				[ 'class' => 'tp-edit-group hidden' ],
				$editGroupNameLabel .
				$editGroupName .
				'<br />' .
				$editGroupDescriptionLabel .
				$editGroupDescription .
				$saveButton .
				$cancelButton
			);

			// Subgroups selector
			$select = Html::input( 'tp-subgroups-input', '', 'text', [ 'class' => 'tp-group-input' ] );
			$addButton = Html::element( 'input',
				[
					'type' => 'button',
					'value' => $this->msg( 'tpt-aggregategroup-add' )->text(),
					'class' => 'tp-aggregate-add-button'
				]
			);
		}

		// Not calling $parent->getGroups() because it has done filtering already
		$subGroups = TranslateMetadata::getSubgroups( $id );
		$shouldExpand = count( $subGroups ) <= 3;
		$subGroupsId = $this->htmlIdForGroup( $group->getId(), 'tp-subgroup-' );

		// Aggregate Group info div
		$groupName = Html::rawElement(
			'h2',
			[ 'class' => 'tp-name' ],
			$this->getGroupToggleIcon( $subGroupsId, $shouldExpand ) . htmlspecialchars( $label ) . $edit . $remove
		);
		$groupDesc = Html::element(
			'p',
			[ 'class' => 'tp-desc' ],
			$desc
		);
		$groupInfo = Html::rawElement(
			'div',
			[ 'class' => 'tp-display-group' ],
			$groupName . $groupDesc
		);

		$out = Html::openElement(
			'div',
			[
				'class' => 'mw-tpa-group js-mw-tpa-group' . ( $shouldExpand ? ' mw-tpa-group-open' : '' ),
				'data-groupid' => $id,
				'data-id' => $this->htmlIdForGroup( $group->getId() )
			]
		);
		$out .= $groupInfo;
		$out .= $editGroup;
		$out .= Html::openElement( 'div', [ 'class' => 'tp-sub-groups', 'id' => $subGroupsId ] );
		$out .= $this->listSubgroups( $id, $subGroups );
		$out .= $select . $addButton;
		$out .= Html::closeElement( 'div' );
		$out .= '</div>';

		return $out;
	}

	/** @param AggregateMessageGroup[] $aggregates */
	private function showAggregateGroups( array $aggregates ): void {
		$out = $this->getOutput();
		$out->addModules( 'ext.translate.special.aggregategroups' );

		$nojs = Html::element(
			'div',
			[ 'class' => 'tux-nojs errorbox' ],
			$this->msg( 'tux-nojs' )->plain()
		);

		$out->addHTML( $nojs );

		// Add new group if user has permissions
		if ( $this->hasPermission ) {
			$out->addHTML(
				"<a class='tpt-add-new-group' href='#'>" .
					$this->msg( 'tpt-aggregategroup-add-new' )->escaped() .
					'</a>'
			);
			$newGroupNameLabel = $this->msg( 'tpt-aggregategroup-new-name' )->escaped();
			$newGroupName = Html::element( 'input', [ 'class' => 'tp-aggregategroup-add-name', 'maxlength' => '200' ] );
			$newGroupDescriptionLabel = $this->msg( 'tpt-aggregategroup-new-description' )->escaped();
			$newGroupDescription = Html::element( 'input', [ 'class' => 'tp-aggregategroup-add-description' ] );
			$saveButton = Html::element(
				'input',
				[
					'type' => 'button',
					'value' => $this->msg( 'tpt-aggregategroup-save' )->text(),
					'id' => 'tpt-aggregategroups-save',
					'class' => 'tp-aggregate-save-button'
				]
			);
			$closeButton = Html::element(
				'input',
				[
					'type' => 'button',
					'value' => $this->msg( 'tpt-aggregategroup-close' )->text(),
					'id' => 'tpt-aggregategroups-close'
				]
			);
			$newGroupDiv = Html::rawElement(
				'div',
				[ 'class' => 'tpt-add-new-group hidden' ],
				"$newGroupNameLabel $newGroupName<br />" .
					"$newGroupDescriptionLabel $newGroupDescription<br />$saveButton $closeButton"
			);
			$out->addHTML( $newGroupDiv );
		}

		/** @var AggregateMessageGroup $group */
		foreach ( $aggregates as $group ) {
			$out->addHTML( $this->showAggregateGroup( $group ) );
		}
	}

	private function listSubgroups( string $groupId, array $subGroupIds ): string {
		$id = $this->htmlIdForGroup( $groupId, 'mw-tpa-grouplist-' );
		$out = Html::openElement( 'ol', [ 'id' => $id ] );

		// Get the respective groups and sort them
		$subgroups = MessageGroups::getGroupsById( $subGroupIds );
		'@phan-var WikiPageMessageGroup[] $subgroups';
		uasort( $subgroups, [ MessageGroups::class, 'groupLabelSort' ] );

		// Avoid potentially thousands of separate database queries
		$lb = $this->linkBatchFactory->newLinkBatch();
		foreach ( $subgroups as $group ) {
			$lb->addObj( $group->getTitle() );
		}
		$lb->setCaller( __METHOD__ );
		$lb->execute();

		// Add missing invalid group ids back, not returned by getGroupsById
		foreach ( $subGroupIds as $id ) {
			if ( !isset( $subgroups[$id] ) ) {
				$subgroups[$id] = null;
			}
		}

		foreach ( $subgroups as $id => $group ) {
			$remove = '';
			if ( $this->hasPermission ) {
				$remove = Html::element(
					'span',
					[ 'class' => 'tp-aggregate-remove-button', 'data-groupid' => $id ]
				);
			}

			if ( $group ) {
				$text = $this->getLinkRenderer()->makeKnownLink( $group->getTitle() );
				$note = htmlspecialchars( MessageGroups::getPriority( $id ) );
			} else {
				$text = htmlspecialchars( $id );
				$note = $this->msg( 'tpt-aggregategroup-invalid-group' )->escaped();
			}

			$out .= Html::rawElement( 'li', [], "$text$remove $note" );
		}
		$out .= Html::closeElement( 'ol' );

		return $out;
	}

	private function htmlIdForGroup( string $groupId, string $prefix = '' ): string {
		$id = sha1( $groupId );
		$id = substr( $id, 5, 8 );

		return $prefix . $id;
	}

	private function getGroupToggleIcon( string $targetElementId, bool $shouldExpand ): string {
		if ( $shouldExpand ) {
			$title = $this->msg( 'tpt-aggregategroup-collapse-group' )->plain();
		} else {
			$title = $this->msg( 'tpt-aggregategroup-expand-group' )->plain();
		}

		return Html::rawElement(
			'button',
			[
				'type' => 'button',
				'title' => $title,
				'class' => 'js-tp-toggle-groups tp-toggle-group-icon',
				'aria-expanded' => $shouldExpand ? 'true' : 'false',
				'aria-controls' => $targetElementId
			]
		);
	}
}
