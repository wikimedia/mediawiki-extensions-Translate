<?php
/**
 * Contains logic for special page Special:AggregateGroups.
 *
 * @file
 * @author Santhosh Thottingal
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @author Kunal Grover
 * @license GPL-2.0-or-later
 */

class SpecialAggregateGroups extends SpecialPage {
	protected $hasPermission = false;

	public function __construct() {
		parent::__construct( 'AggregateGroups', 'translate-manage' );
	}

	protected function getGroupName() {
		return 'wiki';
	}

	public function execute( $parameters ) {
		$this->setHeaders();

		$out = $this->getOutput();
		$out->addModuleStyles( 'ext.translate.special.aggregategroups.styles' );

		// Check permissions
		if ( $this->getUser()->isAllowed( 'translate-manage' ) ) {
			$this->hasPermission = true;
		}

		$groupsPreload = array_merge(
			MessageGroups::getGroupsByType( WikiPageMessageGroup::class ),
			MessageGroups::getGroupsByType( AggregateMessageGroup::class )
		);
		TranslateMetadata::preloadGroups( array_keys( $groupsPreload ) );

		$groups = MessageGroups::getAllGroups();
		uasort( $groups, [ 'MessageGroups', 'groupLabelSort' ] );
		$aggregates = [];
		$pages = [];
		foreach ( $groups as $group ) {
			if ( $group instanceof WikiPageMessageGroup ) {
				$pages[] = $group;
			} elseif ( $group instanceof AggregateMessageGroup ) {
				$subgroups = TranslateMetadata::getSubgroups( $group->getId() );
				if ( $subgroups !== false ) {
					$aggregates[] = $group;
				}
			}
		}

		if ( !count( $pages ) ) {
			// @todo Use different message
			$out->addWikiMsg( 'tpt-list-nopages' );

			return;
		}

		$this->showAggregateGroups( $aggregates );
	}

	/**
	 * @param AggregateMessageGroup $group
	 * @return string
	 */
	protected function showAggregateGroup( $group ) {
		$out = '';
		$id = $group->getId();
		$label = $group->getLabel();
		$desc = $group->getDescription( $this->getContext() );

		$div = Html::openElement( 'div', [
			'class' => 'mw-tpa-group',
			'data-groupid' => $id,
			'data-id' => $this->htmlIdForGroup( $group ),
		] );

		$out .= $div;

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
				[
					'class' => 'tp-edit-group hidden'
				],
				$editGroupNameLabel .
				$editGroupName . '<br />' .
				$editGroupDescriptionLabel .
				$editGroupDescription .
				$saveButton .
				$cancelButton
			);

			// Subgroups selector
			$select = Html::input(
				'tp-subgroups-input',
				'',
				'text',
				[ 'class' => 'tp-group-input' ]
			);
			$addButton = Html::element( 'input',
				[ 'type' => 'button',
					'value' => $this->msg( 'tpt-aggregategroup-add' )->text(),
					'class' => 'tp-aggregate-add-button' ]
			);
		}

		// Aggregate Group info div
		$groupName = Html::rawElement( 'h2',
			[ 'class' => 'tp-name' ],
			htmlspecialchars( $label ) . $edit . $remove
		);
		$groupDesc = Html::element( 'p',
			[ 'class' => 'tp-desc' ],
			$desc
		);
		$groupInfo = Html::rawElement( 'div',
			[ 'class' => 'tp-display-group' ],
			$groupName .
			$groupDesc
		);

		$out .= $groupInfo;
		$out .= $editGroup;
		$out .= $this->listSubgroups( $group );
		$out .= $select . $addButton;
		$out .= '</div>';

		return $out;
	}

	/**
	 * @param array $aggregates
	 */
	protected function showAggregateGroups( array $aggregates ) {
		$out = $this->getOutput();
		$out->addModules( 'ext.translate.special.aggregategroups' );

		$nojs = Html::element(
			'div',
			[ 'class' => 'tux-nojs errorbox' ],
			$this->msg( 'tux-nojs' )->plain()
		);

		$out->addHTML( $nojs );

		/**
		 * @var AggregateMessageGroup $group
		 */
		foreach ( $aggregates as $group ) {
			$out->addHTML( $this->showAggregateGroup( $group ) );
		}

		// Add new group if user has permissions
		if ( $this->hasPermission ) {
			$out->addHTML( "<br/><a class='tpt-add-new-group' href='#'>" .
				$this->msg( 'tpt-aggregategroup-add-new' )->escaped() .
				'</a>' );
			$newGroupNameLabel = $this->msg( 'tpt-aggregategroup-new-name' )->escaped();
			$newGroupName = Html::element(
				'input',
				[ 'class' => 'tp-aggregategroup-add-name', 'maxlength' => '200' ]
			);
			$newGroupDescriptionLabel = $this->msg( 'tpt-aggregategroup-new-description' )->escaped();
			$newGroupDescription = Html::element( 'input',
				[ 'class' => 'tp-aggregategroup-add-description' ]
			);
			$saveButton = Html::element( 'input', [
				'type' => 'button',
				'value' => $this->msg( 'tpt-aggregategroup-save' )->text(),
				'id' => 'tpt-aggregategroups-save',
				'class' => 'tp-aggregate-save-button'
			] );
			$newGroupDiv = Html::rawElement(
				'div',
				[ 'class' => 'tpt-add-new-group hidden' ],
				"$newGroupNameLabel $newGroupName<br />" .
				"$newGroupDescriptionLabel $newGroupDescription<br />$saveButton"
			);
			$out->addHTML( $newGroupDiv );
		}
	}

	/**
	 * @param AggregateMessageGroup $parent
	 * @return string
	 */
	protected function listSubgroups( AggregateMessageGroup $parent ) {
		$id = $this->htmlIdForGroup( $parent, 'mw-tpa-grouplist-' );
		$out = Html::openElement( 'ol', [ 'id' => $id ] );

		// Not calling $parent->getGroups() because it has done filtering already
		$subgroupIds = TranslateMetadata::getSubgroups( $parent->getId() );

		// Get the respective groups and sort them
		$subgroups = MessageGroups::getGroupsById( $subgroupIds );
		uasort( $subgroups, [ 'MessageGroups', 'groupLabelSort' ] );

		// Avoid potentially thousands of separate database queries
		$lb = new LinkBatch();
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
				$remove = Html::element( 'span',
					[
						'class' => 'tp-aggregate-remove-button',
						'data-groupid' => $id,
					]
				);
			}

			if ( $group ) {
				$text = $this->getLinkRenderer()->makeKnownLink( $group->getTitle() );
				$note = MessageGroups::getPriority( $id );
			} else {
				$text = htmlspecialchars( $id );
				$note = $this->msg( 'tpt-aggregategroup-invalid-group' )->escaped();
			}

			$out .= Html::rawElement( 'li', [], "$text$remove $note" );
		}
		$out .= Html::closeElement( 'ol' );

		return $out;
	}

	/**
	 * @param MessageGroup $group
	 * @param string $prefix
	 * @return string
	 */
	protected function htmlIdForGroup( MessageGroup $group, $prefix = '' ) {
		$id = sha1( $group->getId() );
		$id = substr( $id, 5, 8 );

		return $prefix . $id;
	}
}
