<?php

namespace MediaWiki\Extension\Notifications\Hooks;

use MediaWiki\Extension\Notifications\Model\Event;

/**
 * Stub for interface in Echo
 */
interface EchoGetBundleRulesHook {
	public function onEchoGetBundleRules( Event $event, string &$bundleKey );
}
