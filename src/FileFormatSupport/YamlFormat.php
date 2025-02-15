<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

use FileBasedMessageGroup;
use MediaWiki\Extension\Translate\MessageGroupConfiguration\MetaYamlSchemaExtender;
use MediaWiki\Extension\Translate\MessageLoading\Message;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;
use MediaWiki\Extension\Translate\MessageProcessing\ArrayFlattener;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Extension\Translate\Utilities\Yaml;
use RuntimeException;

/**
 * Implements support for message storage in YAML format.
 *
 * This class adds new key into FILES section: \c codeAsRoot.
 * If it is set to true, all messages will under language code.
 * @ingroup FileFormatSupport
 */
class YamlFormat extends SimpleFormat implements MetaYamlSchemaExtender {
	private ArrayFlattener $flattener;

	public function __construct( FileBasedMessageGroup $group ) {
		parent::__construct( $group );

		// Obtains object used to flatten and unflatten arrays. In this implementation
		// we use the ArrayFlattener class which also supports CLDR pluralization rules.
		$this->flattener = new ArrayFlattener(
			$this->extra['nestingSeparator'] ?? '.',
			$this->extra['parseCLDRPlurals'] ?? false
		);
	}

	public function getFileExtensions(): array {
		return [ '.yaml', '.yml' ];
	}

	/** @inheritDoc */
	public function readFromVariable( string $data ): array {
		// Authors first.
		$matches = [];
		preg_match_all( '/^#\s*Author:\s*(.*)$/m', $data, $matches );
		$authors = $matches[1];

		// Then messages.
		$messages = Yaml::loadString( $data );

		// Some groups have messages under language code
		if ( isset( $this->extra['codeAsRoot'] ) ) {
			$messages = array_shift( $messages ) ?? [];
		}

		$messages = $this->flattener->flatten( $messages );
		$messages = $this->group->getMangler()->mangleArray( $messages );
		foreach ( $messages as &$value ) {
			$value = rtrim( $value, "\n" );
		}

		return [
			'AUTHORS' => $authors,
			'MESSAGES' => $messages,
		];
	}

	protected function writeReal( MessageCollection $collection ): string {
		$output = $this->doHeader( $collection );
		$output .= $this->doAuthors( $collection );

		$mangler = $this->group->getMangler();

		$messages = [];

		$collection->filter( MessageCollection::FILTER_HAS_TRANSLATION, MessageCollection::INCLUDE_MATCHING );
		/** @var Message $m */
		foreach ( $collection as $key => $m ) {
			$key = $mangler->unmangle( $key );
			$value = $m->translation();
			if ( $value === null ) {
				throw new RuntimeException( "Expected translation to be present for $key, but found null." );
			}

			$value = str_replace( TRANSLATE_FUZZY, '', $value );
			if ( $value === '' ) {
				continue;
			}

			$messages[$key] = $value;
		}

		if ( !count( $messages ) ) {
			return '';
		}
		$messages = $this->flattener->unflatten( $messages );

		// Some groups have messages under language code.
		if ( isset( $this->extra['codeAsRoot'] ) ) {
			$code = $this->group->mapCode( $collection->code );
			$messages = [ $code => $messages ];
		}

		$output .= Yaml::dump( $messages );

		return $output;
	}

	private function doHeader( MessageCollection $collection ): string {
		global $wgSitename;
		global $wgTranslateYamlLibrary;

		$code = $collection->code;
		$name = Utilities::getLanguageName( $code );
		$native = Utilities::getLanguageName( $code, $code );
		$output = "# Messages for $name ($native)\n";
		$output .= "# Exported from $wgSitename\n";

		if ( $wgTranslateYamlLibrary !== null ) {
			$output .= "# Export driver: $wgTranslateYamlLibrary\n";
		}

		return $output;
	}

	private function doAuthors( MessageCollection $collection ): string {
		$output = '';
		$authors = $collection->getAuthors();
		$authors = $this->filterAuthors( $authors, $collection->code );

		foreach ( $authors as $author ) {
			$output .= "# Author: $author\n";
		}

		return $output;
	}

	public function isContentEqual( ?string $a, ?string $b ): bool {
		return $this->flattener->compareContent( $a, $b );
	}

	public static function getExtraSchema(): array {
		return [
			'root' => [
				'_type' => 'array',
				'_children' => [
					'FILES' => [
						'_type' => 'array',
						'_children' => [
							'codeAsRoot' => [
								'_type' => 'boolean',
							],
							'nestingSeparator' => [
								'_type' => 'text',
							],
							'parseCLDRPlurals' => [
								'_type' => 'boolean',
							]
						]
					]
				]
			]
		];
	}
}

class_alias( YamlFormat::class, 'YamlFFS' );
