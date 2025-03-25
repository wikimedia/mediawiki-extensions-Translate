<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Aid;

use MediaWiki\Extension\Translate\TranslatorInterface\TranslationHelperException;

/**
 * Translation aid that suggests insertables. Insertable is a string that
 * usually does not need translation and is difficult to type manually.
 * @ingroup TranslationAids
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2013.09
 */
class InsertablesAid extends TranslationAid {
	public function getData(): array {
		// We need to get the primary group to get the correct file
		// So $group can be different from $this->group
		$group = $this->handle->getGroup();
		if ( !$group ) {
			throw new TranslationHelperException(
				'Message handle ' . $this->handle->getTitle()->getPrefixedDbKey() . ' has no associated group'
			);
		}

		// This was added later, so not all classes have it. In addition
		// the message group class hierarchy doesn't lend itself easily
		// to the user of interfaces for this purpose.
		if ( !method_exists( $group, 'getInsertablesSuggester' ) ) {
			throw new TranslationHelperException( 'Group does not have insertable suggesters' );
		}

		// @phan-suppress-next-line PhanUndeclaredMethod
		$suggester = $group->getInsertablesSuggester();

		// It is okay to return null suggester
		if ( !$suggester ) {
			throw new TranslationHelperException( 'Group does not have insertable suggesters' );
		}

		$insertables = $suggester->getInsertables( $this->dataProvider->getDefinition() );
		$blob = [];
		foreach ( $insertables as $insertable ) {
			$displayText = $insertable->getDisplayText();

			// The keys are used for de-duplication
			$blob[$displayText] = [
				'display' => $displayText,
				'pre' => $insertable->getPreText(),
				'post' => $insertable->getPostText(),
			];
		}

		$blob = array_values( $blob );
		$blob['**'] = 'insertable';

		return $blob;
	}
}
