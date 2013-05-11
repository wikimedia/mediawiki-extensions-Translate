<?php
/**
 * Implements special page for group management, where file based message
 * groups are be managed.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2009-2013, Niklas Laxström, Siebrand Mazeland
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

	/**
	 * @var DifferenceEngine
	 */
	protected $diff;

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
			// @todo Tell them when changes was last checked/process
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

	protected function getLegend() {
		$this->diff->setText( '', '' );
		$text = $this->diff->getDiff(
			$this->msg( 'translate-smg-left' )->text(),
			$this->msg( 'translate-smg-right' )->text()
		);
		return Html::rawElement( 'div', array( 'class' => "mw-translate-smg-header" ), $text );
	}

	protected function showChanges( $allowed ) {
		global $wgContLang;

		$diff = new DifferenceEngine( $this->getContext() );
		$diff->showDiffStyle();
		$diff->setReducedLineNumbers();
		$this->diff = $diff;

		$out = $this->getOutput();
		$out->addHtml(
			'' .
				Html::openElement( 'form', array( 'method' => 'post' ) ) .
				Html::hidden( 'title', $this->getTitle()->getPrefixedText() ) .
				Html::hidden( 'token', $this->getUser()->getEditToken() ) .
				$this->getLegend()
		);

		$counter = 0;
		$changefile = TranslateUtils::cacheFile( self::CHANGEFILE );
		$reader = CdbReader::open( $changefile );
		$groups = unserialize( $reader->get( '#keys' ) );
		foreach ( $groups as $id ) {
			$group = MessageGroups::getGroup( $id );
			if ( !$group ) {
				continue;
			}

			$changes = unserialize( $reader->get( $id ) );
			$out->addHtml( Html::element( 'h2', array(), $group->getLabel() ) );

			// Reduce page existance queries to one per group
			$lb = new LinkBatch();
			$ns = $group->getNamespace();
			$isCap = MWNamespace::isCapitalized( $ns );
			foreach ( $changes as $code => $subchanges ) {
				foreach ( $subchanges as $messages ) {
					foreach ( $messages as $params ) {
						// Constructing title objects is way slower
						$key = $params['key'];
						if ( $isCap ) {
							$key = $wgContLang->ucfirst( $key );
						}
						$lb->add( $ns, "$key/$code" );
					}
				}
			}
			$lb->execute();

			foreach ( $changes as $code => $subchanges ) {
				foreach ( $subchanges as $type => $messages ) {
					foreach ( $messages as $params ) {
						$counter++;
						$change = $this->formatChange( $group, $code, $type, $params );
						$out->addHtml( $change );
					}
				}
				if ( $counter > 500 ) {
					// Avoid creating too heavy pages
					break 2;
				}
			}
		}

		$attribs = array( 'type' => 'submit', 'class' => 'mw-translate-smg-submit' );
		if ( !$allowed ) {
			$attribs['disabled'] = 'disabled';
			$attribs['title'] = $this->msg( 'translate-smg-notallowed' )->text();
		}
		$button = Html::element( 'button', $attribs, $this->msg( 'translate-smg-submit' )->text() );
		$out->addHtml( $button );
		$out->addHtml( Html::closeElement( 'form' ) );
	}

	/**
	 * @param MessageGroup $group
	 * @param string $code
	 * @param string $type
	 * @param array $params
	 * @return string HTML
	 */
	protected function formatChange( MessageGroup $group, $code, $type, $params ) {
		$key = $params['key'];
		$title = Title::makeTitleSafe( $group->getNamespace(), "$key/$code" );
		$id = self::changeId( $group->getId(), $code, $type, $key );

		if ( $title->exists() && $type === 'addition' ) {
			// The message has for some reason dropped out from cache
			// or perhaps it is being reused. In any case treat it
			// as a change for display, so the admin can see if
			// action is needed and let the message be processed.
			// Otherwise it will end up in the postponed category
			// forever and will prevent rebuilding the cache, which
			// leads to many other annoying problems.
			$type = 'change';
		} elseif ( !$title->exists() && ( $type === 'deletion' || $type === 'change' ) ) {
			return '';
		}

		$text = '';
		if ( $type === 'deletion' ) {
			$wiki = Revision::newFromTitle( $title )->getText();
			$this->diff->setText( $wiki, '' );
			$text = $this->diff->getDiff( Linker::link( $title ), '' );
		} elseif ( $type === 'addition' ) {
			$this->diff->setText( '', $params['content'] );
			$text = $this->diff->getDiff( '', Linker::link( $title ) );
		} elseif ( $type === 'change' ) {
			$wiki = Revision::newFromTitle( $title )->getText();
			$handle = new MessageHandle( $title );
			if ( $handle->isFuzzy() ) {
				$wiki = '!!FUZZY!!' . str_replace( TRANSLATE_FUZZY, '', $wiki );
			}

			$label = $this->msg( 'translate-manage-action-ignore' )->text();
			$actions = Xml::checkLabel( $label, "i/$id", "i/$id" );

			if ( $group->getSourceLanguage() === $code ) {
				$label = $this->msg( 'translate-manage-action-fuzzy' )->text();
				$actions .= ' ' . Xml::checkLabel( $label, "f/$id", "f/$id" );
			}

			$this->diff->setText( $wiki, $params['content'] );
			$text .= $this->diff->getDiff( Linker::link( $title ), $actions );
		}

		$hidden = Html::hidden( $id, 1 );
		$text .= $hidden;
		$classes = "mw-translate-smg-change smg-change-$type";
		return Html::rawElement( 'div', array( 'class' => $classes ), $text );
	}

	protected function processSubmit() {
		$req = $this->getRequest();
		$out = $this->getOutput();

		$jobs = array();
		$jobs[] = MessageIndexRebuildJob::newJob();

		$changefile = TranslateUtils::cacheFile( self::CHANGEFILE );
		$reader = CdbReader::open( $changefile );
		$groups = unserialize( $reader->get( '#keys' ) );

		$postponed = array();

		foreach ( $groups as $groupId ) {
			$group = MessageGroups::getGroup( $groupId );
			$changes = unserialize( $reader->get( $groupId ) );

			foreach ( $changes as $code => $subchanges ) {
				foreach ( $subchanges as $type => $messages ) {
					foreach ( $messages as $index => $params ) {
						$id = self::changeId( $groupId, $code, $type, $params['key'] );
						if ( $req->getVal( $id ) === null ) {
							// We probably hit the limit with number of post parameters.
							$postponed[$groupId][$code][$type][$index] = $params;
							break 1;
						}

						if ( $type === 'deletion' || $req->getCheck( "i/$id" ) ) {
							continue;
						}

						$fuzzy = $req->getCheck( "f/$id" ) ? 'fuzzy' : false;
						$key = $params['key'];
						$title = Title::makeTitleSafe( $group->getNamespace(), "$key/$code" );
						$jobs[] = MessageUpdateJob::newJob( $title, $params['content'], $fuzzy );
					}
				}

				if ( !isset( $postponed[$groupId][$code] ) ) {
					$cache = new MessageGroupCache( $groupId, $code );
					$cache->create();
				}
			}
		}

		Job::batchInsert( $jobs );

		$reader->close();
		rename( $changefile, $changefile . '-' . wfTimestamp() );
		$out->addWikiMsg( 'translate-smg-submitted' );

		if ( count( $postponed ) ) {
			$changefile = TranslateUtils::cacheFile( self::CHANGEFILE );
			$writer = CdbWriter::open( $changefile );
			$keys = array_keys( $postponed );
			$writer->set( '#keys', serialize( $keys ) );
			foreach ( $postponed as $groupId => $changes ) {
				$writer->set( $groupId, serialize( $changes ) );
			}
			$writer->close();

			$out->wrapWikiMsg( "<div class=warning>\n$1\n</div>", 'translate-smg-postponed' );
		}

	}

	protected static function changeId( $groupId, $code, $type, $key ) {
		return 'smg/' . substr( sha1( "$groupId/$code/$type/$key" ), 0, 7 );
	}

	/**
	 * Adds the task-based tabs on Special:Translate and few other special pages.
	 * Hook: SkinTemplateNavigation::SpecialPage
	 * @since 2012-05-14
	 */
	static function tabify( Skin $skin, array &$tabs ) {
		$title = $skin->getTitle();
		list( $alias, ) = SpecialPageFactory::resolveAlias( $title->getText() );

		$pagesInGroup = array(
			'ManageMessageGroups' => 'namespaces',
			'AggregateGroups' => 'namespaces',
			'SupportedLanguages' => 'views',
			'TranslationStats' => 'views',
		);
		if ( !isset( $pagesInGroup[$alias] ) ) {
			return true;
		}

		$skin->getOutput()->addModules( 'ext.translate.tabgroup' );

		$tabs['namespaces'] = array();
		foreach ( $pagesInGroup as $spName => $section ) {
			$spClass = SpecialPageFactory::getPage( $spName );
			if ( $spClass === null ) {
				continue; // Page explicitly disabled
			}
			$spTitle = $spClass->getTitle();

			$tabs[$section][strtolower( $spName )] = array(
				'text' => $spClass->getDescription(),
				'href' => $spTitle->getLocalUrl(),
				'class' => $alias === $spName ? 'selected' : '',
			);
		}

		return true;
	}
}
