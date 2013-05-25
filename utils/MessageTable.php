<?php
/**
 * Contains classes to build tables for MessageCollection objects.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2007-2013 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Pretty formatter for MessageCollection objects.
 */
class MessageTable {
	/*
	 * @var bool
	 */
	protected $reviewMode = false;

	/**
	 * @var MessageCollection
	 */
	protected $collection;

	/**
	 * @var MessageGroup
	 */
	protected $group;

	/**
	 * @var array
	 */
	protected $editLinkParams = array();
	/**
	 * @var IContextSource
	 */
	protected $context;

	/**
	 * @var array
	 */
	protected $headers = array(
		'table' => array( 'msg', 'allmessagesname' ),
		'current' => array( 'msg', 'allmessagescurrent' ),
		'default' => array( 'msg', 'allmessagesdefault' ),
	);

	/**
	 * Use this rather than the constructor directly
	 * to allow alternative implementations.
	 *
	 * @since 2012-11-29
	 */
	public static function newFromContext(
		IContextSource $context,
		MessageCollection $collection,
		MessageGroup $group
	) {
		$table = new self( $collection, $group );
		$table->setContext( $context );

		wfRunHooks( 'TranslateMessageTableInit', array( &$table, $context, $collection, $group ) );

		return $table;
	}

	public function setContext( IContextSource $context ) {
		$this->context = $context;
	}

	/**
	 * Use the newFromContext() function rather than the constructor directly
	 * to construct the object to allow alternative implementations.
	 */
	public function __construct( MessageCollection $collection, MessageGroup $group ) {
		$this->collection = $collection;
		$this->group = $group;
		$this->setHeaderText( 'table', $group->getLabel() );
		$this->appendEditLinkParams( 'loadgroup', $group->getId() );
	}

	public function setEditLinkParams( array $array ) {
		$this->editLinkParams = $array;
	}

	public function appendEditLinkParams( /*string*/$key, /*string*/$value ) {
		$this->editLinkParams[$key] = $value;
	}

	public function setReviewMode( $mode = true ) {
		$this->reviewMode = $mode;
	}

	public function setHeaderTextMessage( $type, $value ) {
		if ( !isset( $this->headers[$type] ) ) {
			throw new MWException( "Unexpected type $type" );
		}

		$this->headers[$type] = array( 'msg', $value );
	}

	public function setHeaderText( $type, $value ) {
		if ( !isset( $this->headers[$type] ) ) {
			throw new MWException( "Unexpected type $type" );
		}

		$this->headers[$type] = array( 'raw', htmlspecialchars( $value ) );
	}

	public function includeAssets() {
		$out = RequestContext::getMain()->getOutput();

		TranslationHelpers::addModules( $out );
		$pages = array();

		foreach ( $this->collection->getTitles() as $title ) {
			$pages[] = $title->getPrefixedDBKey();
		}

		$vars = array( 'trlKeys' => $pages );
		$out->addScript( Skin::makeVariablesScript( $vars ) );
	}

	public function header() {
		$tableheader = Xml::openElement( 'table', array(
			'class' => 'mw-sp-translate-table'
		) );

		if ( $this->reviewMode ) {
			$tableheader .= Xml::openElement( 'tr' );
			$tableheader .= Xml::element( 'th',
				array( 'rowspan' => '2' ),
				$this->headerText( 'table' )
			);
			$tableheader .= Xml::tags( 'th', null, $this->headerText( 'default' ) );
			$tableheader .= Xml::closeElement( 'tr' );

			$tableheader .= Xml::openElement( 'tr' );
			$tableheader .= Xml::tags( 'th', null, $this->headerText( 'current' ) );
			$tableheader .= Xml::closeElement( 'tr' );
		} else {
			$tableheader .= Xml::openElement( 'tr' );
			$tableheader .= Xml::tags( 'th', null, $this->headerText( 'table' ) );
			$tableheader .= Xml::tags( 'th', null, $this->headerText( 'current' ) );
			$tableheader .= Xml::closeElement( 'tr' );
		}

		return $tableheader . "\n";
	}

