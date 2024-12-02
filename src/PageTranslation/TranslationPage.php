<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Content\Content;
use MediaWiki\Content\ContentHandler;
use MediaWiki\Extension\Translate\MessageLoading\Message;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;
use MediaWiki\Language\Language;
use MediaWiki\Parser\Parser;
use MediaWiki\Title\Title;
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
	private ParserOutput $output;
	private WikiPageMessageGroup $group;
	private Language $targetLanguage;
	private Language $sourceLanguage;
	private bool $showOutdated;
	private bool $wrapUntranslated;
	private Title $sourcePageTitle;

	public function __construct(
		ParserOutput $output,
		WikiPageMessageGroup $group,
		Language $targetLanguage,
		Language $sourceLanguage,
		bool $showOutdated,
		bool $wrapUntranslated,
		Title $sourcePageTitle
	) {
		$this->output = $output;
		$this->group = $group;
		$this->targetLanguage = $targetLanguage;
		$this->sourceLanguage = $sourceLanguage;
		$this->showOutdated = $showOutdated;
		$this->wrapUntranslated = $wrapUntranslated;
		$this->sourcePageTitle = $sourcePageTitle;
	}

	/** @since 2021.07 */
	public function getPageContent( Parser $parser, ?int &$percentageTranslated = null ): Content {
		$text = $this->generateSource( $parser, $percentageTranslated );
		$model = $this->sourcePageTitle->getContentModel();
		return ContentHandler::makeContent( $text, null, $model );
	}

	public function getMessageCollection(): MessageCollection {
		return $this->group->initCollection( $this->targetLanguage->getCode() );
	}

	public function filterMessageCollection( MessageCollection $collection ): void {
		$collection->loadTranslations();
		if ( $this->showOutdated ) {
			$collection->filter( MessageCollection::FILTER_HAS_TRANSLATION, MessageCollection::INCLUDE_MATCHING );
		} else {
			$collection->filter( MessageCollection::FILTER_TRANSLATED, MessageCollection::INCLUDE_MATCHING );
		}
	}

	/** @return Message[] */
	private function extractMessages( MessageCollection $collection ): array {
		$messages = [];
		$prefix = $this->sourcePageTitle->getPrefixedDBkey() . '/';
		foreach ( $this->output->units() as $unit ) {
			// Even if a unit id has spaces, the message collection will have the
			// key as spaces replaced with underscore. See: T326516
			$normalizedUnitId = str_replace( ' ', '_', $unit->id );
			$messages[$unit->id] = $collection[$prefix . $normalizedUnitId] ?? null;
		}

		return $messages;
	}

	/**
	 * @param Parser $parser
	 * @param Message[] $messages
	 */
	public function generateSourceFromTranslations( Parser $parser, array $messages ): string {
		return $this->output->getPageTextForRendering(
			$this->sourceLanguage,
			$this->targetLanguage,
			$this->wrapUntranslated,
			$messages,
			$parser
		);
	}

	public function generateSourceFromMessageCollection(
		Parser $parser,
		MessageCollection $collection
	): string {
		$messages = $this->extractMessages( $collection );
		return $this->generateSourceFromTranslations( $parser, $messages );
	}

	/** Generate translation page source using default options. */
	private function generateSource( Parser $parser, ?int &$percentageTranslated = null ): string {
		$collection = $this->getMessageCollection();
		$allKeys = count( $collection );
		$this->filterMessageCollection( $collection );
		$keysWithTranslation = count( $collection );

		if ( $allKeys === 0 ) {
			$percentageTranslated = 0;
		} else {
			$percentageTranslated = (int)( ( $keysWithTranslation / $allKeys ) * 100 );
		}

		$messages = $this->extractMessages( $collection );
		return $this->generateSourceFromTranslations( $parser, $messages );
	}
}
