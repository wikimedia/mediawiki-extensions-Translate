<?php

use MediaWiki\Extension\Translate\TtmServer\ExportTtmServerDumpMaintenanceScript;

$env = getenv( 'MW_INSTALL_PATH' );
$IP = $env !== false ? $env : __DIR__ . '/../../..';
require_once "$IP/maintenance/Maintenance.php";
require_once __DIR__ . '/../src/TtmServer/ExportTtmServerDumpMaintenanceScript.php';
$maintClass = ExportTtmServerDumpMaintenanceScript::class;
require_once RUN_MAINTENANCE_IF_MAIN;
