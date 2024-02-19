<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupConfiguration;

/**
 * Support for tools using Intuition at the Toolserver and Wikimedia Labs. Used on translatewiki.net
 * @author Niklas Laxström
 * @author Krinkle
 * @copyright Copyright © 2008-2013, Niklas Laxström
 * @copyright Copyright © 2011, Krinkle
 * @license GPL-2.0-or-later
 */
class PremadeIntuitionTextdomains extends PremadeMediaWikiExtensionGroups {
	protected array $groups;

	/** @inheritDoc */
	public function __construct( string $def, string $path ) {
		parent::__construct( $def, $path );
		$this->idPrefix = 'tsint-';
	}

	protected function getDefaultNamespace(): int {
		return NS_INTUITION;
	}

	protected function processGroups( array $groups ): array {
		$fixedGroups = [];
		foreach ( $groups as $g ) {
			$name = $g['name'];
			$sanitizedName = preg_replace( '/\s+/', '', strtolower( $name ) );

			$id = $g['id'] ?? $this->idPrefix . $sanitizedName;

			// Canonical names for Intuition text-domains are lowercase
			// e.g. "MyTool" -> "mytool/en.json"
			$file = $g['file'] ?? "$sanitizedName/%CODE%.json";

			$descMsg = $g['descmsg'] ?? "$id-desc";

			$newGroup = [
				'name' => 'Intuition - ' . $name,
				'file' => $file,
				'descmsg' => $descMsg,
			];

			// Prefix is required, if not customized use the sanitized name
			if ( !isset( $g['prefix'] ) ) {
				$g['prefix'] = "$sanitizedName-";
			}

			// All messages are prefixed with their groupname
			$g['mangle'] = [ '*' ];

			// Prevent E_NOTICE undefined index.
			// PremadeMediaWikiExtensionGroups::factory should probably check this better instead
			if ( !isset( $g['ignored'] ) ) {
				$g['ignored'] = [];
			}

			if ( !isset( $g['optional'] ) ) {
				$g['optional'] = [];
			}

			$g['format'] = 'json';

			$copyVars = [
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

			foreach ( $copyVars as $var ) {
				if ( isset( $g[$var] ) ) {
					$newGroup[$var] = $g[$var];
				}
			}

			$fixedGroups[$id] = $newGroup;
		}

		return $fixedGroups;
	}
}

class_alias( PremadeIntuitionTextdomains::class, 'PremadeIntuitionTextdomains' );
