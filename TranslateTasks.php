<?php
if (!defined('MEDIAWIKI')) die();
/**
 * Different tasks which encapsulates processing of messages to requested
 * format.
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2007-2008 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
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

	/* We need $id here because staticness prevents subclass overriding */
	public static function labelForTask( $id ) {
		wfLoadExtensionMessages( 'Translate' );
		return wfMsg( TranslateUtils::MSG . 'task-' . $id );
	}

	public function getId() {
		return $this->id;
	}

	public function plainOutput() {
		return false;
	}

	protected $messageGroup = null;
	protected $messages = null;
	protected $options = null;
	public final function init( MessageGroup $messageGroup, TaskOptions $options ) {
		$this->messageGroup = $messageGroup;
		$this->options = $options;
		$this->messages = new MessageCollection( $this->options->getLanguage() );
	}

	protected $process = array();
	abstract protected function setProcess();
	abstract protected function output();

	public final function execute() {
		$this->setProcess();
		foreach ( $this->process as $function ) {
			call_user_func( $function );
		}
		return $this->output();
	}

	protected function doPaging() {
		$total = count( $this->messages );

		$this->messages->slice(
			$this->options->getOffset(),
			$this->options->getLimit()
		);

		$callback = $this->options->getPagingCB();
		call_user_func( $callback, $this->options->getOffset(), count( $this->messages ), $total );
	}

	protected function getAuthors() {
		$authors = array();
		foreach ( $this->messages->keys() as $key ) {
			// Check if there is authors
			$_authors = $this->messages[$key]->authors;
			if ( !count($_authors) ) continue;

			foreach ( $_authors as $author ) {
				if ( !isset($authors[$author]) ) {
					$authors[$author] = 1;
				} else {
					$authors[$author]++;
				}
			}
		}
		return $authors;
	}

}

class ViewMessagesTask extends TranslateTask {
	protected $id = 'view';

	protected function setProcess() {
		$this->process = array(
			array( $this, 'preinit' ),
			array( $this, 'filterIgnored' ),
			array( $this, 'filterOptional' ),
			array( $this, 'doPaging' ),
			array( $this, 'postinit' ),
		);
	}

	protected function preinit() {
		$definitions = $this->messageGroup->getDefinitions();
		foreach ( $definitions as $key => $definition ) {
			$this->messages->add( new TMessage( $key, $definition ) );
		}

		$bools = $this->messageGroup->getBools();
		foreach ( $bools['optional'] as $key ) {
			if ( isset($this->messages[$key]) ) {
				$this->messages[$key]->optional = true;
			}
		}

		foreach ( $bools['ignored'] as $key ) {
			if ( isset($this->messages[$key]) ) {
				$this->messages[$key]->ignored = true;
			}
		}

	}

	protected function filterIgnored() {
		foreach ( $this->messages->keys() as $key ) {
			if ( $this->messages[$key]->ignored ) {
				unset($this->messages[$key]);
			}
		}
	}

	protected function filterOptional() {
		foreach ( $this->messages->keys() as $key ) {
			if ( $this->messages[$key]->optional ) {
				unset( $this->messages[$key] );
			}
		}
	}

	protected function postinit() {
		$this->messages->populatePageExistence();
		$this->messages->populateTranslationsFromDatabase();
		$this->messageGroup->fill( $this->messages );
	}

	protected function output() {
		$tableheader = TranslateUtils::tableHeader( $this->messageGroup->getLabel() );
		$tablefooter = Xml::closeElement( 'table' );

		return
			$tableheader .
			TranslateUtils::makeListing(
				$this->messages,
				$this->messageGroup->getId()
			) .
			$tablefooter;
	}

}

class ViewUntranslatedTask extends ViewMessagesTask {
	protected $id = 'untranslated';

	protected function setProcess() {
		$this->process = array(
			array( $this, 'preinit' ),
			array( $this, 'filterIgnored' ),
			array( $this, 'filterOptional' ),
			array( $this, 'postinit' ),
			array( $this, 'filterTranslated' ),
			array( $this, 'doPaging' ),
		);
	}

