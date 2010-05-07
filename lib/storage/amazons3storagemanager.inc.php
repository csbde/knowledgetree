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

// NOTE file checks after writing are not implemented here as in the hash driver, because
//      Amazon will not return a 200 response unless the file was successfully and fully created
//      (see Amazon docs for confirmation that a 200 response is only returned if an object
//      was fully and successfully created)

// TODO all the file exists checks are currently head requests instead, but they are probably not needed for S3
//      (operations will fail with an error code rather than a php warning or error);
//      could speed up the code by removing them

// TODO determine which of these is still needed for S3 and remove those not needed
require_once(KT_LIB_DIR . '/storage/storagemanager.inc.php');
require_once(KT_LIB_DIR . '/mime.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/documentmanagement/documentcontentversion.inc.php');
require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');
// config manager for loading amazon config information (TODO replace this with other config manager?)
require_once(KT_LIB_DIR . '/config/ConfigManager.inc.php');
// cloudfusion
require_once(KT_DIR . '/thirdparty/cloudfusion/cloudfusion.class.php');
require_once(KT_DIR . '/thirdparty/cloudfusion/s3.class.php');

// TODO better error handling/messages
// TODO more logging
// TODO use of vhost?
class KTAmazonS3StorageManager extends KTStorageManager {

    private $amazonS3;
    private $bucket;

    // TODO real account name passed through (depends on account routing story)
    // TODO fetch amazon info from a config file - where to put this config file (we want it outside of publicly accesible code...)
    public function __construct()
    {
        // get account name from defined constant, or throw an error
        if (!defined('ACCOUNT_NAME') || (ACCOUNT_NAME == '')) {
            // TODO log error
            throw new RuntimeException('No account name defined');
        }
        
        ConfigManager::load(KT_DIR . '/config/aws_config.ini');
        if (ConfigManager::error()) {
            // TODO log error
            if (ACCOUNT_ROUTING_ENABLED) {
        		liveRenderError::create('Unable to read Amazon config', 
        		                        'Amazon Credentials are not available - please contact your system administrator', 
        		                        new RuntimeException(ConfigManager::getErrorMessage()), AMAZON_CREDENTIALS_MISSING);
            }
            throw new RuntimeException(ConfigManager::getErrorMessage());
        }

        // load amazon authentication information
        $awsAuth = ConfigManager::getSection('AWS Authentication');
        // create the SQS Queue Manager
        try {
            $this->amazonS3 = new AmazonS3($awsAuth['key'], $awsAuth['secret']);
        }
        catch (Exception $e) {
            // TODO log error
            if (ACCOUNT_ROUTING_ENABLED) {
        		liveRenderError::create('Amazon authentication failure', 
        		                        'Unable to authenticate using the supplied credentials - please contact your system administrator', 
        		                        $e, AMAZON_CREDENTIALS_MISSING);
            }
            throw $e;
        }

        $this->bucket = 'ktlive-' . ACCOUNT_NAME;

        // create bucket if it does not exist
        $response = $this->amazonS3->head_bucket($this->bucket);
        if (!$response->isOK()) {
            $response = $this->amazonS3->create_bucket($this->bucket);
            if (!$response->isOK()) {
                // TODO log
                throw new RuntimeException("Unable to create bucket: {$this->bucket}");
            }
        }
    }

