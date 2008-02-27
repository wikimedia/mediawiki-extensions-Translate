<?php

$optionsWithArgs = array( 'lang', 'target', 'group' );

$IP = "../../maintenance/";
require_once( $IP . 'commandLine.inc' );

function showUsage() {
	print <<<EOT
Message exporter.

Usage: php export.php [options...]

Options:
  --target      Target directory for exported files
  --lang        Comma separated list of language codes
  --group       Group id

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

if ( !isset($options['group']) ) {
	echo "You need to specify group\n\n";
	showUsage();
}

if ( !is_writable( $options['target'] ) ) {
	echo "Target directory is not writable\n\n";
	showUsage();
}

$langs = array_map( 'trim', explode( ',', $options['lang'] ) );


$group = MessageGroups::getGroup( $options['group'] );

if ( !$group instanceof MessageGroup ) {
	echo "Invalid group\n\n";
	exit( 1 );
}

if ( $group->canExportToFile() ) {
	$exporter = $group->getFileExporter();
	$exporter->export( $langs, $options['target'] );
} else {
	echo "Cannot export to file\n\n";
}