	/**
	 * Filters all translated messages. Fuzzy messages are not considered to be
	 * translated, because they need attention from translators.
	 */
	protected function filterTranslated() {
		foreach ( $this->messages->keys() as $key ) {
			if ( $this->messages[$key]->translation !== null && !$this->messages[$key]->fuzzy ) {
				unset( $this->messages[$key] );
			}
		}
	}

}

class ViewOptionalTask extends ViewMessagesTask {
	protected $id = 'optional';

	protected function setProcess() {
		$this->process = array(
			array( $this, 'preinit' ),
			array( $this, 'filterIgnored' ),
			array( $this, 'filterNonOptional' ),
			array( $this, 'doPaging' ),
			array( $this, 'postinit' ),
		);
	}

	protected function filterNonOptional() {
		foreach ( $this->messages->keys() as $key ) {
			if ( !$this->messages[$key]->optional ) {
				unset( $this->messages[$key] );
			}
		}
	}

}

class ReviewMessagesTask extends ViewMessagesTask {
	protected $id = 'review';

	protected function setProcess() {
		$this->process = array(
			array( $this, 'preinit' ),
			array( $this, 'filterIgnored' ),
			array( $this, 'postinit' ),
			array( $this, 'filterUnchanged' ),
			array( $this, 'doPaging' ),
		);
	}

	protected function filterUnchanged() {
		foreach ( $this->messages->keys() as $key ) {
			if ( !$this->messages[$key]->changed ) {
				unset( $this->messages[$key] );
			}
		}
	}

	protected function output() {
		$tableheader = TranslateUtils::tableHeader( $this->messageGroup->getLabel() );
		$tablefooter = Xml::closeElement( 'table' );

		return
			$tableheader .
			TranslateUtils::makeListing(
				$this->messages,
				$this->messageGroup->getId(),
				true /* Review mode */
			) .
			$tablefooter;
	}

}

class ReviewAllMessagesTask extends ReviewMessagesTask {
	protected $id = 'reviewall';

	protected function setProcess() {
		$this->process = array(
			array( $this, 'preinit' ),
			array( $this, 'filterIgnored' ),
			array( $this, 'postinit' ),
			array( $this, 'filterUntranslated' ),
			array( $this, 'doPaging' ),
		);
	}

	protected function filterUntranslated() {
		foreach ( $this->messages->keys() as $key ) {
			if ( !$this->messages[$key]->translated ) {
				unset( $this->messages[$key] );
			}
		}
	}

}

class ExportMessagesTask extends ViewMessagesTask {
	protected $id = 'export';

	protected function setProcess() {
		$this->process = array(
			array( $this, 'preinit' ),
			array( $this, 'filterIgnored' ),
			array( $this, 'postinit' ),
		);
	}

	protected function getAuthorsArray() {
		global $wgTranslateFuzzyBotName;
		$_authors = $this->getAuthors();
		arsort( $_authors, SORT_NUMERIC );
		foreach ( $_authors as $author => $edits ) {
			if ( $author !== $wgTranslateFuzzyBotName ) {
				$authors[] = $author;
			}
		}
		return isset($authors) ? $authors : array();
	}

	public function output() {
		$writer = $this->messageGroup->getWriter();
		$writer->addAuthors( $this->getAuthorsArray(), $this->options->getLanguage() );
		$data = $writer->webExport( $this->messages );

		return Xml::openElement( 'textarea', array( 'id' => 'wpTextbox1', 'rows' => '50' ) ) .
			$data .
			"</textarea>";
	}
}

class ExportToFileMessagesTask extends ExportMessagesTask {
	protected $id = 'export-to-file';

	public function plainOutput() {
		return true;
	}

	public function output() {
		$writer = $this->messageGroup->getWriter();
		$writer->addAuthors( $this->getAuthorsArray(), $this->options->getLanguage() );
		return $writer->webExport( $this->messages );
	}
}

