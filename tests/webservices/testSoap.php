<?php
require_once (KT_DIR . '/tests/test.php');

/**
* These are the unit tests for the SOAP webservice
*
*/
class SOAPTestCase extends KTUnitTestCase {

    /**
    * @var object $session The KT session object
    */
    var $session;

    /**
     * @var string $rootUrl The root server url for the rest web service
     */
    var $rootUrl;
    var $uploads_dir;

    public $client;
    private $user;
    private $pass;
    private $content = 'New document for testing the soap webservice api';
    private $content_update = 'Updated document for testing the soap webservice api';
    private $reason = 'Running unit tests';

    /**
    * This method sets up the server url
    *
    */
    public function setUp()
    {
        $url = KTUtil::kt_url();
        $this->rootUrl = $url.'/ktwebservice/webservice.php?';
        $this->user = isset($_GET['user']) ? $_GET['user'] : 'admin';
		$this->pass = isset($_GET['pass']) ? $_GET['pass'] : 'admin';

    	// Login and authenticate
    	$this->connect();
		$this->login('127.0.0.1');
    }

    /**
    * This method is a placeholder
    */
    public function tearDown()
    {
        // Logout
        $this->logout();
    }

    private function connect()
    {
        $wsdl = $this->rootUrl . "wsdl";
        $this->client = new SoapClient($wsdl);
    }

    private function login($ip = null)
    {
        $res = $this->client->__soapCall("login", array($this->user, $this->pass, $ip));
        if($res->status_code != 0){
            return false;
        }
        $this->session = $res->message;
    }

    private function logout()
    {
        $result = $this->client->__soapCall("logout", array($this->session));
        if($result->status_code != 0){
            return true;
        }
    }

    private function getDocTypes()
    {
        $result = $this->client->__soapCall("get_document_types", array($this->session));
        return $result->document_types;
    }

    private function search($expr)
    {
        $result = $this->client->__soapCall("search", array($this->session, $expr, ''));
        return $result->hits;
    }

    private function getFolderDetail($folder, $parentId = 1)
    {
        $result = $this->client->__soapCall("get_folder_detail_by_name", array($this->session, $folder, $parentId));
        return $result;
    }

    private function createFolder($parent_id, $folder)
    {
        $result = $this->client->__soapCall("create_folder", array($this->session, $parent_id, $folder));
        return $result;
    }

    private function createFolderShortcut($target_folder_id, $source_folder_id)
    {
        $result = $this->client->__soapCall("create_folder_shortcut", array($this->session, $target_folder_id, $source_folder_id));
        return $result;
    }

    private function deleteFolder($folder_id)
    {
        $result = $this->client->__soapCall("delete_folder", array($this->session, $folder_id, 'Testing'));
        return $result;
    }

    private function getFolderShortcuts($folder_id)
    {
        $result = $this->client->__soapCall("get_folder_shortcuts", array($this->session, $folder_id));
        return $result;
    }

    private function getDocumentVersionHistory($document_id)
    {
        $result = $this->client->__soapCall("get_document_version_history", array($this->session, $document_id));
        return $result;
    }

    private function createDocument($folder_id,  $title, $filename, $documenttype = 'Default')
    {
        global $default;
        $uploads_dir = $default->uploadDirectory;

        // create document in uploads folder
        $tempfilename = tempnam($uploads_dir, 'myfile');
        $fp = fopen($tempfilename, 'wt');
        fwrite($fp, $this->content);
        fclose($fp);

        // call add document to upload into repo
        $result = $this->client->__soapCall("add_document", array($this->session, $folder_id,  $title, $filename, $documenttype, $tempfilename));
        return $result;
    }

    private function createSmallDocument($folder_id,  $title, $filename, $documenttype = 'Default')
    {
        global $default;
        $uploads_dir = $default->uploadDirectory;

        // create document in uploads folder
        $base64 = base64_encode($this->content);

        // call add document to upload into repo
        $result = $this->client->__soapCall("add_small_document", array($this->session, $folder_id,  $title, $filename, $documenttype, $base64));
        return $result;
    }

    private function checkinDocument($document_id,  $filename, $major_update = false)
    {
        global $default;
        $uploads_dir = $default->uploadDirectory;

        // create document in uploads folder
        $tempfilename = tempnam($uploads_dir, 'myfile');
        $fp = fopen($tempfilename, 'wt');
        fwrite($fp, $this->content_update);
        fclose($fp);

        // call add document to upload into repo
        $result = $this->client->__soapCall("checkin_document", array($this->session, $document_id,  $filename, $this->reason, $tempfilename, $major_update));
        return $result;
    }