	public function contents() {
		$context = RequestContext::getMain();
		$optional = $context->msg( 'translate-optional' )->escaped();

		$this->doLinkBatch();

		$sourceLang = Language::factory( $this->group->getSourceLanguage() );
		$targetLang = Language::factory( $this->collection->getLanguage() );
		$titleMap = $this->collection->keys();

		$output = '';

		$this->collection->initMessages(); // Just to be sure

		/**
		 * @var TMessage $m
		 */
		foreach ( $this->collection as $key => $m ) {
			$tools = array();
			/**
			 * @var Title $title
			 */
			$title = $titleMap[$key];

			$original = $m->definition();
			$translation = $m->translation();

			$hasTranslation = $translation !== null;

			if ( $hasTranslation ) {
				$message = $translation;
				$extraAttribs = self::getLanguageAttributes( $targetLang );
			} else {
				$message = $original;
				$extraAttribs = self::getLanguageAttributes( $sourceLang );
			}

			wfRunHooks(
				'TranslateFormatMessageBeforeTable',
				array( &$message, $m, $this->group, $targetLang, &$extraAttribs )
			);

			// Using Html::element( a ) because Linker::link is memory hog.
			// It takes about 20 KiB per call, and that times 5000 is quite
			// a lot of memory.
			$niceTitle = htmlspecialchars( $context->getLanguage()->truncate(
				$title->getPrefixedText(),
				-35
			) );
			$linkAttribs = array(
				'href' => $title->getLocalUrl( array( 'action' => 'edit' ) + $this->editLinkParams ),
			);
			$linkAttribs += TranslationEditPage::jsEdit( $title, $this->group->getId() );

			$tools['edit'] = Html::element( 'a', $linkAttribs, $niceTitle );

			$anchor = 'msg_' . $key;
			$anchor = Xml::element( 'a', array( 'id' => $anchor, 'href' => "#$anchor" ), "↓" );

			$extra = '';
			if ( $m->hasTag( 'optional' ) ) {
				$extra = '<br />' . $optional;
			}

			$tqeData = $extraAttribs + array(
				'data-title' => $title->getPrefixedText(),
				'data-group' => $this->group->getId(),
				'id' => 'tqe-anchor-' . substr( sha1( $title->getPrefixedText() ), 0, 12 ),
				'class' => 'tqe-inlineeditable ' . ( $hasTranslation ? 'translated' : 'untranslated' )
			);

			$button = $this->getReviewButton( $m );
			$status = $this->getReviewStatus( $m );
			$leftColumn = $button . $anchor . $tools['edit'] . $extra . $status;

			if ( $this->reviewMode ) {
				$output .= Xml::tags( 'tr', array( 'class' => 'orig' ),
					Xml::tags( 'td', array( 'rowspan' => '2' ), $leftColumn ) .
						Xml::tags( 'td', self::getLanguageAttributes( $sourceLang ),
							TranslateUtils::convertWhiteSpaceToHTML( $original )
						)
				);

				$output .= Xml::tags( 'tr', null,
					Xml::tags( 'td', $tqeData, TranslateUtils::convertWhiteSpaceToHTML( $message ) )
				);
			} else {
				$output .= Xml::tags( 'tr', array( 'class' => 'def' ),
					Xml::tags( 'td', null, $leftColumn ) .
						Xml::tags( 'td', $tqeData, TranslateUtils::convertWhiteSpaceToHTML( $message ) )
				);
			}

			$output .= "\n";
		}

		return $output;
	}

	public function fullTable( $offsets, $nondefaults ) {
		$this->includeAssets();

		$content = $this->header() . $this->contents() . '</table>';
		$pager = $this->doStupidLinks( $offsets, $nondefaults );

		if ( $offsets['count'] === 0 ) {
			return $pager;
		} elseif ( $offsets['count'] === $offsets['total'] ) {
			return $content . $pager;
		} else {
			return $pager . $content . $pager;
		}
	}

	protected function headerText( $type ) {
		if ( !isset( $this->headers[$type] ) ) {
			throw new MWException( "Unexpected type $type" );
		}

		list( $format, $value ) = $this->headers[$type];
		if ( $format === 'msg' ) {
			return wfMessage( $value )->escaped();
		} elseif ( $format === 'raw' ) {
			return $value;
		} else {
			throw new MWException( "Unexcepted format $format" );
		}
	}

	protected static function getLanguageAttributes( Language $language ) {
		global $wgTranslateDocumentationLanguageCode;

		$code = $language->getCode();
		$dir = $language->getDir();

		if ( $code === $wgTranslateDocumentationLanguageCode ) {
			// Should be good enough for now
			$code = 'en';
		}

		return array( 'lang' => $code, 'dir' => $dir );
	}

