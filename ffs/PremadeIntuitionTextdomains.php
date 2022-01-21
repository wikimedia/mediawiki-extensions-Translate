<?php
/**
 * Class for Intuition for Translatewiki.net
 *
 * @file
 * @author Niklas Laxström
 * @author Krinkle
 * @copyright Copyright © 2008-2013, Niklas Laxström
 * @copyright Copyright © 2011, Krinkle
 * @license GPL-2.0-or-later
 */

/**
 * Support for tools using Intuition at the Toolserver and Wikimedia Labs.
 */
class PremadeIntuitionTextdomains extends PremadeMediawikiExtensionGroups {
	protected $groups;
	protected $idPrefix = 'tsint-';

	protected function getDefaultNamespace() {
		return NS_INTUITION;
	}

	protected function processGroups( $groups ) {
		$fixedGroups = [];
		foreach ( $groups as $g ) {
			$name = $g['name'];
			$sanitizedName = preg_replace( '/\s+/', '', strtolower( $name ) );

			$id = $g['id'] ?? $this->idPrefix . $sanitizedName;

			// Canonical names for Intuition text-domains are lowercase
			// eg. "MyTool" -> "mytool/en.json"
			$file = $g['file'] ?? "$sanitizedName/%CODE%.json";

			$descmsg = $g['descmsg'] ?? "$id-desc";

			$newgroup = [
				'name' => 'Intuition - ' . $name,
				'file' => $file,
				'descmsg' => $descmsg,
			];

			// Prefix is required, if not customized use the sanitized name
			if ( !isset( $g['prefix'] ) ) {
				$g['prefix'] = "$sanitizedName-";
			}

			// All messages are prefixed with their groupname
			$g['mangle'] = [ '*' ];

			// Prevent E_NOTICE undefined index.
			// PremadeMediawikiExtensionGroups::factory should probably check this better instead
			if ( !isset( $g['ignored'] ) ) {
				$g['ignored'] = [];
			}

			if ( !isset( $g['optional'] ) ) {
				$g['optional'] = [];
			}

			$g['format'] = 'json';

			$copyvars = [
				'aliasfile',
				'desc',
				'format',
				'ignored',
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

			$fixedGroups[$id] = $newgroup;
		}

		return $fixedGroups;
	}
}