class ExportAsPoMessagesTask extends ExportMessagesTask {
	protected $id = 'export-as-po';

	public function plainOutput() {
		return true;
	}

	public function output() {
		global $IP, $wgServer, $wgTranslateDocumentationLanguageCode;

		$lang = Language::factory( 'en' );

		$out = '';
		$now = wfTimestampNow();
		$label = $this->messageGroup->getLabel();
		$code = $this->options->getLanguage();
		$languageName = TranslateUtils::getLanguageName( $code );

		$filename = $code . '_' . $this->messageGroup->getID() . '.po';
		header( "Content-Disposition: attachment; filename=\"$filename\"" );

		$headers = array();
		$headers['Project-Id-Version'] = 'MediaWiki ' . SpecialVersion::getVersion();
		// TODO: make this customisable or something
		$headers['Report-Msgid-Bugs-To'] = $wgServer;
		// TODO: sprintfDate doesn't support any time zone flags
		$headers['POT-Creation-Date'] = $lang->sprintfDate( 'xnY-xnm-xnd xnH:xni:xns+0000', $now );
		$headers['Language-Team'] = TranslateUtils::getLanguageName( $this->options->getLanguage() );
		$headers['Content-Type'] = 'text-plain; charset=UTF-8';
		$headers['Content-Transfer-Encoding'] = '8bit';
		$headers['X-Generator'] = 'MediaWiki Translate extension ' . TRANSLATE_VERSION;
		$headers['X-Language-Code'] = $this->options->getLanguage();
		$headers['X-Message-Group'] = $this->messageGroup->getId();

		$headerlines = array('');
		foreach ( $headers as $key => $value ) {
			$headerlines[] = "$key: $value\n";
		}

		$out .= "# Translation of $label to $languageName\n";
		$out .= self::formatmsg( '', $headerlines  );

		foreach ( $this->messages as $key => $m) {
			$flags = array();

			# CASE1: ignored
			if ( $m->ignored ) continue;

			$translation = $m->translation;
			# CASE2: no translation
			if ( $translation === null ) $translation = '';

			# CASE3: optional messages; accept only if different
			if ( $m->optional ) $flags[] = 'optional';

			# CASE4: don't export non-translations unless translated in wiki
			if( !$m->pageExists && $translation === $m->definition ) $translation = '';

			# Remove fuzzy markings before export
			if ( strpos( $translation, TRANSLATE_FUZZY ) !== false ) {
				$translation = str_replace( TRANSLATE_FUZZY, '', $translation );
				$flags[] = 'fuzzy';
			}

			$comments = '';
			if ( $wgTranslateDocumentationLanguageCode ) {
				$documentation = TranslateUtils::getMessageContent( $key, $wgTranslateDocumentationLanguageCode );
				if ( $documentation ) $comments = $documentation;
			}

			$out .= self::formatcomments( $comments, $flags );
			$out .= self::formatmsg( $m->definition, $translation, $key, $flags );

		}

		return $out;
	}

	private static function escape( $line ) {
		$line = addcslashes( $line, '\\"' );
		$line = str_replace( "\n", '\n', $line );
		$line = '"' . $line . '"';
		return $line;
	}

	private static function formatcomments( $comments = false, $flags = false ) {
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

		if ( !is_array( $msgid ) ) { $msgid = array( $msgid ); }
		if ( !is_array( $msgstr ) ) { $msgstr = array( $msgstr ); }
		$output[] = 'msgid ' . implode( "\n", array_map( array( __CLASS__, 'escape' ), $msgid ) );
		$output[] = 'msgstr ' . implode( "\n", array_map( array( __CLASS__, 'escape' ), $msgstr ) );

		$out = implode( "\n", $output ) . "\n\n";
		return $out;

	}
}

class TranslateTasks {
	public static function getTasks() {
		global $wgTranslateTasks;
		return array_keys( $wgTranslateTasks );
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
