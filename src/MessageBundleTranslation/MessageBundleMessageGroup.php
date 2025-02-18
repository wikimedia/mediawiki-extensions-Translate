<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use LogicException;
use MediaWiki\Context\IContextSource;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupStates;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;
use MediaWiki\Extension\Translate\MessageLoading\MessageDefinitions;
use MediaWiki\Extension\Translate\MessageProcessing\StringMatcher;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Validation\ValidationRunner;
use MediaWiki\Linker\LinkTarget;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;
use MessageGroup;
use const NS_TRANSLATIONS;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2021.12
 */
class MessageBundleMessageGroup implements MessageGroup {
	/** Name of the bundle (prefixed text of the bundle page) */
	private string $name;
	private string $groupId;
	private int $pageId;
	private int $revisionId;
	private ?array $data = null;
	private ?string $description;
	private ?string $label;
	private ?Title $title;

	public function __construct(
		string $groupId,
		string $name,
		int $pageId,
		int $revisionId,
		?string $description,
		?string $label
	) {
		$this->groupId = $groupId;
		$this->name = $name;
		$this->pageId = $pageId;
		$this->revisionId = $revisionId;
		$this->description = $description;
		$this->label = $label;
	}

	/** Suggested default naming pattern */
	public static function getGroupId( string $name ): string {
		return "messagebundle-$name";
	}

	public function getBundlePageId(): int {
		return $this->pageId;
	}

	private function getData(): array {
		if ( $this->data !== null ) {
			return $this->data;
		}

		$revisionStore = MediaWikiServices::getInstance()->getRevisionStore();
		$revision = $revisionStore->getRevisionById( $this->revisionId );

		if ( $revision === null ) {
			throw new LogicException( "Could not find revision id $this->revisionId" );
		}

		$content = $revision->getContent( SlotRecord::MAIN );
		if ( !$content instanceof MessageBundleContent ) {
			throw new LogicException(
				"Content with revision id $this->revisionId has wrong content format"
			);
		}

		$data = json_decode( $content->getText(), true );
		if ( !$data ) {
			throw new LogicException(
				"Content with revision id $this->revisionId is not valid JSON"
			);
		}

		$this->data = $data;
		return $this->data;
	}

	private function makeGroupKeys( array $keys ): array {
		$result = [];
		foreach ( $keys as $key ) {
			$result[] = str_replace( ' ', '_', "$this->name/$key" );
		}
		return $result;
	}

	/** @inheritDoc */
	public function getId(): string {
		return $this->groupId;
	}

	/** @inheritDoc */
	public function getLabel( ?IContextSource $context = null ): string {
		return $this->label ?? $this->name;
	}

	/** @inheritDoc */
	public function getDescription( ?IContextSource $context = null ): string {
		$titleText = Title::newFromID( $this->pageId )->getPrefixedText();
		$linkTargetText = ":$titleText";
		if ( $context ) {
			$message = $context->msg( 'translate-messagebundle-group-description' );
		} else {
			$message = wfMessage( 'translate-messagebundle-group-description' )
				->inContentLanguage();
		}

		$plainMessage = $message->params( $titleText, $linkTargetText )->plain();

		if ( $this->description === null ) {
			return $plainMessage;
		}

		return $plainMessage . ' ' . $this->description;
	}

	/** @inheritDoc */
	public function getIcon(): ?string {
		return null;
	}

	/** @inheritDoc */
	public function getNamespace(): int {
		return NS_TRANSLATIONS;
	}

	/** @inheritDoc */
	public function isMeta(): bool {
		return false;
	}

	/** @inheritDoc */
	public function exists(): bool {
		return true;
	}

	/** @inheritDoc */
	public function getValidator(): ?ValidationRunner {
		return null;
	}

	/** @inheritDoc */
	public function getMangler(): ?StringMatcher {
		return null;
	}

	/** @inheritDoc */
	public function initCollection( $code ): MessageCollection {
		$defs = new MessageDefinitions( $this->getDefinitions(), $this->getNamespace() );
		$collection = MessageCollection::newFromDefinitions( $defs, $code );

		foreach ( $this->getTags() as $type => $tags ) {
			$collection->setTags( $type, $tags );
		}

		return $collection;
	}

	/** @inheritDoc */
	public function load( $code ): array {
		return [];
	}

	/** @inheritDoc */
	public function getDefinitions(): array {
		$data = $this->getData();
		unset( $data['@metadata'] );

		return array_combine(
			$this->makeGroupKeys( array_keys( $data ) ),
			array_values( $data )
		);
	}

	/** @inheritDoc */
	public function getKeys(): array {
		return array_keys( $this->getDefinitions() );
	}

	/** @inheritDoc */
	public function getTags( $type = null ): array {
		return [];
	}

	/** @inheritDoc */
	public function getMessage( $key, $code ): ?string {
		if ( $code === $this->getSourceLanguage() ) {
			return $this->getDefinitions()[$key] ?? null;
		}

		return null;
	}

	/** @inheritDoc */
	public function getSourceLanguage(): string {
		return Title::newFromText( $this->name )->getPageLanguage()->getCode();
	}

	/** @inheritDoc */
	public function getMessageGroupStates(): MessageGroupStates {
		global $wgTranslateWorkflowStates;
		$conf = $wgTranslateWorkflowStates ?: [];

		Services::getInstance()->getHookRunner()
			->onTranslate_modifyMessageGroupStates( $this->getId(), $conf );

		return new MessageGroupStates( $conf );
	}

	/** @inheritDoc */
	public function getTranslatableLanguages(): ?array {
		return self::DEFAULT_LANGUAGES;
	}

	/** @inheritDoc */
	public function getSupportConfig(): ?array {
		return null;
	}

	/** @inheritDoc */
	public function getRelatedPage(): ?LinkTarget {
		$this->title ??= Title::newFromID( $this->pageId );
		return $this->title;
	}
}
