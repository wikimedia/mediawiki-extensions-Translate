<?php
/**
 * Different tasks which encapsulate the processing of messages to requested
 * format for the web interface.
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2007-2008 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @file
 */

/**
 * Container for options that are passed to tasks.
 */
class TaskOptions {
	private $language = null;
	private $limit = 0;
	private $offset = 0;
	private $pagingCB = null;

	public function __construct( $language, $limit = 0, $offset = 0, $pagingCB = null ) {
		$this->language = $language;
		$this->limit = $limit;
		$this->offset = $offset;
		$this->pagingCB = $pagingCB;
	}

	public function getLanguage() {
		return $this->language;
	}

	public function getLimit() {
		return $this->limit;
	}

	public function getOffset() {
		return $this->offset;
	}

	public function getPagingCB() {
		return $this->pagingCB;
	}
}

/**
 * Implements the core of TranslateTask.
 */
abstract class TranslateTask {
	protected $id = '__BUG__';

	/**
	 * We need $id here because staticness prevents subclass overriding.
	 */
	public static function labelForTask( $id ) {
		return wfMsg( TranslateUtils::MSG . 'task-' . $id );
	}

	public function getId() {
		return $this->id;
	}

	public function plainOutput() {
		return false;
	}

	protected $group = null;
	protected $collection = null;
	protected $options = null;

	public final function init( MessageGroup $group, TaskOptions $options ) {
		$this->group = $group;
		$this->options = $options;
	}

	abstract protected function output();

	public final function execute() {
		$this->preinit();
		$this->doPaging();
		$this->postinit();

		return $this->output();
	}

	protected function doPaging() {
		$total = count( $this->collection );
		$this->collection->slice(
			$this->options->getOffset(),
			$this->options->getLimit()
		);
		$left  = count( $this->collection );

		$callback = $this->options->getPagingCB();
		call_user_func( $callback, $this->options->getOffset(), $left, $total );
	}
}

/**
 * @todo Needs documentation.
 */
class ViewMessagesTask extends TranslateTask {
	protected $id = 'view';

	protected function preinit() {
		$code = $this->options->getLanguage();
		$this->collection = $this->group->initCollection( $code );
		$this->collection->setInfile( $this->group->load( $code ) );
		$this->collection->filter( 'ignored' );
		$this->collection->filter( 'optional' );
	}

	protected function postinit() {
		$this->collection->loadTranslations();
	}

	protected function output() {
		$table = new MessageTable( $this->collection, $this->group );
		$table->appendEditLinkParams( 'loadtask', $this->getId() );

		return $table->fullTable();
	}
}

/**
 * @todo Needs documentation.
 */
class ReviewMessagesTask extends ViewMessagesTask {
	protected $id = 'review';

	protected function preinit() {
		$code = $this->options->getLanguage();
		$this->collection = $this->group->initCollection( $code );
		$this->collection->setInfile( $this->group->load( $code ) );
		$this->collection->filter( 'ignored' );
		$this->collection->filter( 'hastranslation', false );
		$this->collection->filter( 'changed', false );
	}

	protected function output() {
		$table = new MessageTable( $this->collection, $this->group );
		$table->appendEditLinkParams( 'loadtask', $this->getId() );
		$table->setReviewMode();

		return $table->fullTable();
	}
}

/**
 * @todo Needs documentation.
 */
class ViewUntranslatedTask extends ReviewMessagesTask {
	protected $id = 'untranslated';

	protected function preinit() {
		$code = $this->options->getLanguage();
		$this->collection = $this->group->initCollection( $code );
		$this->collection->setInfile( $this->group->load( $code ) );
		$this->collection->filter( 'ignored' );
		$this->collection->filter( 'optional' );

		/**
		 * Update the cache while we are at it.
		 */
		$total = count( $this->collection );
		$this->collection->filter( 'translated' );
		$translated = $total - count( $this->collection );
		$fuzzy = count( $this->collection->getTags( 'fuzzy' ) );

		$cache = new ArrayMemoryCache( 'groupstats' );
		$cache->set( $this->group->getID(), $code, array( $fuzzy, $translated, $total ) );
	}
}

/**
 * @todo Needs documentation.
 */
class ViewOptionalTask extends ViewMessagesTask {
	protected $id = 'optional';

	protected function preinit() {
		$code = $this->options->getLanguage();
		$this->collection = $this->group->initCollection( $code );
		$this->collection->setInfile( $this->group->load( $code ) );
		$this->collection->filter( 'ignored' );
		$this->collection->filter( 'optional', false );
	}
}

/**
 * @todo Needs documentation.
 */
class ViewWithSuggestionsTask extends ViewMessagesTask {
	protected $id = 'suggestions';

