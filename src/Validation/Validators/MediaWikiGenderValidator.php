<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation\Validators;

use MediaWiki\Extension\Translate\MessageLoading\Message;
use MediaWiki\Extension\Translate\Validation\MessageValidator;
use MediaWiki\Extension\Translate\Validation\ValidationIssue;
use MediaWiki\Extension\Translate\Validation\ValidationIssues;
use MediaWiki\Language\LanguageFactory;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\ParserFactory;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Parser\PPFrame;
use MediaWiki\User\UserFactory;

/**
 * Warns about redundant forms in MediaWiki {{GENDER:}} syntax.
 *
 * Redundant forms are:
 * - Two forms that are equal: {{GENDER:$1|foo|foo}} should be {{GENDER:$1|foo}}
 * - Three forms where the first equals the third: {{GENDER:$1|foo|bar|foo}} should be
 *   {{GENDER:$1|foo|bar}}
 *
 * @license GPL-2.0-or-later
 * @since 2026.09
 */
readonly class MediaWikiGenderValidator implements MessageValidator {

	public function __construct(
		private LanguageFactory $languageFactory,
		private ParserFactory $parserFactory,
		private UserFactory $userFactory,
	) {
	}

	public function getIssues( Message $message, string $targetLanguage ): ValidationIssues {
		$issues = new ValidationIssues();

		$translation = $message->translation();
		if ( stripos( $translation, '{{gender:' ) === false ) {
			return $issues;
		}

		foreach ( $this->getGenderForms( $translation ) as $forms ) {
			$count = count( $forms );
			if ( $count === 2 && $forms[0] === $forms[1] ) {
				$issues->add( new ValidationIssue( 'gender', 'dupe', 'translate-checks-gender-dupe' ) );
			} elseif ( $count === 3 && $forms[0] === $forms[2] ) {
				$issues->add( new ValidationIssue( 'gender', 'dupe', 'translate-checks-gender-dupe' ) );
			}
		}

		return $issues;
	}

	/**
	 * Parses {{GENDER:}} instances from a translation and returns an array of invocations,
	 * each being an array of forms (excluding the username parameter).
	 *
	 * @return array[]
	 */
	public function getGenderForms( string $translation ): array {
		$genders = [];

		$cb = static function ( $parser, $frame, $args ) use ( &$genders ) {
			$forms = [];
			foreach ( $args as $index => $form ) {
				// The first arg is the username, skip it
				if ( $index !== 0 ) {
					$forms[] = $frame->expand( $form, PPFrame::RECOVER_ORIG );
				}
			}
			$genders[] = $forms;

			return '';
		};

		$parser = $this->parserFactory->create();
		$parser->setFunctionHook( 'gender', $cb, Parser::SFH_NO_HASH | Parser::SFH_OBJECT_ARGS );

		$options = ParserOptions::newFromUserAndLang(
			$this->userFactory->newAnonymous(),
			$this->languageFactory->getLanguage( 'en' )
		);

		$parser->preprocess( $translation, null, $options );

		return $genders;
	}
}
