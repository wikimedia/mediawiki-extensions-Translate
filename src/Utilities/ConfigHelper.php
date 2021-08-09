<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Utilities;

/**
 * A helper class added to work with configuration values of the Translate Extension
 *
 * Also used temporarily to simplify deprecation of old configuration variables. New
 * variable names, if set, are given preference over the old ones.
 * See: https://phabricator.wikimedia.org/T277965
 *
 * @author Abijeet Patro.
 * @license GPL-2.0-or-later
 * @since 2021.06
 */
class ConfigHelper {
	/** @return bool|string */
	public function getValidationExclusionFile() {
		global $wgTranslateValidationExclusionFile;
		return $wgTranslateValidationExclusionFile;
	}

	public function getTranslateAuthorExclusionList(): array {
		global $wgTranslateAuthorExclusionList;
		return $wgTranslateAuthorExclusionList;
	}

	public function getDisabledTargetLanguages(): array {
		global $wgTranslateDisabledTargetLanguages;
		return $wgTranslateDisabledTargetLanguages;
	}

	public function isAuthorExcluded( string $groupId, string $languageCode, string $username ): bool {
		$hash = "$groupId;$languageCode;$username";
		$authorExclusionList = $this->getTranslateAuthorExclusionList();
		$excluded = false;

		foreach ( $authorExclusionList as $rule ) {
			list( $type, $regex ) = $rule;

			if ( preg_match( $regex, $hash ) ) {
				if ( $type === 'include' ) {
					return false;
				} else {
					$excluded = true;
				}
			}
		}

		return $excluded;
	}
}
