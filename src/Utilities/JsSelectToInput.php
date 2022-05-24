<?php
declare( strict_types=1 );

namespace MediaWiki\Extension\Translate\Utilities;

use MWException;
use RequestContext;
use Xml;
use XmlSelect;

/**
 * Code for JavaScript enhanced \<option> selectors.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
class JsSelectToInput {
	/// Id of the text field where stuff is appended
	protected $targetId;
	/// Id of the \<option> field
	protected $sourceId;
	/** @var XmlSelect */
	protected $select;
	/// Id on the button
	protected $buttonId;
	/** @var string Text for the append button */
	protected $msg = 'translate-jssti-add';

	public function __construct( XmlSelect $select = null ) {
		$this->select = $select;
	}

	public function getSourceId(): string {
		return $this->sourceId;
	}

	public function setTargetId( string $id ) {
		$this->targetId = $id;
	}

	public function getTargetId(): string {
		return $this->targetId;
	}

	/** Set the message key. */
	public function setMessage( string $message ): void {
		$this->msg = $message;
	}

	/** @return string a message key. */
	public function getMessage(): string {
		return $this->msg;
	}

	/**
	 * Returns the whole input element and injects needed JavaScript
	 * @throws MWException
	 * @return string Html code.
	 */
	public function getHtmlAndPrepareJS(): string {
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
	protected function getButton( string $msg, string $source, string $target ): string {
		$html = Xml::element( 'input', [
			'type' => 'button',
			'value' => wfMessage( $msg )->text(),
			'class' => 'mw-translate-jssti',
			'data-translate-jssti-sourceid' => $source,
			'data-translate-jssti-targetid' => $target
		] );

		return $html;
	}

	/** Inject needed JavaScript in the page. */
	public static function injectJs(): void {
		static $done = false;
		if ( $done ) {
			return;
		}

		RequestContext::getMain()->getOutput()->addModules( 'ext.translate.selecttoinput' );
	}
}
