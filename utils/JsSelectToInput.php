<?php
/**
 * Code for JavaScript enhanced \<option> selectors.
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2010 Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Code for JavaScript enhanced \<option> selectors.
 */
class JsSelectToInput {
	/// Id of the text field where stuff is appended
	protected $targetId;
	/// Id of the \<option> field
	protected $sourceId;

	/**
	 * @var XmlSelect
	 */
	protected $select;

	/// Id on the button
	protected $buttonId;

	/**
	 * @var string Text for the append button
	 */
	protected $msg = 'translate-jssti-add';

	public function __construct( XmlSelect $select = null ) {
		$this->select = $select;
	}

	/// @return string
	public function getSourceId() {
		return $this->sourceId;
	}

	/**
	 * Set the id of the target text field
	 * @param string $id
	 */
	public function setTargetId( $id ) {
		$this->targetId = $id;
	}

	/**
	 * @return string
	 */
	public function getTargetId() {
		return $this->targetId;
	}

	/**
	 * Set the message key.
	 * @param string $message
	 */
	public function setMessage( $message ) {
		$this->msg = $message;
	}

	/// @return string Message key.
	public function getMessage() {
		return $this->msg;
	}

	/**
	 * Returns the whole input element and injects needed JavaScript
	 * @throws MWException
	 * @return string Html code.
	 */
	public function getHtmlAndPrepareJS() {
		$this->sourceId = $this->select->getAttribute( 'id' );

		if ( !is_string( $this->sourceId ) ) {
			throw new MWException( 'ID needs to be specified for the selector' );
		}

		self::injectJs();
		$html = $this->select->getHTML();
		$html .= $this->getButton( $this->msg, $this->sourceId, $this->targetId );

		return $html;
	}

	/**
	 * Constructs the append button.
	 * @param string $msg Message key.
	 * @param string $source Html id.
	 * @param string $target Html id.
	 * @return string
	 */
	protected function getButton( $msg, $source, $target ) {
		$html = Xml::element( 'input', array(
			'type' => 'button',
			'value' => wfMessage( $msg )->text(),
			'onclick' => Xml::encodeJsCall( 'appendFromSelect', array( $source, $target ) )
		) );

		return $html;
	}

	/// Inject needed JavaScript in the page.
	public static function injectJs() {
		static $done = false;
		if ( $done ) {
			return;
		}

		RequestContext::getMain()->getOutput()->addModules( 'ext.translate.selecttoinput' );
	}
}
