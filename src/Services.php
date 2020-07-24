<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
namespace MediaWiki\Extensions\Translate;

use MediaWiki\Extensions\Translate\Statistics\TranslatorActivity;
use MediaWiki\Extensions\Translate\Synchronization\GroupSynchronizationCache;
use MediaWiki\Extensions\Translate\Utilities\ParsingPlaceholderFactory;
use MediaWiki\MediaWikiServices;
use Psr\Container\ContainerInterface;

/**
 * Minimal service container.
 *
 * Main purpose is to give type-hinted getters for services defined in this extensions.
 *
 * @since 2020.04
 */
class Services implements ContainerInterface {
	/** @var self */
	private static $instance;

	/** @var MediaWikiServices */
	private $container;

	private function __construct( MediaWikiServices $container ) {
		$this->container = $container;
	}

	public static function getInstance(): Services {
		self::$instance = self::$instance ?? new self( MediaWikiServices::getInstance() );
		return self::$instance;
	}

	/** @inheritDoc */
	public function get( $id ) {
		// Can be changed to using ::get once we drop support for MW 1.33
		return $this->container->getService( $id );
	}

	/** @inheritDoc */
	public function has( $id ) {
		// Can be changed to using ::has once we drop support for MW 1.33
		return $this->container->hasService( $id );
	}

	public function getGroupSynchronizationCache(): GroupSynchronizationCache {
		return $this->container->getService( 'Translate:GroupSynchronizationCache' );
	}

	/** @since 2020.07 */
	public function getParsingPlaceholderFactory(): ParsingPlaceholderFactory {
		return $this->container->getService( 'Translate:ParsingPlaceholderFactory' );
	}

	public function getTranslatorActivity(): TranslatorActivity {
		return $this->container->getService( 'Translate:TranslatorActivity' );
	}

}
