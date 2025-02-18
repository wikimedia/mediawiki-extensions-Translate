<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Utilities;

use MediaWiki\Context\RequestContext;
use MediaWiki\Xml\Xml;
use MediaWiki\Xml\XmlSelect;
use RuntimeException;

/**
 * Code for JavaScript enhanced \<option> selectors.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
final class JsSelectToInput {
	/** Id of the text field where stuff is appended */
	private string $targetId;
	private XmlSelect $select;

	public function __construct( XmlSelect $select ) {
		$this->select = $select;
	}

	public function setTargetId( string $id ) {
		$this->targetId = $id;
	}

	/**
	 * Returns the whole input element and injects needed JavaScript
	 * @return string Html code.
	 */
	public function getHtmlAndPrepareJS(): string {
		$sourceId = $this->select->getAttribute( 'id' );

		if ( !is_string( $sourceId ) ) {
			throw new RuntimeException( 'ID needs to be specified for the selector' );
		}

		RequestContext::getMain()->getOutput()->addModules( 'ext.translate.selecttoinput' );
		$html = $this->select->getHTML();
		$html .= Xml::element( 'input', [
			'type' => 'button',
			'value' => wfMessage( 'translate-jssti-add' )->text(),
			'class' => 'mw-translate-jssti',
			'data-translate-jssti-sourceid' => $sourceId,
			'data-translate-jssti-targetid' => $this->targetId
		] );

		return $html;
	}
}
