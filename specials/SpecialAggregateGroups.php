<?php
/**
 * Contains logic for special page Special:AggregateGroups.
 *
 * @file
 * @author Santhosh Thottingal
 * @copyright Copyright © 2012 Santhosh Thottingal
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class SpecialAggregateGroups extends SpecialPage {

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

		// Check permissions
		if ( !$this->user->isAllowed( 'translate-manage' ) ) {
			$wgOut->permissionRequired( 'translate-manage' );
			return;
		}

		// Check permissions
		if ( $wgRequest->wasPosted() && !$this->user->matchEditToken( $wgRequest->getText( 'token' ) ) ) {
			self::superDebug( __METHOD__, "token failure", $this->user );
			$wgOut->permissionRequired( 'translate-manage' );
			return;
		}
		$this->showAggregateGroups();

	}

	public function loadPagesFromDB() {
		$dbr = wfGetDB( DB_MASTER );
		$tables = array( 'page', 'revtag' );
		$vars = array( 'page_id', 'page_title', 'page_namespace', 'page_latest', 'MAX(rt_revision) as rt_revision', 'rt_type' );
		$conds = array(
			'page_id=rt_page',
			'rt_type' => array( RevTag::getType( 'tp:mark' ), RevTag::getType( 'tp:tag' ) ),
		);
		$options = array(
			'ORDER BY' => 'page_namespace, page_title',
			'GROUP BY' => 'page_id, rt_type',
		);
		$res = $dbr->select( $tables, $vars, $conds, __METHOD__, $options );

		return $res;
	}

	protected function buildPageArray( /*db result*/ $res ) {
		$pages = array();
		foreach ( $res as $r ) {
			// We have multiple rows for same page, because of different tags
			if ( !isset( $pages[$r->page_id] ) ) {
				$pages[$r->page_id] = array();
				$title = Title::newFromRow( $r );
				$pages[$r->page_id]['title'] = $title;
				$pages[$r->page_id]['latest'] = intval( $title->getLatestRevID() );
			}

			$tag = RevTag::typeToTag( $r->rt_type );
			$pages[$r->page_id][$tag] = intval( $r->rt_revision );
		}
		return $pages;
	}


	protected function showAggregateGroups() {
		global $wgOut;
		$wgOut->addModules( 'ext.translate.special.aggregategroups' );

		$aggregategroups = ApiAggregateGroups::getAggregateGroups( );
		$res = $this->loadPagesFromDB();
		$pages = $this->buildPageArray( $res );
		$pages = $this->filterUnGroupedPages( $pages,  $aggregategroups );
		foreach ( $aggregategroups as $id => $group ) {
			$wgOut->addHtml( "<div id='tpt-aggregate-group'>" );

			$removeSpan = Html::element( 'span', array(
				'class' => 'tp-aggregate-remove-ag-button',
				'id' => $id ) ) ;
			$wgOut->addHtml( "<h2 id='$id'>" . $group['name'] .  $removeSpan . "</h2>" );

			$wgOut->addHtml( "<p>" . $group['description'] . "</p>" );

			$wgOut->addHtml( "<ol id='tp-aggregate-groups-ol-$id'>" );
			$subgroups = $group['subgroups'];
			foreach ( $subgroups as $subgroupId => $subgroup ) {
				$removeSpan =   Html::element( 'span', array(
						'class' => 'tp-aggregate-remove-button',
						'id' => $subgroupId ) );
				if ( $subgroup ) {
					$wgOut->addHtml( "<li>" .
						Linker::linkKnown( $subgroup->getTitle(),
							null,
							array( 'id' => $subgroupId )
							)
						. "$removeSpan </li>" );
				}
			}
			$wgOut->addHtml( "</ol>" );

			$this->groupSelector ( $pages, $id );
			$addButton = Html::element( 'input',
				array( 'type' => 'button',
					'value' =>  wfMsg( 'tpt-aggregategroup-add' ),
					'id' => $id, 'class' => 'tp-aggregate-add-button' )
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

	protected function groupSelector(  $pages, $id ) {
		global $wgOut;
		$out = $wgOut;
		if ( !count( $pages ) ) {
			$wgOut->addWikiMsg( 'tpt-list-nopages' );
			return;
		}
		$options = "\n";
		if ( count( $pages ) ) {
			foreach ( $pages as $pageId => $page ) {
				$title =  $page['title']->getText();
				$pageid = TranslatablePage::getMessageGroupIdFromTitle( $page['title'] ) ;
				$options .= Xml::option(  $title , $pageid, false , array( 'id' => $pageid ) ) . "\n";
			}
		}
		$selector = Xml::tags( 'select',
				array(
					'id' => 'tp-aggregate-groups-select-' . $id,
					'name' => 'group',
					'class' => 'tp-aggregate-group-chooser',
					),
				$options
			);
		$out->addHtml( $selector );
	}

	protected function filterUnGroupedPages( $pages,  $aggregategroups ) {
		foreach ( $aggregategroups as  $aggregategroup ) {
			$subgroups = $aggregategroup['subgroups'];
			foreach ( $pages as  $id => $page ) {
					$pageid = TranslatablePage::getMessageGroupIdFromTitle( $page['title'] ) ;
					if ( isset( $subgroups[$pageid] ) ) {
						unset( $pages[$id] );
					}
			}
		}
		return $pages;
	}

}
