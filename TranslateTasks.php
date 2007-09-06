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
	abstract function getId();
	public function getLabel() {
		wfLoadExtensionMessages( 'Translate' );
		return wfMsg( TranslateUtils::MSG . 'task-' . $this->getId() );
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

	private $total = 0;
	protected function doPaging() {
		$this->total = count( $this->messages );

		$this->messages = &array_slice(
			$this->messages,
			$this->options->getOffset(),
			$this->options->getLimit()
		);

		$callback = $this->options->getPagingCB();
		call_user_func( $callback, $this->options->getOffset(), count( $this->messages ), $this->total );
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

	public function getId() {
		return 'view';
	}

	protected function setProcess() {
		$this->process = array(
			array( $this, 'preinit' ),
			array( $this, 'filterIgnored' ),
			array( $this, 'doPaging' ),
			array( $this, 'postinit' ),
		);
	}

	protected function preinit() {
		$this->messages = TranslateUtils::initializeMessages( $this->messageGroup->getDefinitions() );
		$this->messageGroup->fillBools( &$this->messages );
		
	}

	protected function filterIgnored() {
		foreach ( $this->messages as $key => $null ) {
			if ( $this->messages[$key]['ignored'] ) {
				unset( $this->messages[$key] );
			}
		}
	}

	protected function postinit() {
		TranslateUtils::fillExistence( &$this->messages, $this->options->getLanguage() );
		TranslateUtils::fillContents( &$this->messages, $this->options->getLanguage() );
		$this->messageGroup->fill( &$this->messages, $this->options->getLanguage() );
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

	public function getId() {
		return 'untranslated';
	}

	protected function setProcess() {
		$this->process = array(
			array( $this, 'preinit' ),
			array( $this, 'filterIgnored' ),
			array( $this, 'postinit' ),
			array( $this, 'filterTranslated' ),
			array( $this, 'doPaging' ),
		);
	}

	protected function filterTranslated() {
		foreach ( $this->messages as $key => $o ) {
			if ( ($o['database'] !== null || $o['infile'] !== null ) && !strstr($o['infile'], '!!FUZZY!!') ) {
				unset( $this->messages[$key] );
			}
		}
	}


}

class ReviewMessagesTask extends ViewMessagesTask {

	public function getId() {
		return 'review';
	}

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

	public function getId() {
		return 'reviewall';
	}

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

	public function getId() {
		return 'export';
	}

	protected function setProcess() {
		$this->process = array(
			array( $this, 'preinit' ),
			array( $this, 'filterIgnored' ),
			array( $this, 'postinit' ),
		);
	}

	protected function getOutputHeader() {
		$names = Language::getLanguageNames( );
		$name = $names[$this->options->getLanguage()];
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

	public function output() {
		return 	Xml::openElement( 'textarea', array( 'id' => 'wpTextbox1', 'rows' => '50' ) ) .
			$this->getOutputHeader() .
			$this->messageGroup->export( $this->messages, $this->options->getLanguage() ) . "\n\n\n" .
			"</textarea>";
	}
}

class ExportToFileMessagesTask extends ExportMessagesTask {

	public function getId() {
		return 'export-to-file';
	}

	public function output() {
		global $wgOut;
		$wgOut->disable();
		header( 'Content-type: text/plain; charset=UTF-8' );
		echo
			$this->getOutputHeader() .
			$this->messageGroup->export( $this->messages, $this->options->getLanguage() );
		return '';
	}
}

class TranslateTasks {
	private static $tasks = null;

	private function init() {
		$tasks = array();
		$tasks[] = new ViewMessagesTask();
		$tasks[] = new ViewUntranslatedTask();
		$tasks[] = new ReviewMessagesTask();
		$tasks[] = new ReviewAllMessagesTask();
		$tasks[] = new ExportMessagesTask();
		$tasks[] = New ExportToFileMessagesTask();
		self::$tasks = $tasks;
	}

	public static function &tasks() {
		if ( self::$tasks === null ) {
			self::init();
		}
		return self::$tasks;
	}

}