    public function upload(&$oDocument, $sTmpFilePath, $aOptions = null)
    {
        global $default;
            
        $sTmpFilePath = $this->getShortPath($sTmpFilePath);
        $response = $this->amazonS3->head_object($this->bucket, $sTmpFilePath);
        if (!$response->isOK()) {
            return new PEAR_Error("$sTmpFilePath does not exist so we can't copy it into the repository! Options: " 
                                . print_r($aOptions,true) );
        }

        $sStoragePath = $this->generateStoragePath($oDocument);
        if (PEAR::isError($sStoragePath)) {
            return $sStoragePath;
        }
        $this->setPath($oDocument, $sStoragePath);
        $oDocument->setFileSize($response->header['_info']['download_content_length']);
        $amazonS3Path = sprintf("%s/%s", 'Documents', $this->getPath($oDocument));

        //copy the file accross
        $start_time = KTUtil::getBenchmarkTime();
        $file_size = $oDocument->getFileSize();
        if ($this->writeToFile($sTmpFilePath, $amazonS3Path, $aOptions, $oDocument)) {
            $end_time = KTUtil::getBenchmarkTime();
            $default->log->info(sprintf("Uploaded %d byte file in %.3f seconds", $file_size, $end_time - $start_time));
            
            return true;

//            $response = $this->amazonS3->head_object($this->bucket, $amazonS3Path);
//            if ($response->isOK()) {
//                return true;
//            }
//            else {
//                return new PEAR_Error("$amazonS3Path does not exist after write to storage path. Options: " . print_r($aOptions,true));
//            }
        }
        else {
            return new PEAR_Error("Could not write $sTmpFilePath to $amazonS3Path with options: " . print_r($aOptions,true));
        }
    }

    /**
     * Upload a temporary file
     *
     * @param string $sUploadedFile
     * @param string $sTmpFilePath
     * @return boolean
     */
    public function uploadTmpFile($sUploadedFile, $sTmpFilePath, $aOptions = null)
    {                
        if (OS_WINDOWS) {
            $sTmpFilePath = str_replace('\\', '/', $sTmpFilePath);
        }
        
        return $this->writeToFile($sUploadedFile, $sTmpFilePath, $aOptions);
        
//        if ($this->writeToFile($sUploadedFile, $sTmpFilePath, $aOptions)) {
//            $response = $this->amazonS3->head_object($this->bucket, $amazonS3Path);
//            return $response->isOK();
//        }
//        
//        return false;
    }

    /**
     * Writes file to Amazon S3 storage (except for bulk uploads, which still use the local system)
     *
     * @param string $sourceFilePath
     * @param string $destinationFilePath
     * @param array $aOptions
     * @return boolean
     */
    protected function writeToFile($sourceFilePath, $destinationFilePath, $aOptions = null, $document = null)
    {        
        // TODO determine what if anything needs to change here - this is only used by bulk upload,
        //      I think for the zip file...
        if(isset($aOptions['copy_upload']) && ($aOptions['copy_upload'] == 'true')) {
            return copy($sourceFilePath, $destinationFilePath);
        }

        // copy from php temp directory to S3
        if (is_uploaded_file($sourceFilePath)) {
            $destinationFilePath = $this->getShortPath($destinationFilePath);
            $content = file_get_contents($sourceFilePath);
            $opt = array('filename' => $destinationFilePath, 'body' => $content);
            $response = $this->createS3Object($opt);
            // ensure php temp file is removed, as we are not using move_uploaded_file()
            @unlink($sourceFilePath);

            return $response;
        }
        // already in S3
        else {
            // check whether the supplied document object is valid
            if (!($document instanceof Document)) {
                return new PEAR_Error('Invalid document supplied to S3 storage driver for upload');
            }
            // NOTE this should probably not be needed as it is already done in the calling function
            //      leaving here as redundancy check
            $sourceFilePath = $this->getShortPath($sourceFilePath);
            // set semantic headers: filename, size (is given by amazon by default), content type - what else?
            $opt['contentType'] = KTMime::getMimeTypeName($document->getMimeTypeID());
            $opt['contentDisposition'] = 'attachment';
            $opt['meta'] = array('title' => $document->getName(), 
                                 'filename' => $document->getFileName());
            $response = $this->copyS3Object($sourceFilePath, $destinationFilePath, $opt);
            if ($response) {
                $response = $this->amazonS3->delete_object($this->bucket, $sourceFilePath);
                return true;
            }
            
            return false;
        }

        return false;
    }

    protected function getPath(&$oDocument)
    {
        return $oDocument->getStoragePath();
    }

    protected function setPath(&$oDocument, $sNewPath)
    {
        $oDocument->setStoragePath($sNewPath);
    }
    