	protected function getReviewButton( TMessage $message ) {
		$revision = $message->getProperty( 'revision' );
		$user = RequestContext::getMain()->getUser();

		if ( !$this->reviewMode || !$user->isAllowed( 'translate-messagereview' ) || !$revision ) {
			return '';
		}

		$attribs = array(
			'type' => 'button',
			'class' => 'mw-translate-messagereviewbutton',
			'data-token' => ApiTranslationReview::getToken( 0, '' ),
			'data-revision' => $revision,
			'name' => 'acceptbutton-' . $revision, // Otherwise Firefox disables buttons on page load
		);

		$reviewers = (array)$message->getProperty( 'reviewers' );
		if ( in_array( $user->getId(), $reviewers ) ) {
			$attribs['value'] = wfMessage( 'translate-messagereview-done' )->text();
			$attribs['disabled'] = 'disabled';
			$attribs['title'] = wfMessage( 'translate-messagereview-doit' )->text();
		} elseif ( $message->hasTag( 'fuzzy' ) ) {
			$attribs['value'] = wfMessage( 'translate-messagereview-submit' )->text();
			$attribs['disabled'] = 'disabled';
			$attribs['title'] = wfMessage( 'translate-messagereview-no-fuzzy' )->text();
		} elseif ( $user->getName() === $message->getProperty( 'last-translator-text' ) ) {
			$attribs['value'] = wfMessage( 'translate-messagereview-submit' )->text();
			$attribs['disabled'] = 'disabled';
			$attribs['title'] = wfMessage( 'translate-messagereview-no-own' )->text();
		} else {
			$attribs['value'] = wfMessage( 'translate-messagereview-submit' )->text();
		}

		$review = Html::element( 'input', $attribs );

		return $review;
	}

	/// For optimization
	protected $reviewStatusCache = array();

	protected function getReviewStatus( TMessage $message ) {
		if ( !$this->reviewMode ) {
			return '';
		}

		$reviewers = (array)$message->getProperty( 'reviewers' );
		$count = count( $reviewers );

		if ( $count === 0 ) {
			return '';
		}

		$userId = RequestContext::getMain()->getUser()->getId();
		$you = in_array( $userId, $reviewers );
		$key = $you ? "y$count" : "n$count";

		// ->text() (and ->parse()) invokes the parser. Each call takes
		// about 70 KiB, so it makes sense to cache these messages which
		// have high repetition.
		if ( isset( $this->reviewStatusCache[$key] ) ) {
			return $this->reviewStatusCache[$key];
		} elseif ( $you ) {
			$msg = wfMessage( 'translate-messagereview-reviewswithyou' )->numParams( $count )->text();
		} else {
			$msg = wfMessage( 'translate-messagereview-reviews' )->numParams( $count )->text();
		}

		$wrap = Html::rawElement( 'div', array( 'class' => 'mw-translate-messagereviewstatus' ), $msg );
		$this->reviewStatusCache[$key] = $wrap;

		return $wrap;
	}

	protected function doLinkBatch() {
		$batch = new LinkBatch();
		$batch->setCaller( __METHOD__ );

		foreach ( $this->collection->getTitles() as $title ) {
			$batch->addObj( $title );
		}

		$batch->execute();
	}

	protected function doStupidLinks( $info, $nondefaults ) {
		// Total number of messages for this query
		$total = $info['total'];
		// Messages in this page
		$count = $info['count'];

		$allInThisPage = $info['start'] === 0 && $total === $count;

		if ( $info['count'] === 0 ) {
			$navigation = wfMessage( 'translate-page-showing-none' )->parse();
		} elseif ( $allInThisPage ) {
			$navigation = wfMessage( 'translate-page-showing-all' )->numParams( $total )->parse();
		} else {
			$previous = wfMessage( 'translate-prev' )->escaped();

			if ( $info['backwardsOffset'] !== false ) {
				$previous = $this->makeOffsetLink( $previous, $info['backwardsOffset'], $nondefaults );
			}

			$nextious = wfMessage( 'translate-next' )->escaped();
			if ( $info['forwardsOffset'] !== false ) {
				$nextious = $this->makeOffsetLink( $nextious, $info['forwardsOffset'], $nondefaults );
			}

			$start = $info['start'] + 1;
			$stop = $start + $info['count'] - 1;
			$total = $info['total'];

			$navigation = wfMessage( 'translate-page-showing' )
				->numParams( $start, $stop, $total )->parse();
			$navigation .= ' ';
			$navigation .= wfMessage( 'translate-page-paging-links' )
				->rawParams( $previous, $nextious )->escaped();
		}

		return Html::openElement( 'fieldset' ) .
			Html::element( 'legend', array(), wfMessage( 'translate-page-navigation-legend' )->text() ) .
			$navigation .
			Html::closeElement( 'fieldset' );
	}

	protected function makeOffsetLink( $label, $offset, $nondefaults ) {
		$query = array_merge(
			$nondefaults,
			array( 'offset' => $offset )
		);

		$link = Linker::link(
			$this->context->getTitle(),
			$label,
			array(),
			$query
		);

		return $link;
	}
}
