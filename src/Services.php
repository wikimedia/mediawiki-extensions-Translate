<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
namespace MediaWiki\Extensions\Translate;

use MediaWiki\Extensions\Translate\Statistics\TranslatorActivity;
use MediaWiki\Extensions\Translate\Synchronization\GroupSynchronizationCache;
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
		return $this->container->get( $id );
	}

	/** @inheritDoc */
	public function has( $id ) {
		return $this->container->has( $id );
	}

	public function getTranslatorActivity(): TranslatorActivity {
		return $this->container->getService( 'Translate:TranslatorActivity' );
	}

	public function getGroupSynchronizationCache(): GroupSynchronizationCache {
		return $this->container->getService( 'Translate:GroupSynchronizationCache' );
	}
}
