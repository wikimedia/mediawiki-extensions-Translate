<?php
/**
 * Class for Toolserver Intuition for TranslateWiki.net
 *
 * @file
 * @author Niklas Laxström
 * @author Krinkle
 * @copyright Copyright © 2008-2010, Niklas Laxström
 * @copyright Copyright © 2011, Krinkle
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Support for tools using Toolserver Intuition at the Toolserver.
 */
class PremadeToolserverTextdomains extends PremadeMediawikiExtensionGroups {
	protected $useConfigure = false;
	protected $groups;
	protected $idPrefix = 'tsint-';
	protected $namespaces = array( NS_TOOLSERVER, NS_TOOLSERVER_TALK );


	public function __construct() {
		global $wgTranslateGroupRoot;

		parent::__construct();
		$dir = dirname( __FILE__ );
		$this->definitionFile = $dir . '/toolserver-textdomains.txt';
		$this->path = "$wgTranslateGroupRoot/ToolserverI18N/language/messages/";
	}

	/// Initialisation function
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
			$sanatizedName = preg_replace( '/\s+/', '', strtolower( $name ) );

			if ( isset( $g['id'] ) ) {
				$id = $g['id'];
			} else {
				$id = $this->idPrefix . $sanatizedName;
			}

			if ( isset( $g['file'] ) ) {
				$file = $g['file'];
			} else {
				// TsIntuition text-domains are case-insensitive and internally
				// converts to lowercase names starting with a capital letter.
				// eg. "My Tool" -> "Mytool.i18n.php"
				// No subdirectories!
				$file = ucfirst( $sanatizedName ) . '.i18n.php';
			}

			if ( isset( $g['descmsg'] ) ) {
				$descmsg = $g['descmsg'];
			} else {
				$descmsg = str_replace( $this->idPrefix, '', $id ) . '-desc';
			}

			if ( isset( $g['url'] ) ) {
				$url = $g['url'];
			} else {
				$url = false;
			}

			$newgroup = array(
				'name' => $name,
				'file' => $file,
				'descmsg' => $descmsg,
				'url' => $url,
			);

			// Allow a custom prefix if needed
			if ( !isset( $g['prefix'] ) ) {
				$g['prefix'] = "$sanatizedName-";
			}
			// All messages are prefixed with their groupname
			$g['mangle'] = array( '*' );
			
			// Prevent E_NOTICE undefined index.
			// PremadeMediawikiExtensionGroups::factory should probably check this better instead
			if ( !isset( $g['ignored'] ) )  $g['ignored'] = array();
			if ( !isset( $g['optional'] ) )  $g['optional'] = array();

			$copyvars = array( 'ignored', 'optional', 'var', 'desc', 'prefix', 'mangle', 'magicfile', 'aliasfile' );
			foreach ( $copyvars as $var ) {
				if ( isset( $g[$var] ) ) {
					$newgroup[$var] = $g[$var];
				}
			}

			$fixedGroups[$id] = $newgroup;
		}

		$this->groups = $fixedGroups;
	}
}
