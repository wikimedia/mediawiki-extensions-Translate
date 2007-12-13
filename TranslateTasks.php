<?php


class TaskOptions {
	private $language = null;
	private $limit = 0;
	private $offset = 0;
	private $pagingCB = null;

	public function __construct( $language, $limit, $offset, $pagingCB ) {
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

	protected $messageGroup = null;
	protected $messages = null;
	protected $options = null;
	public final function init( MessageGroup $messageGroup, TaskOptions $options ) {
		$this->messageGroup = $messageGroup;
		$this->options = $options;
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

		$this->messages = array_slice(
			$this->messages,
			$this->options->getOffset(),
			$this->options->getLimit()
		);

		$callback = $this->options->getPagingCB();
		call_user_func( $callback, $this->options->getOffset(), count( $this->messages ), $total );
	}

	protected function getAuthors() {
		$authors = array();
		foreach ( array_keys($this->messages) as $key ) {
			if ( !$this->messages[$key]['author'] ) continue;
			if ( !isset($authors[$this->messages[$key]['author']]) ) {
				$authors[$this->messages[$key]['author']] = 1;
			} else {
				$authors[$this->messages[$key]['author']]++;
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
		$this->messages = TranslateUtils::initializeMessages( $this->messageGroup->getDefinitions() );
		$this->messageGroup->fillBools( &$this->messages );
		
	}

	protected function filterIgnored() {
		foreach ( array_keys( $this->messages ) as $key ) {
			if ( $this->messages[$key]['ignored'] ) {
				unset( $this->messages[$key] );
			}
		}
	}

	protected function filterOptional() {
		foreach ( array_keys( $this->messages ) as $key ) {
			if ( $this->messages[$key]['optional'] ) {
				unset( $this->messages[$key] );
			}
		}
	}

	protected function postinit() {
		TranslateUtils::fillExistence( &$this->messages, $this->options->getLanguage() );
		TranslateUtils::fillContents( &$this->messages, $this->options->getLanguage() );
		$this->messageGroup->fill( &$this->messages, $this->options->getLanguage() );
		$this->messageGroup->reset();
	}

	protected function output() {
		$tableheader = TranslateUtils::tableHeader( $this->messageGroup->getLabel() );
		$tablefooter = Xml::closeElement( 'table' );

		return
			$tableheader .
			TranslateUtils::makeListing( $this->messages, $this->options->getLanguage(), $this->messageGroup->getId() ) .
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

	protected function filterTranslated() {
		foreach ( $this->messages as $key => $o ) {
			if ( ($o['database'] !== null || $o['infile'] !== null ) && !strstr($o['database'], TRANSLATE_FUZZY) ) {
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
		foreach ( array_keys( $this->messages ) as $key ) {
			if ( !$this->messages[$key]['optional'] ) {
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
			array( $this, 'filterUntranslated' ),
			array( $this, 'doPaging' ),
		);
	}

	protected function filterUntranslated() {
		foreach ( $this->messages as $key => $o ) {
			if ( $o['pageexists'] && ( $o['infile'] !== $o['database'] ) ) {
				$this->messages[$key]['changed'] = true;
			} else {
				unset( $this->messages[$key] );
			}
		}
	}

}

class ReviewAllMessagesTask extends ReviewMessagesTask {
	protected $id = 'reviewall';

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
		foreach ( $this->messages as $key => $o ) {
			$translation = $o['database'] ? $o['database'] : $o['infile'];
			if ( $o['pageexists'] || ( $translation !== null && $translation != $o['definition'] ) ) {
				$this->messages[$key]['changed'] = true;
			} else {
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

	protected function getOutputHeader() {
		$name = TranslateUtils::getLanguageName( $this->options->getLanguage() );
		$_authors = $this->getAuthors();
		arsort( $_authors, SORT_NUMERIC );
		$authors = array();
		foreach ( $_authors as $author => $edits ) {
			$authors[] = "$author - $edits";
		}
		$authors = implode( ', ', $authors );
		$file = $this->messageGroup->getMessageFile( $this->options->getLanguage() );

		$output = '';
		if ( $file ) { $output .= "# $file\n"; }
		$output .= "# $name ($authors)\n";
		return $output;
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
		return 	Xml::openElement( 'textarea', array( 'id' => 'wpTextbox1', 'rows' => '50' ) ) .
			$this->getOutputHeader() .
			$this->messageGroup->export( $this->messages, $this->options->getLanguage() ) . "\n\n\n" .
			"</textarea>";
	}
}

class ExportToFileMessagesTask extends ExportMessagesTask {
	protected $id = 'export-to-file';

	public function output() {
		global $wgOut;
		$wgOut->disable();
		header( 'Content-type: text/plain; charset=UTF-8' );
		echo
			$this->messageGroup->exportToFile(
				$this->messages,
				$this->options->getLanguage(),
				$this->getAuthorsArray()
			);
		return '';
	}
}

class TranslateTasks {
	private static $aTasks = array(
		'view'           => 'ViewMessagesTask',
		'untranslated'   => 'ViewUntranslatedTask',
		'optional'       => 'ViewOptionalTask',
		'review'         => 'ReviewMessagesTask',
		'reviewall'      => 'ReviewAllMessagesTask',
		'export'         => 'ExportMessagesTask',
		'export-to-file' => 'ExportToFileMessagesTask',
	);

	public static function getTasks() {
		return self::$aTasks;
	}

	public static function getTask( $id ) {
		if ( isset(self::$aTasks[$id]) ) {
			return new self::$aTasks[$id];
		} else {
			throw new MWException( "No task for id $id" );
		}
	}
}