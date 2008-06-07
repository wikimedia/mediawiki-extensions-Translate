<?php

$dir = dirname( __FILE__ ); $IP = "$dir/../..";
@include("$dir/../CorePath.php"); // Allow override
require_once( "$IP/maintenance/commandLine.inc" );

$groups = MessageGroups::singleton()->getGroups();

$hugearray = array();

foreach ( $groups as $g ) {
	# Skip meta thingies
	if ( $g->isMeta() ) continue;

	$messages = $g->getDefinitions();
	$id = $g->getId();

	if ( is_array( $messages ) ) {
		echo "Working with $id\n";
	} else {
		echo "Something wrong with $id... skipping\n";
		continue;
	}

	$namespace = $g->namespaces[0];

	foreach ( $messages as $key => $data ) {
		# Force all keys to lower case, because the case doesn't matter and it is
		# easier to do comparing when the case of first letter is unknown, because
		# mediawiki forces it to upper case
		$key = strtolower( "$namespace:$key" );
		if ( isset($hugearray[$key]) ) {
			echo "Key $key already belongs to $hugearray[$key], conflict with $id\n";
		} else {
			$hugearray[$key] = &$id;
		}
	}
	unset($id); // Disconnect the previous references to this $id
}

file_put_contents( TRANSLATE_INDEXFILE, serialize( $hugearray ) );
