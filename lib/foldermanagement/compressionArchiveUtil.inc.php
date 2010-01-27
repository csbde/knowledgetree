<?php
/**
 * $Id:
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
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
 */

require_once('File/Archive.php');
require_once(KT_LIB_DIR . '/util/ktpclzip.inc.php');

/**
* Class to create and download a zip file
*/
class ZipFolder {

    var $sTmpPath = '';
    var $sZipFileName = '';
    var $sZipFile = '';
    var $sPattern = '';
    var $sFolderPattern = '';
    var $aPaths = array();
    var $aReplaceKeys = array();
    var $aReplaceValues = array();
    var $sOutputEncoding = 'UTF-8';
    var $extension = 'zip';
    var $exportCode = null;

    /**
    * Constructor
    *
    * @param string $sZipFileName The name of the zip file - gets ignored at the moment.
    * @param string $exportCode The code to use if a zip file has already been created.
    */
    function ZipFolder($sZipFileName = null, $exportCode = null, $extension = 'zip') {
        $this->oKTConfig =& KTConfig::getSingleton();
        $this->oStorage =& KTStorageManagerUtil::getSingleton();

        $this->sOutputEncoding = $this->oKTConfig->get('export/encoding', 'UTF-8');
        $this->extension = $extension;

        $this->sPattern = "[\*|\%|\\\|\/|\<|\>|\+|\:|\?|\||\'|\"]";
        $this->sFolderPattern = "[\*|\%|\<|\>|\+|\:|\?|\||\'|\"]";

        if(!empty($exportCode)){
            $this->exportCode = $exportCode;
        }else{
            $this->exportCode = KTUtil::randomString();
        }

        // Check if the temp directory has been created and stored in session
        $aData = KTUtil::arrayGet($_SESSION['zipcompression'], $exportCode);
        if(!empty($aData) && isset($aData['dir'])){
            $sTmpPath = $aData['dir'];
        }else {
            $sBasedir = $this->oKTConfig->get("urls/tmpDirectory");
            $sTmpPath = tempnam($sBasedir, 'kt_compress_zip');

            unlink($sTmpPath);
            mkdir($sTmpPath, 0755);
        }

        // Hard coding the zip file name.
        // It normally uses the folder name but if there are special characters in the name then it doesn't download properly.
        $sZipFileName = 'kt_zip';

        $this->sTmpPath = $sTmpPath;
        $this->sZipFileName = $sZipFileName;
        $this->aPaths = array();

        $aReplace = array(
            "[" => "[[]",
            " " => "[ ]",
            "*" => "[*]",
            "?" => "[?]",
        );

        $this->aReplaceKeys = array_keys($aReplace);
        $this->aReplaceValues = array_values($aReplace);
    }

    static public function get($exportCode)
    {
        static $zipFolder = null;
        if(is_null($zipFolder)){
            $zipFolder = new ZipFolder('', $exportCode);
        }
        return $zipFolder;
    }

    /**
     * Return the full path
     *
     * @param mixed $oFolderOrDocument May be a Folder or Document
     */
    function getFullFolderPath($oFolder)
    {
    	static $sRootFolder = null;

    	if (is_null($sRootFolder))
    	{
    		$oRootFolder = Folder::get(1);
    		$sRootFolder = $oRootFolder->getName();
    	}

    	$sFullPath = $sRootFolder . '/';
    	$sFullPath .= $oFolder->getFullPath();


    	if (substr($sFullPath,-1) == '/') $sFullPath = substr($sFullPath,0,-1);
    	return $sFullPath;
    }


