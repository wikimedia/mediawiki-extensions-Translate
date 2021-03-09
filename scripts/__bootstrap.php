<?php
declare( strict_types = 1 );

/*
 * Boilerpate code for bootstrapping maintenance scripts.
 *
 * This code must be in global scope. Callers must define $class;
 */
$env = getenv( 'MW_INSTALL_PATH' );
$IP = $env !== false ? $env : __DIR__ . '/../../..';
require_once "$IP/maintenance/Maintenance.php";
// Manually load required classes, as autoloader is not available until RUN_MAINTENANCE_IF_MAIN
require_once __DIR__ . '/../src/Utilities/BaseMaintenanceScript.php';
// $maintClass must be after Maintenance.php
// @phan-suppress-next-line PhanUndeclaredGlobalVariable
$maintClass = $class;
$file = strtr( $maintClass, [ 'MediaWiki\\Extension\\Translate\\' => '', '\\' => '/' ] );
require_once __DIR__ . "/../src/$file.php";
require_once RUN_MAINTENANCE_IF_MAIN;
