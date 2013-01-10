<?php
/**
 * Tasks which encapsulate the processing of messages to requested
 * format for the web interface.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2007-2012 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Container for options that are passed to tasks.
 */
class TaskOptions {
	/// @var string Language code.
	protected $language;
	/// @var int Number of items to show.
	protected $limit = 0;
	/// @var int Offset to the results.
	protected $offset = 0;
	/// @var Callable Callback which is called to provide information about the result counts.
	protected $pagingCB;

	/**
	 * @param string $language Language code.
	 * @param int $limit Number of items to show.
	 * @param int $offset Offset to the results.
	 * @param Callable $pagingCB  Callback which is called to provide
	 *   information about the paging of results. The callback is provided
	 *   with three parameters:
	 *   - offset given
	 *   - number of messages displayed
	 *   - total number of messages
	 */
	public function __construct( $language, $limit = 0, $offset = 0, $pagingCB = null ) {
		$this->language = $language;
		$this->limit = $limit;
		$this->offset = $offset;
		$this->pagingCB = $pagingCB;
	}

	/**
	 * @return string Language code.
	 */
	public function getLanguage() {
		return $this->language;
	}

	/**
	 * @return int Number of items to show.
	 */
	public function getLimit() {
		return $this->limit;
	}

	/**
	 * @return int Offset to the results.
	 */
	public function getOffset() {
		return $this->offset;
	}

	/**
	 * @return Callable Callback which is called to provide information about
	 *   the result counts.
	 */
	public function getPagingCB() {
		return $this->pagingCB;
	}
}

/**
 * Basic implementation and interface for tasks.
 * Task is a combination of filters and output format that are applied to
 * messages of given message group in given language.
 */
abstract class TranslateTask {
	/// @var string Task identifier.
	protected $id = '__BUG__';

	// We need $id here because staticness prevents subclass overriding.
	/**
	 * Get label for task.
	 * @param string $id Task id
	 * @return string
	 */
	public static function labelForTask( $id ) {
		return wfMessage( 'translate-task-' . $id )->text();
	}

	/**
	 * Get task identifier.
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Indicates whether the task itself will hand the full output page,
	 * including headers. If false, the resulting html should be embedded
	 * to the page of calling context.
	 * @return bool
	 */
	public function plainOutput() {
		return false;
	}

	/**
	 * @var MessageGroup
	 */
	protected $group;

	/**
	 * @var MessageCollection
	 */
	protected $collection;

	/**
	 * @var TaskOptions
	 */
	protected $options;

	/**
	 * @var IContextSource
	 */
	protected $context;

	/**
	 * Constructor.
	 * @param MessageGroup $group Message group.
	 * @param TaskOptions $options Options.
	 * @param IContextSource $context
	 */
	public final function init( MessageGroup $group, TaskOptions $options, IContextSource $context ) {
		$this->group = $group;
		$this->options = $options;
		$this->context = $context;
	}

	/**
	 * Outputs the results.
	 * @return string
	 */
	abstract protected function output();

	/// Processes messages before paging is done.
	abstract protected function preinit();

	/// Processes messages after paging is done.
	abstract protected function postinit();

	/**
	 * Executes the task with given options and outputs the results.
	 * @return string Partial or full html.
	 * @see plainOutput()
	 */
	public final function execute() {
		$this->preinit();
		$this->doPaging();
		$this->postinit();

		return $this->output();
	}

	/**
	 * Takes a slice of messages according to limit and offset given
	 * in option at initialisation time. Calls the callback to provide
	 * information how much messages there is.
	 * @return array|null
	 */
	protected function doPaging() {
		$total = count( $this->collection );
		$offsets = $this->collection->slice(
			$this->options->getOffset(),
			$this->options->getLimit()
		);
		$left = count( $this->collection );

		$params = array(
			'backwardsOffset' => $offsets[0],
			'forwardsOffset' => $offsets[1],
			'start' => $offsets[2],
			'count' => $left,
			'total' => $total,
		);

		$callback = $this->options->getPagingCB();
		call_user_func( $callback, $params );
		return $params;
	}

	/**
	 * Determine whether this user can use this task.
	 * Override this method if the task depends on user rights.
	 * @param User $user
	 * @return string
	 */
	public function isAllowedFor( User $user ) {
		return true;
	}
}

/**
 * Provides essentially free-form filtering access via tasks.
 * This essentially makes all other tasks redundant, and once
 * TUX is finished and everything is using WebAPI we can get
 * rid of these.
 * @since 2012-12-12
 */
class CustomFilteredMessagesTask extends TranslateTask {
	protected $id = 'custom';
	/// Store some info
	protected $offsets = array();