    private function getShortPath($path)
    {
        if (OS_WINDOWS) {
            $path = str_replace('\\', '/', $path);
        }
        
        // if path as received is full system var path, don't want that...feels like a bit of a hack, but...
        // NOTE this will likely break on external Document storage (unless we're lucky)
        $config = KTConfig::getSingleton();
        return str_replace($config->get('urls/varDirectory') . '/', '', $path);
    }

    protected function generateStoragePath(&$oDocument)
    {
        return $this->generateStoragePathForVersion($oDocument->getContentVersionId());
    }

    protected function generateStoragePathForVersion($oContentVersion)
    {
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

        return sprintf("%s/%d", $dir, $iId);
    }

    // TODO find out what these temporaryFile* functions do and whether they must be modified
    public function temporaryFile(&$oDocument)
    {
        return sprintf("%s/%s", 'Documents', $this->getPath($oDocument));
    }

    public function temporaryFileForVersion($iVersionId)
    {
        // get path to the content version
        $oContentVersion = KTDocumentContentVersion::get($iVersionId);
        $sPath = sprintf("%s/%s", 'Documents', $this->getPath($oContentVersion));

        // Ensure the file exists
        $response = $this->amazonS3->head_object($this->bucket, $sPath);
        if ($response->isOK()) {
            return $sPath;
        }
        
        return false;
    }
    
    public function freeTemporaryFile($sPath) {
        return;
    }

    // TODO modify to use direct access to S3 instead of downloading locally
    public function download($oDocument, $bIsCheckout = false)
    {
        global $default;
        
        $amazonS3Path = 'Documents/'. $oDocument->getStoragePath();

        // Ensure the file exists
//        $response = $this->amazonS3->head_object($this->bucket, $amazonS3Path);
//        if ($response->isOK()) {
            // Get the mime type
            $mimeId = $oDocument->getMimeTypeID();
            $mimetype = KTMime::getMimeTypeName($mimeId);

            if ($bIsCheckout && $default->fakeMimetype) {
                // note this does not work for "image" types in some browsers
                $mimetype = 'application/x-download';
            }

            $sFileName = $oDocument->getFileName( );
            $iFileSize = $oDocument->getFileSize();

            // download to local file system
            $oConfig = KTConfig::getSingleton();
            $sPath = sprintf("%s/%s", $oConfig->get('urls/tmpDirectory'), $sFileName);
            $response = $this->amazonS3->get_object($this->bucket, $amazonS3Path);
            if ($response->isOK()) {
                // copy file content to local path & download
                $file = fopen($sPath, 'w');
                if ($file) {
                    fwrite($file, $response->body);
                    fclose($file);
                    KTUtil::download($sPath, $mimetype, $iFileSize, $sFileName);
                }
                else {
                    // TODO logging
                }
            }
//        }
        else {
            // TODO logging
            return false;
        }
    }

    public function createFolder($oFolder)
    {
        // Storage doesn't deal with folders
        return true;
    }

    public function removeFolder($oFolder)
    {
        // Storage doesn't deal with folders
        return true;
    }

    public function removeFolderTree($oFolder)
    {
        // Storage doesn't deal with folders
        return true;
    }

    // TODO modify to use direct access to S3 instead of downloading locally
    public function downloadVersion($oDocument, $iVersionId)
    {
        //get the document
        $oContentVersion = KTDocumentContentVersion::get($iVersionId);
        $sVersion = sprintf("%d.%d", $oContentVersion->getMajorVersionNumber(), $oContentVersion->getMinorVersionNumber());
        $amazonS3Path = sprintf("%s/%s", 'Documents', $this->getPath($oContentVersion));

        // Ensure the file exists
//        $response = $this->amazonS3->head_object($this->bucket, $amazonS3Path);
//        if ($response->isOK()) {
            // Get the mime type
            $mimeId = $oContentVersion->getMimeTypeID();
            $mimetype = KTMime::getMimeTypeName($mimeId);

            $sFileName = $sVersion.'-'.$oContentVersion->getFileName( );
            $iFileSize = $oContentVersion->getFileSize();

            // download to local system
            $oConfig = KTConfig::getSingleton();
            $response = $this->amazonS3->get_object($this->bucket, $amazonS3Path);
            if ($response->isOK()) {
                // copy file content to local path & download
                $sPath = sprintf("%s/%s", $oConfig->get('urls/tmpDirectory'), $sFileName);
                // TODO get actual content - not sure yet how this comes out, test with basic script
                $file = fopen($sPath, 'w');
                if ($file) {
                    fwrite($file, $response->body);
                    fclose($file);
                    KTUtil::download($sPath, $mimetype, $iFileSize, $sFileName);
                }
            }
//        }
        else {
            return false;
        }
    }

