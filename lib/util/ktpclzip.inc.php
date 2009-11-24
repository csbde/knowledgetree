<?php
/**
 * $Id$
 *
 * Simple wrapper class for working with the pecl zip class
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Limited
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
 * California 94120-7775, or email info@knowledgetree.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original
 * copyright notice.
 * Contributor( s): ______________________________________
 */

require_once (KT_DIR . '/thirdparty/peclzip/pclzip.lib.php');

/**
 * Class to create, extract and download a zip file using the pclzip library
 * TODO: Class base was borrowed from lib/foldremanagement/compressionArchiveUtil.inc.php
 * 		 and currently only supports extractintg a zip archive. The other logic needs to be
 * 		 woven to work with pclzip and tested for relevancy.
 */

class KTPclZip {
	
	var $sTmpPath = '';
	var $sZipFileName = '';
	var $sZipFile = '';
	var $sPattern = '';
	var $sFolderPattern = '';
	var $aPaths = array ();
	var $aReplaceKeys = array ();
	var $aReplaceValues = array ();
	var $sOutputEncoding = 'UTF-8';
	var $extension = 'zip';
	var $exportCode = null;
	var $_pclZip = null;
	
	/**
	 * Constructor
	 *
	 * @param string $sZipFileName The name of the zip file.
	 * @param string $exportCode The code to use if a zip file has already been created.
	 */
	function KTPclZip($sZipFileName = 'kt_pclzip', $exportCode = null, $extension = 'zip') {
		
		//TODO: Cherry pick some of this logic borrowed from lib/foldremanagement/compressionArchiveUtil.inc.php
		$this->oKTConfig = & KTConfig::getSingleton ();
		$this->oStorage = & KTStorageManagerUtil::getSingleton ();
		
		$this->sOutputEncoding = $this->oKTConfig->get ( 'export/encoding', 'UTF-8' );
		$this->extension = $extension;
		
		$this->sPattern = "[\*|\%|\\\|\/|\<|\>|\+|\:|\?|\||\'|\"]";
		$this->sFolderPattern = "[\*|\%|\<|\>|\+|\:|\?|\||\'|\"]";
		
		if (! empty ( $exportCode )) {
			$this->exportCode = $exportCode;
		} else {
			$this->exportCode = KTUtil::randomString ();
		}

		// Check if the temp directory has been created and stored in session
		$aData = KTUtil::arrayGet ( $_SESSION ['zipcompression'], $exportCode );
		if (! empty ( $aData ) && isset ( $aData ['dir'] )) {
			$sTmpPath = $aData ['dir'];
		} else {
			$sBasedir = $this->oKTConfig->get ( "urls/tmpDirectory" );
			$sTmpPath = tempnam ( $sBasedir, 'kt_compress_zip' );
			
			unlink ( $sTmpPath );
			mkdir ( $sTmpPath, 0755 );
		}
		
		$this->sTmpPath = $sTmpPath;
		$this->sZipFileName = $sZipFileName;
		$this->aPaths = array ();

		$this->_pclZip = new PclZip($sZipFileName);
		
		/* //TODO: Cherry pick some of this logic borrowed from lib/foldremanagement/compressionArchiveUtil.inc.php
		
		$aReplace = array ("[" => "[[]", " " => "[ ]", "*" => "[*]", "?" => "[?]" );
		
		$this->aReplaceKeys = array_keys ( $aReplace );
		$this->aReplaceValues = array_values ( $aReplace );
		*/
	}

	/**
	 * Creates a zip archive using the sFolder contents
	 */
	function createZipFile($sFolder) {
		//Overriding $this->aPaths with specified
        if (!is_dir($sFolder)) {
            PEAR::raiseError( sprintf( _kt( "Couldn't create zip file, invalid folder was specified %s " ) , $sFolder ));
        }

		if (!is_null($sFolder)) {
			$this->aPaths = $sFolder;
		}
		
		$excludePath = $this->getExcludePath($sFolder, DIRECTORY_SEPARATOR);
		
		// Create the zip archive using the PclZip Wrapper
		if ($this->_pclZip->create ( $sFolder , PCLZIP_OPT_REMOVE_PATH, $excludePath) == 0) {
			//( File_Archive::read ( $this->sTmpPath . '/Root Folder' ), File_Archive::toArchive ( $this->sZipFileName . '.' . $this->extension, File_Archive::toFiles ( $this->sTmpPath ), $this->extension ) );
			return PEAR::raiseError ( _kt ( "Error compressing files" ) );
		}
		
		// Save the zip file and path into session
		$_SESSION ['zipcompression'] = KTUtil::arrayGet ( $_SESSION, 'zipcompression', array () );
		$sExportCode = $this->exportCode;
		$_SESSION ['zipcompression'] [$sExportCode] = array ('file' => $sZipFile, 'dir' => $this->sTmpPath );
		$_SESSION ['zipcompression'] ['exportcode'] = $sExportCode;
		
		$this->sZipFile = $sZipFile;
		return $sExportCode;
	}
	