	protected function preinit() {
		$code = $this->options->getLanguage();
		global $wgTranslateTranslationServices;
		$config = $wgTranslateTranslationServices['tmserver'];
		$server = $config['server'];
		$port   = $config['port'];
		$timeout = $config['timeout-sync'];

		$this->collection = $this->group->initCollection( $code );
		$this->collection->setInfile( $this->group->load( $code ) );
		$this->collection->filter( 'ignored' );
		$this->collection->filter( 'optional' );
		$this->collection->filter( 'translated' );
		$this->collection->filter( 'fuzzy' );
		$this->collection->loadTranslations();

		$start = time();

		foreach ( $this->collection->keys() as $key => $_ ) {
			/**
			 * Allow up to 10 seconds to search for suggestions.
			 */
			if ( time() - $start > 10 || TranslationHelpers::checkTranslationServiceFailure( 'tmserver' ) ) {
				unset( $this->collection[$key] );
				continue;
			}

			$def = rawurlencode( $this->collection[$key]->definition() );
			$url = "$server:$port/tmserver/en/$code/unit/$def";
			$suggestions = Http::get( $url, $timeout );

			if ( $suggestions !== false ) {
				$suggestions = FormatJson::decode( $suggestions, true );
				foreach ( $suggestions as $s ) {
					/**
					 * We have a good suggestion, do not filter.
					 */
					if ( $s['quality'] > 0.80 ) {
						continue 2;
					}
				}
			} else {
				TranslationHelpers::reportTranslationServiceFailure( 'tmserver' );
			}
			unset( $this->collection[$key] );
		}
	}
}

/**
 * @todo Needs documentation.
 */
class ViewUntranslatedOptionalTask extends ViewOptionalTask {
	protected $id = 'untranslatedoptional';

	protected function preinit() {
		$code = $this->options->getLanguage();
		$this->collection = $this->group->initCollection( $code );
		$this->collection->setInfile( $this->group->load( $code ) );
		$this->collection->filter( 'ignored' );
		$this->collection->filter( 'optional', false );
		$this->collection->filter( 'translated' );
	}
}

/**
 * @todo Needs documentation.
 */
class ReviewAllMessagesTask extends ReviewMessagesTask {
	protected $id = 'reviewall';

	protected function preinit() {
		$code = $this->options->getLanguage();
		$this->collection = $this->group->initCollection( $code );
		$this->collection->setInfile( $this->group->load( $code ) );
		$this->collection->filter( 'ignored' );
		$this->collection->filter( 'hastranslation', false );
	}
}

/**
 * @todo Needs documentation.
 */
class ExportMessagesTask extends ViewMessagesTask {
	protected $id = 'export';

	/**
	 * N/A.
	 */
	protected function doPaging() { }

	public function output() {
		if ( $this->group instanceof FileBasedMessageGroup ) {
			$ffs = $this->group->getFFS();
			$data = $ffs->writeIntoVariable( $this->collection );
		} else {
			$writer = $this->group->getWriter();
			$data = $writer->webExport( $this->collection );
		}

		return Xml::openElement( 'textarea', array( 'id' => 'wpTextbox1', 'rows' => '50' ) ) .
			$data .
			"</textarea>";
	}
}

/**
 * @todo Needs documentation.
 */
class ExportToFileMessagesTask extends ExportMessagesTask {
	protected $id = 'export-to-file';

	public function plainOutput() {
		return true;
	}

	public function output() {
		$this->collection->filter( 'translated', false );
		if ( $this->group instanceof FileBasedMessageGroup ) {
			$ffs = $this->group->getFFS();
			$data = $ffs->writeIntoVariable( $this->collection );
		} else {
			$writer = $this->group->getWriter();
			$data = $writer->webExport( $this->collection );
		}
		return $data;
	}
}

/**
 * @todo Needs documentation.
 */
class ExportToXliffMessagesTask extends ExportToFileMessagesTask {
	protected $id = 'export-to-xliff';

	public function output() {
		$writer = new XliffFormatWriter( $this->group );
		return $writer->webExport( $this->collection );
	}
}

/**
 * @todo Needs documentation.
 */
class ExportAsPoMessagesTask extends ExportMessagesTask {
	protected $id = 'export-as-po';

	public function plainOutput() {
		return true;
	}

