<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Utilities;

use MessageGroup;

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

	/**
	 * Helper to validate MessageGroup::getTranslatableLanguage against site configuration.
	 * @param MessageGroup $group The message group to check.
	 * @param string $languageCode Target language code.
	 * @param string|null &$reason Store the reason why the language is disabled, if applicable.
	 * @return bool True if the target language is disabled, false otherwise.
	 */
	public function isTargetLanguageDisabled(
		MessageGroup $group,
		string $languageCode,
		?string &$reason = null
	): bool {
		$globalDisabledReason = null;
		$groupId = $group->getId();
		$checks = [
			$groupId,
			strtok( $groupId, '-' ),
			'*'
		];

		$disabledLanguages = $this->getDisabledTargetLanguages();
		foreach ( $checks as $check ) {
			if ( isset( $disabledLanguages[$check][$languageCode] ) ) {
				$globalDisabledReason = $disabledLanguages[$check][$languageCode];
				break;
			}
		}

		$groupLanguages = $group->getTranslatableLanguages();

		$isLanguageDisabled = $groupLanguages === MessageGroup::DEFAULT_LANGUAGES
			? $globalDisabledReason !== null
			: !array_key_exists( $languageCode, $groupLanguages );

		// Only set the reason if the language is actually disabled
		if ( $isLanguageDisabled ) {
			$reason = $globalDisabledReason;
		}

		return $isLanguageDisabled;
	}

	public function isAuthorExcluded( string $groupId, string $languageCode, string $username ): bool {
		$hash = "$groupId;$languageCode;$username";
		$authorExclusionList = $this->getTranslateAuthorExclusionList();
		$excluded = false;

		foreach ( $authorExclusionList as [ $type, $regex ] ) {
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
