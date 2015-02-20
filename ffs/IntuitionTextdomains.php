<?php
/**
 * Class for Intuition for Translatewiki.net
 *
 * @file
 * @author Niklas Laxström
 * @author Krinkle
 * @copyright Copyright © 2008-2013, Niklas Laxström
 * @copyright Copyright © 2011, Krinkle
 * @license GPL-2.0+
 */

/**
 * Support for tools using Intuition at the Toolserver and Wikimedia Labs.
 */
class PremadeIntuitionTextdomains extends PremadeMediawikiExtensionGroups {
	protected $useConfigure = false;
	protected $groups;
	protected $idPrefix = 'tsint-';
	protected $namespace = NS_INTUITION;

	protected function processGroups( $groups ) {
		$fixedGroups = array();
		foreach ( $groups as $g ) {
			if ( !is_array( $g ) ) {
				$g = array( $g );
			}

			$name = $g['name'];
			$sanitizedName = preg_replace( '/\s+/', '', strtolower( $name ) );

			if ( isset( $g['id'] ) ) {
				$id = $g['id'];
			} else {
				$id = $this->idPrefix . $sanitizedName;
			}

			if ( isset( $g['file'] ) ) {
				$file = $g['file'];
			} else {
				// Canonical names for Intuition text-domains are lowercase
				// eg. "MyTool" -> "mytool/en.json"
				$file = "$sanitizedName/%CODE%.json";
			}

			if ( isset( $g['descmsg'] ) ) {
				$descmsg = $g['descmsg'];
			} else {
				$descmsg = "$id-desc";
			}

			if ( isset( $g['url'] ) ) {
				$url = $g['url'];
			} else {
				$url = false;
			}

			$newgroup = array(
				'name' => 'Intuition - ' . $name,
				'file' => $file,
				'descmsg' => $descmsg,
				'url' => $url,
			);

			// Prefix is required, if not customized use the sanitized name
			if ( !isset( $g['prefix'] ) ) {
				$g['prefix'] = "$sanitizedName-";
			}

			// All messages are prefixed with their groupname
			$g['mangle'] = array( '*' );

			// Prevent E_NOTICE undefined index.
			// PremadeMediawikiExtensionGroups::factory should probably check this better instead
			if ( !isset( $g['ignored'] ) ) {
				$g['ignored'] = array();
			}

			if ( !isset( $g['optional'] ) ) {
				$g['optional'] = array();
			}

			$g['format'] = 'json';

			$copyvars = array(
				'aliasfile',
				'desc',
				'format',
				'ignored',
				'magicfile',
				'mangle',
				'optional',
				'prefix',
				'var',
			);

			foreach ( $copyvars as $var ) {
				if ( isset( $g[$var] ) ) {
					$newgroup[$var] = $g[$var];
				}
			}

			$fixedGroups[$id] = $newgroup;
		}

		return $fixedGroups;
	}
}
