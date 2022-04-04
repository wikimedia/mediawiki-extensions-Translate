<?php

use MediaWiki\Extension\Translate\PageTranslation\MoveTranslatableBundleMaintenanceScript;

trigger_error(
	'The script moveTranslatablePage.php has been deprecated. '
	. 'Use moveTranslatableBundle.php instead.',
	E_USER_DEPRECATED
 );

$class = MoveTranslatableBundleMaintenanceScript::class;
require_once '__bootstrap.php';
