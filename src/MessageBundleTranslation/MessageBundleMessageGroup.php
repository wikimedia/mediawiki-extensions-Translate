<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use Hooks as MediaWikiHooks;
use IContextSource;
use LogicException;
use MediaWiki\Extension\Translate\MessageProcessing\StringMatcher;
use MediaWiki\Extension\Translate\Validation\ValidationRunner;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use MessageCollection;
use MessageDefinitions;
use MessageGroup;
use MessageGroupStates;
use Title;
use const NS_TRANSLATIONS;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2021.12
 */
class MessageBundleMessageGroup implements MessageGroup {
	/** @var string Name of the bundle (prefixed text of the bundle page) */
	private $name;
	/** @var string */
	private $groupId;
	/** @var int */
	private $pageId;
	/** @var int */
	private $revisionId;
	/** @var array */
	private $data;

	public function __construct( string $groupId, string $name, int $pageId, int $revisionId ) {
		$this->groupId = $groupId;
		$this->name = $name;
		$this->pageId = $pageId;
		$this->revisionId = $revisionId;
	}

	/** Suggested default naming pattern */
	public static function getGroupId( string $name ): string {
		return "messagebundle-$name";
	}

	private function getData(): array {
		if ( !$this->data ) {
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
		}

		return $this->data;
	}

	private function prefixKeys( array $keys ): array {
		$result = [];
		foreach ( $keys as $key ) {
			$result[] = "$this->name/$key";
		}
		return $result;
	}

	/** @inheritDoc */
	public function getId(): string {
		return $this->groupId;
	}

	/** @inheritDoc */
	public function getLabel( IContextSource $context = null ): string {
		return $this->name;
	}

	/** @inheritDoc */
	public function getDescription( IContextSource $context = null ): string {
		$titleText = Title::newFromID( $this->pageId )->getPrefixedText();
		$linkTargetText = ":$titleText";

		if ( $context ) {
			$message = $context->msg( 'translate-messagebundle-group-description' );
		} else {
			$message = wfMessage( 'translate-messagebundle-group-description' )
				->inContentLanguage();
		}

		return $message->params( $titleText, $linkTargetText )->plain();
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
			$this->prefixKeys( array_keys( $data ) ),
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
			return $this->getData()[$key] ?? null;
		}

		return null;
	}

	/** @inheritDoc */
	public function getSourceLanguage(): string {
		return Title::newFromID( $this->pageId )->getPageLanguage()->getCode();
	}

	/** @inheritDoc */
	public function getMessageGroupStates(): MessageGroupStates {
		global $wgTranslateWorkflowStates;
		$conf = $wgTranslateWorkflowStates ?: [];

		MediaWikiHooks::run( 'Translate:modifyMessageGroupStates', [ $this->getId(), &$conf ] );

		return new MessageGroupStates( $conf );
	}

	/** @inheritDoc */
	public function getTranslatableLanguages(): ?array {
		return null;
	}

	/** @inheritDoc */
	public function getSupportConfig(): ?array {
		return null;
	}
}