    public function moveDocument(&$oDocument, $oSourceFolder, $oDestinationFolder)
    {
        // Storage path isn't based on location folder hierarchy
        return true;
    }

    /**
     * Move a file
     *
     * @param string source path
     * @param string destination path
     */
    public function move($sOldDocumentPath, $sNewDocumentPath)
    {
//        $response = $this->amazonS3->head_object($this->bucket, $sOldDocumentPath);
//        if ($response->isOK()) {
            // move the file to the new destination
            $response = $this->moveS3Object($sOldDocumentPath, $sNewDocumentPath);
            return $response;
//        }
//        
//        return false;
    }

    public function moveFolder($oFolder, $oDestFolder)
    {
        // Storage path isn't based on folder hierarchy
        return true;
    }

    public function renameFolder($oFolder, $sNewName)
    {
        // Storage path isn't based on folder hierarchy
        return true;
    }

    /**
     * Perform any storage changes necessary to account for a copied
     * document object.
     */
    public function copyDocument($oSrcDocument, &$oNewDocument)
    {        
        $oVersion = $oNewDocument->_oDocumentContentVersion;
        $sDocumentRoot = 'Documents';
        $sNewPath = $this->generateStoragePath($oNewDocument);
        $sFullOldPath = sprintf("%s/%s", $sDocumentRoot, $this->getPath($oSrcDocument));
        $sFullNewPath = sprintf("%s/%s", $sDocumentRoot, $sNewPath);

        $response = $this->copyS3Object($sFullOldPath, $sFullNewPath, null);
        if (!$response) {
            return new PEAR_Error("There was an error copying the file from $sFullOldPath to $sFullNewPath");
        }
        
        $oVersion->setStoragePath($sNewPath);
        $oVersion->update();
    }

    public function renameDocument(&$oDocument, $oOldContentVersion, $sNewFilename)
    {
        // Storage isn't based on document name
        return true;
    }

    public function delete($oDocument)
    {
        // Storage doesn't care if the document is deleted
        return true;
    }

    /**
     * Completely remove a document from the bucket
     *
     * return boolean true on successful expunge
     */
    public function expunge($oDocument)
    {
        parent::expunge($oDocument);

        $sDocumentRoot = 'Documents';
        $aVersions = KTDocumentContentVersion::getByDocument($oDocument);
        foreach ($aVersions as $oVersion) {
            $sPath = sprintf('%s/%s', $sDocumentRoot, $oVersion->getStoragePath());
            $response = $this->amazonS3->delete_object($this->bucket, $sPath);
        }

        // TODO proper error handling
        return true;
    }

    /**
	 * Completely remove a document version
	 *
	 * return boolean true on successful delete
	 */
    public function deleteVersion($oVersion)
    {
        $iContentId = $oVersion->getContentVersionId();
        $oContentVersion = KTDocumentContentVersion::get($iContentId);

        $amazonS3Path = sprintf("%s/%s", 'Documents', $oContentVersion->getStoragePath());
        // NOTE do we need to check, or can we just issue the delete anyway?
        //      existing storage driver checks, so we check...
//        $response = $this->amazonS3->head_object($this->bucket, $amazonS3Path);
//        if ($response->isOK()) {
            $response = $this->amazonS3->delete_object($this->bucket, $amazonS3Path);
//        }

        // TODO proper error handling
        return $response;
    }

    public function restore($oDocument)
    {
        // Storage doesn't care if the document is deleted or restored
        return true;
    }
    
