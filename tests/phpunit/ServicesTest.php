<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\Services;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Services
 */
class ServicesTest extends MediaWikiIntegrationTestCase {
	public function testNoExceptions() {
		$services = Services::getInstance();
		$class = new ReflectionClass( Services::class );
		$reflectionMethods = $class->getMethods( ReflectionMethod::IS_PUBLIC );
		$methods = [];
		foreach ( $reflectionMethods as $methodObj ) {
			$method = $methodObj->getName();
			if ( !$methodObj->isStatic() && preg_match( '/^get[A-Z]/', $method ) ) {
				$methods[] = $method;
			}
		}

		foreach ( $methods as $method ) {
			$services->$method();
		}

		$this->assertTrue( true, 'All services can be constructed' );
	}
}
