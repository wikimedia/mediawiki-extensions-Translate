<?php
/**
 * Quick script to generate YAML configuration for StatusNet plugins.
 *
 * @todo Use Maitenance class and add target option for writing output file.
 */

$plugins = array();

function getPotFiles( $path, $pattern ) {
	global $plugins;

	$path = rtrim( str_replace( "\\", "/", $path ), '/' ) . '/';
	$matches = Array();
	$entries = Array();
	$dir = dir( $path );
	while ( false !== ( $entry = $dir->read() ) ) {
		$entries[] = $entry;
	}
	$dir->close();
	foreach ( $entries as $entry ) {
		$fullname = $path . $entry;
		if ( $entry != '.' && $entry != '..' && is_dir( $fullname ) ) {
			$subFolderResults = getPotFiles( $fullname, $pattern );
		} else if ( is_file( $fullname ) && preg_match( $pattern, $entry ) ) {
			$pathParts = explode( '/', $fullname );
			$plugins[] = substr( array_pop( $pathParts ), 0, -4 );
		}
	}
}

$baseFolder = '/home/betawiki/projects/statusnet/plugins/';
$filePattern = '/[.]pot$/';

getPotFiles( $baseFolder, $filePattern );

$header = <<<PHP
TEMPLATE:
  BASIC:
    description: "{{int:translate-group-desc-statusnet-plugin}}"
    namespace: NS_STATUSNET
    display: out/statusnet/
    class: FileBasedMessageGroup

  FILES:
    class: GettextFFS
    codeMap:
      en-gb:   en_GB
      en-us:   en_US
      nl-be:   nl_BE
      no:      nb
      pt-br:   pt_BR
      zh-hans: zh_CN
      zh-hant: zh_TW

    header: |
      # This file is distributed under the same license as the StatusNet package.
      #

  MANGLER:
    class: StringMatcher
    patterns:
      - "*"

  CHECKER:
    class: MessageChecker
    checks:
      - printfCheck
---
PHP;

echo $header . "\n";

$basePluginFolder = "statusnet/plugins/";
$localeFolder = "/locale/%CODE%/LC_MESSAGES/";

asort( $plugins );
$numberPlugins = count( $plugins );
$count = 0;

foreach ( $plugins as $plugin ) {
	$pluginL = strtolower( $plugin );

	echo "BASIC:\n";
	echo "  id: out-statusnet-plugin-" . $pluginL . "\n";
	echo "  label: StatusNet - " . $plugin . "\n";
	echo "  display: out/statusnet/plugin/" . $pluginL . "\n";
	echo "  codeBrowser: http://gitorious.org/statusnet/mainline/blobs/0.9.x/plugins/" . $plugin . "/%FILE%#line%LINE%\n\n";
	echo "FILES:\n";
	echo "  sourcePattern: %GROUPROOT%/" . $basePluginFolder . $plugin . $localeFolder . $plugin . ".po\n";
	echo "  definitionFile: %GROUPROOT%/" . $basePluginFolder . $plugin . "/locale/" . $plugin . ".pot\n";
	echo "  targetPattern: " . $basePluginFolder . $plugin . $localeFolder . $plugin . ".po\n\n";
	echo "MANGLER:\n";
	echo "  prefix: " . $pluginL . "-\n";

	$count++;

	if ( $count < $numberPlugins ) {
		echo "---\n";
	}
}
