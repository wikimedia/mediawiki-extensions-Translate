<?php

use MediaWiki\Extension\Translate\Statistics\UpdateTranslatorActivityMaintenanceScript;

$env = getenv( 'MW_INSTALL_PATH' );
$IP = $env !== false ? $env : __DIR__ . '/../../..';
require_once "$IP/maintenance/Maintenance.php";
require_once __DIR__ . '/../src/Statistics/UpdateTranslatorActivityMaintenanceScript.php';
$maintClass = UpdateTranslatorActivityMaintenanceScript::class;
require_once RUN_MAINTENANCE_IF_MAIN;
