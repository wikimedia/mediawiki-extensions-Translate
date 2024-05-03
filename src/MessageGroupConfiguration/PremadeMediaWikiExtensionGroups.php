<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupConfiguration;

use FileDependency;
use MediaWiki\Extension\Translate\FileFormatSupport\JsonFormat;
use MediaWiki\Extension\Translate\MessageProcessing\StringMatcher;
use MediaWiki\Extension\Translate\TranslatorInterface\Insertable\MediaWikiInsertablesSuggester;
use MediaWiki\Extension\Translate\TranslatorInterface\Insertable\UrlInsertablesSuggester;
use MediaWikiExtensionMessageGroup;
use MessageGroup;
use MessageGroupBase;
use RuntimeException;
use UnexpectedValueException;

/**
 * Class which handles special definition format for %MediaWiki extensions and skins.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
class PremadeMediaWikiExtensionGroups {
	protected string $idPrefix = 'ext-';
	protected ?int $namespace = null;
	/** @see __construct */
	protected string $path;
	/** @see __construct */
	protected string $definitionFile;

	/**
	 * @param string $def Absolute path to the definition file. See
	 *   tests/data/mediawiki-extensions.txt for example.
	 * @param string $path General prefix to the file locations without
	 *   the extension specific part. Should start with %GROUPROOT%/ or
	 *   otherwise export path will be wrong. The export path is
	 *   constructed by replacing %GROUPROOT%/ with target directory.
	 */
	public function __construct( string $def, string $path ) {
		$this->definitionFile = $def;
		$this->path = rtrim( $path, '/' );
	}

	/** Get the default namespace. Subclasses can override this. */
	protected function getDefaultNamespace(): int {
		return NS_MEDIAWIKI;
	}

	/** Get the namespace ID */
	protected function getNamespace(): int {
		if ( $this->namespace === null ) {
			$this->namespace = $this->getDefaultNamespace();
		}
		return $this->namespace;
	}

	/** How to prefix message group ids. */
	public function setGroupPrefix( string $value ): void {
		$this->idPrefix = $value;
	}

	/** Which namespace holds the messages. */
	public function setNamespace( int $value ): void {
		$this->namespace = $value;
	}

	/** Hook: TranslatePostInitGroups */
	public function register( array &$list, array &$deps ): void {
		$groups = $this->parseFile();
		$groups = $this->processGroups( $groups );
		foreach ( $groups as $id => $g ) {
			$list[$id] = $this->createMessageGroup( $id, $g );
		}

		$deps[] = new FileDependency( $this->definitionFile );
	}

	/**
	 * Creates MediaWikiExtensionMessageGroup objects from parsed data.
	 * @param string $id unique group id already prefixed
	 * @param array $info array of group info
	 */
	protected function createMessageGroup( string $id, array $info ): MessageGroup {
		$conf = [];
		$conf['BASIC']['class'] = MediaWikiExtensionMessageGroup::class;
		$conf['BASIC']['id'] = $id;
		$conf['BASIC']['namespace'] = $this->getNamespace();
		$conf['BASIC']['label'] = $info['name'];

		if ( isset( $info['desc'] ) ) {
			$conf['BASIC']['description'] = $info['desc'];
		} else {
			$conf['BASIC']['descriptionmsg'] = $info['descmsg'];
		}

		$conf['FILES']['class'] = JsonFormat::class;
		$conf['FILES']['sourcePattern'] = $this->path . '/' . $info['file'];

		// @todo Find a better way
		if ( isset( $info['aliasfile'] ) ) {
			$conf['FILES']['aliasFileSource'] = $this->path . '/' . $info['aliasfile'];
			$conf['FILES']['aliasFile'] = $info['aliasfile'];
		}
		if ( isset( $info['magicfile'] ) ) {
			$conf['FILES']['magicFileSource'] = $this->path . '/' . $info['magicfile'];
			$conf['FILES']['magicFile'] = $info['magicfile'];
		}

		if ( isset( $info['prefix'] ) ) {
			$conf['MANGLER']['class'] = StringMatcher::class;
			$conf['MANGLER']['prefix'] = $info['prefix'];
			$conf['MANGLER']['patterns'] = $info['mangle'];

			$mangler = new StringMatcher( $info['prefix'], $info['mangle'] );
			if ( isset( $info['ignored'] ) ) {
				$info['ignored'] = $mangler->mangleList( $info['ignored'] );
			}
			if ( isset( $info['optional'] ) ) {
				$info['optional'] = $mangler->mangleList( $info['optional'] );
			}
		}

		$conf['VALIDATORS'] = [
			[ 'id' => 'BraceBalance' ],
			[ 'id' => 'MediaWikiLink' ],
			[ 'id' => 'MediaWikiPageName' ],
			[ 'id' => 'MediaWikiParameter' ],
			[ 'id' => 'MediaWikiPlural' ],
		];

		$conf['INSERTABLES'] = [
			[ 'class' => MediaWikiInsertablesSuggester::class ],
			[ 'class' => UrlInsertablesSuggester::class ]
		];

		if ( isset( $info['optional'] ) ) {
			$conf['TAGS']['optional'] = $info['optional'];
		}
		if ( isset( $info['ignored'] ) ) {
			$conf['TAGS']['ignored'] = $info['ignored'];
		}

		if ( isset( $info['languages'] ) ) {
			$conf['LANGUAGES'] = [
				'include' => [],
				'exclude' => [],
			];

			foreach ( $info['languages'] as $tagSpec ) {
				if ( preg_match( '/^([+-])?(.+)$/', $tagSpec, $m ) ) {
					[ , $sign, $tag ] = $m;
					if ( $sign === '+' ) {
						$conf['LANGUAGES']['include'][] = $tag;
					} elseif ( $sign === '-' ) {
						$conf['LANGUAGES']['exclude'][] = $tag;
					} else {
						$conf['LANGUAGES']['exclude'] = '*';
						$conf['LANGUAGES']['include'][] = $tag;
					}
				}
			}
		}

		return MessageGroupBase::factory( $conf );
	}

	protected function parseFile(): array {
		$defines = file_get_contents( $this->definitionFile );
		$linefeed = '(\r\n|\n)';
		$sections = array_map(
			'trim',
			preg_split( "/$linefeed{2,}/", $defines, -1, PREG_SPLIT_NO_EMPTY )
		);
		$groups = [];

		foreach ( $sections as $section ) {
			$lines = array_map( 'trim', preg_split( "/$linefeed/", $section ) );
			$newGroup = [];

			foreach ( $lines as $line ) {
				if ( $line === '' || $line[0] === '#' ) {
					continue;
				}

				if ( !str_contains( $line, '=' ) ) {
					if ( empty( $newGroup['name'] ) ) {
						$newGroup['name'] = $line;
					} else {
						throw new RuntimeException( 'Trying to define name twice: ' . $line );
					}
				} else {
					[ $key, $value ] = array_map( 'trim', explode( '=', $line, 2 ) );
					switch ( $key ) {
						case 'aliasfile':
						case 'desc':
						case 'descmsg':
						case 'file':
						case 'id':
						case 'magicfile':
						case 'var':
							$newGroup[$key] = $value;
							break;
						case 'optional':
						case 'ignored':
						case 'languages':
							$values = array_map( 'trim', explode( ',', $value ) );
							if ( !isset( $newGroup[$key] ) ) {
								$newGroup[$key] = [];
							}
							$newGroup[$key] = array_merge( $newGroup[$key], $values );
							break;
						case 'prefix':
							[ $prefix, $messages ] = array_map(
								'trim',
								explode( '|', $value, 2 )
							);
							if ( isset( $newGroup['prefix'] ) && $newGroup['prefix'] !== $prefix ) {
								throw new RuntimeException(
									"Only one prefix supported: {$newGroup['prefix']} !== $prefix"
								);
							}
							$newGroup['prefix'] = $prefix;

							if ( !isset( $newGroup['mangle'] ) ) {
								$newGroup['mangle'] = [];
							}

							$messages = array_map( 'trim', explode( ',', $messages ) );
							$newGroup['mangle'] = array_merge( $newGroup['mangle'], $messages );
							break;
						default:
							throw new UnexpectedValueException( 'Unknown key:' . $key );
					}
				}
			}

			if ( count( $newGroup ) ) {
				if ( empty( $newGroup['name'] ) ) {
					throw new RuntimeException( "Name missing\n" . print_r( $newGroup, true ) );
				}
				$groups[] = $newGroup;
			}
		}

		return $groups;
	}

	protected function processGroups( array $groups ): array {
		$fixedGroups = [];
		foreach ( $groups as $g ) {
			$name = $g['name'];

			$id = $g['id'] ?? $this->idPrefix . preg_replace( '/\s+/', '', strtolower( $name ) );

			if ( !isset( $g['file'] ) ) {
				$file = preg_replace( '/\s+/', '', "$name/i18n/%CODE%.json" );
			} else {
				$file = $g['file'];
			}

			$descMsg = $g['descmsg'] ?? str_replace( $this->idPrefix, '', $id ) . '-desc';

			$newGroup = [
				'name' => $name,
				'file' => $file,
				'descmsg' => $descMsg,
			];

			$copyVars = [
				'aliasfile',
				'desc',
				'ignored',
				'languages',
				'magicfile',
				'mangle',
				'optional',
				'prefix',
				'var',
			];

			foreach ( $copyVars as $var ) {
				if ( isset( $g[$var] ) ) {
					$newGroup[$var] = $g[$var];
				}
			}

			// Mark some fixed form optional messages automatically
			if ( !isset( $newGroup['optional' ] ) ) {
				$newGroup['optional'] = [];
			}

			// Mark extension name and skin names optional.
			$newGroup['optional'][] = '*-extensionname';
			$newGroup['optional'][] = 'skinname-*';

			$fixedGroups[$id] = $newGroup;
		}

		return $fixedGroups;
	}
}

class_alias( PremadeMediaWikiExtensionGroups::class, 'PremadeMediaWikiExtensionGroups' );
