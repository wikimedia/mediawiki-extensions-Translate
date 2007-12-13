<?php
$IP = "../../maintenance/";
require_once( $IP . 'commandLine.inc' );

$groups = MessageGroups::singleton()->getGroups();

$hugearray = array();

foreach ( $groups as $g ) {
	# Skip meta thingies
	if ( $g->isMeta() ) continue;

	$messages = $g->getDefinitions();
	$id = $g->getId();


	echo "Working with $id\n";
	foreach ( $messages as $key => $data ) {
		if ( isset($hugearray[$key]) ) {
			echo "Key $key already belongs to $hugearray[$key], conflict with $id\n";
		} else {
			$hugearray[$key] = &$id;
		}
	}
	unset($id); // Disconnect the previous references to this $id
}

file_put_contents( TRANSLATE_INDEXFILE, serialize( $hugearray ) );