    /**
    * Add a document to the zip file
    */
    function addDocumentToZip($oDocument, $oFolder = null) {
        if(empty($oFolder)){
            $oFolder = Folder::get($oDocument->getFolderID());
        }

        $sDocPath = $this->getFullFolderPath($oFolder);
        $sDocPath = preg_replace($this->sFolderPattern, '-', $sDocPath);
        $sDocPath = $this->_convertEncoding($sDocPath, true);


        $sDocName = $oDocument->getFileName();
        $sDocName = preg_replace($this->sPattern, '-', $sDocName);
        $sDocName = $this->_convertEncoding($sDocName, true);

        $sParentFolder = $this->sTmpPath.'/'.$sDocPath;
        $newDir = $this->sTmpPath;

        $aFullPath = split('/', $sDocPath);
        foreach ($aFullPath as $dirPart) {
            $newDir = sprintf("%s/%s", $newDir, $dirPart);
            if (!file_exists($newDir)) {
                mkdir($newDir, 0700);
            }
        }

        $sOrigFile = $this->oStorage->temporaryFile($oDocument);
        $sFilename = $sParentFolder.'/'.$sDocName;
        @copy($sOrigFile, $sFilename);

        $this->aPaths[] = $sDocPath.'/'.$sDocName;
        return true;
    }

    /**
    * Add a folder to the zip file
    */
    function addFolderToZip($oFolder) {
        $sFolderPath = $this->getFullFolderPath($oFolder) .'/';
        $sFolderPath = preg_replace($this->sFolderPattern, '-', $sFolderPath);
        $sFolderPath = $this->_convertEncoding($sFolderPath, true);

        $newDir = $this->sTmpPath;

        $aFullPath = split('/', $sFolderPath);
        foreach ($aFullPath as $dirPart) {
            $newDir = sprintf("%s/%s", $newDir, $dirPart);
            if (!file_exists($newDir)) {
                mkdir($newDir, 0700);
            }
        }

        $this->aPaths[] = $sFolderPath;
        return true;
    }

    /**
    * Zip the temp folder
    */
    function createZipFile($bEchoStatus = FALSE) {
        if(empty($this->aPaths)){
            return PEAR::raiseError(_kt("No folders or documents found to compress"));
        }

		$sZipFile = sprintf("%s/%s.".$this->extension, $this->sTmpPath, $this->sZipFileName);
        $sZipFile = str_replace('<', '', str_replace('</', '', str_replace('>', '', $sZipFile)));
        
        $archive = new KTPclZip($sZipFile);
        $archive->createZipFile($this->sTmpPath.'/Root Folder');
                
        /*
        $config = KTConfig::getSingleton();
        $useBinary = true; //$config->get('export/useBinary', false);

        // Set environment language to output character encoding
        $loc = $this->sOutputEncoding;
        putenv("LANG=$loc");
        putenv("LANGUAGE=$loc");
        $loc = setlocale(LC_ALL, $loc);

        if($useBinary){
            $sManifest = sprintf("%s/%s", $this->sTmpPath, "MANIFEST");
            file_put_contents($sManifest, join("\n", $this->aPaths));
        }

        $sZipFile = sprintf("%s/%s.".$this->extension, $this->sTmpPath, $this->sZipFileName);
        $sZipFile = str_replace('<', '', str_replace('</', '', str_replace('>', '', $sZipFile)));

        if($useBinary){
            $sZipCommand = KTUtil::findCommand("export/zip", "zip");
            $aCmd = array($sZipCommand, "-r", $sZipFile, ".", "-i@MANIFEST");
            $sOldPath = getcwd();
            chdir($this->sTmpPath);

            // Note that the popen means that pexec will return a file descriptor
            $aOptions = array('popen' => 'r');
            $fh = KTUtil::pexec($aCmd, $aOptions);

            if($bEchoStatus){
                $last_beat = time();
                while(!feof($fh)) {
                    if ($i % 1000 == 0) {
                        $this_beat = time();
                        if ($last_beat + 1 < $this_beat) {
                            $last_beat = $this_beat;
                            print "&nbsp;";
                        }
                    }
                    $contents = fread($fh, 4096);
                    if ($contents) {
                        print nl2br($this->_convertEncoding($contents, false));
                    }
                    $i++;
                }
            }
            pclose($fh);
        }else{
            // Create the zip archive using the PEAR File_Archive
            File_Archive::extract(
                File_Archive::read($this->sTmpPath.'/Root Folder'),
                File_Archive::toArchive($this->sZipFileName.'.'.$this->extension, File_Archive::toFiles($this->sTmpPath), $this->extension)
            );
        }
		*/
        
        // Save the zip file and path into session
        $_SESSION['zipcompression'] = KTUtil::arrayGet($_SESSION, 'zipcompression', array());
        $sExportCode = $this->exportCode;
        $_SESSION['zipcompression'][$sExportCode] = array(
            'file' => $sZipFile,
            'dir' => $this->sTmpPath,
        );
        $_SESSION['zipcompression']['exportcode'] = $sExportCode;

        $this->sZipFile = $sZipFile;
        return $sExportCode;
    }

