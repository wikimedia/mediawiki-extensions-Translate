<?php

namespace MediaWiki\Extension\Notifications\Hooks;

/** Stub for interface in Echo */
interface BeforeCreateEchoEventHook {
	public function onBeforeCreateEchoEvent(
		array &$notifications,
		array &$notificationCategories,
		array &$notificationIcons
	);
}
