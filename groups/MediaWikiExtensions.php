<?php
/**
 * Classes for MediaWiki extension translation.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2010, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * @todo Needs documentation.
 */
class PremadeMediawikiExtensionGroups {
	protected $groups;
	protected $definitionFile = null;
	protected $useConfigure = true;
	protected $idPrefix = 'ext-';

	public function __construct() {
		global $wgTranslateExtensionDirectory;
		$dir = dirname( __FILE__ );
		$this->definitionFile = $dir . '/mediawiki-defines.txt';
		$this->path = $wgTranslateExtensionDirectory;
	}

	public function init() {
		if ( $this->groups !== null ) return;

		global $wgAutoloadClasses, $IP, $wgTranslateExtensionDirectory;

		$postfix = 'Configure/load_txt_def/TxtDef.php';
		if ( file_exists( "$IP/extensions/$postfix" ) ) {
			$prefix = "$IP/extensions";
		} elseif( file_exists( "$wgTranslateExtensionDirectory/$postfix" ) ) {
			$prefix = $wgTranslateExtensionDirectory;
		} else {
			$prefix = false;
		}

		if ( $this->useConfigure && $prefix ) {
			$wgAutoloadClasses['TxtDef'] = "$prefix/$postfix";
			$tmp = TxtDef::loadFromFile( "$prefix/Configure/settings/Settings-ext.txt" );
			$configureData = array_combine( array_map( array( __CLASS__, 'foldId' ), array_keys( $tmp ) ), array_values( $tmp ) );
		} else {
			$configureData = array();
		}

		$defines = file_get_contents( $this->definitionFile );

		$linefeed = '(\r\n|\n)';

		$sections = array_map( 'trim', preg_split( "/$linefeed{2,}/", $defines, - 1, PREG_SPLIT_NO_EMPTY ) );

		$groups = $fixedGroups = array();

		foreach ( $sections as $section ) {
			$lines = array_map( 'trim', preg_split( "/$linefeed/", $section ) );
			$newgroup = array();

			foreach ( $lines as $line ) {
				if ( $line === '' || $line[0] === '#' ) continue;

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
					case 'aliasvar':
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

						if ( !isset( $newgroup['mangle'] ) ) $newgroup['mangle'] = array();

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

			$copyvars = array( 'ignored', 'optional', 'var', 'desc', 'prefix', 'mangle', 'magicfile', 'aliasfile', 'aliasvar' );
			foreach ( $copyvars as $var ) {
				if ( isset( $g[$var] ) ) {
					$newgroup[$var] = $g[$var];
				}
			}

			$fixedGroups[$id] = $newgroup;
		}

		$this->groups = $fixedGroups;
	}

	static function foldId( $name ) {
		return preg_replace( '/\s+/', '', strtolower( $name ) );
	}

	public function addAll() {
		global $wgTranslateAC, $wgTranslateEC;
		$this->init();

		if ( !count( $this->groups ) ) return;

		foreach ( $this->groups as $id => $g ) {
			$wgTranslateAC[$id] = array( $this, 'factory' );
			$wgTranslateEC[] = $id;
		}

		$this->addAllMeta();
	}

	protected function addAllMeta() {
		global $wgTranslateAC, $wgTranslateEC;

		$meta = array(
			'ext-0-all'               => 'AllMediawikiExtensionsGroup',
		);

		foreach ( $meta as $id => $g ) {
			$wgTranslateAC[$id] = $g;
			$wgTranslateEC[] = $id;
		}
	}

	public function factory( $id ) {
		$info = $this->groups[$id];
		$group = ExtensionMessageGroup::factory( $info['name'], $id );
		$group->setMessageFile( $info['file'] );
		$group->setPath( $this->path );

		if ( isset( $info['prefix'] ) ) {
			$mangler = new StringMatcher( $info['prefix'], $info['mangle'] );
			$group->setMangler( $mangler );
			$info['ignored'] = $mangler->mangle( $info['ignored'] );
			$info['optional'] = $mangler->mangle( $info['optional'] );
		}

		if ( !empty( $info['var'] ) ) $group->setVariableName( $info['var'] );
		if ( !empty( $info['optional'] ) ) $group->setOptional( $info['optional'] );
		if ( !empty( $info['ignored'] ) ) $group->setIgnored( $info['ignored'] );
		if ( isset( $info['desc'] ) ) {
			$group->setDescription( $info['desc'] );
		} else {
			$group->setDescriptionMsg( $info['descmsg'], $info['url'] );
		}

		if ( isset( $info['aliasfile'] ) ) $group->setAliasFile( $info['aliasfile'] );
		if ( isset( $info['aliasvar'] ) ) $group->setVariableNameAlias( $info['aliasvar'] );
		if ( isset( $info['magicfile'] ) ) $group->setMagicFile( $info['magicfile'] );

		return $group;
	}
}

/**
 * Adds a message group containing all supported MediaWiki extensions in the
 * Wikimedia Subversion repository.
 */
class AllMediawikiExtensionsGroup extends MessageGroupOld {
	protected $label = 'MediaWiki extensions';
	protected $id    = 'ext-0-all';
	protected $meta  = true;
	protected $type  = 'mediawiki';
	protected $classes = null;
	protected $description = '{{int:translate-group-desc-mediawikiextensions}}';

	// Don't add the (mw ext) thingie
	public function getLabel() { return $this->label; }

	protected function init() {
		if ( $this->classes === null ) {
			$this->classes = MessageGroups::singleton()->getGroups();
			foreach ( $this->classes as $index => $class ) {
				if ( ( strpos( $class->getId(), 'ext-' ) !== 0 ) || $class->isMeta() || !$class->exists() ) {
					unset( $this->classes[$index] );
				}
			}
		}
	}

	public function load( $code ) {
		$this->init();
		$array = array();
		foreach ( $this->classes as $class ) {
			// Use wfArrayMerge because of string keys
			$array = wfArrayMerge( $array, $class->load( $code ) );
		}
		return $array;
	}

	public function getMessage( $key, $code ) {
		$this->init();
		$msg = null;
		foreach ( $this->classes as $class ) {
			$msg = $class->getMessage( $key, $code );
			if ( $msg !== null ) return $msg;
		}
		return null;
	}

	function getDefinitions() {
		$this->init();
		$array = array();
		foreach ( $this->classes as $class ) {
			// Use wfArrayMerge because of string keys
			$array = wfArrayMerge( $array, $class->getDefinitions() );
		}
		return $array;
	}

	function getBools() {
		$this->init();
		$bools = parent::getBools();
		foreach ( $this->classes as $class ) {
			$newbools = ( $class->getBools() );
			if ( count( $newbools['optional'] ) || count( $newbools['ignored'] ) ) {
				$bools = array_merge_recursive( $bools, $class->getBools() );
			}
		}
		return $bools;
	}

	public function exists() {
		$this->init();
		foreach ( $this->classes as $class ) {
			if ( $class->exists() ) return true;
		}
		return false;
	}
}
