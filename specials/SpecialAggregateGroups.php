<?php
/**
 * Contains logic for special page Special:AggregateGroups.
 *
 * @file
 * @author Santhosh Thottingal
 * @author Niklas Laxström
 * @copyright Copyright © 2012 Santhosh Thottingal
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class SpecialAggregateGroups extends UnlistedSpecialPage {

	/**
	 * @var User
	 */
	protected $user;

	function __construct() {
		parent::__construct( 'AggregateGroups' );
	}

	public function execute( $parameters ) {
		$this->setHeaders();

		global $wgRequest, $wgOut, $wgUser;
		$this->user = $wgUser;
		$request = $wgRequest;
		$out = $this->getOutput();

		// Check permissions
		if ( !$this->user->isAllowed( 'translate-manage' ) ) {
			$out->permissionRequired( 'translate-manage' );
			return;
		}

		// Check permissions
		if ( $wgRequest->wasPosted() && !$this->user->matchEditToken( $wgRequest->getText( 'token' ) ) ) {
			self::superDebug( __METHOD__, "token failure", $this->user );
			$out->permissionRequired( 'translate-manage' );
			return;
		}
		
		$groups = MessageGroups::getAllGroups();
		$aggregates = array();
		$pages = array();
		foreach ( $groups as $group ) {
			if ( $group instanceof WikiPageMessageGroup ) {
				$pages[] = $group;
			} elseif ( $group instanceof AggregateMessageGroup ) {
				if ( TranslateMetadata::get( $group->getId(), 'subgroups' ) !== false ) {
					$aggregates[] = $group;
				}
			}
		}

		if ( !count( $pages ) ) {
			// @TODO use different message
			$out->addWikiMsg( 'tpt-list-nopages' );
			return;
		}

		$this->showAggregateGroups( $aggregates, $pages );

	}

	protected function showAggregateGroups( array $aggregates, array $pages ) {
		global $wgOut;
		$wgOut->addModules( 'ext.translate.special.aggregategroups' );

		foreach ( $aggregates as $group ) {
			$id = $group->getId();
			$div = Html::openElement( 'div', array(
				'class' => 'mw-tpa-group',
				'data-groupid' => $id,
				'data-id' => $this->htmlIdForGroup( $group ),
			) );

			$wgOut->addHtml( $div );

			$remove = Html::element( 'span', array( 'class' => 'tp-aggregate-remove-ag-button' ) );

			$hid = $this->htmlIdForGroup( $group );
			$header = Html::rawElement( 'h2', null, 	htmlspecialchars( $group->getLabel() ) . $remove );
			$wgOut->addHtml( $header );
			$wgOut->addWikiText( $group->getDescription() );
			$this->listSubgroups( $group );
			$select = $this->getGroupSelector( $pages, $group );
			$wgOut->addHtml( $select->getHtml() );
			$addButton = Html::element( 'input',
				array( 'type' => 'button',
					'value' =>  wfMsg( 'tpt-aggregategroup-add' ),
					'class' => 'tp-aggregate-add-button' )
				);
			$wgOut->addHtml( $addButton );
			$wgOut->addHtml( "</div>" );
		}


		$wgOut->addHtml( Html::element( 'input',
			array( 'type' => 'hidden',
				'id' => 'token',
				'value' => ApiAggregateGroups::getToken( 0, '' )
				) ) );
		$wgOut->addHtml( "<br/><a class='tpt-add-new-group' href='#'>" .
			wfMsg( 'tpt-aggregategroup-add-new' ) .
			 "</a>" );
		$newGroupNameLabel = wfMsg( 'tpt-aggregategroup-new-name' );
		$newGroupName = Html::element( 'input', array( 'class' => 'tp-aggregategroup-add-name' ) );
		$newGroupDescriptionLabel = wfMsg( 'tpt-aggregategroup-new-description' );
		$newGroupDescription = Html::element( 'input',
				array( 'class' => 'tp-aggregategroup-add-description' )
			 );
		$saveButton = Html::element( 'input',
			array( 'type' => 'button',
				'value' =>  wfMsg( 'tpt-aggregategroup-save' ),
				'id' => 'tpt-aggregategroups-save', 'class' => 'tp-aggregate-save-button' )
			);
		$newGroupDiv = Html::rawElement( 'div',
			array( 'class' => 'tpt-add-new-group hidden' ) ,
			"$newGroupNameLabel $newGroupName <br/> $newGroupDescriptionLabel $newGroupDescription <br/> $saveButton" );
		$wgOut->addHtml( $newGroupDiv );
	}

	protected function listSubgroups( AggregateMessageGroup $parent ) {
		$out = $this->getOutput();
		$sanid = Sanitizer::escapeId( $parent->getId() );
		
		$id = $this->htmlIdForGroup( $parent, 'mw-tpa-grouplist-' );
		$out->addHtml( Html::openElement( 'ol', array( 'id' => $id ) ) );

		foreach ( $parent->getGroups() as $id => $group ) {
			$remove = Html::element( 'span',
				array(
					'class' => 'tp-aggregate-remove-button',
					'data-groupid' => $group->getId(),
				)
			);

			$link = Linker::linkKnown( $group->getTitle(), null, array( 'id' => $id ) );
			$out->addHtml( Html::rawElement( 'li', null, "$link$remove" ) );
		}
		$out->addHtml( Html::closeElement( 'ol' ) );
	}

	protected function getGroupSelector( $availableGroups, $parent ) {
		$id = $this->htmlIdForGroup( $parent, 'mw-tpa-groupselect-' );
		$select = new XmlSelect( 'group', $id );

		$subgroups = $parent->getGroups();
		foreach ( $availableGroups as $group ) {
			$groupId = $group->getId();
			// Do not include already included groups in the list
			if ( isset( $subgroups[$groupId] ) ) continue;
			$select->addOption( $group->getLabel(), $groupId );
		}
	
		return $select;
	}

	protected function htmlIdForGroup( MessageGroup $group, $prefix = '' ) {
		$id = sha1( $group->getId() );
		$id = substr( $id, 5, 8 );
		return $prefix . $id;
	}

}
