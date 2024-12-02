<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation\Validators;

use MediaWiki\Extension\Translate\MessageLoading\Message;
use MediaWiki\Extension\Translate\Validation\MessageValidator;
use MediaWiki\Extension\Translate\Validation\ValidationIssue;
use MediaWiki\Extension\Translate\Validation\ValidationIssues;
use MediaWiki\Languages\LanguageFactory;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\ParserFactory;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Parser\PPFrame;
use MediaWiki\User\UserFactory;

/**
 * Handles plural validation for MediaWiki inline plural syntax.
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2019.06
 */
class MediaWikiPluralValidator implements MessageValidator {
	/** @var LanguageFactory */
	private $languageFactory;
	/** @var ParserFactory */
	private $parserFactory;
	/** @var UserFactory */
	private $userFactory;

	public function __construct(
		LanguageFactory $languageFactory,
		ParserFactory $parserFactory,
		UserFactory $userFactory
	) {
		$this->languageFactory = $languageFactory;
		$this->parserFactory = $parserFactory;
		$this->userFactory = $userFactory;
	}

	public function getIssues( Message $message, string $targetLanguage ): ValidationIssues {
		$issues = new ValidationIssues();
		$this->pluralCheck( $message, $issues );
		$this->pluralFormsCheck( $message, $targetLanguage, $issues );

		return $issues;
	}

	private function pluralCheck( Message $message, ValidationIssues $issues ): void {
		$definition = $message->definition();
		$translation = $message->translation();

		if (
			stripos( $definition, '{{plural:' ) !== false &&
			stripos( $translation, '{{plural:' ) === false
		) {
			$issue = new ValidationIssue( 'plural', 'missing', 'translate-checks-plural' );
			$issues->add( $issue );
		}
	}

	protected function pluralFormsCheck(
		Message $message, string $code, ValidationIssues $issues
	): void {
		$translation = $message->translation();
		// Are there any plural forms for this language in this message?
		if ( stripos( $translation, '{{plural:' ) === false ) {
			return;
		}

		$plurals = $this->getPluralForms( $translation );
		$allowed = $this->getPluralFormCount( $code );

		foreach ( $plurals as $forms ) {
			$forms = self::removeExplicitPluralForms( $forms );
			$provided = count( $forms );

			if ( $provided > $allowed ) {
				$issue = new ValidationIssue(
					'plural',
					'forms',
					'translate-checks-plural-forms',
					[
						[ 'COUNT', $provided ],
						[ 'COUNT', $allowed ],
					]
				);

				$issues->add( $issue );
			}

			// Are the last two forms identical?
			if ( $provided > 1 && $forms[$provided - 1] === $forms[$provided - 2] ) {
				$issue = new ValidationIssue( 'plural',	'dupe', 'translate-checks-plural-dupe' );
				$issues->add( $issue );
			}
		}
	}

	/** Returns the number of plural forms %MediaWiki supports for a language. */
	public function getPluralFormCount( string $code ): int {
		$forms = $this->languageFactory->getLanguage( $code )->getPluralRules();

		// +1 for the 'other' form
		return count( $forms ) + 1;
	}

	/**
	 * Ugly home made probably awfully slow looping parser that parses {{PLURAL}} instances from
	 * a message and returns array of invocations having array of forms.
	 *
	 * @return array[]
	 */
	public function getPluralForms( string $translation ): array {
		// Stores the forms from plural invocations
		$plurals = [];

		$cb = static function ( $parser, $frame, $args ) use ( &$plurals ) {
			$forms = [];

			foreach ( $args as $index => $form ) {
				// The first arg is the number, we skip it
				if ( $index !== 0 ) {
					// Collect the raw text
					$forms[] = $frame->expand( $form, PPFrame::RECOVER_ORIG );
					// Expand the text to process embedded plurals
					$frame->expand( $form );
				}
			}
			$plurals[] = $forms;

			return '';
		};

		// Setup parser
		$parser = $this->parserFactory->create();
		$parser->setFunctionHook( 'plural', $cb, Parser::SFH_NO_HASH | Parser::SFH_OBJECT_ARGS );

		// Setup things needed for preprocess
		$title = null;
		$options = ParserOptions::newFromUserAndLang(
			$this->userFactory->newAnonymous(),
			$this->languageFactory->getLanguage( 'en' )
		);

		$parser->preprocess( $translation, $title, $options );

		return $plurals;
	}

	/** Remove forms that start with an explicit number. */
	public static function removeExplicitPluralForms( array $forms ): array {
		// Handle explicit 0= and 1= forms
		foreach ( $forms as $index => $form ) {
			if ( preg_match( '/^[0-9]+=/', $form ) ) {
				unset( $forms[$index] );
			}
		}

		return array_values( $forms );
	}
}
