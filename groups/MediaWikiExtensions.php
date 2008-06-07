<?php

class PremadeMediawikiExtensionGroups {
	protected $groups;

	public function init() {
		if ( $this->groups !== null ) return;

		$dir = dirname( __FILE__ );
		$defines = file_get_contents( $dir . '/defines.txt' );
		$sections = preg_split( "/\n\n/", $defines, -1, PREG_SPLIT_NO_EMPTY );

		$groups = $fixedGroups = array();

		foreach ( $sections as $section ) {
			$lines = preg_split( "/\n/", $section );
			$newgroup = array();

			foreach ( $lines as $line ) {
				if ( strpos( $line, '=' ) === false ) {
					if ( empty($newgroup['name']) ) {
						$newgroup['name'] = $line;
					} else {
						throw new MWException( "Trying to define name twice: " . $line );
					}
				} else {
					list( $key, $value ) = array_map( 'trim', explode( '=', $line, 2 ) );
					switch ($key) {
					case 'file':
					case 'var':
					case 'id':
						$newgroup[$key] = $value;
						break;
					case 'optional':
					case 'ignored':
						$values = array_map( 'trim', explode( ',', $value ) );
						if ( !isset($newgroup[$key]) ) {
							$newgroup[$key] = array();
						}
						$newgroup[$key] = array_merge( $newgroup[$key], $values );
						break;
					default:
						throw new MWException( "Unknown key:" . $key );
					}
				}
			}

			if ( count($newgroup) ) {
				if ( empty($newgroup['name']) ) {
					throw new MWException( "Name missing\n" . print_r($newgroup, true) );
				}
				$groups[] = $newgroup;
			}
		}
		

		foreach ( $groups as $g ) {
			if ( !is_array($g) ) {
				$g = array($g);
			}

			$name = $g['name'];

			if ( isset($g['id']) ) {
				$id = $g['id'];
			} else {
				$id = 'ext-' . preg_replace( '/\s+/', '', strtolower( $name ) );
			}

			if ( isset($g['file']) ) {
				$file = $g['file'];
			} else {
				$file = preg_replace( '/\s+/', '', "$name/$name.i18n.php" );
			}

			$newgroup = array(
				'name' => $name,
				'file' => $file,
			);

			$copyvars = array( 'ignored', 'optional', 'var' );
			foreach ( $copyvars as $var ) {
				if ( isset($g[$var]) ) {
					$newgroup[$var] = $g[$var];
				}
			}

			$fixedGroups[$id] = $newgroup;
		}

		$this->groups = $fixedGroups;
	}

	public function addAll() {
		global $wgTranslateAC, $wgTranslateEC;
		$this->init();
		foreach ( $this->groups as $id => $g ) {
			$wgTranslateAC[$id] = array( $this, 'factory' );
			$wgTranslateEC[] = $id;
		}
	}

	public function factory( $id ) {
		$info = $this->groups[$id];
		$group = ExtensionMessageGroup::factory( $info['name'] . ' (mw ext)', $id );
		$group->setMessageFile( $info['file'] );
		if ( !empty($info['var']) ) $group->setVariableName( $info['var'] );
		if ( !empty($info['optional']) ) $group->setOptional( $info['optional'] );
		if ( !empty($info['ignored']) ) $group->setIgnored( $info['ignored'] );
		return $group;
	}

}