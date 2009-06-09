<?php
/**
 * Support Wikiblame: http://wikipedia.ramselehof.de/wikiblame.php.
 *
 * @addtogroup Extensions
 *
 * @copyright Copyright © 2009, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 *
 */

class WikiblameMessageGroup extends MessageGroup {
	protected $label = 'Wikiblame';
	protected $id    = 'out-wikiblame';
	protected $type  = 'wikiblame';

	protected   $fileDir  = '__BUG__';

	public function getPath() { return $this->fileDir; }
	public function setPath( $value ) { $this->fileDir = $value; }

	protected $optional = array();

	public $header = '<?php
/** Wikiblame
 *
 */';

	public function getMessageFile( $code ) {
		if ( isset( $this->codeMap[$code] ) ) {
			$code = $this->codeMap[$code];
		}
		return "$code.php";
	}

	protected function getFileLocation( $code ) {
		return $this->fileDir . '/' . $this->getMessageFile( $code );
	}

	public function getReader( $code ) {
		return new PhpVariablesFormatReader( $this->getFileLocation( $code ) );
	}

	public function getWriter() {
		return new PhpVariablesFormatWriter( $this );
	}
}
