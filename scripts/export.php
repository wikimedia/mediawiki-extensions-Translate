<?php

use MediaWiki\Extension\Translate\Synchronization\ExportTranslationsMaintenanceScript;

$env = getenv( 'MW_INSTALL_PATH' );
$IP = $env !== false ? $env : __DIR__ . '/../../..';
require_once "$IP/maintenance/Maintenance.php";
require_once __DIR__ . '/../src/Synchronization/ExportTranslationsMaintenanceScript.php';
$maintClass = ExportTranslationsMaintenanceScript::class;
require_once RUN_MAINTENANCE_IF_MAIN;
