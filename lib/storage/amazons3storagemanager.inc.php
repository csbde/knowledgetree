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

// TODO determine which of these is still needed for S3
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
// TODO logging
// TODO use of vhost
class KTAmazonS3StorageManager extends KTStorageManager {

    private $amazonS3;
    private $bucket;

    // TODO real account name passed through (depends on account routing story)
    // TODO fetch amazon info from a config file - where to put this config file (we want it outside of publicly accesible code...)
    function __construct()
    {
        ConfigManager::load(KT_DIR . '/config/aws_config.ini');
        if (ConfigManager::error()) {
            // TODO log error
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
            throw $e;
        }

        // TODO get proper account name from session value
        $accountName = 'default';
        $this->bucket = "ktlive-$accountName";

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

    function upload(&$oDocument, $sTmpFilePath, $aOptions = null)
    {
        if (OS_WINDOWS) {
            $sTmpFilePath = str_replace('\\','/',$sTmpFilePath);
        }
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
        $oDocument->setFileSize($response->header->info->download_content_length);
        $oConfig =& KTConfig::getSingleton();
        $amazonS3Path = sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $this->getPath($oDocument));

        //copy the file accross
        $start_time = KTUtil::getBenchmarkTime();
        $file_size = $oDocument->getFileSize();
        if ($this->writeToFile($sTmpFilePath, $amazonS3Path, $aOptions)) {
            $end_time = KTUtil::getBenchmarkTime();
            global $default;
            $default->log->info(sprintf("Uploaded %d byte file in %.3f seconds", $file_size, $end_time - $start_time));

            //remove the temporary file
            //            @unlink($sTmpFilePath);
            $response = $this->amazonS3->head_object($this->bucket, $amazonS3Path);
            if ($response->isOK()) {
                return true;
            }
            else {
                return new PEAR_Error("$amazonS3Path does not exist after write to storage path. Options: " . print_r($aOptions,true));
            }
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
    function uploadTmpFile($sUploadedFile, $sTmpFilePath, $aOptions = null)
    {
        if (OS_WINDOWS) {
            $sTmpFilePath = str_replace('\\','/',$sTmpFilePath);
        }

        if ($this->writeToFile($sUploadedFile, $sTmpFilePath, $aOptions)) {
            if (file_exists($sTmpFilePath)) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * Writes file to Amazon S3 storage (except for bulk uploads, which still use the local system)
     *
     * @param string $sourceFilePath
     * @param string $destinationFilePath
     * @param array $aOptions
     * @return boolean
     */
    function writeToFile($sourceFilePath, $destinationFilePath, $aOptions = null)
    {
        // TODO determine what if anything needs to change here - this is only used by bulk upload,
        //      I think for the zip file...
        if(isset($aOptions['copy_upload']) && ($aOptions['copy_upload'] == 'true')) {
            return copy($sourceFilePath, $destinationFilePath);
        }

        // copy from php temp directory to S3
        if (is_uploaded_file($sourceFilePath)) {
            $content = file_get_contents($sourceFilePath);
            $opt = array('filename' => $destinationFilePath, 'body' => $content);
            $response = $this->amazonS3->create_object($this->bucket, $opt);
            // ensure php temp file is removed, as we are not using move_uploaded_file()
            @unlink($sTmpFilePath);

            return $response->isOK();
        }
        // already in S3, just do a rename (equivalent of move, restricted to single bucket)
        else {
            $response = $this->amazonS3->rename_object($this->bucket, $sourceFilePath, $destinationFilePath);
            return $response['copy']->isOK() && $response['delete']->isOK();
        }

        return false;
    }

    function getPath(&$oDocument)
    {
        return $oDocument->getStoragePath();
    }

    function setPath(&$oDocument, $sNewPath)
    {
        $oDocument->setStoragePath($sNewPath);
    }

    function generateStoragePath(&$oDocument)
    {
        return $this->generateStoragePathForVersion($oDocument->getContentVersionId());
    }

    function generateStoragePathForVersion($oContentVersion)
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
    function temporaryFile(&$oDocument)
    {
        $oConfig =& KTConfig::getSingleton();
        return sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $this->getPath($oDocument));
    }

    function temporaryFileForVersion($iVersionId)
    {
        $oConfig =& KTConfig::getSingleton();

        // get path to the content version
        $oContentVersion = KTDocumentContentVersion::get($iVersionId);
        $sPath = sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $this->getPath($oContentVersion));

        // Ensure the file exists
        if (file_exists($sPath)) {
            return $sPath;
        }
        return false;
    }

    // TODO modify to use direct access to S3 instead of downloading locally
    function download($oDocument, $bIsCheckout = false)
    {
        $oConfig =& KTConfig::getSingleton();
        $amazonS3Path = $oConfig->get('urls/documentRoot') .'/'. $oDocument->getStoragePath();

        // Ensure the file exists
        $response = $this->amazonS3->head_object($this->bucket, $amazonS3Path);
        if ($response->isOK()) {
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
            $sPath = sprintf("%s/%s", $oConfig->get('urls/tmpDirectory'), $sFileName);
            $response = $this->amazonS3->get_object($this->bucket, $amazonS3Path);
            if ($response->isOK()) {
                // copy file content to local path & download
                // TODO get actual content - not sure yet how this comes out, test with basic script
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
        }
        else {
            // TODO logging
            return false;
        }
    }

    function createFolder($oFolder)
    {
        // Storage doesn't deal with folders
        return true;
    }

    function removeFolder($oFolder)
    {
        // Storage doesn't deal with folders
        return true;
    }

    function removeFolderTree($oFolder)
    {
        // Storage doesn't deal with folders
        return true;
    }

    // TODO modify to use direct access to S3 instead of downloading locally
    function downloadVersion($oDocument, $iVersionId)
    {
        //get the document
        $oContentVersion = KTDocumentContentVersion::get($iVersionId);
        $sVersion = sprintf("%d.%d", $oContentVersion->getMajorVersionNumber(), $oContentVersion->getMinorVersionNumber());
        $oConfig =& KTConfig::getSingleton();
        $amazonS3Path = sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $this->getPath($oContentVersion));

        // Ensure the file exists
        $response = $this->amazonS3->head_object($this->bucket, $amazonS3Path);
        if ($response->isOK()) {
            // Get the mime type
            $mimeId = $oContentVersion->getMimeTypeID();
            $mimetype = KTMime::getMimeTypeName($mimeId);

            $sFileName = $sVersion.'-'.$oContentVersion->getFileName( );
            $iFileSize = $oContentVersion->getFileSize();

            // download to local system
            $response = $this->amazonS3->get_object($this->bucket, $amazonS3Path);
            if ($response->isOK()) {
                // copy file content to local path & download
                $oConfig =& KTConfig::getSingleton();
                $sPath = sprintf("%s/%s", $oConfig->get('urls/tmpDirectory'), $this->getPath($oContentVersion));
                // TODO get actual content - not sure yet how this comes out, test with basic script
                $file = fopen($sPath, 'w');
                if ($file) {
                    fwrite($file, $response->body);
                    fclose($file);
                    KTUtil::download($sPath, $mimetype, $iFileSize, $sFileName);
                }
            }
        }
        else {
            return false;
        }
    }

    function moveDocument(&$oDocument, $oSourceFolder, $oDestinationFolder)
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
    function move($sOldDocumentPath, $sNewDocumentPath)
    {
        $response = $this->amazonS3->head_object($this->bucket, $sOldDocumentPath);
        if ($response->isOK()) {
            // move the file to the new destination
            $response = $this->amazonS3->move_object($this->bucket, $sOldDocumentPath, $this->bucket, $sNewDocumentPath);
            return $response->isOK();
        }
        else {
            return false;
        }
    }

    function moveFolder($oFolder, $oDestFolder)
    {
        // Storage path isn't based on folder hierarchy
        return true;
    }

    function renameFolder($oFolder, $sNewName)
    {
        // Storage path isn't based on folder hierarchy
        return true;
    }

    /**
     * Perform any storage changes necessary to account for a copied
     * document object.
     */
    function copy($oSrcDocument, &$oNewDocument)
    {
        $oVersion = $oNewDocument->_oDocumentContentVersion;
        $oConfig =& KTConfig::getSingleton();
        $sDocumentRoot = $oConfig->get('urls/documentRoot');
        $sNewPath = $this->generateStoragePath($oNewDocument);
        $sFullOldPath = sprintf("%s/%s", $sDocumentRoot, $this->getPath($oSrcDocument));
        $sFullNewPath = sprintf("%s/%s", $sDocumentRoot, $sNewPath);

        $response = $this->amazonS3->copy_object($this->bucket, $sFullOldPath, $this->bucket, $sFullNewPath);
        if (!$response->isOK()) {
            return new PEAR_Error("There was an error copying the file from $sFullOldPath to $sFullNewPath");
        }
        $oVersion->setStoragePath($sNewPath);
        $oVersion->update();
    }

    function renameDocument(&$oDocument, $oOldContentVersion, $sNewFilename)
    {
        // Storage isn't based on document name
        return true;
    }

    function delete($oDocument)
    {
        // Storage doesn't care if the document is deleted
        return true;
    }

    /**
     * Completely remove a document from the bucket
     *
     * return boolean true on successful expunge
     */
    function expunge($oDocument)
    {
        parent::expunge($oDocument);

        $oConfig =& KTConfig::getSingleton();
        $sDocumentRoot = $oConfig->get('urls/documentRoot');
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
    function deleteVersion($oVersion)
    {
        $oConfig =& KTConfig::getSingleton();
        $iContentId = $oVersion->getContentVersionId();
        $oContentVersion = KTDocumentContentVersion::get($iContentId);

        $amazonS3Path = sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $oContentVersion->getStoragePath());
        // NOTE do we need to check, or can we just issue the delete anyway?
        //      existing storage driver checks, so we check...
        $response = $this->amazonS3->head_object($this->bucket, $amazonS3Path);
        if ($response->isOK()) {
            $response = $this->amazonS3->delete_object($this->bucket, $amazonS3Path);
        }

        // TODO proper error handling
        return true;
    }

    function restore($oDocument)
    {
        // Storage doesn't care if the document is deleted or restored
        return true;
    }

}

?>