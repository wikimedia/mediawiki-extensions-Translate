<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use AggregateMessageGroup;
use Html;
use MediaWiki\Cache\LinkBatchFactory;
use MessageGroup;
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
		$out = '';
		$id = $group->getId();
		$label = $group->getLabel();
		$desc = $group->getDescription( $this->getContext() );

		$out .= Html::openElement(
			'div',
			[ 'class' => 'mw-tpa-group', 'data-groupid' => $id, 'data-id' => $this->htmlIdForGroup( $group ) ]
		);

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

		// Aggregate Group info div
		$groupName = Html::rawElement(
			'h2',
			[ 'class' => 'tp-name' ],
			htmlspecialchars( $label ) . $edit . $remove
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

		$out .= $groupInfo;
		$out .= $editGroup;
		$out .= $this->listSubgroups( $group );
		$out .= $select . $addButton;
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

		/** @var AggregateMessageGroup $group */
		foreach ( $aggregates as $group ) {
			$out->addHTML( $this->showAggregateGroup( $group ) );
		}

		// Add new group if user has permissions
		if ( $this->hasPermission ) {
			$out->addHTML(
				"<br/><a class='tpt-add-new-group' href='#'>" .
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
			$newGroupDiv = Html::rawElement(
				'div',
				[ 'class' => 'tpt-add-new-group hidden' ],
				"$newGroupNameLabel $newGroupName<br />" .
					"$newGroupDescriptionLabel $newGroupDescription<br />$saveButton"
			);
			$out->addHTML( $newGroupDiv );
		}
	}

	private function listSubgroups( AggregateMessageGroup $parent ): string {
		$id = $this->htmlIdForGroup( $parent, 'mw-tpa-grouplist-' );
		$out = Html::openElement( 'ol', [ 'id' => $id ] );

		// Not calling $parent->getGroups() because it has done filtering already
		$subgroupIds = TranslateMetadata::getSubgroups( $parent->getId() ) ?? [];

		// Get the respective groups and sort them
		$subgroups = MessageGroups::getGroupsById( $subgroupIds );
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
		foreach ( $subgroupIds as $id ) {
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

	private function htmlIdForGroup( MessageGroup $group, string $prefix = '' ): string {
		$id = sha1( $group->getId() );
		$id = substr( $id, 5, 8 );

		return $prefix . $id;
	}
}
