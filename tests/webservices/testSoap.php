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
    
    public $client;
    private $user;
    private $pass;

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
    }

    /**
    * This method is a placeholder
    */
    public function tearDown()
    {
    }

    function connect()
    {
        $wsdl = $this->rootUrl . "wsdl";
        $this->client = new SoapClient($wsdl);
    }

    function login($ip = null)
    {
        $res = $this->client->__soapCall("login", array($this->user, $this->pass, $ip));
        if($res->status_code != 0){
            return false;
        }
        $this->session = $res->message;
    }


    function getDocTypes()
    {
        $result = $this->client->__soapCall("get_document_types", array($this->session));
        return $result->document_types;
    }

    function search($expr)
    {
        $result = $this->client->__soapCall("search", array($this->session, $expr, ''));
        return $result->hits;
    }

    function getFolderDetail($folder, $parentId = 1)
    {
        $result = $this->client->__soapCall("get_folder_detail_by_name", array($this->session, $folder, $parentId));
        return $result;
    }

    function createFolder($parent_id, $folder)
    {
        $result = $this->client->__soapCall("create_folder", array($this->session, $parent_id, $folder));
        return $result;
    }

    function deleteFolder($folder_id)
    {
        $result = $this->client->__soapCall("delete_folder", array($this->session, "$folder_id", 'Testing'));
        return $result;
    }

    function logout()
    {
        $result = $this->client->__soapCall("logout", array($this->session));
        if($result->status_code != 0){
            return true;
        }
    }
    
    // now the test functions
    
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
    	// Login and authenticate
    	$this->connect();
		$this->login('127.0.0.1');
		
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
                
        // Logout
        $this->logout();
    }

}
?>