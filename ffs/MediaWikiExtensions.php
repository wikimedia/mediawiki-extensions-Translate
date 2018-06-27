<?php
/**
 * Classes for %MediaWiki extension translation.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Class which handles special definition format for %MediaWiki extensions and skins.
 */
class PremadeMediawikiExtensionGroups {
	/** @var bool */
	protected $useConfigure = true;

	/** @var string */
	protected $idPrefix = 'ext-';

	/** @var int */
	protected $namespace = NS_MEDIAWIKI;

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
		$this->path = $path;
	}

	/**
	 * Whether to use the Configure extension to load extension home pages.
	 *
	 * @since 2012-03-22
	 * @param bool $value Whether Configure should be used.
	 */
	public function setUseConfigure( $value ) {
		$this->useConfigure = $value;
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
	 * Makes an group id from extension name
	 * @param string $name
	 * @return string
	 */
	public static function foldId( $name ) {
		return preg_replace( '/\s+/', '', strtolower( $name ) );
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
		$conf['BASIC']['class'] = 'MediaWikiExtensionMessageGroup';
		$conf['BASIC']['id'] = $id;
		$conf['BASIC']['namespace'] = $this->namespace;
		$conf['BASIC']['label'] = $info['name'];

		if ( isset( $info['desc'] ) ) {
			$conf['BASIC']['description'] = $info['desc'];
		} else {
			$conf['BASIC']['descriptionmsg'] = $info['descmsg'];
			$conf['BASIC']['extensionurl'] = $info['url'];
		}

		$conf['FILES']['class'] = 'JsonFFS';
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
			$conf['MANGLER']['class'] = 'StringMatcher';
			$conf['MANGLER']['prefix'] = $info['prefix'];
			$conf['MANGLER']['patterns'] = $info['mangle'];

			$mangler = new StringMatcher( $info['prefix'], $info['mangle'] );
			if ( isset( $info['ignored'] ) ) {
				$info['ignored'] = $mangler->mangle( $info['ignored'] );
			}
			if ( isset( $info['optional'] ) ) {
				$info['optional'] = $mangler->mangle( $info['optional'] );
			}
		}

		$conf['CHECKER']['class'] = 'MediaWikiMessageChecker';
		$conf['CHECKER']['checks'] = [
			'pluralCheck',
			'pluralFormsCheck',
			'wikiParameterCheck',
			'wikiLinksCheck',
			'braceBalanceCheck',
			'pagenameMessagesCheck',
			'miscMWChecks',
		];

		$conf['INSERTABLES']['class'] = 'MediaWikiInsertablesSuggester';

		if ( isset( $info['optional'] ) ) {
			$conf['TAGS']['optional'] = $info['optional'];
		}
		if ( isset( $info['ignored'] ) ) {
			$conf['TAGS']['ignored'] = $info['ignored'];
		}

		if ( isset( $info['languages'] ) ) {
			$conf['LANGUAGES'] = [
				'whitelist' => [],
				'blacklist' => [],
			];

			foreach ( $info['languages'] as $tagSpec ) {
				if ( preg_match( '/^([+-])?(.+)$/', $tagSpec, $m ) ) {
					list( , $sign, $tag ) = $m;
					if ( $sign === '+' ) {
						$conf['LANGUAGES']['whitelist'][] = $tag;
					} elseif ( $sign === '-' ) {
						$conf['LANGUAGES']['blacklist'][] = $tag;
					} else {
						$conf['LANGUAGES']['blacklist'] = '*';
						$conf['LANGUAGES']['whitelist'][] = $tag;
					}
				}
			}
		}

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
		$configureData = $this->loadConfigureExtensionData();
		$fixedGroups = [];
		foreach ( $groups as $g ) {
			if ( !is_array( $g ) ) {
				$g = [ $g ];
			}

			$name = $g['name'];

			if ( isset( $g['id'] ) ) {
				$id = $g['id'];
			} else {
				$id = $this->idPrefix . preg_replace( '/\s+/', '', strtolower( $name ) );
			}

			if ( !isset( $g['file'] ) ) {
				$file = preg_replace( '/\s+/', '', "$name/i18n/%CODE%.json" );
			} else {
				$file = $g['file'];
			}

			if ( isset( $g['descmsg'] ) ) {
				$descmsg = $g['descmsg'];
			} else {
				$descmsg = str_replace( $this->idPrefix, '', $id ) . '-desc';
			}

			$configureId = self::foldId( $name );
			if ( isset( $configureData[$configureId]['url'] ) ) {
				$url = $configureData[$configureId]['url'];
			} else {
				$url = false;
			}

			$newgroup = [
				'name' => $name,
				'file' => $file,
				'descmsg' => $descmsg,
				'url' => $url,
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

	protected function loadConfigureExtensionData() {
		if ( !$this->useConfigure ) {
			return [];
		}

		global $wgAutoloadClasses;

		$postfix = 'Configure/load_txt_def/TxtDef.php';
		if ( !file_exists( "{$this->path}/$postfix" ) ) {
			return [];
		}

		$wgAutoloadClasses['TxtDef'] = "{$this->path}/$postfix";
		$tmp = TxtDef::loadFromFile( "{$this->path}/Configure/settings/Settings-ext.txt" );

		return array_combine(
			array_map( [ __CLASS__, 'foldId' ], array_keys( $tmp ) ),
			array_values( $tmp )
		);
	}
}
