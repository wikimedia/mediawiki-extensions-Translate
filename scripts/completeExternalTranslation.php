<?php

use MediaWiki\Extension\Translate\Synchronization\CompleteExternalTranslationMaintenanceScript;

$env = getenv( 'MW_INSTALL_PATH' );
$IP = $env !== false ? $env : __DIR__ . '/../../..';
require_once "$IP/maintenance/Maintenance.php";
require_once __DIR__ . '/../src/Synchronization/CompleteExternalTranslationMaintenanceScript.php';
$maintClass = CompleteExternalTranslationMaintenanceScript::class;
require_once RUN_MAINTENANCE_IF_MAIN;
