<?php

/**
 * Generic file format support for Phython single dictionary formatted files.
 * @ingroup FFS
 */
class PythonSingleFFS extends SimpleFFS {
	private $fw = null;
	static $data = null;

	/**
	 * @param $code
	 * @return array
	 */
	public function read( $code ) {
		// Map codes
		$code = $this->group->mapCode( $code );

		// TODO: Improve this code to not use static variables.
		if ( !isset( self::$data[$this->group->getId()] ) ) {
			/* N levels of escaping
			 * - for PHP string
			 * - for Python string
			 * - for shell command
			 * - and wfShellExec will wrap the whole command once more
			 */
			$filename = $this->group->getSourceFilePath( $code );
			$filename = addcslashes( $filename, '\\"' );
			$command = wfEscapeShellArg( "import simplejson as json; execfile(\"$filename\"); print json.dumps(msg)" );
			$json = wfShellExec( "python -c $command" );
			self::$data[$this->group->getId()] = FormatJson::decode( $json, true );
		}

		if ( !isset( self::$data[$this->group->getId()][$code] ) ) {
			self::$data[$this->group->getId()][$code] = array();
		}

		return array( 'MESSAGES' => self::$data[$this->group->getId()][$code] );
	}

	/**
	 * @param $collection MessageCollection
	 */
	public function write( MessageCollection $collection ) {
		if ( $this->fw === null ) {
			$sourceLanguage = $this->group->getSourceLanguage();
			$outputFile = $this->writePath . '/' . $this->group->getTargetFilename( $sourceLanguage );
			wfMkdirParents( dirname( $outputFile ), null, __METHOD__ );
			$this->fw = fopen( $outputFile, 'w' );
			$this->fw = fopen( $this->writePath . '/' . $this->group->getTargetFilename( $sourceLanguage ), 'w' );
			fwrite( $this->fw, "# -*- coding: utf-8 -*-\nmsg = {\n" );
		}

		// Not sure why this is needed, only continue if there are translations.
		$collection->loadTranslations();
		$ok = false;
		foreach ( $collection as $messages ) {
			if ( $messages->translation() != '' ) {
				$ok = true;
			}
		}

		if ( !$ok ) {
			return;
		}

		$authors = $this->doAuthors( $collection );
		if ( $authors != '' ) {
			fwrite( $this->fw, "$authors" );
		}

		$code = $this->group->mapCode( $collection->code );
		fwrite( $this->fw, "\t'{$code}': {\n" );
		fwrite( $this->fw, $this->writeBlock( $collection ) );
		fwrite( $this->fw, "\t},\n" );
	}

	/**
	 * @param $collection MessageCollection
	 * @return string
	 */
	public function writeIntoVariable( MessageCollection $collection ) {
		return <<<PHP
# -*- coding: utf-8 -*-
msg = {
{$this->doAuthors($collection)}\t'{$collection->code}': {
{$this->writeBlock( $collection )}\t}
}
PHP;
	}

	/**
	 * @param $collection MessageCollection
	 * @return string
	 */
	protected function writeBlock( MessageCollection $collection ) {
		$block = '';
		$messages = array();

		foreach ( $collection as $message ) {
			if ( $message->translation() == '' ) {
				continue;
			}

			$translation = str_replace( '\\', '\\\\', $message->translation() );
			$translation = str_replace( '\'', '\\\'', $translation );
			$translation = str_replace( "\n", '\n', $translation );
			$translation = str_replace( TRANSLATE_FUZZY, '', $translation );

			$messages[$message->key()] = $translation;
		}

		ksort( $messages );

		foreach ( $messages as $key => $translation ) {
			$block .= "\t\t'{$key}': u'{$translation}',\n";
		}

		return $block;
	}

	/**
	 * @param $collection MessageCollection
	 * @return string
	 */
	protected function doAuthors( MessageCollection $collection ) {
		$output = '';

		// Read authors.
		$fr = fopen( $this->group->getSourceFilePath( $collection->code ), 'r' );
		$authors = array();

		while ( !feof( $fr ) ) {
			$line = fgets( $fr );

			if ( strpos( $line, "\t# Author:" ) === 0 ) {
				$authors[] = trim( substr( $line, strlen( "\t# Author: " ) ) );
			} elseif ( $line === "\t'{$collection->code}': {\n" ) {
				break;
			} else {
				$authors = array();
			}
		}

		$authors2 = $collection->getAuthors();
		$authors2 = $this->filterAuthors( $authors2, $collection->code );
		$authors = array_unique( array_merge( $authors, $authors2 ) );

		foreach ( $authors as $author ) {
			$output .= "\t# Author: $author\n";
		}

		return $output;
	}

	public function __destruct() {
		if ( $this->fw !== null ) {
			fwrite( $this->fw, "}" );
			fclose( $this->fw );
		}
	}
}
