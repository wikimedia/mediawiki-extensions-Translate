<?php
declare( strict_types=1 );

namespace MediaWiki\Extension\Translate;

use Wikimedia\Parsoid\DOM\DocumentFragment;
use Wikimedia\Parsoid\DOM\Element;
use Wikimedia\Parsoid\Ext\DOMProcessor;
use Wikimedia\Parsoid\Ext\ParsoidExtensionAPI;

class ParsoidDOMProcessor extends DOMProcessor {
	/** @inheritDoc */
	public function wtPostprocess(
		ParsoidExtensionAPI $extApi, Element|DocumentFragment $root, array $options
	): void {
		HookHandler::maybeRemoveCategories(
			$extApi->getPageConfig()->getLinkTarget(),
			$extApi->getMetadata()
		);
	}
}