    /*
     * @params: $sPath folder to start from.
     * @params: $ds directory separator
     */
    static function getExcludePath($sPath, $ds = '/') {
        //Will grab the part of the full path to exclude from the zip contents

        /*
         * For windows the pre drive letter needs to be removed for it to work with the pclzip class for version 2.5
         */
        // Now using pclzip 2.8.2 so no need to strip the drive letter.
        /*
        if (stristr(PHP_OS,'WIN')) {
            $sPath = end(explode(':', $sPath));
        }
        */

        //Generating the exclude path : Flexible method (Can set $cutOff = count($aDir) - 1;) to include the parent folder.
        /*
        $aDir = explode($ds, $sPath);
        $cutOff = count($aDir);
        for ($i = 0; $i < $cutOff; $i++) {
            //echo $aDir[$i] . "\n";
            $excludePath .= $aDir[$i] . '/';
        }
		*/

    	$excludePath = str_replace('\\', '/', $sPath); 
    	
        return $excludePath;
    }
    
	/**
	 * Extract/Unzip the temp folder
	 */
	function extractZipFile($tmpPath = null) {
		
		//Overriding $this->tmpPath if specified
		if (!is_null($tmpPath)) {
			$this->sTmpPath = $tmpPath;
		}

		//Further checking that $tmpPath isn't empty
		/*
		if (empty ( $this->tmpPath )) {
			return PEAR::raiseError ( _kt ( "No temporary path specified to extract to" ) );
		}
		*/		
		
		if ($this->_pclZip->extract ( PCLZIP_OPT_PATH, $this->sTmpPath ) == 0) {
			return PEAR::raiseError ( _kt ( "<font color='red'>Error : Unable to unzip archive</font>" ) );
		}
		
		//Returning the sExportCode for uniformity.
		return $this->sExportCode;
		
		// Save the zip file and path into session
		/* //TODO: Cherry pick some of this logic borrowed from lib/foldremanagement/compressionArchiveUtil.inc.php
		
		$_SESSION ['zipcompression'] = KTUtil::arrayGet ( $_SESSION, 'zipcompression', array () );
		$sExportCode = $this->exportCode;
		$_SESSION ['zipcompression'] [$sExportCode] = array ('file' => $sZipFile, 'dir' => $this->sTmpPath );
		$_SESSION ['zipcompression'] ['exportcode'] = $sExportCode;
		
		$this->sZipFile = $sZipFile;
		return $sExportCode;
		*/
	}
	
	/* //TODO: Cherry pick some of this logic borrowed from lib/foldremanagement/compressionArchiveUtil.inc.php
	static public function get($exportCode) {
		static $zipFolder = null;
		if (is_null ( $zipFolder )) {
			$zipFolder = new KTPclZip ( 'kt_pclzip', $exportCode );
		}
		return $zipFolder;
	}
	*/
	
	/**
	 * Return the full path
	 *
	 * @param mixed $oFolderOrDocument May be a Folder or Document
	 */
	/* //TODO: Cherry pick some of this logic borrowed from lib/foldremanagement/compressionArchiveUtil.inc.php
	
	function getFullFolderPath($oFolder) {
		static $sRootFolder = null;
		
		if (is_null ( $sRootFolder )) {
			$oRootFolder = Folder::get ( 1 );
			$sRootFolder = $oRootFolder->getName ();
		}
		
		$sFullPath = $sRootFolder . '/';
		$sFullPath .= $oFolder->getFullPath ();
		
		if (substr ( $sFullPath, - 1 ) == '/')
			$sFullPath = substr ( $sFullPath, 0, - 1 );
		return $sFullPath;
	}
	*/
	
	/**
	 * Add a document to the zip file
	 */
	function addDocumentToZip($oDocument, $oFolder = null) {
		
		if (empty ( $oFolder )) {
			$oFolder = Folder::get ( $oDocument->getFolderID () );
		}
		/* //TODO: Cherry pick some of this logic borrowed from lib/foldremanagement/compressionArchiveUtil.inc.php
		if (empty ( $oFolder )) {
			$oFolder = Folder::get ( $oDocument->getFolderID () );
		}
		
		$sDocPath = $this->getFullFolderPath ( $oFolder );
		$sDocPath = preg_replace ( $this->sFolderPattern, '-', $sDocPath );
		$sDocPath = $this->_convertEncoding ( $sDocPath, true );
		
		$sDocName = $oDocument->getFileName ();
		$sDocName = preg_replace ( $this->sPattern, '-', $sDocName );
		$sDocName = $this->_convertEncoding ( $sDocName, true );
		
		$sParentFolder = $this->sTmpPath . '/' . $sDocPath;
		$newDir = $this->sTmpPath;
		
		$aFullPath = split ( '/', $sDocPath );
		foreach ( $aFullPath as $dirPart ) {
			$newDir = sprintf ( "%s/%s", $newDir, $dirPart );
			if (! file_exists ( $newDir )) {
				mkdir ( $newDir, 0700 );
			}
		}
		
		$sOrigFile = $this->oStorage->temporaryFile ( $oDocument );
		$sFilename = $sParentFolder . '/' . $sDocName;
		@copy ( $sOrigFile, $sFilename );
		
		$this->aPaths [] = $sDocPath . '/' . $sDocName;
		return true;
		*/
	}
	
	
	
