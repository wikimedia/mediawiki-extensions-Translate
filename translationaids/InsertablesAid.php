<?php
/**
 * Translation aid provider.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Translation aid which suggests insertables. Insertable is a string that
 * usually does not need translation and is difficult to type manually.
 *
 * @ingroup TranslationAids
 * @since 2013.09
 */
class InsertablesAid extends TranslationAid {
	public function getData() {

		// We need to get the primary group to get the correct file
		// So $group can be different from $this->group
		$group = $this->handle->getGroup();

		// This was added later, so not all classes have it. In addition
		// the message group class hierarche doesn't lend itself easily
		// to the user of interfaces for this purpose.
		if ( !method_exists( $group, 'getInsertablesSuggester' ) ) {
			throw new TranslationHelperException( 'Group does not have a suggester' );
		}

		$suggester = $group->getInsertablesSuggester();

		// It is okay to return null suggester
		if ( !$suggester ) {
			throw new TranslationHelperException( 'Group does not have a suggester' );
		}

		$insertables = $suggester->getInsertables( $this->getDefinition() );
		$blob = array();
		foreach ( $insertables as $insertable ) {
			$displayText = $insertable->getDisplayText();

			// The keys are used for de-duplication
			$blob[$displayText] = array(
				'display' => $displayText,
				'pre' => $insertable->getPreText(),
				'post' => $insertable->getPostText(),
			);
		}

		$blob = array_values( $blob );
		$blob['**'] = 'insertable';

		return $blob;
	}
}
