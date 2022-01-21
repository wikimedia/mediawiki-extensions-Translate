<?php
/**
 * Classes for %MediaWiki extension translation.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\MessageProcessing\StringMatcher;
use MediaWiki\Extension\Translate\TranslatorInterface\Insertable\MediaWikiInsertablesSuggester;

/**
 * Class which handles special definition format for %MediaWiki extensions and skins.
 */
class PremadeMediawikiExtensionGroups {
	/** @var string */
	protected $idPrefix = 'ext-';
	/** @var int */
	protected $namespace;
	/**
	 * @var string
	 * @see __construct
	 */
	protected $path;
	/**
	 * @var string
	 * @see __construct
	 */
	protected $definitionFile;

	/**
	 * @param string $def Absolute path to the definition file. See
	 *   tests/data/mediawiki-extensions.txt for example.
	 * @param string $path General prefix to the file locations without
	 *   the extension specific part. Should start with %GROUPROOT%/ or
	 *   otherwise export path will be wrong. The export path is
	 *   constructed by replacing %GROUPROOT%/ with target directory.
	 */
	public function __construct( $def, $path ) {
		$this->definitionFile = $def;
		$this->path = rtrim( $path, '/' );
	}

	/**
	 * Get the default namespace. Subclasses can override this.
	 *
	 * @return int
	 */
	protected function getDefaultNamespace() {
		return NS_MEDIAWIKI;
	}

	/**
	 * Get the namespace ID
	 *
	 * @return int
	 */
	protected function getNamespace() {
		if ( $this->namespace === null ) {
			$this->namespace = $this->getDefaultNamespace();
		}
		return $this->namespace;
	}

	/**
	 * How to prefix message group ids.
	 *
	 * @since 2012-03-22
	 * @param string $value
	 */
	public function setGroupPrefix( $value ) {
		$this->idPrefix = $value;
	}

	/**
	 * Which namespace holds the messages.
	 *
	 * @since 2012-03-22
	 * @param int $value
	 */
	public function setNamespace( $value ) {
		$this->namespace = $value;
	}

	/**
	 * Hook: TranslatePostInitGroups
	 * @param array &$list
	 * @param array &$deps
	 * @return true
	 */
	public function register( array &$list, array &$deps ) {
		$groups = $this->parseFile();
		$groups = $this->processGroups( $groups );
		foreach ( $groups as $id => $g ) {
			$list[$id] = $this->createMessageGroup( $id, $g );
		}

		$deps[] = new FileDependency( $this->definitionFile );

		return true;
	}

	/**
	 * Creates MediaWikiExtensionMessageGroup objects from parsed data.
	 * @param string $id unique group id already prefixed
	 * @param array $info array of group info
	 * @return MediaWikiExtensionMessageGroup
	 */
	protected function createMessageGroup( $id, $info ) {
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

		$conf['FILES']['class'] = JsonFFS::class;
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
			[ 'class' => MediaWikiInsertablesSuggester::class ]
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
					list( , $sign, $tag ) = $m;
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

		// @phan-suppress-next-line PhanTypeMismatchReturnSuperType
		return MessageGroupBase::factory( $conf );
	}

	protected function parseFile() {
		$defines = file_get_contents( $this->definitionFile );
		$linefeed = '(\r\n|\n)';
		$sections = array_map(
			'trim',
			preg_split( "/$linefeed{2,}/", $defines, -1, PREG_SPLIT_NO_EMPTY )
		);
		$groups = [];

		foreach ( $sections as $section ) {
			$lines = array_map( 'trim', preg_split( "/$linefeed/", $section ) );
			$newgroup = [];

			foreach ( $lines as $line ) {
				if ( $line === '' || $line[0] === '#' ) {
					continue;
				}

				if ( strpos( $line, '=' ) === false ) {
					if ( empty( $newgroup['name'] ) ) {
						$newgroup['name'] = $line;
					} else {
						throw new MWException( 'Trying to define name twice: ' . $line );
					}
				} else {
					list( $key, $value ) = array_map( 'trim', explode( '=', $line, 2 ) );
					switch ( $key ) {
						case 'aliasfile':
						case 'desc':
						case 'descmsg':
						case 'file':
						case 'id':
						case 'magicfile':
						case 'var':
							$newgroup[$key] = $value;
							break;
						case 'optional':
						case 'ignored':
						case 'languages':
							$values = array_map( 'trim', explode( ',', $value ) );
							if ( !isset( $newgroup[$key] ) ) {
								$newgroup[$key] = [];
							}
							$newgroup[$key] = array_merge( $newgroup[$key], $values );
							break;
						case 'prefix':
							list( $prefix, $messages ) = array_map(
								'trim',
								explode( '|', $value, 2 )
							);
							if ( isset( $newgroup['prefix'] ) && $newgroup['prefix'] !== $prefix ) {
								throw new MWException(
									"Only one prefix supported: {$newgroup['prefix']} !== $prefix"
								);
							}
							$newgroup['prefix'] = $prefix;

							if ( !isset( $newgroup['mangle'] ) ) {
								$newgroup['mangle'] = [];
							}

							$messages = array_map( 'trim', explode( ',', $messages ) );
							$newgroup['mangle'] = array_merge( $newgroup['mangle'], $messages );
							break;
						default:
							throw new MWException( 'Unknown key:' . $key );
					}
				}
			}

			if ( count( $newgroup ) ) {
				if ( empty( $newgroup['name'] ) ) {
					throw new MWException( "Name missing\n" . print_r( $newgroup, true ) );
				}
				$groups[] = $newgroup;
			}
		}

		return $groups;
	}

	protected function processGroups( $groups ) {
		$fixedGroups = [];
		foreach ( $groups as $g ) {
			$name = $g['name'];

			$id = $g['id'] ?? $this->idPrefix . preg_replace( '/\s+/', '', strtolower( $name ) );

			if ( !isset( $g['file'] ) ) {
				$file = preg_replace( '/\s+/', '', "$name/i18n/%CODE%.json" );
			} else {
				$file = $g['file'];
			}

			$descmsg = $g['descmsg'] ?? str_replace( $this->idPrefix, '', $id ) . '-desc';

			$newgroup = [
				'name' => $name,
				'file' => $file,
				'descmsg' => $descmsg,
			];

			$copyvars = [
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

			foreach ( $copyvars as $var ) {
				if ( isset( $g[$var] ) ) {
					$newgroup[$var] = $g[$var];
				}
			}

			// Mark some fixed form optional messages automatically
			if ( !isset( $newgroup['optional' ] ) ) {
				$newgroup['optional'] = [];
			}

			// Mark extension name and skin names optional.
			$newgroup['optional'][] = '*-extensionname';
			$newgroup['optional'][] = 'skinname-*';

			$fixedGroups[$id] = $newgroup;
		}

		return $fixedGroups;
	}
}
