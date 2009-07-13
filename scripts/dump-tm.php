<?php
/**
 * @author Niklas Laxstrom
 *
 * @copyright Copyright © 2009, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @file
 */

$optionsWithArgs = array( 'lang', 'target' );
require( dirname( __FILE__ ) . '/cli.inc' );

function showUsage() {
	STDERR( <<<EOT
Translation memory dumper. Dumps are in po format

Usage: php dump-tm.php [options...]

Options:
  --target      Target directory for exported translation memories
  --lang        Comma separated list of language codes or *
EOT
);
	exit( 1 );
}

if ( isset( $options['help'] ) || $args === 1 ) {
	showUsage();
}

if ( !isset( $options['target'] ) ) {
	STDERR( "You need to specify target directory" );
	exit( 1 );
}
if ( !isset( $options['lang'] ) ) {
	STDERR( "You need to specify languages to export" );
	exit( 1 );
}

$langs = Cli::parseLanguageCodes( $options['lang'] );
$target = $options['target'];

$groups = MessageGroups::singleton()->getGroups();

foreach( $langs as $code ) {
$data = '';
foreach ( $groups as $g ) {
	if ( $g->isMeta() || !$g->exists() ) continue;

	$writer = new GettextFormatWriter( $g );
	$collection = $g->initCollection( $code );
	$collection->setInfile( $g->load( $code ) );
	$collection->filter( 'ignored' );
	$collection->filter( 'translated', false );
	$output = $writer->webExport( $collection );
	$data .= preg_replace( "/^.*\n\n/Us", '', $output );
}
$data = <<<FOO
# Translation of none to Finnish
msgid ""
msgstr ""


FOO
. $data;
file_put_contents( "$target/$code.po", $data );

}
