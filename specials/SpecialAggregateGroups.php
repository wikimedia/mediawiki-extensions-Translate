<?php
/**
 * Contains logic for special page Special:AggregateGroups.
 *
 * @file
 * @author Santhosh Thottingal
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2012 Santhosh Thottingal, Niklas Laxström, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class SpecialAggregateGroups extends SpecialPage {
	function __construct() {
		parent::__construct( 'AggregateGroups', 'translate-manage' );
	}

	public function execute( $parameters ) {
		$this->setHeaders();

		$out = $this->getOutput();

		// Check permissions
		// @todo Allow read only for other users
		if ( !$this->getUser()->isAllowed( 'translate-manage' ) ) {
			throw new PermissionsError( 'translate-manage' );
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
			$id = $group->getId();
			$div = Html::openElement( 'div', array(
				'class' => 'mw-tpa-group',
				'data-groupid' => $id,
				'data-id' => $this->htmlIdForGroup( $group ),
			) );

			$out->addHtml( $div );

			$remove = Html::element( 'span', array( 'class' => 'tp-aggregate-remove-ag-button' ) );

			$header = Html::rawElement( 'h2', array(), htmlspecialchars( $group->getLabel() ) . $remove );
			$out->addHtml( $header );
			$out->addWikiText( $group->getDescription( $this->getContext() ) );
			$this->listSubgroups( $group );
			$select = $this->getGroupSelector( $pages, $group );
			$out->addHtml( $select->getHtml() );
			$addButton = Html::element( 'input',
				array( 'type' => 'button',
					'value' => $this->msg( 'tpt-aggregategroup-add' )->text(),
					'class' => 'tp-aggregate-add-button' )
			);
			$out->addHtml( $addButton );
			$out->addHtml( "</div>" );
		}

		$out->addHtml( Html::element( 'input', array(
			'type' => 'hidden',
			'id' => 'token',
			'value' => ApiAggregateGroups::getToken()
		) ) );
		$out->addHtml( "<br/><a class='tpt-add-new-group' href='#'>" .
			$this->msg( 'tpt-aggregategroup-add-new' )->escaped() .
			"</a>" );
		$newGroupNameLabel = $this->msg( 'tpt-aggregategroup-new-name' )->escaped();
		$newGroupName = Html::element( 'input', array( 'class' => 'tp-aggregategroup-add-name', 'maxlength' => '200' ) );
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
		$newGroupDiv = Html::rawElement( 'div',
			array( 'class' => 'tpt-add-new-group hidden' ),
			"$newGroupNameLabel $newGroupName <br /> $newGroupDescriptionLabel $newGroupDescription <br /> $saveButton" );
		$out->addHtml( $newGroupDiv );
	}

	/**
	 * @param AggregateMessageGroup $parent
	 */
	protected function listSubgroups( AggregateMessageGroup $parent ) {
		$out = $this->getOutput();

		$id = $this->htmlIdForGroup( $parent, 'mw-tpa-grouplist-' );
		$out->addHtml( Html::openElement( 'ol', array( 'id' => $id ) ) );

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
			$remove = Html::element( 'span',
				array(
					'class' => 'tp-aggregate-remove-button',
					'data-groupid' => $id,
				)
			);

			if ( $group ) {
				$text = Linker::linkKnown( $group->getTitle() );
				$note = MessageGroups::getPriority( $id );
			} else {
				$text = htmlspecialchars( $id );
				$note = $this->msg( 'tpt-aggregategroup-invalid-group' )->escaped();
			}

			$out->addHtml( Html::rawElement( 'li', array(), "$text$remove $note" ) );
		}
		$out->addHtml( Html::closeElement( 'ol' ) );
	}

	/**
	 * @param $availableGroups
	 * @param MessageGroup $parent
	 * @return XmlSelect
	 */
	protected function getGroupSelector( $availableGroups, $parent ) {
		$id = $this->htmlIdForGroup( $parent, 'mw-tpa-groupselect-' );
		$select = new XmlSelect( 'group', $id );
		$select->setAttribute( 'class', 'mw-tpa-groupselect' );

		// Not calling $parent->getGroups() because it has done filtering already
		$subgroups = TranslateMetadata::getSubgroups( $parent->getId() );
		$subgroups = array_flip( $subgroups );
		/**
		 * @var $group MessageGroup
		 */
		foreach ( $availableGroups as $group ) {
			$groupId = $group->getId();

			// Do not include already included groups in the list
			if ( isset( $subgroups[$groupId] ) ) {
				continue;
			}

			$select->addOption( $group->getLabel(), $groupId );
		}

		return $select;
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