    /**
     * Determine the md5 of a file stored in S3
     *
     * @param string $path
     * @return string the md5 value
     */
    public function md5File($path)
    {
        $path = $this->getShortPath($path);
        $response = $this->amazonS3->get_object($this->bucket, $path);
        if ($response->isOK()) {
            return md5($response->body);
        }
        
        // TODO proper error handling and logging
        return null;
    }
    
    /**
     * Returns whether the supplied path is a file stored on S3
     *
     * @param string $path
     * @return boolean
     */
    public function isFile($path)
    {
        $path = $this->getShortPath($path);
        $response = $this->amazonS3->head_object($this->bucket, $path);
        return $response->isOK();
    }
    
    public function fileSize($path)
    {
        $response = $this->amazonS3->head_object($this->bucket, $this->getShortPath($path));
        if ($response->isOK()) {
            return $response->header['_info']['download_content_length'];
        }
        
        return 0;
    }
    
    /**
	 * Write contents to a file.
	 */
    public function write_file($filename, $mode, $string)
    {
        $opt = array('filename' => $this->getShortPath($filename), 'body' => $string);
        $response = $this->createS3Object($opt);
        
        return $response;
    }

    /**
	 * Read contents of a file.
	 */
    // TODO handle length based reads
    public function read_file($filename = "", $mode = "", $length, $fileHandle = null)
    {
        // S3 driver cannot work with file handles
        if (empty($filename)) {
            return false;
        }

        $response = $this->amazonS3->get_object($this->bucket, $this->getShortPath($filename));
        if ($response->isOK()) {
            return $response->body;
        }

        return false;
    }
    
    /**
     * Checks whether a file or directory exists. 
     *
     * @param string $filename - Path to the file to open.
     */
    public function file_exists($filename)
    {
        $response = $this->amazonS3->head_object($this->bucket, $this->getShortPath($filename));
        return $response->isOK();
    }

    /**
     * Write a string to a file
     *
     * @param string $filename - Path to the file where to write the data.
     * @param mixed $data - The data to write
     * @param boolean $flags - The value of flags can be any combination of the following flags (with some restrictions)
     * @param resource $context - A valid context resource created with stream_context_create().
     */
    public function file_put_contents($filename, $data, $flags = null, $context = null)
    {
        $filename = $this->getShortPath($filename);
        $opt = array('filename' => $filename, 'body' => $data);
        $response = $this->createS3Object($opt);
        
        return $response;
    }

    /**
     * Reads entire file into a string
     *
     * @param string $filename - Name of the file to read. 
     * @param string $flags - The data to write
     * @param resource $context - A valid context resource created with stream_context_create().
     * @param integer $offset - The offset where the reading starts on the original stream. 
     * @param integer $maxlen - Maximum length of data read. The default is to read until end of file is reached. Note that this parameter is applied to the stream processed by the filters.
     */
    // TODO offset based reading
    public function file_get_contents($filename, $flags = null, $context = null, $offset = null, $maxlen = null)
    {
        $response = $this->amazonS3->get_object($this->bucket, $this->getShortPath($filename));
        if ($response->isOK()) {
            return $response->body;
        }

        return false;
    }

    /**
     * Determine whether file is writable - has no equivalent on S3
     *
     * @param string $filename - The filename being checked. 
     */
    public function is_writable($filename)
    {
        return true;
    }

    /**
     * Moves an uploaded file to a new location
     * 
     * @param string $filename - The filename of the uploaded file. 
     * @param string $destination - The destination of the moved file. 
     */
    public function move_uploaded_file($filename, $destination)
    {
        $response = $this->moveS3Object($this->getShortPath($filename), $this->getShortPath($destination));   
        return $response;
    }
    
    /**
     * Remove a file
     * 
     * @param string $filename - Path to the file. 
     * @param resource $context - A valid context resource created with stream_context_create().
     */
    public function unlink($filename, $context = null)
    {
        $response = $this->amazonS3->delete_object($this->bucket, $this->getShortPath($filename));
        return $response->isOK();
    }
    
