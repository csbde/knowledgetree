<?php
/**
 * $Id$
 *
 * Provides storage for contents of documents on disk, using a hashed
 * folder path and the content version as the file name.
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
 *
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

require_once(KT_LIB_DIR . '/storage/storagemanager.inc.php');
require_once(KT_LIB_DIR . '/mime.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/documentmanagement/documentcontentversion.inc.php');
require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');

class KTOnDiskHashedStorageManager extends KTStorageManager {
	/**
	 * Handle direct file system access
	 */
	
	/**
	 * Write contents to a file.
	 */
	function write_file($filename, $mode, $string) {
		$fileHandle = KTOnDiskHashedStorageManager::fopen($filename, $mode);
		if($fileHandle === false) {
			
			return $fileHandle;
		} else {
			KTOnDiskHashedStorageManager::fwrite($fileHandle, $string);
		}
		
		return KTOnDiskHashedStorageManager::fclose($fileHandle);
	}
	
	/**
	 * Read contents of a file.
	 */
	function read_file($filename = "", $mode = "", $length, $fileHandle = null) {
		$content = "";
		// Check if a file handle exists
		if(is_null($fileHandle))
		{
			// Get file handle
			$fileHandle = KTOnDiskHashedStorageManager::fopen($filename, $mode);
		}
		// Check if a file handle exists
		if($fileHandle === false) {
			// Return file handle
			return $fileHandle;
		} else {
			// Read contents of file
			$content = KTOnDiskHashedStorageManager::fread($fileHandle, $length);
		}
		// Close file handle
		KTOnDiskHashedStorageManager::fclose($fileHandle);
		// Return content
		return $content;
	}
	
    /**
     * Opens file or URL
     *
     * @param string $filename - Path to the file to open.
     * @param string $mode - The mode parameter specifies the type of access you require to the stream.
     * @param boolean $use_include_path - The data to write
     * @param resource $context - A valid context resource created with stream_context_create().
     * 
     * URL : http://www.php.net/manual/en/function.fopen.php
     * 
     */
	function fopen($filename, $mode, $use_include_path = false, $context = null)
	{
		if (is_null($context))
		{
			$context = stream_context_create(array());
		}
		
		return fopen($filename, $mode, $use_include_path, $context);
	}
	
    /**
     * Binary-safe file write
     *
     * @param string $handle - A file system pointer resource that is typically created using fopen().
     * @param string $string - The string that is to be written. 
     * @param integer $length - If the length argument is given, writing will stop after length bytes 
     * 							have been written or the end of string is reached, whichever comes first. 
     * 
     * URL : http://www.php.net/manual/en/function.fwrite.php
     * 
     */
	function fwrite($handle, $string, $length = null)
	{
		return fwrite($handle, $string, $length);
	}
	
    /**
     * Binary-safe file read
     *
     * @param string $handle - A file system pointer resource that is typically created using fopen().
     * @param integer $length - Up to length number of bytes read. 
     * 
     * URL : http://www.php.net/manual/en/function.fread.php
     * 
     */
	function fread($handle, $length) 
	{
		return fread($handle, $length);
	}
	
    /**
     * Closes an open file pointer
     *
     * @param resource $handle - he file pointer must be valid, and must point to a file successfully opened by fopen() or fsockopen(). 
     * 
     * URL : http://www.php.net/manual/en/function.fclose.php
     * 
     */
	function fclose($handle) 
	{
		return fclose($handle);
	}
	
    /**
     * Checks whether a file or directory exists. 
     *
     * @param string $filename - Path to the file to open.
     * 
     * URL : http://www.php.net/manual/en/function.file-exists.php
     * 
     */
	function file_exists($filename) 
	{
		return file_exists($filename);
	}
	
    /**
     * Write a string to a file
     *
     * @param string $filename - Path to the file where to write the data.
     * @param mixed $data - The data to write
     * @param boolean $flags - The value of flags can be any combination of the following flags (with some restrictions)
     * @param resource $context - A valid context resource created with stream_context_create().
     * 
     * URL : http://www.php.net/manual/en/function.file-put-contents.php
     * 
     */
	function file_put_contents($filename, $data, $flags = null, $context = null) 
	{
		if (is_null($context))
		{
			$context = stream_context_create(array());
		}
		
		return file_put_contents($filename, $data, $flags, $context);
	}
	
    /**
     * Reads entire file into a string
     *
     * @param string $filename - Name of the file to read. 
     * @param string $flags - The data to write
     * @param resource $context - A valid context resource created with stream_context_create().
     * @param integer $offset - The offset where the reading starts on the original stream. 
     * @param integer $maxlen - Maximum length of data read. The default is to read until end of file is reached. Note that this parameter is applied to the stream processed by the filters.
     * 
     * URL : http://www.php.net/manual/en/function.file-get-contents.php
     * 
     */
	function file_get_contents($filename, $flags = null, $context = null, $offset = null, $maxlen = null)
	{
		if (is_null($context))
		{
			$context = stream_context_create(array());
		}
		
		return file_get_contents($filename, $flags, $context, $offset, $maxlen);
	}
	
    /**
     * Open Internet or Unix domain socket connection
     *
     * @param string $hostname - Name host.
     * @param integer $port - The port number.
     * @param integer $errno - If provided, holds the system level error number that occurred in the system-level connect() call. 
     * @param string $errstr - The error message as a string.
     * @param float $timeout - The connection timeout, in seconds.
     * 
     * URL : http://www.php.net/manual/en/function.fsockopen.php
     * 
     */
	function fsockopen($hostname, $port = null, &$errno , &$errstr, $timeout = null) 
	{
		return fsockopen($hostname, $port, $errno, $errstr, $timeout);
	}
	
    /**
     * Open Internet or Unix domain socket connection
     *
     * @param string $filename - The filename being checked. 
     * 
     * URL : http://www.php.net/manual/en/function.is-writable.php
     * 
     */
	function is_writable($filename) 
	{
		return is_writable($filename);
	}
	
    /**
     * This function is an alias of: is_writable(). 
     * 
     * URL : http://www.php.net/manual/en/function.is-writeable.php
     * 
     */
	function is_writeable($filename)
	{
		return KTOnDiskPathStorageManager::is_writeable($filename);
	}
	
    /**
     * Create file with unique file name
     * 
     * @param string $dir - The directory where the temporary filename will be created.
     * @param string $prefix - The prefix of the generated temporary filename. 
     * 
     * URL : http://www.php.net/manual/en/function.tempnam.php
     * 
     */
	function tempnam($dir, $prefix) 
	{
		return tempnam($dir, $prefix);
	}
	
    /**
     * Moves an uploaded file to a new location
     * 
     * @param string $filename - The filename of the uploaded file. 
     * @param string $destination - The destination of the moved file. 
     * 
     * URL : http://www.php.net/manual/en/function.move-uploaded-file.php
     * 
     */
	function move_uploaded_file($filename, $destination) 
	{
		return move_uploaded_file($filename, $destination);
	}
	
    /**
     * Create file with unique file name
     * 
     * @param string $filename - Path to the file. 
     * @param resource $context - A valid context resource created with stream_context_create().
     * 
     * URL : http://www.php.net/manual/en/function.unlink.php
     * 
     */
	function unlink($filename, $context = null) 
	{
		if (is_null($context))
		{
			$context = stream_context_create(array());
		}
		
		return unlink($filename, $context);
	}
	
    /**
     * Sets access and modification time of file
     * 
     * @param string $filename - Path to the file. 
     * @param integer $time - The touch time. If time is not supplied, the current system time is used. 
     * @param integer $atime - If present, the access time of the given filename is set to the value of atime. Otherwise, it is set to time. 
     * 
     * URL : http://www.php.net/manual/en/function.touch.php
     * 
     */
	function touch($filename, $time = null, $atime = null)
	{
		return touch($filename, $time, $atime);
	}
	
    /**
     * Makes directory
     * 
     * @param string $pathname - The directory path. 
     * @param integer $mode - The mode is 0777 by default, which means the widest possible access. For more information on modes, read the details on the chmod() page. 
     * @param boolean $recursive - Allows the creation of nested directories specified in the pathname. Defaults to FALSE. 
     * @param resource $context - A valid context resource created with stream_context_create().
     * 
     * URL : http://www.php.net/manual/en/function.mkdir.php
     * 
     */
	function mkdir($pathname, $mode = 0777, $recursive = false, $context = null) {
		if (is_null($context))
		{
			$context = stream_context_create(array());
		}
		
		return mkdir($pathname, $mode, $recursive, $context);
	}
	
    /**
     * Tells whether the filename is a directory
     * 
     * @param string $filename - Path to the file
     * 
     * URL : http://www.php.net/manual/en/function.is-dir.php
     * 
     */
	function is_dir($filename) {
		
		return is_dir($filename);
	}
	
    /**
     * 
     * 
     * @param string $filename - Path to the file
     * 
     * URL :
     * 
     */
	function filesize($filename) {
		
		return filesize($filename);
	}
	
    function upload(&$oDocument, $sTmpFilePath, $aOptions = null) {

    	if (!KTOnDiskHashedStorageManager::file_exists($sTmpFilePath)) {

            	return new PEAR_Error("$sTmpFilePath does not exist so we can't copy it into the repository! Options: "  . print_r($aOptions,true) );
            }


        $oConfig =& KTConfig::getSingleton();
        $sStoragePath = $this->generateStoragePath($oDocument);
        if (PEAR::isError($sStoragePath)) {
            return $sStoragePath;
        }
        $this->setPath($oDocument, $sStoragePath);
        $oDocument->setFileSize(filesize($sTmpFilePath));
        $sDocumentFileSystemPath = sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $this->getPath($oDocument));

        //copy the file accross
        $start_time = KTUtil::getBenchmarkTime();
        $file_size = $oDocument->getFileSize();
        if (OS_WINDOWS) {
            $sDocumentFileSystemPath = str_replace('\\','/',$sDocumentFileSystemPath);
        }
        if ($this->writeToFile($sTmpFilePath, $sDocumentFileSystemPath, $aOptions)) {
            $end_time = KTUtil::getBenchmarkTime();
            global $default;
            $default->log->info(sprintf("Uploaded %d byte file in %.3f seconds", $file_size, $end_time - $start_time));

            //remove the temporary file
            //@unlink($sTmpFilePath);
            if (KTOnDiskHashedStorageManager::file_exists($sDocumentFileSystemPath)) {
                return true;
            } else {
            	return new PEAR_Error("$sDocumentFileSystemPath does not exist after write to storage path. Options: " . print_r($aOptions,true));
            }
        } else {
            return new PEAR_Error("Could not write $sTmpFilePath to $sDocumentFileSystemPath with options: " . print_r($aOptions,true));
        }
    }

    /**
     * Upload a temporary file
     *
     * @param unknown_type $sUploadedFile
     * @param unknown_type $sTmpFilePath
     * @return unknown
     */
    function uploadTmpFile($sUploadedFile, $sTmpFilePath, $aOptions = null) {

        //copy the file accross
        if (OS_WINDOWS) {
            $sTmpFilePath = str_replace('\\','/',$sTmpFilePath);
        }
        if ($this->writeToFile($sUploadedFile, $sTmpFilePath, $aOptions)) {
            if (KTOnDiskHashedStorageManager::file_exists($sTmpFilePath)) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    function writeToFile($sTmpFilePath, $sDocumentFileSystemPath, $aOptions = null) {
        // Make it easy to write compressed/encrypted storage
        if(isset($aOptions['copy_upload']) && ($aOptions['copy_upload'] == 'true')) {
            return copy($sTmpFilePath, $sDocumentFileSystemPath);
        }

        if (is_uploaded_file($sTmpFilePath))
            $res = KTOnDiskHashedStorageManager::move_uploaded_file($sTmpFilePath, $sDocumentFileSystemPath);
        else
            $res = @rename($sTmpFilePath, $sDocumentFileSystemPath);

        if ($res === false)
        {
        	$res = @copy($sTmpFilePath, $sDocumentFileSystemPath);
        }

        return $res;
    }

    function getPath(&$oDocument) {
        return $oDocument->getStoragePath();
    }

    function setPath(&$oDocument, $sNewPath) {
        $oDocument->setStoragePath($sNewPath);
    }

    function generateStoragePath(&$oDocument) {
        return $this->generateStoragePathForVersion($oDocument->getContentVersionId());
    }

    function generateStoragePathForVersion($oContentVersion) {
        $iId = KTUtil::getId($oContentVersion);
        $str = (string)$iId;
        if (strlen($str) < 4) {
            $str = sprintf('%s%s', str_repeat('0', 4 - strlen($str)), $str);
        }
        if (strlen($str) % 2 == 1) {
            $str = sprintf('0%s', $str);
        }

        $str = substr($str, 0, -2);

        $dir = preg_replace('#(\d\d)(\d\d)#', '\1/\2', $str);

        // Now, create the directory (including intermediaries)
        $oConfig =& KTConfig::getSingleton();
        $sDocumentRoot = $oConfig->get('urls/documentRoot');

        $path = "";
        foreach(split('/', $dir) as $sDirPart) {
            $path = sprintf('%s/%s', $path, $sDirPart);
            $createPath = sprintf('%s%s', $sDocumentRoot, $path);
            if (!KTOnDiskHashedStorageManager::file_exists($createPath)) {
                $res = @mkdir($createPath, 0777, true);
                if ($res === false) {
                    return PEAR::raiseError(sprintf(_kt("Could not create directory for storage" .': ' . '%s') , $createPath));
                }
            }
        }
        return sprintf("%s/%d", $dir, $iId);
    }

    function temporaryFile(&$oDocument) {
        $oConfig =& KTConfig::getSingleton();
        return sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $this->getPath($oDocument));
    }

    function temporaryFileForVersion($iVersionId) {
        $oConfig =& KTConfig::getSingleton();

        // get path to the content version
        $oContentVersion = KTDocumentContentVersion::get($iVersionId);
        $sPath = sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $this->getPath($oContentVersion));

        // Ensure the file exists
        if (KTOnDiskHashedStorageManager::file_exists($sPath)) {
            return $sPath;
        }
        return false;
    }

    function freeTemporaryFile($sPath) {
        // Storage uses file-on-filesystem for temporaryFile
        return;
    }

    function download($oDocument, $bIsCheckout = false) {
        global $default;

        //get the path to the document on the server
        //$docRoot = $default->documentRoot;
        $oConfig =& KTConfig::getSingleton();
        $docRoot  = $oConfig->get('urls/documentRoot');

        $path = $docRoot .'/'. $oDocument->getStoragePath();

        // Ensure the file exists
        if (KTOnDiskHashedStorageManager::file_exists($path)) {
            // Get the mime type
            $mimeId = $oDocument->getMimeTypeID();
            $mimetype = KTMime::getMimeTypeName($mimeId);

            if ($bIsCheckout && $default->fakeMimetype) {
                // note this does not work for "image" types in some browsers
                $mimetype = 'application/x-download';
            }

            $sFileName = $oDocument->getFileName( );
            $iFileSize = $oDocument->getFileSize();

            KTUtil::download($path, $mimetype, $iFileSize, $sFileName);
        } else {
            return false;
        }
    }

    function createFolder($oFolder) {
        // Storage doesn't deal with folders
        return true;
    }

    function removeFolder($oFolder) {
        // Storage doesn't deal with folders
        return true;
    }

    function removeFolderTree($oFolder) {
        // Storage doesn't deal with folders
        return true;
    }

    function downloadVersion($oDocument, $iVersionId) {
        //get the document
        $oContentVersion = KTDocumentContentVersion::get($iVersionId);
        $oConfig =& KTConfig::getSingleton();
        $sPath = sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $this->getPath($oContentVersion));
        $sVersion = sprintf("%d.%d", $oContentVersion->getMajorVersionNumber(), $oContentVersion->getMinorVersionNumber());
        if (KTOnDiskHashedStorageManager::file_exists($sPath)) {
            // Get the mime type
            $mimeId = $oContentVersion->getMimeTypeID();
            $mimetype = KTMime::getMimeTypeName($mimeId);

            $sFileName = $sVersion.'-'.$oContentVersion->getFileName( );
            $iFileSize = $oContentVersion->getFileSize();

            KTUtil::download($sPath, $mimetype, $iFileSize, $sFileName);
        } else {
            return false;
        }
    }

    function moveDocument(&$oDocument, $oSourceFolder, $oDestinationFolder) {
        // Storage path isn't based on location folder hierarchy
        return true;
    }

    /**
     * Move a file
     *
     * @param string source path
     * @param string destination path
     */
    function move($sOldDocumentPath, $sNewDocumentPath) {
        global $default;
        if (KTOnDiskHashedStorageManager::file_exists($sOldDocumentPath)) {
            //copy the file    to the new destination
            if (rename($sOldDocumentPath, $sNewDocumentPath)) {
                //delete the old one
                //@unlink($sOldDocumentPath);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function moveFolder($oFolder, $oDestFolder) {
        // Storage path isn't based on folder hierarchy
        return true;
    }

    function renameFolder($oFolder, $sNewName) {
        // Storage path isn't based on folder hierarchy
        return true;
    }

    /**
     * Perform any storage changes necessary to account for a copied
     * document object.
     */
    function copy($oSrcDocument, &$oNewDocument) {
        // we get the Folder object
        $oVersion = $oNewDocument->_oDocumentContentVersion;
        $oConfig =& KTConfig::getSingleton();
        $sDocumentRoot = $oConfig->get('urls/documentRoot');

        $sNewPath = $this->generateStoragePath($oNewDocument);
        $sFullOldPath = sprintf("%s/%s", $sDocumentRoot, $this->getPath($oSrcDocument));
        $sFullNewPath = sprintf("%s/%s", $sDocumentRoot, $sNewPath);

        $res = KTUtil::copyFile($sFullOldPath, $sFullNewPath);
        if (PEAR::isError($res)) { return $res; }
        $oVersion->setStoragePath($sNewPath);
        $oVersion->update();
    }

    function renameDocument(&$oDocument, $oOldContentVersion, $sNewFilename) {
        // Storage isn't based on document name
        return true;
     }

    function delete($oDocument) {
        // Storage doesn't care if the document is deleted
        return true;
    }

    /**
     * Completely remove a document from the Deleted/ folder
     *
     * return boolean true on successful expunge
     */
    function expunge($oDocument) {
    	parent::expunge($oDocument);
    	$oConfig =& KTConfig::getSingleton();
        $sCurrentPath = $this->getPath($oDocument);

        $sDocumentRoot = $oConfig->get('urls/documentRoot');

        $aVersions = KTDocumentContentVersion::getByDocument($oDocument);
        foreach ($aVersions as $oVersion) {
            $sPath = sprintf('%s/%s', $sDocumentRoot, $oVersion->getStoragePath());
            @unlink($sPath);
        }
        return true;
    }

	/**
	 * Completely remove a document version
	 *
	 * return boolean true on successful delete
	 */
	function deleteVersion($oVersion) {
	    $oConfig =& KTConfig::getSingleton();
	    $sDocumentRoot = $oConfig->get('urls/documentRoot');
	    $iContentId = $oVersion->getContentVersionId();
        $oContentVersion = KTDocumentContentVersion::get($iContentId);

	    $sPath = $oContentVersion->getStoragePath();
	    $sFullPath = sprintf("%s/%s", $sDocumentRoot, $sPath);
	    if (KTOnDiskHashedStorageManager::file_exists($sFullPath)) {
            unlink($sFullPath);
	    }
	    return true;
	}

    function restore($oDocument) {
        // Storage doesn't care if the document is deleted or restored
        return true;
    }
    
	/**
	 * Get the storage path of a documents content.
	 *
	 * @param unknown_type $oDocument
	 * @param unknown_type $type
	 * @return unknown
	 */
    function getDocStoragePath($oDocument = null, $type = 'document', $document_id = null) {
    	if (is_null($oDocument)) 
    	{
    		if(is_null($document_id))
    		{
    			return PEAR::isError("No document supplied.");
    		}
    		$oDocument = Document::get($document_id);
    	}
    	global $default;
    	$varDirectory = $default->varDirectory;
    	switch ($type) {
    		case 'pdf' :
	               $sFile = $varDirectory . '/Pdf/' . $oDocument->getId() . '.pdf';
    			break;
    		case 'document' :
					$sFile = $varDirectory . '/Documents/' . $oDocument->getStoragePath();
    				$tempFile = $varDirectory . '/tmp/'. $oDocument->getId() . '.' . KTMime::getFileType($oDocument->getMimeTypeID());
					copy($sFile, $tempFile);
					return $tempFile;
    			break;
    		case 'flash':
					$sFile = $varDirectory . '/flash/' . $oDocument->getId() . '.swf';
    			break;
    		case 'thumbnail':
					$sFile = $varDirectory . '/thumbnails/' . $oDocument->getId() . '.jpg';
    			break;
    	}

		return $sFile;
    }
}

?>
