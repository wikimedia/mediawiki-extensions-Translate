<?php
/**
 * Contains logic for special page Special:AggregateGroups.
 *
 * @file
 * @author Santhosh Thottingal
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @author Kunal Grover
 * @license GPL-2.0+
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

		// Check permissions
		if ( $this->getUser()->isAllowed( 'translate-manage' ) ) {
			$this->hasPermission = true;
		}

		$groups = MessageGroups::getAllGroups();
		uasort( $groups, array( 'MessageGroups', 'groupLabelSort' ) );
		$aggregates = array();
		$pages = array();
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

		$this->showAggregateGroups( $aggregates, $pages );
	}

	/**
	 * @param AggregateMessageGroup $group
	 * @param array $pages
	 * @return string
	 */
	protected function showAggregateGroup( $group, array $pages ) {
		$out = '';
		$id = $group->getId();
		$label = $group->getLabel();
		$desc = $group->getDescription( $this->getContext() );

		$div = Html::openElement( 'div', array(
			'class' => 'mw-tpa-group',
			'data-groupid' => $id,
			'data-id' => $this->htmlIdForGroup( $group ),
		) );

		$out .= $div;

		$edit = '';
		$remove = '';
		$editGroup = '';
		$select = '';
		$addButton = '';

		// Add divs for editing Aggregate Groups
		if ( $this->hasPermission ) {
			// Group edit and remove buttons
			$edit = Html::element( 'span', array( 'class' => 'tp-aggregate-edit-ag-button' ) );
			$remove = Html::element( 'span', array( 'class' => 'tp-aggregate-remove-ag-button' ) );

			// Edit group div
			$editGroupNameLabel = $this->msg( 'tpt-aggregategroup-edit-name' )->escaped();
			$editGroupName = Html::input(
				'tp-agg-name',
				$label,
				'text',
				array( 'class' => 'tp-aggregategroup-edit-name', 'maxlength' => '200' )
			);
			$editGroupDescriptionLabel = $this->msg( 'tpt-aggregategroup-edit-description' )->escaped();
			$editGroupDescription = Html::input(
				'tp-agg-desc',
				$desc,
				'text',
				array( 'class' => 'tp-aggregategroup-edit-description' )
			);
			$saveButton = Xml::submitButton(
				$this->msg( 'tpt-aggregategroup-update' )->text(),
				array( 'class' => 'tp-aggregategroup-update' )
			);
			$cancelButton = Xml::submitButton(
				$this->msg( 'tpt-aggregategroup-update-cancel' )->text(),
				array( 'class' => 'tp-aggregategroup-update-cancel' )
			);
			$editGroup = Html::rawElement(
				'div',
				array(
					'class' => 'tp-edit-group hidden'
				),
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
				array( 'class' => 'tp-group-input' )
			);
			$addButton = Html::element( 'input',
				array( 'type' => 'button',
					'value' => $this->msg( 'tpt-aggregategroup-add' )->text(),
					'class' => 'tp-aggregate-add-button' )
			);
		}

		// Aggregate Group info div
		$groupName = Html::rawElement( 'h2',
			array( 'class' => 'tp-name' ),
			htmlspecialchars( $label ) . $edit . $remove
		);
		$groupDesc = Html::element( 'p',
			array( 'class' => 'tp-desc' ),
			$desc
		);
		$groupInfo = Html::rawElement( 'div',
			array( 'class' => 'tp-display-group' ),
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
	 * @param array $pages
	 */
	protected function showAggregateGroups( array $aggregates, array $pages ) {
		$out = $this->getOutput();
		$out->addModules( 'ext.translate.special.aggregategroups' );

		/**
		 * @var $group AggregateMessageGroup
		 */
		foreach ( $aggregates as $group ) {
			$out->addHTML( $this->showAggregateGroup( $group, $pages ) );
		}

		// Add new group if user has permissions
		if ( $this->hasPermission ) {
			$out->addHTML( "<br/><a class='tpt-add-new-group' href='#'>" .
				$this->msg( 'tpt-aggregategroup-add-new' )->escaped() .
				'</a>' );
			$newGroupNameLabel = $this->msg( 'tpt-aggregategroup-new-name' )->escaped();
			$newGroupName = Html::element(
				'input',
				array( 'class' => 'tp-aggregategroup-add-name', 'maxlength' => '200' )
			);
			$newGroupDescriptionLabel = $this->msg( 'tpt-aggregategroup-new-description' )->escaped();
			$newGroupDescription = Html::element( 'input',
				array( 'class' => 'tp-aggregategroup-add-description' )
			);
			$saveButton = Html::element( 'input', array(
				'type' => 'button',
				'value' => $this->msg( 'tpt-aggregategroup-save' )->text(),
				'id' => 'tpt-aggregategroups-save',
				'class' => 'tp-aggregate-save-button'
			) );
			$newGroupDiv = Html::rawElement(
				'div',
				array( 'class' => 'tpt-add-new-group hidden' ),
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
		$out = Html::openElement( 'ol', array( 'id' => $id ) );

		// Not calling $parent->getGroups() because it has done filtering already
		$subgroupIds = TranslateMetadata::getSubgroups( $parent->getId() );

		// Get the respective groups and sort them
		$subgroups = MessageGroups::getGroupsById( $subgroupIds );
		uasort( $subgroups, array( 'MessageGroups', 'groupLabelSort' ) );

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
					array(
						'class' => 'tp-aggregate-remove-button',
						'data-groupid' => $id,
					)
				);
			}

			if ( $group ) {
				$text = Linker::linkKnown( $group->getTitle() );
				$note = MessageGroups::getPriority( $id );
			} else {
				$text = htmlspecialchars( $id );
				$note = $this->msg( 'tpt-aggregategroup-invalid-group' )->escaped();
			}

			$out .= Html::rawElement( 'li', array(), "$text$remove $note" );
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
