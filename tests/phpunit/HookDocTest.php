<?php
/**
 * Checks hook documentation is up to date.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 */

class HookDocTest extends MediaWikiTestCase {
	protected $documented = [];
	protected $used = [];
	protected $paths = [
		'php' => [
			'',
			'api',
			'ffs',
			'messagegroups',
			'specials',
			'tag',
			'translationaids',
			'ttmserver',
			'utils',
			'webservices',
		],
		'js' => [
			'resources/js',
		],
	];

	protected function setUp() {
		parent::setUp();
		$contents = file_get_contents( __DIR__ . '/../../hooks.txt' );
		$blocks = preg_split( '/\n\n/', $contents );
		$type = false;

		foreach ( $blocks as $block ) {
			if ( $block === '=== PHP events ===' ) {
				$type = 'php';
				continue;
			} elseif ( $block === '=== JavaScript events ===' ) {
				$type = 'js';
				continue;
			} elseif ( !$type ) {
				continue;
			}

			if ( $type ) {
				list( $name, $params ) = self::parseDocBlock( $block );
				$this->documented[$type][$name] = $params;
			}
		}

		$prefix = __DIR__ . '/../..';
		foreach ( $this->paths['php'] as $path ) {
			$path = "$prefix/$path/";
			$hooks = self::getHooksFromPath( $path, 'self::getPHPHooksFromFile' );
			foreach ( $hooks as $name => $params ) {
				$this->used['php'][$name] = $params;
			}
		}

		foreach ( $this->paths['js'] as $path ) {
			$path = "$prefix/$path/";
			$hooks = self::getHooksFromPath( $path, 'self::getJSHooksFromFile' );
			foreach ( $hooks as $name => $params ) {
				$this->used['js'][$name] = $params;
			}
		}
	}

	protected static function getJSHooksFromFile( $file ) {
		$content = file_get_contents( $file );
		$m = [];
		preg_match_all( '/\bmw\.hook\(\s*[\'"]([^\'"]+)[\'"]\s*\).fire\(/', $content, $m );
		$hooks = [];
		foreach ( $m[1] as $hook ) {
			$hooks[$hook] = [];
		}

		return $hooks;
	}

	protected static function getPHPHooksFromFile( $file ) {
		$content = file_get_contents( $file );
		$m = [];
		preg_match_all( '/\bHooks::run\(\s*[\'"]([^\'"]+)/', $content, $m );
		$hooks = [];
		foreach ( $m[1] as $hook ) {
			$hooks[$hook] = [];
		}

		return $hooks;
	}

	protected static function getHooksFromPath( $path, $callback ) {
		$hooks = [];
		$dh = opendir( $path );
		if ( $dh ) {
			$file = readdir( $dh );
			while ( $file !== false ) {
				if ( filetype( $path . $file ) === 'file' ) {
					$hooks = array_merge( $hooks, call_user_func( $callback, $path . $file ) );
				}
				$file = readdir( $dh );
			}
			closedir( $dh );
		}

		return $hooks;
	}

	protected static function parseDocBlock( $block ) {
		preg_match( '/^;([^ ]+):/', $block, $match );
		$name = $match[1];
		preg_match_all( '/^ ([^ ]+)\s+([ ^])/', $block, $matches, PREG_SET_ORDER );
		$params = [];
		foreach ( $matches as $match ) {
			$params[$match[2]] = $match[1];
		}

		return [ $name, $params ];
	}

	public function testHookIsDocumentedPHP() {
		foreach ( $this->used['php'] as $hook => $params ) {
			$this->assertArrayHasKey( $hook, $this->documented['php'], "PHP hook $hook is documented" );
		}
	}

	public function testHookExistsPHP() {
		foreach ( $this->documented['php'] as $hook => $params ) {
			$this->assertArrayHasKey( $hook, $this->used['php'], "Documented php hook $hook exists" );
		}
	}

	public function testHookIsDocumentedJS() {
		foreach ( $this->used['js'] as $hook => $params ) {
			$this->assertArrayHasKey( $hook, $this->documented['js'], "Js hook $hook is documented" );
		}
	}

	public function testHookExistsJS() {
		foreach ( $this->documented['js'] as $hook => $params ) {
			$this->assertArrayHasKey( $hook, $this->used['js'], "Documented js hook $hook exists" );
		}
	}
}
