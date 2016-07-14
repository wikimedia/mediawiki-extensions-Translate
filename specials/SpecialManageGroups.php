<?php
/**
 * Implements special page for group management, where file based message
 * groups are be managed.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0+
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
	const RIGHT = 'translate-manage';

	/**
	 * @var DifferenceEngine
	 */
	protected $diff;

	/**
	 * @var string Path to the change cdb file.
	 */
	protected $cdb;

	public function __construct() {
		// Anyone is allowed to see, but actions are restricted
		parent::__construct( 'ManageMessageGroups' );
	}

	public function doesWrites() {
		return true;
	}

	protected function getGroupName() {
		return 'wiki';
	}

	function getDescription() {
		return $this->msg( 'managemessagegroups' )->text();
	}

	public function execute( $par ) {
		$this->setHeaders();
		$out = $this->getOutput();
		$out->addModuleStyles( 'ext.translate.special.managegroups' );
		$out->addHelpLink( 'Help:Extension:Translate/Group_management' );

		$name = $par ?: MessageChangeStorage::DEFAULT_NAME;

		$this->cdb = MessageChangeStorage::getCdbPath( $name );
		if ( !MessageChangeStorage::isValidCdbName( $name ) || !file_exists( $this->cdb ) ) {
			// @todo Tell them when changes was last checked/process
			// or how to initiate recheck.
			$out->addWikiMsg( 'translate-smg-nochanges' );

			return;
		}

		$user = $this->getUser();
		$allowed = $user->isAllowed( self::RIGHT );

		$req = $this->getRequest();
		if ( !$req->wasPosted() ) {
			$this->showChanges( $allowed, $this->getLimit() );

			return;
		}

		$token = $req->getVal( 'token' );
		if ( !$allowed || !$user->matchEditToken( $token ) ) {
			throw new PermissionsError( self::RIGHT );
		}

		$this->processSubmit();
	}

	/**
	 * How many changes can be shown per page.
	 * @return int
	 */
	protected function getLimit() {
		$limits = array(
			1000, // Default max
			ini_get( 'max_input_vars' ),
			ini_get( 'suhosin.post.max_vars' ),
			ini_get( 'suhosin.request.max_vars' )
		);
		// Ignore things not set
		$limits = array_filter( $limits );
		return min( $limits );
	}

	protected function getLegend() {
		$text = $this->diff->addHeader(
			'',
			$this->msg( 'translate-smg-left' )->escaped(),
			$this->msg( 'translate-smg-right' )->escaped()
		);

		return Html::rawElement( 'div', array( 'class' => 'mw-translate-smg-header' ), $text );
	}

	protected function showChanges( $allowed, $limit ) {
		global $wgContLang;

		$diff = new DifferenceEngine( $this->getContext() );
		$diff->showDiffStyle();
		$diff->setReducedLineNumbers();
		$this->diff = $diff;

		$out = $this->getOutput();
		$out->addHTML(
			'' .
				Html::openElement( 'form', array( 'method' => 'post' ) ) .
				Html::hidden( 'title', $this->getPageTitle()->getPrefixedText() ) .
				Html::hidden( 'token', $this->getUser()->getEditToken() ) .
				$this->getLegend()
		);

		// The above count as two
		$limit = $limit - 2;

		$reader = \Cdb\Reader::open( $this->cdb );
		$groups = unserialize( $reader->get( '#keys' ) );
		foreach ( $groups as $id ) {
			$group = MessageGroups::getGroup( $id );
			if ( !$group ) {
				continue;
			}

			$changes = unserialize( $reader->get( $id ) );
			$out->addHTML( Html::element( 'h2', array(), $group->getLabel() ) );

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
						$change = $this->formatChange( $group, $code, $type, $params, $limit );
						$out->addHTML( $change );

						if ( $limit <= 0 ) {
							// We need to restrict the changes per page per form submission
							// limitations as well as performance.
							$out->wrapWikiMsg( "<div class=warning>\n$1\n</div>", 'translate-smg-more' );
							break 4;
						}
					}
				}
			}
		}

		$attribs = array( 'type' => 'submit', 'class' => 'mw-translate-smg-submit' );
		if ( !$allowed ) {
			$attribs['disabled'] = 'disabled';
			$attribs['title'] = $this->msg( 'translate-smg-notallowed' )->text();
		}
		$button = Html::element( 'button', $attribs, $this->msg( 'translate-smg-submit' )->text() );
		$out->addHTML( $button );
		$out->addHTML( Html::closeElement( 'form' ) );
	}

	/**
	 * @param MessageGroup $group
	 * @param string $code
	 * @param string $type
	 * @param array $params
	 * @param int $limit
	 * @return string HTML
	 */
	protected function formatChange( MessageGroup $group, $code, $type, $params, &$limit ) {
		$key = $params['key'];
		$title = Title::makeTitleSafe( $group->getNamespace(), "$key/$code" );
		$id = self::changeId( $group->getId(), $code, $type, $key );

		if ( $title && $type === 'addition' && $title->exists() ) {
			// The message has for some reason dropped out from cache
			// or perhaps it is being reused. In any case treat it
			// as a change for display, so the admin can see if
			// action is needed and let the message be processed.
			// Otherwise it will end up in the postponed category
			// forever and will prevent rebuilding the cache, which
			// leads to many other annoying problems.
			$type = 'change';
		} elseif ( $title && ( $type === 'deletion' || $type === 'change' ) && !$title->exists() ) {
			return '';
		}

		$text = '';
		if ( $type === 'deletion' ) {
			$wiki = ContentHandler::getContentText( Revision::newFromTitle( $title )->getContent() );
			$oldContent = ContentHandler::makeContent( $wiki, $title );
			$newContent = ContentHandler::makeContent( '', $title );

			$this->diff->setContent( $oldContent, $newContent );

			$text = $this->diff->getDiff( Linker::link( $title ), '' );
		} elseif ( $type === 'addition' ) {
			$oldContent = ContentHandler::makeContent( '', $title );
			$newContent = ContentHandler::makeContent( $params['content'], $title );

			$this->diff->setContent( $oldContent, $newContent );

			$text = $this->diff->getDiff( '', Linker::link( $title ) );
		} elseif ( $type === 'change' ) {
			$wiki = ContentHandler::getContentText( Revision::newFromTitle( $title )->getContent() );

			$handle = new MessageHandle( $title );
			if ( $handle->isFuzzy() ) {
				$wiki = '!!FUZZY!!' . str_replace( TRANSLATE_FUZZY, '', $wiki );
			}

			$label = $this->msg( 'translate-manage-action-ignore' )->text();
			$actions = Xml::checkLabel( $label, "i/$id", "i/$id" );
			$limit--;

			if ( $group->getSourceLanguage() === $code ) {
				$label = $this->msg( 'translate-manage-action-fuzzy' )->text();
				$actions .= ' ' . Xml::checkLabel( $label, "f/$id", "f/$id", true );
				$limit--;
			}

			$oldContent = ContentHandler::makeContent( $wiki, $title );
			$newContent = ContentHandler::makeContent( $params['content'], $title );

			$this->diff->setContent( $oldContent, $newContent );
			$text .= $this->diff->getDiff( Linker::link( $title ), $actions );
		}

		$hidden = Html::hidden( $id, 1 );
		$limit--;
		$text .= $hidden;
		$classes = "mw-translate-smg-change smg-change-$type";

		if ( $limit < 0 ) {
			// Don't add if one of the fields might get dropped of at submission
			return '';
		}

		return Html::rawElement( 'div', array( 'class' => $classes ), $text );
	}

	protected function processSubmit() {
		$req = $this->getRequest();
		$out = $this->getOutput();

		$jobs = array();
		$jobs[] = MessageIndexRebuildJob::newJob();

		$reader = \Cdb\Reader::open( $this->cdb );
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
							continue;
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

		JobQueueGroup::singleton()->push( $jobs );

		$reader->close();
		rename( $this->cdb, $this->cdb . '-' . wfTimestamp() );

		if ( count( $postponed ) ) {
			MessageChangeStorage::writeChanges( $postponed, $this->cdb );
			$this->showChanges( true, $this->getLimit() );
		} else {
			$out->addWikiMsg( 'translate-smg-submitted' );
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
	public static function tabify( Skin $skin, array &$tabs ) {
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

		$skin->getOutput()->addModuleStyles( 'ext.translate.tabgroup' );

		$tabs['namespaces'] = array();
		foreach ( $pagesInGroup as $spName => $section ) {
			$spClass = SpecialPageFactory::getPage( $spName );
			if ( $spClass === null ) {
				continue; // Page explicitly disabled
			}
			$spTitle = $spClass->getPageTitle();

			$tabs[$section][strtolower( $spName )] = array(
				'text' => $spClass->getDescription(),
				'href' => $spTitle->getLocalURL(),
				'class' => $alias === $spName ? 'selected' : '',
			);
		}

		return true;
	}
}
