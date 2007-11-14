<?php
$IP = "../../maintenance/";
require_once( $IP . 'commandLine.inc' );

$groups = MessageGroups::singleton()->getGroups();

$hugearray = array();

foreach ( $groups as $g ) {
	$messages = $g->getDefinitions();
	$id = $g->getId();

	if ( $g->isMeta() ) continue;
	echo "Working with $id\n";
	foreach ( $messages as $key => $data ) {
		if ( isset($hugearray[$key]) ) {
			echo "Key $key already belongs to $hugearray[$key], conflict with $id\n";
		} else {
			// PHP doesn't seem to be intelligent enough to save memory
			$hugearray[$key] =& $id;
		}
	}
}

file_put_contents(TRANSLATE_INDEXFILE, serialize( $hugearray ) );