	/**
	 * Add a folder to the zip file
	 */
	/* //TODO: Cherry pick some of this logic borrowed from lib/foldremanagement/compressionArchiveUtil.inc.php
	
	function addFolderToZip($oFolder) {
		$sFolderPath = $this->getFullFolderPath ( $oFolder ) . '/';
		$sFolderPath = preg_replace ( $this->sFolderPattern, '-', $sFolderPath );
		$sFolderPath = $this->_convertEncoding ( $sFolderPath, true );
		
		$newDir = $this->sTmpPath;
		
		$aFullPath = split ( '/', $sFolderPath );
		foreach ( $aFullPath as $dirPart ) {
			$newDir = sprintf ( "%s/%s", $newDir, $dirPart );
			if (! file_exists ( $newDir )) {
				mkdir ( $newDir, 0700 );
			}
		}
		
		$this->aPaths [] = $sFolderPath;
		return true;
	}
	*/
	
	
	/**
	 * Zip the temp folder
	 */
	/* //TODO: Cherry pick some of this logic borrowed from lib/foldremanagement/compressionArchiveUtil.inc.php
	
	function createZipFile($bEchoStatus = FALSE) {
		if (empty ( $this->aPaths )) {
			return PEAR::raiseError ( _kt ( "No folders or documents found to compress" ) );
		}
		
		$config = KTConfig::getSingleton ();
		$useBinary = false; //$config->get('export/useBinary', false);
		

		// Set environment language to output character encoding
		$loc = $this->sOutputEncoding;
		putenv ( "LANG=$loc" );
		putenv ( "LANGUAGE=$loc" );
		$loc = setlocale ( LC_ALL, $loc );
		
		if ($useBinary) {
			$sManifest = sprintf ( "%s/%s", $this->sTmpPath, "MANIFEST" );
			file_put_contents ( $sManifest, join ( "\n", $this->aPaths ) );
		}
		
		$sZipFile = sprintf ( "%s/%s." . $this->extension, $this->sTmpPath, $this->sZipFileName );
		$sZipFile = str_replace ( '<', '', str_replace ( '</', '', str_replace ( '>', '', $sZipFile ) ) );
		
		if ($useBinary) {
			$sZipCommand = KTUtil::findCommand ( "export/zip", "zip" );
			$aCmd = array ($sZipCommand, "-r", $sZipFile, ".", "-i@MANIFEST" );
			$sOldPath = getcwd ();
			chdir ( $this->sTmpPath );
			
			// Note that the popen means that pexec will return a file descriptor
			$aOptions = array ('popen' => 'r' );
			$fh = KTUtil::pexec ( $aCmd, $aOptions );
			
			if ($bEchoStatus) {
				$last_beat = time ();
				while ( ! feof ( $fh ) ) {
					if ($i % 1000 == 0) {
						$this_beat = time ();
						if ($last_beat + 1 < $this_beat) {
							$last_beat = $this_beat;
							print "&nbsp;";
						}
					}
					$contents = fread ( $fh, 4096 );
					if ($contents) {
						print nl2br ( $this->_convertEncoding ( $contents, false ) );
					}
					$i ++;
				}
			}
			pclose ( $fh );
		} else {
			// Create the zip archive using the PEAR File_Archive
			File_Archive::extract ( File_Archive::read ( $this->sTmpPath . '/Root Folder' ), File_Archive::toArchive ( $this->sZipFileName . '.' . $this->extension, File_Archive::toFiles ( $this->sTmpPath ), $this->extension ) );
		}
		
		// Save the zip file and path into session
		$_SESSION ['zipcompression'] = KTUtil::arrayGet ( $_SESSION, 'zipcompression', array () );
		$sExportCode = $this->exportCode;
		$_SESSION ['zipcompression'] [$sExportCode] = array ('file' => $sZipFile, 'dir' => $this->sTmpPath );
		$_SESSION ['zipcompression'] ['exportcode'] = $sExportCode;
		
		$this->sZipFile = $sZipFile;
		return $sExportCode;
	}
	*/

	
	/**
	 * Download the zip file
	 */
	/* //TODO: Cherry pick some of this logic borrowed from lib/foldremanagement/compressionArchiveUtil.inc.php
	
	function downloadZipFile($exportCode = NULL) {
		if (! (isset ( $exportCode ) && ! empty ( $exportCode ))) {
			$exportCode = KTUtil::arrayGet ( $_SESSION ['zipcompression'], 'exportcode' );
		}
		
		$aData = KTUtil::arrayGet ( $_SESSION ['zipcompression'], $exportCode );
		
		if (! empty ( $aData )) {
			$sZipFile = $aData ['file'];
			$sTmpPath = $aData ['dir'];
		} else {
			$sZipFile = $this->sZipFile;
			$sTmpPath = $this->sTmpPath;
		}
		
		if (! file_exists ( $sZipFile )) {
			return PEAR::raiseError ( _kt ( 'The zip file has not been created, if you are downloading a large number of documents
            or a large document then it may take a few minutes to finish.' ) );
		}
		
		$mimeType = 'application/zip; charset=utf-8;';
		$fileSize = filesize ( $sZipFile );
		$fileName = $this->sZipFileName . '.' . $this->extension;
		
		KTUtil::download ( $sZipFile, $mimeType, $fileSize, $fileName );
		KTUtil::deleteDirectory ( $sTmpPath );
		return true;
	}
	*/
	
