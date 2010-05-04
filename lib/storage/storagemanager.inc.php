<?php
/**
 * $Id$
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
 *
 * -------------------------------------------------------------------------
 *
 * Manages the storage and storage location of a file.
 *
 * The Document Manager may only use setDiskPath on the oDocument
 * object, and should not update the document object.
 */
require_once(KT_DIR . '/search2/indexing/indexerCore.inc.php');

class KTStorageManager {
	/**
	 * Handle direct file system access
	 */
	
	/**
	 * Write contents to a file.
	 */
	function write_file($filename, $mode, $string) {
		$fileHandle = KTStorageManager::fopen($filename, $mode);
		if($fileHandle === false) {
			
			return $fileHandle;
		} else {
			KTStorageManager::fwrite($fileHandle, $string);
		}
		
		return KTStorageManager::fclose($fileHandle);
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
			$fileHandle = KTStorageManager::fopen($filename, $mode);
		}
		// Check if a file handle exists
		if($fileHandle === false) {
			// Return file handle
			return $fileHandle;
		} else {
			// Read contents of file
			$content = KTStorageManager::fread($fileHandle, $length);
		}
		// Close file handle
		KTStorageManager::fclose($fileHandle);
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
		return KTOnDiskPathStorageManager::is_writable($filename);
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
     * Puts the given file into storage, and saves the storage details
     * into the document.
     */
    public function upload (&$oDocument, $sTmpFilePath) {
        return PEAR::raiseError(_kt("Not implemented"));
    }

    /**
     * Upload a temporary file
     *
     * @param unknown_type $sUploadedFile
     * @param unknown_type $sTmpFilePath
     * @return unknown
     */
    public function uploadTmpFile($sUploadedFile, $sTmpFilePath, $aOptions = null) {
        return PEAR::raiseError(_kt('Not implemented'));
    }

    protected function writeToFile($sTmpFilePath, $sDocumentFileSystemPath, $aOptions = null) {
        return PEAR::raiseError(_kt("Not implemented"));
    }

    /**
     * Gets the latest verison of a document's contents from storage and
     * writes it to the standard content with HTTP headers as an
     * attachment.
     */
    public function download (&$oDocument) {
        return PEAR::raiseError(_kt("Not implemented"));
    }

    /**
     * Gets a specific version of a document's contents from storage and
     * writes it to the standard content with HTTP headers.
     */
    public function downloadVersion (&$oDocument) {
        return PEAR::raiseError(_kt("Not implemented"));
    }

    /**
     * Gets the latest verison of a document's contents from storage and
     * writes it to the standard content with HTTP headers for inline
     * view.
     */
    public function inlineView (&$oDocument) {
        return PEAR::raiseError(_kt("Not implemented"));
    }

    /**
     * Performs any storage changes necessary to account for a changed
     * repository path.
     *
     * The info arrays must contain the following information:
     *      "names" => an array of the names of the folders in the path
     *          from the root of the repository
     *          ("Root Folder", "foo", "bar", "baz")
     *      "ids" => an array of the ids of the folders in the path from
     *          the root of the repository
     *          (1, 3, 9, 27)
     */
    public function move (&$oDocument, $aOldInfo, $aNewInfo) {
        return PEAR::raiseError(_kt("Not implemented"));
    }

    /**
     * Perform any storage changes necessary to account for moving one
     * tree in the repository to a different location.
     */
    public function moveFolder ($oFolder, $oDestFolder) {
        return PEAR::raiseError(_kt("Not implemented"));
    }

    public function renameFolder($oFolder, $sNewName) {
        return PEAR::raiseError(_kt("Not implemented"));
    }

    /**
     * Perform any storage changes necessary to account for a copied
     * document object.
     */
    public function copy ($oSrcDocument, &$oNewDocument) {
       return PEAR::raiseError(_kt("Not implemented"));
    }

    /**
     * Performs any storage changes necessary to account for the
     * document being marked as deleted.
     */
    public function delete (&$oDocument) {
        return PEAR::raiseError(_kt("Not implemented"));
    }

    /**
     * Remove the documents (already marked as deleted) from the
     * storage.
     */
    public function expunge (&$oDocument) {
		$documentid = $oDocument->getId();
    	$indexer = Indexer::get();
        $indexer->deleteDocument($documentid);
    }

    public function deleteVersion(&$oVersion) {
        return PEAR::raiseError(_kt("Not implemented"));
    }

    /**
     * Performs any storage changes necessary to account for the
     * document (previously marked as deleted) being restored.
     */
    public function restore (&$oDocument) {
        return PEAR::raiseError(_kt("Not implemented"));
    }

    protected function getPath(&$oDocument) {
        return PEAR::raiseError(_kt("Not implemented"));
    }

    protected function setPath(&$oDocument) {
        return PEAR::raiseError(_kt("Not implemented"));
    }

    protected function generatePath(&$oDocument) {
        return PEAR::raiseError(_kt("Not implemented"));
    }

    public function createFolder($sFolderPath) {
        return PEAR::raiseError(_kt("Not implemented"));
    }

    public function renameDocument(&$oDocument, $oOldContentVersion, $sNewFilename) {
        return PEAR::raiseError(_kt("Not implemented"));
    }
    
    /**
     * Wrapper function
     * Returns the md5 hash of the file content
     *
     * @param string $path the location of the file
     * @return string the md5 hash
     */
    public function md5File($path) {
        return md5_file($path);
    }
    
    /**
     * Wrapper function
     * Returns whether the supplied path is a file
     *
     * @param string $path
     * @return boolean
     */
    public function isFile($path)
    {
        return is_file($path);
    }
    
    /**
     * Wrapper function for filesize
     *
     * @param string $path
     */
    public function fileSize($path)
    {
        return filesize($path);
    }
    
    /*
    TODO: Remove as it is only needed for testing.
    */
    public function getDocStoragePath($oDocument, $type = 'document') {
    	return PEAR::raiseError(_kt("Not implemented"));
    }
}

class KTStorageManagerUtil {
    static function &getSingleton() {


    	static $singleton = null;

    	if (is_null($singleton))
    	{
    		$oConfig =& KTConfig::getSingleton();
        	$sDefaultManager = 'KTOnDiskHashedStorageManager';
        	$klass = $oConfig->get('storage/manager', $sDefaultManager);
        	if (!class_exists($klass)) {
            	$klass = $sDefaultManager;
        	}
        	$singleton = new $klass;
    	}

    	return $singleton;
    }
}

?>