    /**
     * Sets access and modification time of file
     * 
     * @param string $filename - Path to the file. 
     * @param integer $time - The touch time. If time is not supplied, the current system time is used.
     *                        Not currently supported by this driver.
     * @param integer $atime - If present, the access time of the given filename is set to the value of atime. Otherwise, it is set to time. 
     *                         Not currently supported by this driver.
     */
    public function touch($filename, $time = null, $atime = null)
    {
        // TODO implement this function with S3 header information supporting $time and $atime?
        $opt = array('filename' => $filename);
        $response = $this->createS3Object($opt);
    }
    
    /**
     * Makes directory - no equivalent on S3, so just return TRUE
     * 
     * @param string $pathname - The directory path. 
     * @param integer $mode - The mode is 0777 by default, which means the widest possible access. For more information on modes, read the details on the chmod() page. 
     * @param boolean $recursive - Allows the creation of nested directories specified in the pathname. Defaults to FALSE. 
     * @param resource $context - A valid context resource created with stream_context_create().
     */
    public function mkdir($pathname, $mode = 0777, $recursive = false, $context = null)
    {
        return true;
    }
	
    /**
     * Tells whether the filename is a directory - no equivalent on S3, but can't just return TRUE?
     * 
     * @param string $filename - Path to the file/directory
     */
    public function is_dir($filename)
    {
        return true;
    }
    
    /**
     * Copies a file
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function copy($source, $destination)
    {
        $response = $this->copyS3Object($source, $destination);
        return $response;
    }
    
    /**
     * Wrapper function for tempnam
     *
     * @param string $dir
     * @param string $file
     * @return string
     */
    public function tempnam($dir, $file)
    {
        $opt['filename'] = $dir . '/' . $file;
        $response = $this->createS3Object($opt);
        if ($response) {
            return $this->getShortPath($opt['filename']);
        }
        
        return null;
    }
    
    /**
     * Utility function to encapsulate create_object and logging of all creates
     * Additionally ensures paths are short paths
     *
     * @param array $opt Parameters required for the object creation
     * @return booleane
     */
    private function createS3Object($opt)
    {
        global $default;
        
        // ensure paths are not full paths
        $opt['filename'] = $this->getShortPath($opt['filename']);
        
        $response = $this->amazonS3->create_object($this->bucket, $opt);
        if ($response->isOK()) {
            $default->log->info("Amazon S3 PUT operation [CREATE]: {$this->bucket}/{$opt['filename']}");
            return true;
        }
        
        return false;
    }
    
    /**
     * Utility function to encapsulate copy_object and logging of all copies
     * Additionally ensures paths are short paths
     *
     * @param string $sourceFilePath
     * @param string $destinationFilePath
     * @param array $opt Parameters required for the object copy
     * @return booleane
     */
    private function copyS3Object($sourceFilePath, $destinationFilePath, $opt)
    {
        global $default;
        
        // ensure paths are not full paths
        if (isset($opt['filename'])) {
            $opt['filename'] = $this->getShortPath($opt['filename']);
        }
        $sourceFilePath = $this->getShortPath($sourceFilePath);
        $destinationFilePath = $this->getShortPath($destinationFilePath);
                
        $response = $this->amazonS3->copy_object($this->bucket, $sourceFilePath, $this->bucket, $destinationFilePath, $opt);
        if ($response->isOK()) {
            $default->log->info("Amazon S3 PUT operation [COPY]: {$this->bucket}/$destinationFilePath");
            return true;
        }

        return false;
    }
    
    /**
     * Utility function to encapsulate move_object and logging of all moves
     * Additionally ensures paths are short paths
     *
     * @param string $sOldDocumentPath
     * @param string $sNewDocumentPath
     * @return booleane
     */
    private function moveS3Object($sourceFilePath, $destinationFilePath)
    {
        global $default;        
        
        // ensure paths are not full paths
        $sourceFilePath = $this->getShortPath($sourceFilePath);
        $destinationFilePath = $this->getShortPath($destinationFilePath);
        
        $response = $this->amazonS3->move_object($this->bucket, $sourceFilePath, $this->bucket, $destinationFilePath);
        if ($response->isOK()) {
            $default->log->info("Amazon S3 PUT operation [MOVE]: {$this->bucket}/$destinationFilePath");
            return true;
        }
        
        return false;
    }

}

?>