	public function output() {
		global $wgServer, $wgTranslateDocumentationLanguageCode;

		$lang = Language::factory( 'en' );

		$out = '';
		$now = wfTimestampNow();
		$label = $this->group->getLabel();
		$code = $this->options->getLanguage();
		$languageName = TranslateUtils::getLanguageName( $code );

		$filename = $code . '_' . $this->group->getID() . '.po';
		header( "Content-Disposition: attachment; filename=\"$filename\"" );

		$headers = array();
		$headers['Project-Id-Version'] = 'MediaWiki ' . SpecialVersion::getVersion( 'nodb' );
		/**
		 * @todo Make this customisable or something.
		 */
		$headers['Report-Msgid-Bugs-To'] = $wgServer;
		/**
		 * @todo sprintfDate does not support any time zone flags.
		 */
		$headers['POT-Creation-Date'] = $lang->sprintfDate( 'xnY-xnm-xnd xnH:xni:xns+0000', $now );
		$headers['Language-Team'] = TranslateUtils::getLanguageName( $this->options->getLanguage() );
		$headers['Content-Type'] = 'text-plain; charset=UTF-8';
		$headers['Content-Transfer-Encoding'] = '8bit';
		$headers['X-Generator'] = 'MediaWiki Translate extension ' . TRANSLATE_VERSION;
		$headers['X-Language-Code'] = $this->options->getLanguage();
		$headers['X-Message-Group'] = $this->group->getId();

		$headerlines = array( '' );
		foreach ( $headers as $key => $value ) {
			$headerlines[] = "$key: $value\n";
		}

		$out .= "# Translation of $label to $languageName\n";
		$out .= self::formatmsg( '', $headerlines  );

		foreach ( $this->collection as $key => $m ) {
			$flags = array();

			$translation = $m->translation();
			/**
			 * CASE2: no translation.
			 */
			if ( $translation === null ) {
				$translation = '';
			}

			/**
			 * CASE3: optional messages; accept only if different.
			 */
			if ( $m->hasTag( 'optional' ) ) {
				$flags[] = 'optional';
			}

			/**
			 * Remove fuzzy markings before export.
			 */
			if ( strpos( $translation, TRANSLATE_FUZZY ) !== false ) {
				$translation = str_replace( TRANSLATE_FUZZY, '', $translation );
				$flags[] = 'fuzzy';
			}

			$comments = '';
			if ( $wgTranslateDocumentationLanguageCode ) {
				$documentation = TranslateUtils::getMessageContent( $key, $wgTranslateDocumentationLanguageCode );
				if ( $documentation ) {
					$comments = $documentation;
				}
			}

			$out .= self::formatComments( $comments, $flags );
			$out .= self::formatmsg( $m->definition(), $translation, $key, $flags );

		}

		return $out;
	}

	private static function escape( $line ) {
		$line = addcslashes( $line, '\\"' );
		$line = str_replace( "\n", '\n', $line );
		$line = '"' . $line . '"';

		return $line;
	}

	private static function formatComments( $comments = false, $flags = false ) {
		$output = array();

		if ( $comments ) {
			$output[] = '#. ' . implode( "\n#. ", explode( "\n", $comments ) );
		}

		if ( $flags ) {
			$output[] = '#, ' . implode( ', ', $flags );
		}

		if ( !count( $output ) ) {
			$output[] = '#:';
		}

		return implode( "\n", $output ) . "\n";
	}

	private static function formatmsg( $msgid, $msgstr, $msgctxt = false ) {
		$output = array();

		if ( $msgctxt ) {
			$output[] = 'msgctxt ' . self::escape( $msgctxt );
		}

		if ( !is_array( $msgid ) ) {
			$msgid = array( $msgid );
		}

		if ( !is_array( $msgstr ) ) {
			$msgstr = array( $msgstr );
		}

		$output[] = 'msgid ' . implode( "\n", array_map( array( __CLASS__, 'escape' ), $msgid ) );
		$output[] = 'msgstr ' . implode( "\n", array_map( array( __CLASS__, 'escape' ), $msgstr ) );

		$out = implode( "\n", $output ) . "\n\n";

		return $out;
	}
}

/**
 * @todo Needs documentation.
 */
class TranslateTasks {
	public static function getTasks( $pageTranslation = false ) {
		global $wgTranslateTasks, $wgTranslateTranslationServices;

		/**
		 * Tasks not to be available in page translation.
		 */
		$filterTasks = array(
			'optional',
			'untranslatedoptional',
			'review',
			'export-to-file',
			'export-to-xliff'
		);

		$allTasks = array_keys( $wgTranslateTasks );

		if ( $pageTranslation ) {
			foreach ( $allTasks as $id => $task ) {
				if ( in_array( $task, $filterTasks ) ) {
					unset( $allTasks[$id] );
				}
			}
		}

		if ( !isset( $wgTranslateTranslationServices['tmserver'] ) ) {
			unset( $allTasks['suggestions'] );
		}

		return $allTasks;
	}

	public static function getTask( $id ) {
		global $wgTranslateTasks;

		if ( array_key_exists( $id, $wgTranslateTasks ) ) {
			if ( is_callable( $wgTranslateTasks[$id] ) ) {
				return call_user_func( $wgTranslateTasks[$id], $id );
			} else {
				return new $wgTranslateTasks[$id];
			}
		} else {
			return null;
		}
	}
}
