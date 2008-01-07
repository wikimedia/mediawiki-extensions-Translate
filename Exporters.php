<?php
if (!defined('MEDIAWIKI')) die();

/**
 * Classes which faciliate command line exporting of messages to source files.
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2008 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

interface MessageExporter {
	public function __construct( $group, $languages, $target );
	public function export();
}

class CoreExporter implements MessageExporter {
	protected $group = null;
	protected $languages = array();
	protected $target = null;

	public function __construct( $group, $languages, $target ) {
		$this->group = $group;
		$this->languages = $languages;
		$this->target = $target;
	}

	public function export() {
		foreach ( $this->languages as $code ) {
			$taskOptions = new TaskOptions( $code, 0, 0, 0, null );
			$task = TranslateTasks::getTask( 'export-to-file' );
			$task->init( $this->group, $taskOptions );
			file_put_contents(
				$this->target . '/'. $this->group->getMessageFile( $code ),
				$task->execute()
			);
		}
	}

}

class StandardExtensionExporter implements MessageExporter {
	protected $group = null;
	protected $languages = array();
	protected $target = null;

	public function __construct( $group, $languages, $target ) {
		$this->group = $group;
		$this->languages = $languages;
		$this->target = $target;
	}

	public function export() {
		global $wgTranslateExtensionDirectory;
		$filename = $this->group->getMessageFile( '' /* Ignored */ );
		list( $header, $sections ) = $this->parse( $wgTranslateExtensionDirectory . '/' . $filename );
		$output = $header;
		$output .= $this->exportLanguage( 'en', $sections );
		$output .= $this->exportLanguage( 'qqq', $sections );

		$languages = Language::getLanguageNames( false );
		foreach ( array_keys( $languages ) as $code ) {
			if ( $code === 'en' || $code === 'qqq' ) continue;
			$output .= $this->exportLanguage( $code, $sections );
		}

		// The hacks, aka copies of another languages
		$output .= implode( '', $sections );

		$targetFile = $this->target . '/' . $filename;
		wfMkdirParents( dirname( $targetFile ) );
		file_put_contents( $targetFile, $output );
	}

	protected function exportLanguage( $code, &$sections ) {
		$output = '';
		if ( in_array( $code, $this->languages ) ) {
			$taskOptions = new TaskOptions( $code, 0, 0, 0, null );
			$task  = TranslateTasks::getTask( 'export-to-file' );
			$task->init( $this->group, $taskOptions );
			$output = $task->execute() . "\n";
			unset( $sections[$code] );
		} elseif ( isset( $sections[$code] ) ) {
			# Hacks...
			if ( strpos( $sections[$code], "];\n" ) === false ) {
				$output = $sections[$code];
				unset( $sections[$code] );
			}
		}
		return $output;
	}

	protected function parse( $filename ) {
		$data = file_get_contents( $filename );

		$headerP = '
		.*? # Ungreedily eat header
		\$messages \s* = \s* array\(\);';
		/*
		* x to have nice syntax
		* u for utf-8
		* s for dot matches newline
		*/
		$fileStructure = "~^($headerP)(.*)~xsu";

		$matches = array();
		if ( !preg_match( $fileStructure, $data, $matches ) ) {
			throw new MWException( "Unable to parse file structure" );
		}

		list( , $header, $data) = $matches;

		$sectionP = '(?: /\*\* .*? \*/ )? (?: ( [^\n]*?  \S;\n ) | (?: .*?  \n\);\n\n ) )';
		$codeP = '\$messages\[\' (.*?) \'\]';

		$sectionMatches = array();
		if ( !preg_match_all( "~$sectionP~xsu", $data, $sectionMatches, PREG_SET_ORDER ) ) {
			throw new MWException( "Unable to parse sections" );
		}

		$sections = array();
		$unknown = array();
		foreach ( $sectionMatches as $index => $data ) {
			$code = array();
			if ( !preg_match( "~$codeP~xsu", $data[0], $code ) ) {
				echo "Malformed section:\n$data[0]";
				$unknown[] = $data[0];
			} else {
				$sections[$code[1]] = $data[0];
			}
		}

		ksort( $sections );
		$sections[] = implode( "\n", $unknown );

		return array( $header, $sections );
	}
}