	protected function preinit() {
		$code = $this->options->getLanguage();
		$this->collection = $this->group->initCollection( $code );
		$this->collection->setReviewMode( true );
		$this->collection->filter( 'ignored' );
		if ( $this->context->getRequest()->getBool( 'optional' ) ) {
			$this->collection->filter( 'optional' );
		}

		$filter = $this->context->getRequest()->getVal( 'filter' );
		if ( !$filter ) {
			return;
		}
		$negate = false;
		if ( $filter[0] === '!' ) {
			$negate = true;
			$filter = substr( $filter, 1 );
		}
		$this->collection->filter( $filter, $negate );
	}

	protected function doPaging() {
		$this->offsets = parent::doPaging();
		return $this->offsets;
	}

	protected function postinit() {
		$this->collection->loadTranslations();
	}

	protected function output() {
		$table = MessageTable::newFromContext( $this->context, $this->collection, $this->group );
		$table->appendEditLinkParams( 'loadtask', $this->getId() );
		if ( method_exists( $table, 'setOffsets' ) ) {
			$table->setOffsets( $this->offsets );
		}

		return $table->fullTable();
	}
}

/**
 * Lists all non-optional messages with translations if any.
 */
class ViewMessagesTask extends TranslateTask {
	protected $id = 'view';

	protected function preinit() {
		$code = $this->options->getLanguage();
		$this->collection = $this->group->initCollection( $code );
		$this->collection->filter( 'ignored' );
		$this->collection->filter( 'optional' );
	}

	protected function postinit() {
		$this->collection->loadTranslations();
	}

	protected function output() {
		$table = MessageTable::newFromContext( $this->context, $this->collection, $this->group );
		$table->appendEditLinkParams( 'loadtask', $this->getId() );

		return $table->fullTable();
	}
}

/**
 * Basic task class for review mode.
 */
class ReviewMessagesTask extends ViewMessagesTask {
	protected $id = 'review';

	protected function preinit() {
		$code = $this->options->getLanguage();
		$this->collection = $this->group->initCollection( $code );
		$this->collection->setReviewMode( true );
		$this->collection->filter( 'ignored' );
	}

	protected function output() {
		$table = MessageTable::newFromContext( $this->context, $this->collection, $this->group );
		$table->appendEditLinkParams( 'loadtask', $this->getId() );
		$table->setReviewMode();
		return $table->fullTable();
	}
}

/**
 * Lists untranslated non-optional messages. This is often good default
 * task when translating.
 */
class ViewUntranslatedTask extends ViewMessagesTask {
	protected $id = 'untranslated';

	protected function preinit() {
		$code = $this->options->getLanguage();
		$this->collection = $this->group->initCollection( $code );
		$this->collection->filter( 'ignored' );
		$this->collection->filter( 'optional' );
		$this->collection->filter( 'translated' );
	}
}

/**
 * Lists optional messages.
 */
class ViewOptionalTask extends ViewMessagesTask {
	protected $id = 'optional';

	protected function preinit() {
		$code = $this->options->getLanguage();
		$this->collection = $this->group->initCollection( $code );
		$this->collection->filter( 'ignored' );
		$this->collection->filter( 'optional', false );
	}
}

/**
 * Lists messages with good translation memory suggestions.
 * The number of results is limited by the speed of translation memory.
 */
class ViewWithSuggestionsTask extends ViewMessagesTask {
	protected $id = 'suggestions';

	protected function preinit() {
		$code = $this->options->getLanguage();
		$sourceLanguage = $this->group->getSourceLanguage();

		$this->collection = $this->group->initCollection( $code );
		$this->collection->filter( 'ignored' );
		$this->collection->filter( 'optional' );
		$this->collection->filter( 'translated' );
		$this->collection->filter( 'fuzzy' );
		$this->collection->loadTranslations();

		$start = time();

		foreach ( $this->collection->getMessageKeys() as $key ) {
			// Allow up to 10 seconds to search for suggestions.
			if ( time() - $start > 10 ) {
				unset( $this->collection[$key] );
				continue;
			}

			$definition = $this->collection[$key]->definition();
			$suggestions = TTMServer::primary()->query( $sourceLanguage, $code, $definition );
			foreach ( $suggestions as $s ) {
				// We have a good suggestion, do not filter.
				if ( $s['quality'] > 0.80 ) {
					continue 2;
				}
			}
			unset( $this->collection[$key] );
		}
	}
}

/**
 * Lists untranslated optional messages.
 */
class ViewUntranslatedOptionalTask extends ViewOptionalTask {
	protected $id = 'untranslatedoptional';

	protected function preinit() {
		$code = $this->options->getLanguage();
		$this->collection = $this->group->initCollection( $code );
		$this->collection->filter( 'ignored' );
		$this->collection->filter( 'optional', false );
		$this->collection->filter( 'translated' );
	}
}

/**
 * Lists all translations for reviewing.
 */
class ReviewAllMessagesTask extends ReviewMessagesTask {
	protected $id = 'reviewall';

	protected function preinit() {
		parent::preinit();
		$this->collection->filter( 'ignored' );
		$this->collection->filter( 'hastranslation', false );
	}
}

