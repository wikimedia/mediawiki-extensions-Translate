<?php
/**
 * Classes for %MediaWiki extension translation.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2013, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Class which handles special definition format for %MediaWiki extensions.
 */
class PremadeMediawikiExtensionGroups {
	protected $useConfigure = true;
	protected $idPrefix = 'ext-';
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
	 * @since 2012-03-22
	 */
	public function setUseConfigure( $value ) {
		$this->useConfigure = $value;
	}

	/**
	 * How to prefix message group ids.
	 * @since 2012-03-22
	 */
	public function setGroupPrefix( $value ) {
		$this->idPrefix = $value;
	}

	/**
	 * What namespace holds the messages.
	 * @since 2012-03-22
	 */
	public function setNamespace( $value ) {
		$this->namespace = $value;
	}

	/// Makes an group id from extension name
	static function foldId( $name ) {
		return preg_replace( '/\s+/', '', strtolower( $name ) );
	}

	/// Hook: TranslatePostInitGroups
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
		$conf = array();
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

		$conf['FILES']['class'] = 'MediaWikiExtensionFFS';
		$conf['FILES']['sourcePattern'] = $this->path . '/' . $info['file'];
		// Kind of hacky, export path will be wrong if %GROUPROOT% not used.
		$target = str_replace( '%GROUPROOT%/', '', $conf['FILES']['sourcePattern'] );
		$conf['FILES']['targetPattern'] = $target;

		// @todo Find a better way
		if ( isset( $info['aliasfile'] ) ) {
			$conf['FILES']['aliasFile'] = $info['aliasfile'];
		}
		if ( isset( $info['magicfile'] ) ) {
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
		$conf['CHECKER']['checks'] = array(
			'pluralCheck',
			'pluralFormsCheck',
			'wikiParameterCheck',
			'wikiLinksCheck',
			'XhtmlCheck',
			'braceBalanceCheck',
			'pagenameMessagesCheck',
			'miscMWChecks',
		);

		if ( isset( $info['optional'] ) ) {
			$conf['TAGS']['optional'] = $info['optional'];
		}
		if ( isset( $info['ignored'] ) ) {
			$conf['TAGS']['ignored'] = $info['ignored'];
		}

		return MessageGroupBase::factory( $conf );
	}

	protected function parseFile() {
		$defines = file_get_contents( $this->definitionFile );
		$linefeed = '(\r\n|\n)';
		$sections = array_map( 'trim', preg_split( "/$linefeed{2,}/", $defines, -1, PREG_SPLIT_NO_EMPTY ) );
		$groups = array();

		foreach ( $sections as $section ) {
			$lines = array_map( 'trim', preg_split( "/$linefeed/", $section ) );
			$newgroup = array();

			foreach ( $lines as $line ) {
				if ( $line === '' || $line[0] === '#' ) {
					continue;
				}

				if ( strpos( $line, '=' ) === false ) {
					if ( empty( $newgroup['name'] ) ) {
						$newgroup['name'] = $line;
					} else {
						throw new MWException( "Trying to define name twice: " . $line );
					}
				} else {
					list( $key, $value ) = array_map( 'trim', explode( '=', $line, 2 ) );
					switch ( $key ) {
						case 'file':
						case 'var':
						case 'id':
						case 'descmsg':
						case 'desc':
						case 'magicfile':
						case 'aliasfile':
							$newgroup[$key] = $value;
							break;
						case 'optional':
						case 'ignored':
							$values = array_map( 'trim', explode( ',', $value ) );
							if ( !isset( $newgroup[$key] ) ) {
								$newgroup[$key] = array();
							}
							$newgroup[$key] = array_merge( $newgroup[$key], $values );
							break;
						case 'prefix':
							list( $prefix, $messages ) = array_map( 'trim', explode( '|', $value, 2 ) );
							if ( isset( $newgroup['prefix'] ) && $newgroup['prefix'] !== $prefix ) {
								throw new MWException( "Only one prefix supported: {$newgroup['prefix']} !== $prefix" );
							}
							$newgroup['prefix'] = $prefix;

							if ( !isset( $newgroup['mangle'] ) ) {
								$newgroup['mangle'] = array();
							}

							$messages = array_map( 'trim', explode( ',', $messages ) );
							$newgroup['mangle'] = array_merge( $newgroup['mangle'], $messages );
							break;
						default:
							throw new MWException( "Unknown key:" . $key );
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
		$fixedGroups = array();
		foreach ( $groups as $g ) {
			if ( !is_array( $g ) ) {
				$g = array( $g );
			}

			$name = $g['name'];

			if ( isset( $g['id'] ) ) {
				$id = $g['id'];
			} else {
				$id = $this->idPrefix . preg_replace( '/\s+/', '', strtolower( $name ) );
			}

			if ( isset( $g['file'] ) ) {
				$file = $g['file'];
			} else {
				$file = preg_replace( '/\s+/', '', "$name/$name.i18n.php" );
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

			$newgroup = array(
				'name' => $name,
				'file' => $file,
				'descmsg' => $descmsg,
				'url' => $url,
			);

			$copyvars = array( 'ignored', 'optional', 'var', 'desc', 'prefix', 'mangle', 'magicfile', 'aliasfile' );
			foreach ( $copyvars as $var ) {
				if ( isset( $g[$var] ) ) {
					$newgroup[$var] = $g[$var];
				}
			}

			$fixedGroups[$id] = $newgroup;
		}

		return $fixedGroups;
	}

	protected function loadConfigureExtensionData() {
		if ( !$this->useConfigure ) {
			return array();
		}

		global $wgAutoloadClasses, $IP, $wgTranslateExtensionDirectory;

		$postfix = 'Configure/load_txt_def/TxtDef.php';
		if ( file_exists( "$IP/extensions/$postfix" ) ) {
			$prefix = "$IP/extensions";
		} elseif ( file_exists( "$wgTranslateExtensionDirectory/$postfix" ) ) {
			$prefix = $wgTranslateExtensionDirectory;
		} else {
			$prefix = false;
		}

		if ( $prefix ) {
			$wgAutoloadClasses['TxtDef'] = "$prefix/$postfix";
			$tmp = TxtDef::loadFromFile( "$prefix/Configure/settings/Settings-ext.txt" );

			return array_combine( array_map( array( __CLASS__, 'foldId' ), array_keys( $tmp ) ), array_values( $tmp ) );
		}

		return array();
	}
}
