<?php
/**
 * @todo Needs documentation.
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2010 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * @todo Needs documentation.
 */
class JsSelectToInput {
	protected $targetId, $sourceId;
	protected $select;
	protected $buttonId;
	protected $msg = 'translate-jssti-add';

	public function __construct( XmlSelect $select = null ) {
		$this->select = $select;
	}

	public function setSourceId( $id ) {
		$this->sourceId = $id;
	}

	public function getSourceId() {
		return $this->sourceId;
	}

	public function setTargetId( $id ) {
		$this->targetId = $id;
	}

	public function getTargetId() {
		return $this->targetId;
	}

	public function setMessage( $message ) {
		$this->msg = $message;
	}

	public function getMessage() {
		return $this->msg;
	}

	public function getHtmlAndPrepareJS() {
		if ( $this->sourceId === false ) {
			if ( is_callable( array( $select, 'getAttribute' ) ) ) {
				$this->sourceId = $select->getAttribute['id'];
			}

			if ( !$this->sourceId ) {
				throw new MWException( "ID needs to be specified for the selector" );
			}
		}

		self::injectJs();
		$html = $this->getButton( $this->msg, $this->sourceId, $this->targetId );
		$html .= $this->select->getHtml();

		return $html;
	}

	protected function getButton( $msg, $source, $target ) {
		$source = Xml::escapeJsString( $source );
		$target = Xml::escapeJsString( $target );
		$html = Xml::element( 'input', array(
			'type' => 'button',
			'value' => wfMsg( $msg ),
			'onclick' => "appendFromSelect( '$source', '$target' );"
		) );

		return $html;
	}

	public static function injectJs() {
		static $done = false;

		if ( $done ) {
			return;
		}

		global $wgOut;

		$wgOut->addScriptFile( TranslateUtils::assetPath( 'utils/JsSelectToInput.js' ) );
	}
}
