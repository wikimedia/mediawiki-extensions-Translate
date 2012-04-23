<?php
/**
 * Implements special page for group management, where file based message
 * groups are be managed.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2009-2012, Niklas Laxström, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Class for special page Special:ManageMessageGroups. On this special page
 * file based message groups can be managed (FileBasedMessageGroup). This page
 * allows updating of the file cache, import and fuzzy for source language
 * messages, as well as import/update of messages in other languages.
 *
 * @ingroup SpecialPage TranslateSpecialPage
 * Rewritten in 2012-04-23
 */
class SpecialManageGroups extends SpecialPage {
	const CHANGEFILE = 'translate_messagechanges.cdb';
	const RIGHT = 'translate-manage';

	public function __construct() {
		// Anyone is allowed to see, but actions are restricted
		parent::__construct( 'ManageMessageGroups' );
	}

	public function execute( $par ) {
		$this->setHeaders();
		$out = $this->getOutput();
		$out->addModules( 'ext.translate.special.managegroups' );
		TranslateUtils::addSpecialHelpLink( $out, 'Help:Extension:Translate/Group_management' );

		$changefile = TranslateUtils::cacheFile( self::CHANGEFILE );
		if ( !file_exists( $changefile ) ) {
			// TODO: Tell them when changes was last checked/process
			// or how to initiate recheck.
			$out->addWikiMsg( 'translate-smg-nochanges' );
			return;
		}

		$user = $this->getUser();
		$allowed = $user->isAllowed( self::RIGHT );

		$req = $this->getRequest();
		if ( !$req->wasPosted() ) {
			$this->showChanges( $allowed );
			return;
		}

		$token = $req->getVal( 'token' );
		if ( !$allowed || !$user->matchEditToken( $token ) ) {
			throw new PermissionsError( self::RIGHT );
		}

		$this->processSubmit();
	}

	protected function showChanges( $allowed ) {
		$out = $this->getOutput();
		$user = $this->getUser();

		$out->addHtml( Html::openElement( 'form', array( 'method' => 'post' ) ) );
		$out->addHtml( Html::hidden( 'title', $this->getTitle()->getPrefixedText() ) );
		$out->addHtml( Html::hidden( 'token', $user->getEditToken() ) );

		$diff = new DifferenceEngine;
		$diff->showDiffStyle();

		$changefile = TranslateUtils::cacheFile( self::CHANGEFILE );
		$reader = CdbReader::open( $changefile );
		$groups = unserialize( $reader->get( '#keys' ) );
		foreach ( $groups as $id ) {
			$group = MessageGroups::getGroup( $id );
			$changes = unserialize( $reader->get( $id ) );
			$out->addHtml( Html::element( 'h2', array(), $group->getLabel() ) );

			foreach ( $changes as $code => $subchanges ) {
				foreach ( $subchanges as $type => $messages ) {
					foreach ( $messages as $params ) {
						$change = $this->formatChange( $group, $code, $type, $params );
						$out->addHtml( $change );
					}
				}
			}
		}

		$attribs = array( 'type' => 'submit', 'class' => 'mw-translate-smg-submit' );
		if ( !$allowed ) {
			$attribs['disabled'] = 'disabled';
			$attribs['title'] = wfMessage( 'translate-smg-notallowed' )->text();
		}
		$button = Html::element( 'button', $attribs, wfMessage( 'translate-smg-submit' )->text() );
		$out->addHtml( $button );
		$out->addHtml( Html::closeElement( 'form' ) );
	}

	protected function formatChange( $group, $code, $type, $params ) {
		$key = $params['key'];
		$id = sha1( "{$group->getId()}/$code/$type/$key" );
		$id = Sanitizer::escapeId( "smg/$id" );

		$filesystem = $wiki = $fuzzy = '';

		if ( isset( $params['content'] ) ) {
			$filesystem = $params['content'];
		}



		$title = Title::makeTitleSafe( $group->getNamespace(), "$key/$code" );
		if ( $title->exists() ) {
			$wiki = Revision::newFromTitle( $title )->getText();
			$handle = new MessageHandle( $title );
			if ( $handle->isFuzzy() ) {
				$wiki = '!!FUZZY!!' . $wiki;
			}

			if ( $group->getSourceLanguage() === $code ) {
				$fuzzy = Xml::checkLabel( wfMsg( 'translate-manage-action-fuzzy' ), "f/$id", "f/$id" );
			}
		}

		$diff = new DifferenceEngine();
		$diff->setReducedLineNumbers();
		$diff->setText( $filesystem, $wiki );
		$text = $diff->getDiff( $fuzzy, Linker::link( $title ) );
		$change = Html::rawElement( 'div', array( 'class' => 'mw-translate-smg-change' ), $text );
		$hidden = Html::hidden( $id, 1 );
		return $hidden . $change;
	}

	protected function processSubmit() {
		$req = $this->getRequest();
		$out = $this->getOutput();

		$jobs = array();
		$jobs[] = MessageIndexRebuildJob::newJob();

		$changefile = TranslateUtils::cacheFile( self::CHANGEFILE );
		$reader = CdbReader::open( $changefile );
		$groups = unserialize( $reader->get( '#keys' ) );

		$toRebuild = array();

		foreach ( $groups as $id ) {
			$group = MessageGroups::getGroup( $id );
			$changes = unserialize( $reader->get( $id ) );
			foreach ( $changes as $code => $subchanges ) {
				foreach ( $subchanges as $type => $messages ) {
					foreach ( $messages as $params ) {
						$key = $params['key'];
						$id = sha1( "{$group->getId()}/$code/$type/$key" );
						$id = Sanitizer::escapeId( "smg/$id" );
						if ( $req->getVal( $id ) === null ) {
							throw new MWException( "Request is inconsistent. Not found '$id'." );
						}
						// Do nothing if message was deleted
						if ( !isset( $params['content'] ) ) {
							continue;
						}

						$fuzzy = false;
						if ( $group->getSourceLanguage() === $code ) {
							$fuzzy = $req->getCheck( "f/$id" ) ? 'fuzzy' : false;
						}

						$toRebuild[$group->getId()][$code] = true;

						$title = Title::makeTitleSafe( $group->getNamespace(), "$key/$code" );
						$jobs[] = MessageUpdateJob::newJob( $title, $params['content'], $fuzzy );
					}
				}
			}
		}

		foreach ( $toRebuild as $groupId => $languages ) {
			foreach ( array_keys( $languages ) as $language ) {
				$cache = new MessageGroupCache( $groupId, $language );
				$cache->create();
			}
		}

		Job::batchInsert( $jobs );

		$reader->close();
		rename( $changefile, $changefile . '-' . wfTimestamp() );
		$out->addWikiMsg( 'translate-smg-submitted' );

	}
}