	/* //TODO: Cherry pick some of this logic borrowed from lib/foldremanagement/compressionArchiveUtil.inc.php
	
	function checkArchiveExists($exportCode = null) {
		if (! (isset ( $exportCode ) && ! empty ( $exportCode ))) {
			$exportCode = KTUtil::arrayGet ( $_SESSION ['zipcompression'], 'exportcode' );
		}
		
		$aData = KTUtil::arrayGet ( $_SESSION ['zipcompression'], $exportCode );
		
		if (! empty ( $aData )) {
			$sZipFile = $aData ['file'];
			$sTmpPath = $aData ['dir'];
		} else {
			$sZipFile = $this->sZipFile;
			$sTmpPath = $this->sTmpPath;
		}
		
		if (! file_exists ( $sZipFile )) {
			return false;
		}
		return true;
	}
	*/
	
	/**
	 * Check that iconv exists and that the selected encoding is supported.
	 */
	/* //TODO: Cherry pick some of this logic borrowed from lib/foldremanagement/compressionArchiveUtil.inc.php
	
	function checkConvertEncoding() {
		if (! function_exists ( "iconv" )) {
			return PEAR::raiseError ( _kt ( 'IConv PHP extension not installed. The zip file compression could not handle output filename encoding conversion !' ) );
		}
		$oKTConfig = $this->oKTConfig;
		$this->sOutputEncoding = $oKTConfig->get ( 'export/encoding', 'UTF-8' );
		
		// Test the specified encoding
		if (iconv ( "UTF-8", $this->sOutputEncoding, "" ) === FALSE) {
			return PEAR::raiseError ( _kt ( 'Specified output encoding for the zip files compression does not exists !' ) );
		}
		return true;
	}
	
	function _convertEncoding($sMystring, $bEncode) {
		if (strcasecmp ( $this->sOutputEncoding, "UTF-8" ) === 0) {
			return $sMystring;
		}
		if ($bEncode) {
			return iconv ( "UTF-8", $this->sOutputEncoding, $sMystring );
		} else {
			return iconv ( $this->sOutputEncoding, "UTF-8", $sMystring );
		}
	}
	*/
	
	/* //TODO: Cherry pick some of this logic borrowed from lib/foldremanagement/compressionArchiveUtil.inc.php
	
	static public function checkDownloadSize($object) {
		return true;
		
		if ($object instanceof Document || $object instanceof DocumentProxy) {
		}
		
		if ($object instanceof Folder || $object instanceof FolderProxy) {
			$id = $object->iId;
			
			// If we're working with the root folder
			if ($id = 1) {
				$sql = 'SELECT count(*) as cnt FROM documents where folder_id = 1';
			} else {
				$sql [] = "SELECT count(*) as cnt FROM documents where parent_folder_ids like '%,?' OR parent_folder_ids like '%,?,%' OR folder_id = ?";
				$sql [] = array ($id, $id, $id );
			}
			
			
            //SELECT count(*) FROM documents d
            //INNER JOIN document_metadata_version m ON d.metadata_version_id = m.id
            //INNER JOIN document_content_version c ON m.content_version_id = c.id
            //where (d.parent_folder_ids like '%,12' OR d.parent_folder_ids like '%,12,%' OR d.folder_id = 12) AND d.status_id < 3 AND size > 100000
            
			
			$result = DBUtil::getOneResult ( $sql );
			
			if ($result ['cnt'] > 10) {
				return true;
			}
		}
		
		return false;
	}
	*/
	
	
}
?>
