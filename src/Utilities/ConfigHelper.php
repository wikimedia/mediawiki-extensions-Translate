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
		global $wgTranslateCheckBlacklist, $wgTranslateValidationExclusionFile;

		if ( $wgTranslateValidationExclusionFile !== false ) {
			return $wgTranslateValidationExclusionFile;
		}

		return $wgTranslateCheckBlacklist;
	}

	public function getTranslateAuthorExclusionList(): array {
		global $wgTranslateAuthorBlacklist, $wgTranslateAuthorExclusionList;

		if ( $wgTranslateAuthorExclusionList !== [] ) {
			return $wgTranslateAuthorExclusionList;
		}

		return $wgTranslateAuthorBlacklist;
	}

	public function getDisabledTargetLanguages(): array {
		global $wgTranslateBlacklist, $wgTranslateDisabledTargetLanguages;

		if ( $wgTranslateDisabledTargetLanguages !== [] ) {
			return $wgTranslateDisabledTargetLanguages;
		}

		return $wgTranslateBlacklist;
	}

	public function isAuthorExcluded( string $groupId, string $languageCode, string $username ): bool {
		$hash = "$groupId;$languageCode;$username";
		$authorExclusionList = $this->getTranslateAuthorExclusionList();
		$excluded = false;

		foreach ( $authorExclusionList as $rule ) {
			list( $type, $regex ) = $rule;

			if ( preg_match( $regex, $hash ) ) {
				if ( $type === 'white' || $type === 'include' ) {
					return false;
				} else {
					$excluded = true;
				}
			}
		}

		return $excluded;
	}
}
