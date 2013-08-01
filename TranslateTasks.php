<?php
/**
 * Tasks which encapsulate the processing of messages to requested
 * format for the web interface.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2007-2013 Niklas Laxström
 * @license GPL-2.0+
 */

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
	 * @var array
	 */
	protected $options;

	/**
	 * @var array
	 */
	protected $nondefaults;

	/**
	 * @var IContextSource
	 */
	protected $context;

	/**
	 * @var array Offsets stored after the collection has been paged.
	 */
	protected $offsets;

	/**
	 * Constructor.
	 * @param MessageGroup $group Message group.
	 * @param array $options Options.
	 * @param array $nondefaults List of non-default options for links.
	 * @param IContextSource $context
	 */
	final public function init( MessageGroup $group, array $options, array $nondefaults,
		IContextSource $context
	) {
		$this->group = $group;
		$this->options = $options;
		$this->nondefaults = $nondefaults;
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
	final public function execute() {
		$this->preinit();
		$this->doPaging();
		$this->postinit();

		return $this->output();
	}

	/**
	 * Takes a slice of messages according to limit and offset given
	 * in option at initialisation time. Calls the callback to provide
	 * information how much messages there is.
	 */
	protected function doPaging() {
		$total = count( $this->collection );
		$offsets = $this->collection->slice(
			$this->options['offset'],
			$this->options['limit']
		);
		$left = count( $this->collection );

		$this->offsets = array(
			'backwardsOffset' => $offsets[0],
			'forwardsOffset' => $offsets[1],
			'start' => $offsets[2],
			'count' => $left,
			'total' => $total,
		);
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

	protected function preinit() {
	}

	protected function postinit() {
	}

	protected function doPaging() {
	}

	protected function output() {
		$table = new TuxMessageTable( $this->context, $this->group, $this->options['language'] );

		return $table->fullTable();
	}
}

/**
 * Lists all non-optional messages with translations if any.
 */
class ViewMessagesTask extends TranslateTask {
	protected $id = 'view';

	protected function preinit() {
		$code = $this->options['language'];
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

		return $table->fullTable( $this->offsets, $this->nondefaults );
	}
}

/**
 * Basic task class for review mode.
 */
class ReviewMessagesTask extends ViewMessagesTask {
	protected $id = 'review';

	protected function preinit() {
		$code = $this->options['language'];
		$this->collection = $this->group->initCollection( $code );
		$this->collection->filter( 'ignored' );
	}

	protected function output() {
		$table = MessageTable::newFromContext( $this->context, $this->collection, $this->group );
		$table->appendEditLinkParams( 'loadtask', $this->getId() );
		$table->setReviewMode();

		return $table->fullTable( $this->offsets, $this->nondefaults );
	}
}

/**
 * Lists untranslated non-optional messages. This is often good default
 * task when translating.
 */
class ViewUntranslatedTask extends ViewMessagesTask {
	protected $id = 'untranslated';

	protected function preinit() {
		$code = $this->options['language'];
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
		$code = $this->options['language'];
		$this->collection = $this->group->initCollection( $code );
		$this->collection->filter( 'ignored' );
		$this->collection->filter( 'optional', false );
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
		$code = $this->options['language'];
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
	protected function doPaging() {
	}

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

		$code = $this->options['language'];
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
		global $wgTranslateTasks;

		// Tasks not to be available in page translation.
		$filterTasks = array(
			'optional',
			'export-to-file',
		);

		$allTasks = array_keys( $wgTranslateTasks );

		if ( $pageTranslation ) {
			$allTasks = array_diff( $allTasks, $filterTasks );
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