    /**
    * Download the zip file
    */
    function downloadZipFile($exportCode = NULL) {
        if(!(isset($exportCode) && !empty($exportCode))) {
            $exportCode = KTUtil::arrayGet($_SESSION['zipcompression'], 'exportcode');
        }

        $aData = KTUtil::arrayGet($_SESSION['zipcompression'], $exportCode);

        if(!empty($aData)){
            $sZipFile = $aData['file'];
            $sTmpPath = $aData['dir'];
        }else{
            $sZipFile = $this->sZipFile;
            $sTmpPath = $this->sTmpPath;
        }

        if (!file_exists($sZipFile)) {
            return PEAR::raiseError(_kt('The zip file has not been created, if you are downloading a large number of documents
            or a large document then it may take a few minutes to finish.'));
        }

        $mimeType = 'application/zip; charset=utf-8;';
        $fileSize = filesize($sZipFile);
        $fileName = $this->sZipFileName .'.'.$this->extension;

        KTUtil::download($sZipFile, $mimeType, $fileSize, $fileName);
        KTUtil::deleteDirectory($sTmpPath);
        // remove notification file if it exists
        DownloadQueue::removeNotificationFile();
        
        // remove zip file database entry and any stragglers
        DBUtil::whereDelete('download_queue', array('code' => $exportCode));
        
        return true;
    }

    function checkArchiveExists($exportCode = null)
    {
        if(!(isset($exportCode) && !empty($exportCode))) {
            $exportCode = KTUtil::arrayGet($_SESSION['zipcompression'], 'exportcode');
        }

        $aData = KTUtil::arrayGet($_SESSION['zipcompression'], $exportCode);

        if(!empty($aData)){
            $sZipFile = $aData['file'];
            $sTmpPath = $aData['dir'];
        }else{
            $sZipFile = $this->sZipFile;
            $sTmpPath = $this->sTmpPath;
        }

        if (!file_exists($sZipFile)) {
            return false;
        }
        return true;
    }

    /**
    * Check that iconv exists and that the selected encoding is supported.
    */
    function checkConvertEncoding() {
        if(!function_exists("iconv")) {
            return PEAR::raiseError(_kt('IConv PHP extension not installed. The zip file compression could not handle output filename encoding conversion !'));
        }
        $oKTConfig = $this->oKTConfig;
        $this->sOutputEncoding = $oKTConfig->get('export/encoding', 'UTF-8');

        // Test the specified encoding
        if(iconv("UTF-8", $this->sOutputEncoding, "") === FALSE) {
            return PEAR::raiseError(_kt('Specified output encoding for the zip files compression does not exists !'));
        }
        return true;
    }

    function _convertEncoding($sMystring, $bEncode) {
    	if (strcasecmp($this->sOutputEncoding, "UTF-8") === 0) {
    		return $sMystring;
    	}
    	if ($bEncode) {
    		return iconv("UTF-8", $this->sOutputEncoding, $sMystring);
    	} else {
    		return iconv($this->sOutputEncoding, "UTF-8", $sMystring);
    	}
    }

    static public function checkDownloadSize($object)
    {
        return true;

        if($object instanceof Document || $object instanceof DocumentProxy){
        }

        if($object instanceof Folder || $object instanceof FolderProxy){
            $id = $object->iId;

            // If we're working with the root folder
            if($id = 1){
                $sql = 'SELECT count(*) as cnt FROM documents where folder_id = 1';
            }else{
                $sql[] = "SELECT count(*) as cnt FROM documents where parent_folder_ids like '%,?' OR parent_folder_ids like '%,?,%' OR folder_id = ?";
                $sql[] = array($id, $id, $id);
            }

            /*
            SELECT count(*) FROM documents d
            INNER JOIN document_metadata_version m ON d.metadata_version_id = m.id
            INNER JOIN document_content_version c ON m.content_version_id = c.id
            where (d.parent_folder_ids like '%,12' OR d.parent_folder_ids like '%,12,%' OR d.folder_id = 12) AND d.status_id < 3 AND size > 100000
            */

            $result = DBUtil::getOneResult($sql);

            if($result['cnt'] > 10){
                return true;
            }
        }

        return false;
    }
    
    /**
     * Returns the zip file name with extension
     *
     * @return string
     */
    public function getZipFileName()
    {
    	return $this->sZipFileName . '.' . $this->extension;
    }
    
    /**
     * Returns the path to the zip file
     *
     * @return string
     */
    public function getTmpPath()
    {
    	return $this->sTmpPath;
    }
}

/**
 * Class to manage the queue of bulk downloads
 *
 */
class DownloadQueue
{
    private $bNoisy;
    private $bNotifications;
    private $errors;
    private $lockFile;
    static private $notificationFile = 'download_queue_notification';

    /**
     * Construct download queue object
     *
     * @return DownloadQueue
     */
    public function __construct()
    {
        $config = KTConfig::getSingleton();
        $this->bNoisy = $config->get('tweaks/noisyBulkOperations', false);
        $this->bNotifications = ($config->get('export/enablenotifications', 'on') == 'on') ? true : false;
        $this->lockFile = $config->get('cache/cacheDirectory') . '/download_queue_lock.lock';
    }

    /**
     * Add an item to the download queue
     *
     * @param string $code The identification string for the download
     * @param string $id The object id
     * @param string $type The type of object Folder | Document
     */
    static public function addItem($code, $folderId, $id, $type)
    {
        $fields = array();
        $fields['code'] = $code;
        $fields['folder_id'] = $folderId;
        $fields['object_id'] = $id;
        $fields['object_type'] = $type;
        $fields['user_id'] = $_SESSION['userID'];
        $fields['date_added'] = date('Y-m-d H:i:s');

        $res = DBUtil::autoInsert('download_queue', $fields);
    }

    /**
     * Remove an item from the download queue
     * Will not remove zip object types, these must be independently removed
     *
     * @param string $code Identification string for the download item
     * @return boolean | PEAR_Error
     */
    public function removeItem($code)
    {
        $res = DBUtil::runQuery('DELETE FROM download_queue WHERE code = "' . $code . '" AND object_type != "zip"');
        return $res;
    }

    /**
     * Get all download items (other than zip object type) in the queue
     *
     * @return Queue array | PEAR_Error
     */
    public function getQueue()
    {
        $sql = 'SELECT * FROM download_queue d WHERE status = 0 AND object_type != "zip" ORDER BY date_added, code';
        $rows = DBUtil::getResultArray($sql);

        if(PEAR::isError($rows)){
            return $rows;
        }

        $queue = array();
        foreach ($rows as $item){
            $queue[$item['code']][] = $item;
        }
        return $queue;
    }

    /**
     * Get the status of an item in the queue
     * 0 = new item; 1 = in progress; 2 = completed; 3 = error;
     *
     * @param string $code
     * @return Queue array
     */
    public function getItemStatus($code)
    {
        $sql = array();
        $sql[] = 'SELECT status, errors FROM download_queue WHERE code = ?';
        $sql[] = $code;
        $result = DBUtil::getResultArray($sql);
        return $result;
    }

    /**
     * Set the status of an item and any errors that may have occurred while trying to archive it.
     *
     * @param string $code The identification string
     * @param integer $status The new status of the item
     * @param string $error Optional. The error's generated during the archive
     * @return boolean
     */
    public function setItemStatus($code, $status = 1, $error = null, $zip = false)
    {
        $fields = array();
        $fields['status'] = $status;
        $fields['errors'] = !empty($error) ? json_encode($error) : null;
        $where = array('code' => $code);
        if ($zip) {
        	$where['object_type'] = 'zip';
        }
        $res = DBUtil::whereUpdate('download_queue', $fields, $where);
        return $res;
    }

    /**
     * Loop through the queue and archive the items.
     *
     * @return unknown
     */
    public function processQueue()
    {
        global $default;

        // get items from queue
        $queue = $this->getQueue();
        if(PEAR::isError($queue)){
            $default->log->debug('Download Queue: error on fetching queue - '.$queue->getMessage());
            return false;
        }

        // Set queue as locked
        touch($this->lockFile);

        // Loop through items and create downloads
        foreach ($queue as $code => $download){
            // reset the error messages
            $this->errors = null;

            // if the user_id is not set then skip
            if(!isset($download[0]['user_id']) || empty($download[0]['user_id'])){
                $default->log->debug('Download Queue: no user id set for download code '.$code);
                $error = array(_kt('No user id has been set, the archive cannot be created.'));
                $result = $this->setItemStatus($code, 3, $error);
                continue;
            }

            // Force a session for the user
            $_SESSION['userID'] = $download[0]['user_id'];
            $baseFolderId = $download[0]['folder_id'];

            // Create a new instance of the archival class
            $zip = new ZipFolder('', $code);
            $res = $zip->checkConvertEncoding();

            if(PEAR::isError($res)){
                $default->log->error('Download Queue: Archive class check convert encoding error - '.$res->getMessage());
                $error = array(_kt('The archive cannot be created. An error occurred in the encoding.'));
                $result = $this->setItemStatus($code, 3, $error);
                continue;
            }

            $result = $this->setItemStatus($code, 1);
            if(PEAR::isError($result)){
                $default->log->error('Download Queue: item status could not be set for user: '.$_SESSION['userID'].', code: '.$code.', error: '.$result->getMessage());
            }

            $default->log->debug('Download Queue: Creating download for user: '.$_SESSION['userID'].', code: '.$code);

            DBUtil::startTransaction();

            // Add the individual files and folders into the archive
            foreach ($download as $item){
                if($item['object_type'] == 'document'){
                    $docId = $item['object_id'];
                    $this->addDocument($zip, $docId);
                }
                if($item['object_type'] == 'folder'){
                    $folderId = $item['object_id'];
                    $this->addFolder($zip, $folderId);
                }
            }

            $res = $zip->createZipFile();

            if(PEAR::isError($res)){
                $default->log->debug('Download Queue: Archive could not be created. Exiting transaction. '.$res->getMessage());
                DBUtil::rollback();

                $error = array(_kt('The archive could not be created.'));
                $result = $this->setItemStatus($code, 3, $error);
                continue;
            }

            $default->log->debug('Download Queue: Archival successful');

            $oTransaction = KTFolderTransaction::createFromArray(array(
                'folderid' => $baseFolderId,
                'comment' => "Bulk export",
                'transactionNS' => 'ktstandard.transactions.bulk_export',
                'userid' => $_SESSION['userID'],
                'ip' => Session::getClientIP(),
            ));

            if(PEAR::isError($oTransaction)){
                $default->log->debug('Download Queue: transaction could not be logged. '.$oTransaction->getMessage());
            }

            DBUtil::commit();

            // Set status for the download
            $this->errors['archive'] = $_SESSION['zipcompression'];
            $result = $this->setItemStatus($code, 2, $this->errors);
            if(PEAR::isError($result)){
                $default->log->error('Download Queue: item status could not be set for user: '.$_SESSION['userID'].', code: '.$code.', error: '.$result->getMessage());
            }
            // reset the error messages
            $this->errors = null;
            $_SESSION['zipcompression'] = null;
        }
        
        if (count($queue) && !PEAR::isError($res) && !PEAR::isError($result)) {
        	// create the db entry
        	self::addItem($code, self::getFolderId($code), -1, 'zip');
        	// update the db entry with the appropriate status and message
        	$this->setItemStatus($code, 2, serialize(array($zip->getTmpPath(), $zip->getZipFileName())), true);
        	// write a file which will be checked if the user has not been logged out and back in 
        	// (in which case the required session value will not be set and this file acts as a trigger instead)
        	$config = KTConfig::getSingleton();
        	@touch($config->get('cache/cacheDirectory') . '/' . self::$notificationFile);
        }

        // Remove lock file
        @unlink($this->lockFile);
    }

    /**
     * Add a document to the archive
     *
     * @param unknown_type $zip
     * @param unknown_type $docId
     * @param boolean $alerts
     * @return unknown
     */
    public function addDocument(&$zip, $docId, $alerts = true)
    {

        $oDocument = Document::get($docId);
        if(PEAR::isError($oDocument)){
            $this->errors[] = _kt('Document cannot be exported, an error occurred: ').$oDocument->getMessage();
            return $oDocument;
        }

        if ($this->bNoisy) {
            $oDocumentTransaction = new DocumentTransaction($oDocument, "Document part of bulk export", 'ktstandard.transactions.bulk_export', array());
            $oDocumentTransaction->create();
        }

        // fire subscription alerts for the downloaded document - if global config is set
        if($this->bNotifications && $alerts){
            $oSubscriptionEvent = new SubscriptionEvent();
            $oFolder = Folder::get($oDocument->getFolderID());
            $oSubscriptionEvent->DownloadDocument($oDocument, $oFolder);
        }

        return $zip->addDocumentToZip($oDocument);
    }

    /**
     * Add a folder to the archive
     *
     * @param unknown_type $zip
     * @param unknown_type $folderId
     * @return unknown
     */
    public function addFolder(&$zip, $folderId)
    {
        $oFolder = Folder::get($folderId);

        if(PEAR::isError($oFolder)){
            $this->errors[] = _kt('Folder cannot be exported, an error occurred: ').$oFolder->getMessage();
            return $oFolder;
        }

        $sFolderDocs = $oFolder->getDocumentIDs($folderId);
        if(PEAR::isError($sFolderDocs)){
            $default->log->error('Download Queue: get document ids for folder caused an error: '.$sFolderDocs->getMessage());
            $sFolderDocs = '';
        }

        // Add folder to zip
        $zip->addFolderToZip($oFolder);

        $aDocuments = array();
        if(!empty($sFolderDocs)){
            $aDocuments = explode(',', $sFolderDocs);
        }

        // Get all the folders within the current folder
        $sWhereClause = "parent_folder_ids like '%,{$folderId}'
            OR parent_folder_ids like '%,{$folderId},%'
            OR parent_folder_ids like '{$folderId},%'
            OR parent_id = {$folderId}";

        $aFolderList = $oFolder->getList($sWhereClause);
		$aLinkingFolders = $this->getLinkingEntities($aFolderList);
        $aFolderList = array_merge($aFolderList,$aLinkingFolders);

        $aFolderObjects = array();
        $aFolderObjects[$folderId] = $oFolder;

        // Export the folder structure to ensure the export of empty directories
        if(!empty($aFolderList)){
            foreach($aFolderList as $k => $oFolderItem){
                if($oFolderItem->isSymbolicLink()){
                	$oFolderItem = $oFolderItem->getLinkedFolder();
                }
            	if(Permission::userHasFolderReadPermission($oFolderItem)){
                    // Get documents for each folder
                    $sFolderItemId = $oFolderItem->getID();
                    $sFolderItemDocs = $oFolderItem->getDocumentIDs($sFolderItemId);

                    if(!empty($sFolderItemDocs)){
                        $aFolderDocs = explode(',', $sFolderItemDocs);
                        $aDocuments = array_merge($aDocuments, $aFolderDocs);
                    }
                    $zip->addFolderToZip($oFolderItem);
                    $aFolderObjects[$oFolderItem->getId()] = $oFolderItem;
            	}
            }
        }

        // Add all documents to the export
        if(!empty($aDocuments)){
            foreach($aDocuments as $sDocumentId){
                $oDocument = Document::get($sDocumentId);
             	if($oDocument->isSymbolicLink()){
    				$oDocument->switchToLinkedCore();
    			}
    			if(Permission::userHasDocumentReadPermission($oDocument)){

                    if(!KTWorkflowUtil::actionEnabledForDocument($oDocument, 'ktcore.actions.document.view')){
                        $this->errors[] = $oDocument->getName().': '._kt('Document cannot be exported as it is restricted by the workflow.');
                        continue;
                    }

                    $sDocFolderId = $oDocument->getFolderID();
                    $oFolder = isset($aFolderObjects[$sDocFolderId]) ? $aFolderObjects[$sDocFolderId] : Folder::get($sDocFolderId);

                    if ($this->bNoisy) {
                        $oDocumentTransaction = new DocumentTransaction($oDocument, "Document part of bulk export", 'ktstandard.transactions.bulk_export', array());
                        $oDocumentTransaction->create();
                    }

                    // fire subscription alerts for the downloaded document
                    if($this->bNotifications){
                        $oSubscriptionEvent = new SubscriptionEvent();
                        $oSubscriptionEvent->DownloadDocument($oDocument, $oFolder);
                    }

                    $zip->addDocumentToZip($oDocument, $oFolder);
    			}
            }
        }
    }

    /**
     * Fetch any linked folders
     *
     * @param Folder array $aFolderList
     * @return unknown
     */
    function getLinkingEntities($aFolderList)
    {
    	$aSearchFolders = array();
    	if(!empty($aFolderList)){
            foreach($aFolderList as $oFolderItem){
            	if(Permission::userHasFolderReadPermission($oFolderItem)){
	                // If it is a shortcut, we should do some more searching
	                if($oFolderItem->isSymbolicLink()){
	                    $oFolderItem = $oFolderItem->getLinkedFolder();
	                    $aSearchFolders[] = $oFolderItem->getID();
	                }
            	}
             }
        }
    	$aLinkingFolders = array();
    	$aSearchCompletedFolders = array();
    	$count = 0;
        while(count($aSearchFolders)>0){
        	$count++;
        	$oFolder = Folder::get(array_pop($aSearchFolders));
        	$folderId = $oFolder->getId();
        	 // Get all the folders within the current folder
            $sWhereClause = "parent_folder_ids = '{$folderId}' OR
            parent_folder_ids LIKE '{$folderId},%' OR
            parent_folder_ids LIKE '%,{$folderId},%' OR
            parent_folder_ids LIKE '%,{$folderId}'";
            $aFolderList = $this->oFolder->getList($sWhereClause);
            foreach($aFolderList as $oFolderItem){
	            if($oFolderItem->isSymbolicLink()){
	            	$oFolderItem = $oFolderItem->getLinkedFolder();
	            }
				if(Permission::userHasFolderReadPermission($oFolderItem)){
		            if($aSearchCompletedFolders[$oFolderItem->getID()] != true){
	            		$aSearchFolders[] = $oFolderItem->getID();
	            		$aSearchCompletedFolders[$oFolderItem->getID()] = true;
	            	}
				}
            }
            if(!isset($aLinkingFolders[$oFolder->getId()])){
            	$aLinkingFolders[$oFolder->getId()] = $oFolder;
            }
        }
        return $aLinkingFolders;
    }

    /**
     * Check if the archive has been created and is ready for download
     *
     * @param string $code
     * @return boolean
     */
    public function isDownloadAvailable($code)
    {
        $check = $this->getItemStatus($code);
        $status = $check[0]['status'];

        if($status < 2){
            return false;
        }

        $message = $check[0]['errors'];
        $message = json_decode($message, true);

        if($status > 2){
            return $message;
        }

        // Create the archive session variables
        $_SESSION['zipcompression'] = $message['archive'];
        unset($message['archive']);

        // Check that the archive has been created
        $zip = new ZipFolder('', $code);
        if($zip->checkArchiveExists($code)){
            // Clean up the download queue and return errors
            $this->removeItem($code);
            return $message;
        }
        return false;
    }

    /**
     * Check if the queue is locked and busy processing
     *
     * @return boolean
     */
    public function isLocked()
    {
        return file_exists($this->lockFile);
    }
    
    /**
     * The code below has all been added for bulk download notifications
     * 
     * The functions were declared as static because they are related to but not entirely part of the download queue process
     * and I wanted to use them without having to instantiate the class
     */
    
    /**
     * Checks whether there are any bulk downloads which have passed the timeout limit
     * Default limit is set to 48 hours but this can be changed in the calling code
     *
     * @param int $limit the number of hours after which a download should be considered timed out
     */
    static public function timeout($limit = 48)
    {
    	DBUtil::runQuery('DELETE FROM download_queue WHERE DATE_ADD(date_added, INTERVAL ' . $limit . ' HOUR) < NOW()');
    }
    
    /**
     * Checks whether there is a bulk download available and waiting for the supplied user
     *
     * @param int $userID
     */
    static public function userDownloadAvailable($userID)
    {
    	$result = DBUtil::getOneResult('SELECT code, errors FROM download_queue WHERE user_id = ' . $userID 
    								 . ' AND object_type = "zip" AND status = 2');
    	if (PEAR::isError($result)) {
    		return false;
    	}
    	
    	$code = $result['code'];
    	
    	$message = $result['errors'];
        $message = json_decode($message, true);
        $data = unserialize($message);

        // Create the archive session variables - I can't recall if this was needed, will have to find out by testing...
        $_SESSION['zipcompression'][$code]['file'] = $data[0] . DIRECTORY_SEPARATOR . $data[1];
        $_SESSION['zipcompression'][$code]['dir'] = $data[0];

        // Check that the archive file has been created
        // NOTE If it does not exist, consider deleting the db entry and looping back to check again, only returning when no more results are found in the db?
        //      The reason for this is that it is sometimes possible to end up with multiple zips listed in the db as waiting for the same user, 
        //      some of which may no longer exist as physical files, and since the request only checks for one, it may miss am existing download.
        // NOTE the above issue may only occur due to the way I have been testing, often breaking the process before it is finished, 
        //      but conceivably this could actually happen in the real world also.
        $zip = new ZipFolder('', $code);
        if($zip->checkArchiveExists($code)) {
            return $code;
        }
        
        return false;
    }
    
    /**
     * Delete the download, including the database entry
     *
     * @param string $code
     */
    static public function deleteDownload($code)
    {
    	// retrieve the data to allow deletion of the physical file
    	$result = DBUtil::getOneResult('SELECT errors FROM download_queue WHERE code = "' . $code . '"'
    								 . ' AND object_type = "zip" AND status = 2');
    								 
    	if (PEAR::isError($result)) {
    		return $result;
    	}
    								 
    	$message = $result['errors'];
        $message = json_decode($message, true);
        $data = unserialize($message);
        
    	// remove the database entry
    	DBUtil::whereDelete('download_queue', array('code' => $code));
    	
    	// remove the actual file/directory
    	KTUtil::deleteDirectory($data[0]);
    	
    	// remove the notification file if present
    	self::removeNotificationFile();
    }
    
    /**
     * Removes the notification file, if it exists, in a safe manner
     *
     * @param string $code
     */
    static public function removeNotificationFile() {
    	$config = KTConfig::getSingleton();
    	$file = DownloadQueue::getNotificationFileName();
        if (!PEAR::isError($file)) {
			$notificationFile = $config->get('cache/cacheDirectory') . '/' . $file;
        }
        
        // ensure the file is actually a file and not a directory
        if (is_file($notificationFile)) {
        	@unlink($notificationFile);
        }
    }
    
    /**
     * Fetches a link to the download
     *
     * @param string $code
     */
    static public function getDownloadLink($code)
    {
    	return KTUtil::kt_url() . '/action.php?kt_path_info=ktcore.actions.bulk.export&action=downloadZipFile&fFolderId=' . self::getFolderId($code) . '&exportcode=' . $code;
    }
    
    /**
     * Fetches the folder id associated with the supplied code
     * Based on testing just before file corruption this may not actually be necessary, but we'll do it anyway
     *
     * @param string $code
     */
    static public function getFolderId($code)
    {
    	$result = DBUtil::getOneResult('SELECT folder_id FROM download_queue WHERE code = "' . $code . '"');
    	
    	if (PEAR::isError($result)) {
    		return -1;
    	}
    	
    	return $result['folder_id'];
    }
    
    static public function getNotificationFileName() {
    	if (!empty(self::$notificationFile)) {
    		return self::$notificationFile;
    	}
    	
    	return new PEAR_Error('Unable to get file name');
    }
}
?>