    private function checkinSmallDocument($document_id,  $filename, $major_update = false)
    {
        global $default;
        $uploads_dir = $default->uploadDirectory;

        // encode as base64
        $base64 = base64_encode($this->content_update);

        // call add document to upload into repo
        $result = $this->client->__soapCall("checkin_small_document", array($this->session, $document_id,  $filename, $this->reason, $base64, $major_update));
        return $result;
    }

    private function checkoutDocument($document_id, $download = true)
    {
        $result = $this->client->__soapCall("checkout_document", array($this->session, $document_id, $this->reason, $download));
        return $result;
    }

    private function checkoutSmallDocument($document_id, $download = true)
    {
        $result = $this->client->__soapCall("checkout_small_document", array($this->session, $document_id, $this->reason, $download));
        return $result;
    }

    private function createDocumentShortcut($folder_id, $target_document_id)
    {
        $result = $this->client->__soapCall("create_document_shortcut", array($this->session, $folder_id, $target_document_id));
        return $result;
    }

    private function getDocumentShortcuts($document_id)
    {
        $result = $this->client->__soapCall("get_document_shortcuts", array($this->session, $document_id));
        return $result;
    }

    private function getFolderContents($folder_id, $depth=1, $what='DFS')
    {
        $result = $this->client->__soapCall("get_folder_contents", array($this->session, $folder_id, $depth, $what));
        return $result;
    }

    private function getDocumentMetadata($document_id, $version = null)
    {
        $result = $this->client->__soapCall("get_document_metadata", array($this->session, $document_id, $version));
        return $result;
    }

    private function updateDocumentMetadata($document_id, $metadata, $sysdata=null)
    {
        $result = $this->client->__soapCall("update_document_metadata", array($this->session, $document_id, $metadata, $sysdata));
        return $result;
    }

    private function downloadDocument($document_id, $version = null)
    {
        $result = $this->client->__soapCall("download_document", array($this->session, $document_id, $version));
        return $result;
    }

    private function downloadSmallDocument($document_id, $version = null)
    {
        $result = $this->client->__soapCall("download_small_document", array($this->session, $document_id, $version));
        return $result;
    }

