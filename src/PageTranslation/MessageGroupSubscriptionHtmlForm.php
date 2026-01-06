<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\HTMLForm\OOUIHTMLForm;

class MessageGroupSubscriptionHtmlForm extends OOUIHTMLForm {
	/** @inheritDoc */
	public function getLegend( $namespace ) {
		$namespace = (int)substr( $namespace, 2 );
		return $namespace == NS_MAIN
			? $this->msg( 'blanknamespace' )->text()
			: $this->getContext()->getLanguage()->getFormattedNsText( $namespace );
	}

	/** @inheritDoc */
	public function displaySection(
		$fields, $sectionName = '', $fieldsetIDPrefix = '', &$hasUserVisibleFields = false
	) {
		return parent::displaySection( $fields, $sectionName, 'translate-mmgs-', $hasUserVisibleFields );
	}
}