/**
 * Lists all translations the user can accept.
 */
class AcceptQueueMessagesTask extends ReviewMessagesTask {
	protected $id = 'acceptqueue';

	protected function preinit() {
		$user = $this->context->getUser();
		parent::preinit();
		$this->collection->filter( 'ignored' );
		$this->collection->filter( 'hastranslation', false );
		$this->collection->filter( 'fuzzy' );
		$this->collection->filter( 'reviewer', true, $user->getId() );
		$this->collection->filter( 'last-translator', true, $user->getId() );
	}

	public function isAllowedFor( User $user ) {
		return $user->isAllowed( 'translate-messagereview' );
	}
}

/**
 * Exports messages to their native format with embedded textarea.
 */
class ExportMessagesTask extends ViewMessagesTask {
	protected $id = 'export';

	protected function preinit() {
		$code = $this->options->getLanguage();
		$this->collection = $this->group->initCollection( $code );
		// Don't export ignored, unless it is the source language
		// or message documentation
		global $wgTranslateDocumentationLanguageCode;
		if ( $code !== $wgTranslateDocumentationLanguageCode
			&& $code !== $this->group->getSourceLanguage()
		) {
			$this->collection->filter( 'ignored' );
		}
	}

	// No paging should be done.
	protected function doPaging() {}

	public function output() {
		if ( $this->group instanceof MessageGroupBase ) {
			$ffs = $this->group->getFFS();
			$data = $ffs->writeIntoVariable( $this->collection );
		} else {
			$writer = $this->group->getWriter();
			$data = $writer->webExport( $this->collection );
		}

		return Html::element( 'textarea', array( 'id' => 'wpTextbox1', 'rows' => '50' ), $data );
	}
}

/**
 * Exports messages to their native format as whole page.
 */
class ExportToFileMessagesTask extends ExportMessagesTask {
	protected $id = 'export-to-file';

	public function plainOutput() {
		return true;
	}

	public function output() {
		if ( !$this->group instanceof FileBasedMessageGroup ) {
			return 'Not supported';
		}

		$ffs = $this->group->getFFS();
		$data = $ffs->writeIntoVariable( $this->collection );

		$filename = basename( $this->group->getSourceFilePath( $this->collection->getLanguage() ) );
		header( "Content-Disposition: attachment; filename=\"$filename\"" );
		return $data;
	}
}

/**
 * Exports messages as special Gettext format that is suitable for off-line
 * translation with tools that support Gettext. These files can later be
 * imported back to the wiki.
 */
class ExportAsPoMessagesTask extends ExportMessagesTask {
	protected $id = 'export-as-po';

	public function plainOutput() {
		return true;
	}

	public function output() {
		if ( MessageGroups::isDynamic( $this->group ) ) {
			return 'Not supported';
		}

		$ffs = null;
		if ( $this->group instanceof FileBasedMessageGroup ) {
			$ffs = $this->group->getFFS();
		}

		if ( !$ffs instanceof GettextFFS ) {
			$group = FileBasedMessageGroup::newFromMessageGroup( $this->group );
			$ffs = new GettextFFS( $group );
		}

		$ffs->setOfflineMode( 'true' );

		$code = $this->options->getLanguage();
		$id = $this->group->getID();
		$filename = "${id}_$code.po";
		header( "Content-Disposition: attachment; filename=\"$filename\"" );
		return $ffs->writeIntoVariable( $this->collection );
	}
}

/**
 * Collection of functions to get tasks.
 */
class TranslateTasks {
	/**
	 * Return list of available tasks.
	 * @param bool $pageTranslation Whether this group is page translation group.
	 * @todo Make the above parameter a group and check its class?
	 * @return string[] Task identifiers.
	 */
	public static function getTasks( $pageTranslation = false ) {
		global $wgTranslateTasks, $wgTranslateTranslationServices;

		// Tasks not to be available in page translation.
		$filterTasks = array(
			'optional',
			'untranslatedoptional',
			'export-to-file',
		);

		$allTasks = array_keys( $wgTranslateTasks );

		if ( $pageTranslation ) {
			$allTasks = array_diff( $allTasks, $filterTasks );
		}

		if ( !isset( $wgTranslateTranslationServices['tmserver'] ) ) {
			unset( $allTasks['suggestions'] );
		}

		return $allTasks;
	}

	/**
	 * Get task by id.
	 * @param string $id Unique task identifier.
	 * @return TranslateTask|null Null if no such task.
	 */
	public static function getTask( $id ) {
		global $wgTranslateTasks;

		if ( array_key_exists( $id, $wgTranslateTasks ) ) {
			if ( is_callable( $wgTranslateTasks[$id] ) ) {
				return call_user_func( $wgTranslateTasks[$id], $id );
			}

			return new $wgTranslateTasks[$id];
		}

		return null;
	}
}