    private function doDownload($url)
    {
        $ch = curl_init($url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

		$download = curl_exec($ch);
		$status = curl_getinfo($ch,CURLINFO_HTTP_CODE);

		$error = curl_errno($ch);

		curl_close($ch);

		return array('status' => $status, 'error' => $error, 'download' => $download);
    }

    /* *** now the test functions *** */

    /**
     * Tests finding of a folder or folder detail by name
     * Runs the following sub-tests:
     * . Folder Detail by Name in root folder (no duplicate names)
     * . Folder Detail by Name in subfolder of root folder (no duplicate names)
     * . Folder Detail by Name in subfolder of root folder (duplicate names)
     *
     * NOTE there are less tests here because we cannot test the get_folder_by_name function since it does not exist within the web service code
     */
    public function testGetFolderByName()
    {
		// set up
    	$root_folder_id = array();
    	$sub_folder_id = array();
    	$folders[0][1] = 'Root Folder';

    	// Create a sub folder in the root folder
    	$parentId = 1;
    	$folderName = 'Test api sub-folder ONE';
    	$response = $this->createFolder($parentId, $folderName);
    	$root_folder_id[] = $response->id;
        $folders[$parentId][$response->id] = $folderName;
    	$this->assertEqual($response->status_code, 0);
    	$this->assertFalse(!empty($response->message));

    	// Create a second sub folder in the root folder
    	$parentId = 1;
    	$folderName = 'Test api sub-folder TWO';
    	$response = $this->createFolder($parentId, $folderName);
    	$root_folder_id[] = $response->id;
        $folders[$parentId][$response->id] = $folderName;
    	$this->assertEqual($response->status_code, 0);
    	$this->assertFalse(!empty($response->message));

    	// Create a sub folder in the first sub folder
    	$parentId = $root_folder_id[0];
    	$folderName = 'Test api sub-folder THREE';
    	$response = $this->createFolder($parentId, $folderName);
    	$sub_folder_id[0][] = $response->id;
        $folders[$parentId][$response->id] = $folderName;
    	$this->assertEqual($response->status_code, 0);
    	$this->assertFalse(!empty($response->message));

        // Create a sub folder within the first sub folder which shares a name with one of the root sub folders
    	$parentId = $sub_folder_id[0][0];
    	$folderName = 'Test api sub-folder TWO';
    	$response = $this->createFolder($parentId, $folderName);
    	$sub_folder_id[0][] = $response->id;
        $folders[$parentId][$response->id] = $folderName;
    	$this->assertEqual($response->status_code, 0);
    	$this->assertFalse(!empty($response->message));

        // Create a second sub folder in the first sub folder
    	$parentId = $root_folder_id[0];
    	$folderName = 'Test api sub-folder FOUR';
    	$response = $this->createFolder($parentId, $folderName);
    	$sub_folder_id[0][] = $response->id;
        $folders[$parentId][$response->id] = $folderName;
    	$this->assertEqual($response->status_code, 0);
    	$this->assertFalse(!empty($response->message));

    	// Create a sub folder within the second sub folder
    	$parentId = $root_folder_id[1];
    	$folderName = 'Test api sub-folder FIVE';
    	$response = $this->createFolder($parentId, $folderName);
    	$sub_folder_id[1][] = $response->id;
        $folders[$parentId][$response->id] = $folderName;
    	$this->assertEqual($response->status_code, 0);
    	$this->assertFalse(!empty($response->message));

    	// Create a sub folder within the second sub folder which shares a name with a sub folder in the first sub folder
    	$parentId = $sub_folder_id[1][0];
    	$folderName = 'Test api sub-folder THREE';
    	$response = $this->createFolder($parentId, $folderName);
    	$sub_folder_id[1][] = $response->id;
        $folders[$parentId][$response->id] = $folderName;
    	$this->assertEqual($response->status_code, 0);
    	$this->assertFalse(!empty($response->message));

		// NOTE default parent is 1, so does not need to be declared when searching the root folder, but we use it elsewhere

        // Webservice needs to be version 3
        if(LATEST_WEBSERVICE_VERSION < 3){

            // Clean up - delete all of the folders
            foreach ($root_folder_id as $folder_id) {
            	$this->deleteFolder($folder_id);
            }
            return ;
        }

        // Fetching of root folder
		$parentId = 0;
        $folderName = 'Root Folder';
        $response = $this->getFolderDetail($folderName);
    	$this->assertEqual($response->status_code, 0);
    	$this->assertFalse(!empty($response->message));
    	if (($response->status_code == 0)) {
    		$this->assertEqual($folders[$parentId][$response->id], $folderName);
    	}

        // Folder Detail by Name in root folder (no duplicate names)
        $parentId = 1;
        $folderName = 'Test api sub-folder ONE';
        // no parent required
    	$response = $this->getFolderDetail($folderName);
    	$this->assertEqual($response->status_code, 0);
    	$this->assertFalse(!empty($response->message));
    	if (($response->status_code == 0)) {
    		$this->assertEqual($folders[$parentId][$response->id], $folderName);
    	}

        // Folder Detail by Name in sub folder of root folder (no duplicate names)
        $parentId = $root_folder_id[0];
        $folderName = 'Test api sub-folder FOUR';
        $response = $this->getFolderDetail($folderName, $parentId);
    	$this->assertEqual($response->status_code, 0);
    	$this->assertFalse(!empty($response->message));
    	if (($response->status_code == 0)) {
    		$this->assertEqual($folders[$parentId][$response->id], $folderName);
    	}

        // Folder Detail by Name in root folder (duplicate names)
        $parentId = 1;
        $folderName = 'Test api sub-folder TWO';
        $response = $this->getFolderDetail($folderName, $parentId);
    	$this->assertEqual($response->status_code, 0);
    	$this->assertFalse(!empty($response->message));
    	if (($response->status_code == 0)) {
    		$this->assertEqual($folders[$parentId][$response->id], $folderName);
    	}

        // Folder Detail by Name in sub folder of root folder (duplicate names)
        $parentId = $root_folder_id[0];
        $folderName = 'Test api sub-folder THREE';
        $response = $this->getFolderDetail($folderName, $parentId);
    	$this->assertEqual($response->status_code, 0);
    	$this->assertFalse(!empty($response->message));
    	if (($response->status_code == 0)) {
    		$this->assertEqual($folders[$parentId][$response->id], $folderName);
    	}

    	// Negative test with non duplicated folder - look for folder in location it does not exist
        $parentId = $root_folder_id[0];
        $folderName = 'Test api sub-folder ONE';
        $response = $this->getFolderDetail($folderName, $parentId);
        $this->assertNotEqual($response->status_code, 0);
    	$this->assertTrue(!empty($response->message));
    	$this->assertNotEqual($folders[$parentId][$response->id], $folderName);

    	// Negative test with duplicated folder - look for folder with incorrect parent id, result should not match expected folder
    	$parentId = 1;
    	$actualParentId = $sub_folder_id[0][0];
        $folderName = 'Test api sub-folder TWO';
        $response = $this->getFolderDetail($folderName, $parentId);
        // we should get a result
        $this->assertEqual($response->status_code, 0);
    	$this->assertFalse(!empty($response->message));
    	// but not the one we wanted
    	$expectedResponse = $this->getFolderDetail($folderName, $actualParentId);
    	$this->assertNotEqual($response->id, $expectedResponse->id);

        // Clean up - delete all of the folders
        foreach ($root_folder_id as $folder_id) {
        	$this->deleteFolder($folder_id);
        }
    }

    /**
     * Tests the creation of folders and folder shortcuts, getting the folder shortcut(s)
     * WebService V3 and higher
     */
    public function testCreateFolderShortcut()
    {
        // if V3
        if(LATEST_WEBSERVICE_VERSION < 3){
            return ;
        }

        // Create folder 1
        $result = $this->createFolder(1, 'Test Shortcut Container');
        $this->assertEqual($result->status_code, 0, 'Create folder - '.$result->message);
        $parentFolderId = $result->id;

        // Create folder 2
        $result = $this->createFolder(1, 'Test Shortcut Target');
        $this->assertEqual($result->status_code, 0, 'Create folder - '.$result->message);
        $targetFolderId = $result->id;

        // Create folder 3
        $result = $this->createFolder($targetFolderId, 'Test Shortcut Target 2');
        $this->assertEqual($result->status_code, 0, 'Create folder - '.$result->message);
        $target2FolderId = $result->id;

        // Create shortcut in folder 1 targeting folder 2
        $result = $this->createFolderShortcut($parentFolderId, $targetFolderId);
        $this->assertEqual($result->status_code, 0, 'Create folder Shortcut - '.$result->message);

        $shortcutId = $result->id;
        $linkedFolderId = $result->linked_folder_id;
        $this->assertTrue(is_numeric($shortcutId), 'Shortcut id should be numeric');
        $this->assertTrue(is_numeric($linkedFolderId), 'Linked folder id should be numeric');
        $this->assertEqual($linkedFolderId, $targetFolderId, 'Shortcut should contain link the target folder');

        // Create shortcut in folder 1 targeting folder 3
        $result = $this->createFolderShortcut($parentFolderId, $target2FolderId);
        $this->assertEqual($result->status_code, 0, 'Create folder shortcut - '.$result->message);

        $shortcut2Id = $result->id;
        $linkedFolder2Id = $result->linked_folder_id;
        $this->assertTrue(is_numeric($shortcut2Id), 'Shortcut id should be numeric');
        $this->assertTrue(is_numeric($linkedFolder2Id), 'Linked folder id should be numeric');
        $this->assertEqual($linkedFolder2Id, $target2FolderId, 'Shortcut should contain link the target folder');

        // Get the folder shortcut details
        $result = $this->getFolderShortcuts($targetFolderId);
        $this->assertEqual($result->status_code, 0, 'Should return list of folder shortcuts - '.$result->message);

        $shortcuts = $result[1];
        foreach ($shortcuts as $item) {
            $this->assertEqual($item->id, $shortcutId);
            $this->assertEqual($item->linked_folder_id, $targetFolderId);
        }

        $result = $this->getFolderShortcuts($target2FolderId);
        $this->assertEqual($result->status_code, 0, 'Should return list of folder shortcuts - '.$result->message);

        $shortcuts2 = $result[1];
        foreach ($shortcuts2 as $item) {
            $this->assertEqual($item->id, $shortcut2Id);
            $this->assertEqual($item->linked_folder_id, $target2FolderId);
        }

        // delete and cleanup
        $this->deleteFolder($parentFolderId);
        $this->deleteFolder($targetFolderId);
    }

    /**
     * Tests the document upload and the creation and retrieval of document shortcuts
     * WS V3
     */
    public function testCreateDocumentShortcut()
    {
        // if V3
        if(LATEST_WEBSERVICE_VERSION < 3){
            //$this->assertEqual(LATEST_WEBSERVICE_VERSION, 3, 'Webservice version is less than 3. Exiting test, functionality is V3 only');
            return ;
        }

        // Create folder 1 containing the shortcut
        $result = $this->createFolder(1, 'Test Shortcut Container');
        $this->assertEqual($result->status_code, 0, 'Create folder - '.$result->message);
        $parentFolderId = $result->id;

        // Create folder 2 containing the document
        $result = $this->createFolder(1, 'Test Shortcut Target');
        $this->assertEqual($result->status_code, 0, 'Create folder - '.$result->message);
        $targetFolderId = $result->id;

        // Upload document to folder 2
        $result = $this->createDocument($targetFolderId, 'Target Doc', 'target.txt');
        $this->assertEqual($result->status_code, 0, 'Create document - '.$result->message);
        $documentId = $result->document_id;
        $this->assertTrue(is_numeric($documentId), 'Document id should be numeric');

        // Create shortcut in folder 1, pointing to the document in folder 2
        $result = $this->createDocumentShortcut($parentFolderId, $documentId);
        $this->assertEqual($result->status_code, 0, 'Create document shortcut - '.$result->message);
        $shortcutId = $result->document_id;
        $linkedDocumentId = $result->linked_document_id;
        $this->assertTrue(is_numeric($shortcutId), 'Shortcut id should be numeric');
        $this->assertTrue(is_numeric($linkedDocumentId), 'Linked document id should be numeric');
        $this->assertEqual($linkedDocumentId, $documentId, 'Shortcut should contain link to the target document');

        // delete and cleanup
        $this->deleteFolder($parentFolderId);
        $this->deleteFolder($targetFolderId);
    }

    /**
     * Tests document uploads, checkin / checkout, downloads and document version downloads (V3)
     * Normal documents and small / base64 documents
     */
    public function testUploadDownloadDocument()
    {
        // Create folder containing the documents
        $result = $this->createFolder(1, 'Test Container');
        $this->assertEqual($result->status_code, 0, 'Create folder - '.$result->message);
        $folderId = $result->id;

        // Upload document to folder
        $filename = 'test_doc.txt';
        $result = $this->createDocument($folderId, 'Test Doc', $filename);
        $this->assertEqual($result->status_code, 0, 'Create document - '.$result->message);
        $documentId = $result->document_id;
        $this->assertTrue(is_numeric($documentId), 'Document id should be numeric');

        if(LATEST_WEBSERVICE_VERSION < 3){
            // delete and cleanup
            $this->deleteFolder($folderId);
            return ;
        }

        // Get the download url for the document
        $result = $this->downloadDocument($documentId);
        $this->assertEqual($result->status_code, 0, 'Get document download url - '.$result->message);
        $url = $result->message;
        $this->assertTrue(is_string($url), 'URL should be a string');
        $this->assertTrue(strpos($url, 'http') !== false, 'URL should contain http');

        // Download the document
        $result = $this->doDownload($url);
        $this->assertEqual($result['status'], 200, 'Status should be code 200 for success - '.$result['status']);
        $this->assertEqual($result['error'], 0, 'Error code should be 0 for success - '.$result['error']);
        $this->assertEqual($result['download'], $this->content, 'Document content should be the same as that uploaded');

        // ----------

        // Upload base64 document to folder
        $filename2 = 'test_base64_doc.txt';
        $result = $this->createSmallDocument($folderId, 'Test Base64 Doc', $filename2);
        $this->assertEqual($result->status_code, 0, 'Create base64 document - '.$result->message);
        $documentId2 = $result->document_id;
        $this->assertTrue(is_numeric($documentId2), 'Document id should be numeric');

        // Download base64 document
        $result = $this->downloadSmallDocument($documentId2);
        $this->assertEqual($result->status_code, 0, 'Download small document - '.$result->message);
        $content = $result->message;
        $this->assertTrue(is_string($content), 'Content should be a string');
        $content = base64_decode($content);
        $this->assertEqual($content, $this->content, 'Document content should be the same as that uploaded');

        /* *** V3 functions *** */

        // Checkout first document
        $result = $this->checkoutDocument($documentId);
        $this->assertEqual($result->status_code, 0, 'Checkout document - '.$result->message);
        $checkoutBy = $result->checked_out_by;
        $checkoutDate = $result->checked_out_date;
        $url = $result->message;
        $this->assertFalse(is_null($checkoutDate), 'Checked out date should not be null / empty - '.$checkoutDate);
        $this->assertEqual($checkoutBy, 'Administrator', 'Using the Administrative user, checked out user must match - '.$checkoutBy);
        $this->assertTrue(is_string($url), 'Download URL should be a string');
        $this->assertTrue(strpos($url, 'http') !== false, 'Download URL should contain http');

        // Download the document
        $result = $this->doDownload($url);
        $content = $result['download'];
        $this->assertEqual($result['status'], 200, 'Status should be code 200 for success - '.$result['status']);
        $this->assertEqual($result['error'], 0, 'Error code should be 0 for success - '.$result['error']);
        $this->assertEqual($result['download'], $this->content, 'Document content should be the same as that uploaded');

        // ----------

        // Checkin a new version
        $result = $this->checkinDocument($documentId, $filename);
        $this->assertEqual($result->status_code, 0, 'Checkin document - '.$result->message);
        $checkoutBy = $result->checked_out_by;
        $this->assertTrue(empty($checkoutBy) || $checkoutBy == 'n/a', 'Document should no longer be checked out by anyone - '.$checkoutBy);

        // Download new version
        $result = $this->downloadDocument($documentId);
        $this->assertEqual($result->status_code, 0, 'Get checkedin document download url - '.$result->message);
        $url = $result->message;

        // Download the document
        $result = $this->doDownload($url);
        $this->assertEqual($result['download'], $this->content_update, 'Document content should be the same as the updated content');

        // ----------

        // Get previous versions of the document
        $result = $this->getDocumentVersionHistory($documentId);
        $this->assertEqual($result->status_code, 0, 'Get document version history - '.$result->message);
        $history = $result->history;
        $this->assertTrue(is_array($history), 'Version history should be an array');
        $this->assertEqual(count($history), 2, 'Version history should contain 2 items / versions');

        // Get the previous version number
        $version = isset($history[1]) ? $history[1]->content_version : null;

        // Download previous version
        $result = $this->downloadDocument($documentId, $version);
        $this->assertEqual($result->status_code, 0, "Get document download url for  previous version ($version) - ".$result->message);
        $url = $result->message;

        // Download the document
        $result = $this->doDownload($url);
        $this->assertEqual($result['status'], 200, 'Status should be code 200 for success - '.$result['status']);
        $this->assertEqual($result['error'], 0, 'Error code should be 0 for success - '.$result['error']);
        $this->assertEqual($result['download'], $this->content, 'Document content should be the same as the original content of the initial upload');

        // ----------

        // Checkout base64 document
        $result = $this->checkoutSmallDocument($documentId2);
        $this->assertEqual($result->status_code, 0, 'Checkout base64 document - '.$result->message);
        $checkoutBy = $result->checked_out_by;
        $checkoutDate = $result->checked_out_date;
        $content = $result->message;
        $this->assertFalse(is_null($checkoutDate), 'Checked out date should not be null / empty - '.$checkoutDate);
        $this->assertEqual($checkoutBy, 'Administrator', 'Using the Administrative user, checked out user must match - '.$checkoutBy);
        $this->assertTrue(is_string($content), 'Base64 content should be a string');
        $content = base64_decode($content);
        $this->assertEqual($content, $this->content, 'Document content should be the same as that uploaded');

        // ----------

        // Checkin a new base64 version
        $result = $this->checkinSmallDocument($documentId2, $filename2);
        $this->assertEqual($result->status_code, 0, 'Checkin base64 document - '.$result->message);
        $checkoutBy = $result->checked_out_by;
        $this->assertTrue(empty($checkoutBy) || $checkoutBy == 'n/a', 'Document should no longer be checked out by anyone - '.$checkoutBy);

        // Download new version
        $result = $this->downloadSmallDocument($documentId2);
        $this->assertEqual($result->status_code, 0, 'Download checkedin base64 document - '.$result->message);
        $content = $result->message;
        $this->assertTrue(is_string($content), 'Content should be a string');
        $content = base64_decode($content);
        $this->assertEqual($content, $this->content_update, 'Document content should be the same as the updated content');

        // ----------

        // Get previous versions of the base64 document
        $result = $this->getDocumentVersionHistory($documentId2);
        $this->assertEqual($result->status_code, 0, 'Get document version history - '.$result->message);
        $history = $result->history;
        $this->assertTrue(is_array($history), 'Version history should be an array');
        $this->assertEqual(count($history), 2, 'Version history should contain 2 items / versions');

        // Get the previous version number
        $version = isset($history[1]) ? $history[1]->content_version : null;

        // Download previous version
        $result = $this->downloadSmallDocument($documentId2, $version);
        $this->assertEqual($result->status_code, 0, "Download previous version ($version) - ".$result->message);
        $content = $result->message;
        $this->assertTrue(is_string($content), 'Content should be a string');
        $content = base64_decode($content);
        $this->assertEqual($content, $this->content, 'Document content should be the same as the original content of the initial upload');

        // delete and cleanup
        $this->deleteFolder($folderId);
    }

    /**
     * Tests getting and updating document metadata
     */
    public function testDocumentMetadata()
    {
        // functions require WS version 3 to run correctly
        // Fix: refactor functions to check version and pass correct number of parameters
        if(LATEST_WEBSERVICE_VERSION < 3){
            return ;
        }

        // Create folder containing the documents
        $result = $this->createFolder(1, 'Test Metadata Container');
        $this->assertEqual($result->status_code, 0, 'Create folder - '.$result->message);
        $folderId = $result->id;

        // Upload document to folder
        $filename = 'test_doc.txt';
        $result = $this->createDocument($folderId, 'Test Doc', $filename);
        $this->assertEqual($result->status_code, 0, 'Create document - '.$result->message);
        $documentId = $result->document_id;
        $this->assertTrue(is_numeric($documentId), 'Document id should be numeric');

        // get document metadata
        $result = $this->getDocumentMetadata($documentId);
        $this->assertEqual($result->status_code, 0, 'Get document metadata - '.$result->message);
        $metadata = $result->metadata;
        $this->assertTrue(is_array($metadata), 'Returned document metadata should be an array of fieldsets');

        // Add a new tag and a document author
        foreach ($metadata as $fieldset){
            $fields = $fieldset->fields;
            switch($fieldset->fieldset){
                case 'Tag Cloud':
                    $field_name = 'Tag';
                    $curr_val = 'n/a';
                    $new_val = 'unit test';
                    break;
                case 'General information':
                    $field_name = 'Document Author';
                    $curr_val = 'n/a';
                    $new_val = 'Test Framework';
                    break;
            }

            foreach ($fields as $field){
                if($field->name == $field_name){
                    $this->assertEqual($field->value, $curr_val, "The current value of the given field, $field_name, should be \"$curr_val\"");
                    // update the value
                    $field->value = $new_val;
                }
                if($field->value == 'n/a'){
                    $field->value = '';
                }
            }
        }

        // update metadata
        $result = $this->updateDocumentMetadata($documentId, $metadata);

        // get metadata - ensure it matches the updated metadata
        $result = $this->getDocumentMetadata($documentId);
        $this->assertEqual($result->status_code, 0, 'Get document metadata - '.$result->message);
        $metadata = $result->metadata;
        $this->assertTrue(is_array($metadata), 'Returned document metadata should be an array of fieldsets');

        // Add a new tag and a document author
        foreach ($metadata as $fieldset){
            $fields = $fieldset->fields;
            switch($fieldset->fieldset){
                case 'Tag Cloud':
                    $field_name = 'Tag';
                    $curr_val = 'unit test';
                    break;
                case 'General information':
                    $field_name = 'Document Author';
                    $curr_val = 'Test Framework';
                    break;
            }

            foreach ($fields as $field){
                if($field->name == $field_name){
                    $this->assertEqual($field->value, $curr_val, "The current value of the given field, $field_name, should be the same as the updated value: \"$curr_val\"");
                }
            }
        }

        // Get previous versions of the document
        $result = $this->getDocumentVersionHistory($documentId);
        $this->assertEqual($result->status_code, 0, 'Get document version history - '.$result->message);
        $history = $result->history;
        $this->assertTrue(is_array($history), 'Version history should be an array');
        $this->assertEqual(count($history), 2, 'Version history should contain 2 items / versions');

        // Get the previous version number
        $version = isset($history[1]) ? $history[1]->metadata_version : null;

        // get document metadata for previous version
        $result = $this->getDocumentMetadata($documentId, $version);
        $this->assertEqual($result->status_code, 0, 'Get document metadata - '.$result->message);
        $metadata = $result->metadata;
        $this->assertTrue(is_array($metadata), 'Returned document metadata should be an array of fieldsets');

        // Add a new tag and a document author
        foreach ($metadata as $fieldset){
            $fields = $fieldset->fields;
            switch($fieldset->fieldset){
                case 'Tag Cloud':
                    $field_name = 'Tag';
                    $curr_val = 'n/a';
                    break;
                case 'General information':
                    $field_name = 'Document Author';
                    $curr_val = 'n/a';
                    break;
            }

            foreach ($fields as $field){
                if($field->name == $field_name){
                    $this->assertEqual($field->value, $curr_val, "The current value of the given field, $field_name, should be the same as the previous version's value: \"$curr_val\"");
                }
            }
        }

        // delete and cleanup
        $this->deleteFolder($folderId);
    }

    /**
     * Test getting the contents of a folder
     *
     */
    public function testGetFolderListing()
    {
        // create folder
        $main_folder = 'Test Listing Container';
        $result = $this->createFolder(1, $main_folder);
        $this->assertEqual($result->status_code, 0, 'Create folder - '.$result->message);
        $folderId = $result->id;

        // create subfolder 1
        $sub_folder_name = 'Subfolder One';
        $result = $this->createFolder($folderId, $sub_folder_name);
        $this->assertEqual($result->status_code, 0, 'Create subfolder 1 - '.$result->message);
        $subFolderId1 = $result->id;

        // create subfolder 2
        $result = $this->createFolder($folderId, 'Subfolder Two');
        $this->assertEqual($result->status_code, 0, 'Create subfolder 2 - '.$result->message);
        $subFolderId2 = $result->id;

        // create subfolder 3 under subfolder 1
        $result = $this->createFolder($subFolderId1, 'Subfolder Three');
        $this->assertEqual($result->status_code, 0, 'Create subfolder 3 under subfolder 1 - '.$result->message);
        $subFolderId3 = $result->id;

        // upload document into main folder
        $filename = 'test_doc.txt';
        $result = $this->createDocument($folderId, 'Test Doc', $filename);
        $this->assertEqual($result->status_code, 0, 'Create document 1 in folder - '.$result->message);
        $documentId = $result->document_id;
        $this->assertTrue(is_numeric($documentId), 'Document id should be numeric');

        // upload document 2 into main folder
        $filename2 = 'test_doc2.txt';
        $result = $this->createDocument($folderId, 'Test Doc 2', $filename2);
        $this->assertEqual($result->status_code, 0, 'Create document 1 in folder - '.$result->message);
        $documentId2 = $result->document_id;
        $this->assertTrue(is_numeric($documentId2), 'Document id should be numeric');

        // upload document 3 into subfolder 1
        $filename3 = 'test_doc3.txt';
        $result = $this->createDocument($subFolderId1, 'Test Doc 3', $filename3);
        $this->assertEqual($result->status_code, 0, 'Create document 1 in sub folder 1 - '.$result->message);
        $documentId3 = $result->document_id;
        $this->assertTrue(is_numeric($documentId3), 'Document id should be numeric');

        // Get folder listing for folder 1 - folders only, depth of 1
        $result = $this->getFolderContents($folderId, 1, 'F');
        $this->assertEqual($result->status_code, 0, 'Get subfolders in folder - '.$result->message);
        $folder_name = $result->folder_name;
        $sub_folders = $result->items;
        $this->assertEqual($folder_name, $main_folder, 'Folder name is - '.$folder_name);
        $this->assertTrue(is_array($sub_folders), 'There should be an array of subfolders');
        $this->assertEqual(count($sub_folders), 2, 'There should be 2 subfolders');

        // Get folder listing for folder 1 - folders and documents, infinite depth
        $result = $this->getFolderContents($folderId, 5, 'FD');
        $this->assertEqual($result->status_code, 0, 'Get all subfolders and documents under the folder - '.$result->message);
        $items = $result->items;
        $this->assertTrue(is_array($items), 'There should be an array of subfolders and documents');
        $this->assertEqual(count($items), 4, 'There should be 2 subfolders and 2 documents in the immediate folder');

        // Loop through items, find sub folder 1
        $docs = 0;
        $folders = 0;
        foreach ($items as $item){
            // increment count of item type
            $folders = ($item->item_type == 'F') ? $folders + 1 : $folders;
            $docs = ($item->item_type == 'D') ? $docs + 1 : $docs;

            if($item->id == $subFolderId1){
                $sub_items = $item->items;

                $this->assertTrue(is_array($sub_items), 'Subfolder 1 should contain an array of contents');
                $this->assertEqual(count($sub_items), 2, 'Subfolder 1 should contain a folder and document');

            }
        }

        $this->assertEqual($folders, 2, 'There should be 2 folders');
        $this->assertEqual($docs, 2, 'There should be 2 documents');

        // Get folder listing for subfolder 1 - documents only, depth of 1
        $result = $this->getFolderContents($subFolderId1, 1, 'D');
        $this->assertEqual($result->status_code, 0, 'Get documents under subfolder 1 - '.$result->message);
        $folder_name = $result->folder_name;
        $items = $result->items;
        $this->assertEqual($folder_name, $sub_folder_name, 'Subfolder name is - '.$folder_name);
        $this->assertTrue(is_array($items), 'There should be an array of documents');
        $this->assertEqual(count($items), 1, 'There should be 1 document');

        // delete and cleanup
        $this->deleteFolder($folderId);
    }
}
?>