<?php

$optionsWithArgs = array( 'lang', 'target' );

$IP = "../../maintenance/";
require_once( $IP . 'commandLine.inc' );

function showUsage() {
	print <<<EOT
Message exporter. Currently supports only cor messages.

Usage: php export.php [options...]

Options:
  --target      Target directory for exported files
  --lang        Comma separated list of language codes

EOT;
	exit( 1 );
}

if ( !isset($options['target']) ) {
	echo "You need to specify target directory\n\n";
	showUsage();
}

if ( !isset($options['lang']) ) {
	echo "You need to specify languages to export\n\n";
	showUsage();
}

if ( !is_writable( $options['target'] ) ) {
	echo "Target directory is not writable\n\n";
	showUsage();
}

$langs = array_map( 'trim', explode( ',', $options['lang'] ) );


$group = MessageGroups::getGroup( 'core' );

foreach ( $langs as $code ) {
	$taskOptions = new TaskOptions( $code, 0, 0, 0, null );
	$task  = TranslateTasks::getTask( 'export-to-file' );
	$task->init( $group, $taskOptions );
	file_put_contents(
		$options['target'] . '/'. $group->getMessageFile( $code ),
		$task->execute()
	);
}