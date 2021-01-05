<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use Language;
use MessageCollection;
use TMessage;
use WikiPageMessageGroup;

/**
 * Generates wikitext source code for translation pages.
 *
 * Also handles loading of translations, but that can be skipped and translations given directly.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2020.08
 */
class TranslationPage {
	/** @var ParserOutput */
	private $output;
	/** @var WikiPageMessageGroup */
	private $group;
	/** @var Language */
	private $targetLanguage;
	/** @var Language */
	private $sourceLanguage;
	/** @var bool */
	private $showOutdated;
	/** @var bool */
	private $wrapUntranslated;
	/** @var string */
	private $prefix;

	public function __construct(
		ParserOutput $output,
		WikiPageMessageGroup $group,
		Language $targetLanguage,
		Language $sourceLanguage,
		bool $showOutdated,
		bool $wrapUntranslated,
		string $prefix
	) {
		$this->output = $output;
		$this->group = $group;
		$this->targetLanguage = $targetLanguage;
		$this->sourceLanguage = $sourceLanguage;
		$this->showOutdated = $showOutdated;
		$this->wrapUntranslated = $wrapUntranslated;
		$this->prefix = $prefix;
	}

	/** Generate translation page source using default options. */
	public function generateSource(): string {
		$collection = $this->getMessageCollection();
		$this->filterMessageCollection( $collection );
		$messages = $this->extractMessages( $collection );
		return $this->generateSourceFromTranslations( $messages );
	}

	public function getMessageCollection(): MessageCollection {
		return $this->group->initCollection( $this->targetLanguage->getCode() );
	}

	public function filterMessageCollection( MessageCollection $collection ): void {
		$collection->loadTranslations();
		if ( $this->showOutdated ) {
			$collection->filter( 'hastranslation', false );
		} else {
			$collection->filter( 'translated', false );
		}
	}

	/** @return TMessage[] */
	public function extractMessages( MessageCollection $collection ): array {
		$messages = [];
		foreach ( $this->output->units() as $unit ) {
			$messages[$unit->id] = $collection[$this->prefix . $unit->id] ?? null;
		}

		return $messages;
	}

	/** @param TMessage[] $messages */
	public function generateSourceFromTranslations( array $messages ): string {
		$replacements = [];
		foreach ( $this->output->units() as $placeholder => $unit ) {
			/** @var TMessage $msg */
			$msg = $messages[$unit->id] ?? null;
			$replacements[$placeholder] = $unit->getTextForRendering(
				$msg,
				$this->sourceLanguage,
				$this->targetLanguage,
				$this->wrapUntranslated
			);
		}

		$template = $this->output->translationPageTemplate();
		return strtr( $template, $replacements );
	}
}

class_alias( TranslationPage::class, '\MediaWiki\Extensions\Translate\TranslationPage' );
