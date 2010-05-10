<?php
require_once (KT_DIR . '/tests/test.php');
require_once (KT_DIR . '/ktapi/ktapi.inc.php');

// username and password for authentication
// must be set correctly for all of the tests to pass in all circumstances
define (KT_TEST_USER, 'admin');
define (KT_TEST_PASS, 'admin');

/**
 * These are the unit tests for the main KTAPI class
 *
 * NOTE All functions which require electronic signature checking need to send
 * the username and password and reason arguments, else the tests WILL fail IF
 * API Electronic Signatures are enabled.
 * Tests will PASS when API Signatures NOT enabled whether or not
 * username/password are sent.
 */
class APITestCase extends KTUnitTestCase {

    /**
    * @var object $ktapi The main ktapi object
    */
    var $ktapi;

    /**
    * @var object $session The KT session object
    */
    var $session;

    /**
     * @var object $root The KT folder object
     */
    var $root;

    /**
    * This method sets up the KT session
    *
    */
    public function setUp() {
        $this->ktapi = new KTAPI();
        $this->session = $this->ktapi->start_session(KT_TEST_USER, KT_TEST_PASS);
        $this->root = $this->ktapi->get_root_folder();
        $this->assertTrue($this->root instanceof KTAPI_Folder);
        $this->storage = KTStorageManagerUtil::getSingleton();
    }

    /**
    * This method emds the KT session
    *
    */
    public function tearDown() {
        $this->session->logout();
    }

    /**
    * This method tests for the session object
    *
    */
    public function testGetSession()
    {
        $session = $this->ktapi->get_session();

        $this->assertNotNull($session);
        $this->assertIsA($session, 'KTAPI_Session');
        $this->assertNoErrors();
    }

    /**
    * This method tests for the user object
    *
    */
    public function testGetUser()
    {
        $user = $this->ktapi->get_user();

        $this->assertNotNull($user);
        $this->assertIsA($user, 'User');
        $this->assertNoErrors();
    }

    /**
    * This method tests for the permission object
    *
    */
    public function testGetPermission()
    {
        // test case 1
        // the permissions string
        $permission = 'ktcore.permissions.read';

        $permissions = $this->ktapi->get_permission($permission);

        $this->assertNotNull($permissions);
        $this->assertIsA($permissions, 'KTPermission');
        $this->assertNoErrors();

        // test case 2
        // the permissions string
        $permission = 'ktcore.permissions.write';

        $permissions = $this->ktapi->get_permission($permission);

        $this->assertNotNull($permissions);
        $this->assertIsA($permissions, 'KTPermission');
        $this->assertNoErrors();

        // test case 3
        // the permissions string
        $permission = 'ktcore.permissions.security';

        $permissions = $this->ktapi->get_permission($permission);

        $this->assertNotNull($permissions);
        $this->assertIsA($permissions, 'KTPermission');
        $this->assertNoErrors();
    }

    /**
    * This method tests if a user can access an object with certain permssions
    *
    */
    public function testCheckAccess()
    {
        // test case 1 - normal test
        // the permission string
        $permission = 'ktcore.permissions.read';

        // create the document object
        $randomFile = $this->createRandomFile();
        $document = $this->root->add_document('title_1.txt', 'name_1.txt', 'Default', $randomFile, KT_TEST_USER, KT_TEST_PASS, 'Testing API');

        $internalDocObject = $document->getObject();
        $user = $this->ktapi->can_user_access_object_requiring_permission($internalDocObject, $permission);

        $this->assertNotNull($user);
        $this->assertIsA($user, 'User');
        $this->assertNoErrors();

        // test case 2 - test for bad permissions string
        $permission = 'ktcore.permissions.badstring';

        // create the document object
        $randomFile = $this->createRandomFile();
        $document2 = $this->root->add_document('title_2.txt', 'name_2.txt', 'Default', $randomFile, KT_TEST_USER, KT_TEST_PASS, 'Testing API');

        $internalDocObject2 = $document2->getObject();
        $user = $this->ktapi->can_user_access_object_requiring_permission($internalDocObject2, $permission);

        $this->assertNotNull($user);
        $this->assertIsA($user, 'PEAR_Error');
        $this->assertNoErrors();

        // clean up
        $document->delete('Testing');
        $document->expunge();
        $document2->delete('Testing');
        $document2->expunge();
    }

    /**
    * This method tests the retrieval of a document by its oem number
    *
    *
    public function testGetDocByOem()
    {
        // test case 1 - no matching oem numbers
        // create the document object
        $randomFile = $this->createRandomFile();
        $document = $this->root->add_document('title_4.txt', 'name_4.txt', 'Default', $randomFile);

        $list = $this->ktapi->get_documents_by_oem_no('1');

        $this->assertTrue(empty($list));
        $this->assertNoErrors();

        // test case 2 - matching oem numbers
        // create the document object
        $randomFile = $this->createRandomFile();
        $document2 = $this->root->add_document('title_5.txt', 'name_5.txt', 'Default', $randomFile);

        $list = $this->ktapi->get_documents_by_oem_no('2');

        $this->assertFalse(empty($list));
        $this->assertNoErrors();


        // clean up
        $document->delete('Testing');
        $document->expunge();
        $document2->delete('Testing');
        $document2->expunge();
    }
    */

    /**
    * This method tests for the current session
    *
    *
    public function testGetActiveSession()
    {
        // get session id of active session
        $sessionID = $this->session->get_sessionid();

        $session = $this->ktapi->get_active_session($sessionID);
        $this->assertNotNull($session);
        $this->assertIsA($session, 'KTAPI_Session');
        $this->assertNoErrors();
    }

    /**
    * This method tests the creation of a session
    *
    */
    public function testStartSession()
    {
        $this->ktapi->session_logout();

        $this->session = $this->ktapi->start_session(KT_TEST_USER, KT_TEST_PASS);

        $this->assertNotNull($this->session);
        $this->assertIsA($this->session, 'KTAPI_Session');
        $this->assertNoErrors();
    }

    /**
    * This method tests the creation of a root session
    *
    */
    public function testStartSystemSession()
    {
        $this->ktapi->session_logout();

        $session = $this->ktapi->start_system_session();

        $this->assertNotNull($session);
        $this->assertIsA($session, 'KTAPI_Session');
        $this->assertNoErrors();
    }

    /**
    * This method tests the creation of an anonymous session
    *
    */
    public function testStartAnonymousSession()
    {
        $this->ktapi->session_logout();

        $session = $this->ktapi->start_anonymous_session();

        $config = &KTConfig::getSingleton();
		$allow_anonymous = $config->get('session/allowAnonymousLogin', false);

		$this->assertNotNull($session);

		if($allow_anonymous){
            $this->assertIsA($session, 'KTAPI_Session');
            $this->assertNoErrors();
		}else{
		    $this->assertError($session);
		}
    }

    /**
    * This method tests the retrieval of the root folder
    *
    */
    public function testGetRootFolder()
    {
        $folder = $this->ktapi->get_root_folder();

        $this->assertNotNull($folder);
        $this->assertIsA($folder, 'KTAPI_Folder');
        $this->assertNoErrors();
    }

    /**
    * This method tests the retrieval of a folder by id
    *
    */
    public function testGetFolderById()
    {
        $folder = $this->ktapi->get_folder_by_id(1);

        $this->assertNotNull($folder);
        $this->assertIsA($folder, 'KTAPI_Folder');
        $this->assertNoErrors();
    }

    /**
    * This method tests the retrieval of a folder by name
    *
    */
    /* replaced by the new function below
    public function testGetFolderByName()
    {
        $folder = $this->ktapi->get_folder_by_name('Root Folder');

        $this->assertNotNull($folder);
        $this->assertIsA($folder, 'KTAPI_Folder');
        $this->assertNoErrors();
    }
    */

    /**
    * This method tests the retrieval of a document by it's id
    *
    */
    public function testGetDocumentById()
    {
        // create the document object
        $randomFile = $this->createRandomFile();        
        $document = $this->root->add_document('title_5.txt', 'name_5.txt', 'Default', $randomFile, KT_TEST_USER, KT_TEST_PASS, 'reason');

        $documentID = $document->get_documentid();
        $docObject = $this->ktapi->get_document_by_id($documentID);

        $this->assertNotNull($docObject);
        $this->assertIsA($docObject, 'KTAPI_Document');
        $this->assertNoErrors();

        $document->delete('Testing');
        $document->expunge();
    }

    /**
    * This method tests the retrieval of a document type id based on the type name
    *
    */
    public function testGetDocumentTypeid()
    {
        $typeID = $this->ktapi->get_documenttypeid('Default');

        $this->assertNotNull($typeID);
        $this->assertNoErrors();
   }

    /**
    * This method tests the retrieval of a link type id based on the link type name
    *
    */
    public function testGetLinkTypeid()
    {
        $typeID = $this->ktapi->get_link_type_id('Default');

        $this->assertNotNull($typeID);
        $this->assertNoErrors();
    }

    /**
    * This method tests the retrieval of document types
    *
    */
    public function testGetDocTypes()
    {
        $types = $this->ktapi->get_documenttypes();

        $this->assertNotNull($types);
        $this->assertNoErrors();
    }

    /**
    * This method tests the retrieval of Link types
    *
    */
    public function testGetLinkTypes()
    {
        $types = $this->ktapi->get_document_link_types();

        $this->assertNotNull($types);
        $this->assertNoErrors();
    }

    /**
    * This method tests the retrieval of metadata fieldsets
    *
    */
    public function testGetTypeMetadata()
    {
        $fieldsets = $this->ktapi->get_document_type_metadata();

        $this->assertNotNull($fieldsets);
        $this->assertNoErrors();
    }

    /**
    * This method tests the retrieval of users
    *
    */
    public function testGetUsers()
    {
        $users = $this->ktapi->get_users();

        $this->assertNotNull($users);
        $this->assertNoErrors();
    }

    /**
    * This method tests the retrieval of metadata based on the document field id
    *
    */
    public function testGetMetadataLookup()
    {
        $name = $this->ktapi->get_metadata_lookup(4);

        $this->assertNotNull($name);
        $this->assertNoErrors();
    }

    /**
    * This method tests the loading of a metadata tree on the document field id
    *
    */
    public function testGetMetadataTree()
    {
        $tree = $this->ktapi->get_metadata_tree(4);

        $this->assertNotNull($tree);
        $this->assertNoErrors();
    }


    /**
    * This method tests the retrieval of active workflows
    *
    */
    public function testGetWorkflows()
    {
        $workflows = $this->ktapi->get_workflows();

        $this->assertNotNull($workflows);
        $this->assertNoErrors();
    }

    /**
     * Testing the getSubscriptions function
     */
    public function testGetSubscriptions()
    {
        // Create a document and subscribe to it
        $randomFile = $this->createRandomFile();
        $document = $this->root->add_document('test title 1', 'testfile1.txt', 'Default', $randomFile, KT_TEST_USER, KT_TEST_PASS, 'Testing API');

        $this->assertEntity($document, 'KTAPI_Document');
        if(PEAR::isError($document)) return;

        $document->subscribe();

        $subscriptions = $this->ktapi->getSubscriptions();
        $this->assertIsA($subscriptions, 'array', 'Subscriptions should return an array');
        $this->assertEntity($subscriptions[0], 'Subscription');

        $document->unsubscribe();
        $document->delete('Testing');
        $document->expunge();
    }

    /* *** Test webservice functions *** */

    /**
     * Testing folder creation and deletion, add document, get folder contents, folder detail
     * Folder shortcuts and actions
     */
    public function testFolderApiFunctions()
    {
        global $default;
        
        // check for a negative result
        $result = $this->ktapi->create_folder(0, 'New test error api folder', KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $this->assertNotEqual($result['status_code'], 0);

        // Create a folder
        $result1 = $this->ktapi->create_folder(1, 'New test api folder', KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $folder_id = $result1['results']['id'];
        $this->assertEqual($result1['status_code'], 0);
        $this->assertTrue($result1['results']['parent_id'] == 1);

        // Create a sub folder
        $result2 = $this->ktapi->create_folder($folder_id, 'New test api sub-folder', KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $folder_id2 = $result2['results']['id'];
        $this->assertEqual($result2['status_code'], 0);

        // Add a document
        $dir = $default->uploadDirectory;
        $tempfilename = $this->createRandomFile('some text', $dir);
        $doc = $this->ktapi->add_document($folder_id,  'New API test doc', 'testdoc1.txt', 'Default',
                                          $tempfilename, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        
        $this->assertEqual($doc['status_code'], 0);
        $this->assertEqual($doc['results']['title'], 'New API test doc');

        // Get folder 1 contents
        $contents = $this->ktapi->get_folder_contents($folder_id, $depth=1, $what='DFS');
        $this->assertEqual($contents['status_code'], 0);
        $this->assertEqual(count($contents['results']['items']), 2);

        $detail = $this->ktapi->get_folder_detail($folder_id2);
        $this->assertEqual($detail['status_code'], 0);
        $this->assertTrue($detail['results']['parent_id'] == $folder_id);

        // Create a shortcut to the subfolder from the root folder
        $shortcut = $this->ktapi->create_folder_shortcut(1, $folder_id2, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $this->assertEqual($shortcut['status_code'], 0);
        $this->assertEqual($shortcut['results']['folder_name'], 'New test api sub-folder');
        $this->assertEqual($shortcut['results']['parent_id'], 1);

        $shortcut_list = $this->ktapi->get_folder_shortcuts($folder_id2);
        $this->assertEqual($shortcut['status_code'], 0);
        $this->assertEqual(count($shortcut_list['results']), 1);

        // Rename the folder
        $renamed = $this->ktapi->rename_folder($folder_id, 'Renamed test folder', KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $this->assertEqual($renamed['status_code'], 0);

        $renamed_detail = $this->ktapi->get_folder_detail_by_name('Renamed test folder');
        $this->assertEqual($renamed_detail['status_code'], 0);
        $this->assertEqual($renamed_detail['results']['id'], $folder_id);

//        $this->ktapi->copy_folder($source_id, $target_id, $reason);
//        $this->ktapi->move_folder($source_id, $target_id, $reason);

        // Clean up - delete the folder
        $this->ktapi->delete_folder($folder_id, 'Testing API', KT_TEST_USER, KT_TEST_PASS);
        
        $detail2 = $this->ktapi->get_folder_detail($folder_id);
        $this->assertNotEqual($detail2['status_code'], 0);
    }

    /**
     * Testing document get, update, actions, delete, shortcuts and detail
     */
    public function testDocumentApiFunctions()
    {
        global $default;
        
        // Create a folder
        $result1 = $this->ktapi->create_folder(1, 'New test api folder', KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $folder_id = $result1['results']['id'];
        $this->assertEqual($result1['status_code'], 0);

        // Create a sub folder
        $result2 = $this->ktapi->create_folder($folder_id, 'New test api sub-folder', KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $folder_id2 = $result2['results']['id'];
        $this->assertEqual($result2['status_code'], 0);

        // Add a document
        $dir = $default->uploadDirectory;
        $tempfilename = $this->createRandomFile('some text', $dir);
        $doc = $this->ktapi->add_document($folder_id,  'New API test doc', 'testdoc1.txt', 'Default', $tempfilename,
                                          KT_TEST_USER, KT_TEST_PASS, 'Testing API');        
        $doc_id = $doc['results']['document_id'];
        $this->assertEqual($doc['status_code'], 0);

        // Get document detail
        $detail = $this->ktapi->get_document_detail($doc_id);//, 'MLTVH');
        $this->assertEqual($detail['status_code'], 0);
        $this->assertEqual($detail['results']['document_type'], 'Default');
        $this->assertEqual($detail['results']['folder_id'], $folder_id);

        // Get document detail - filename
        $detail2 = $this->ktapi->get_document_detail_by_filename($folder_id, 'testdoc1.txt');
        $this->assertEqual($detail2['status_code'], 0);
        $this->assertEqual($detail2['results']['title'], 'New API test doc');

        // Get document detail - title
        $detail3 = $this->ktapi->get_document_detail_by_title($folder_id, 'New API test doc');
        $this->assertEqual($detail3['status_code'], 0);
        $this->assertEqual($detail3['results']['filename'], 'testdoc1.txt');

        // Get document detail - name
        $detail4 = $this->ktapi->get_document_detail_by_name($folder_id, 'New API test doc');
        $this->assertEqual($detail4['status_code'], 0);
        $this->assertEqual($detail4['results']['title'], 'New API test doc');

        // Checkout the document
        $result1 = $this->ktapi->checkout_document($doc_id, 'Testing API', true, KT_TEST_USER, KT_TEST_PASS);
        $this->assertEqual($result1['status_code'], 0);
        $this->assertTrue(!empty($result1['results']));

        // Checkin the document
        $dir = $default->uploadDirectory;
        $tempfilename = $this->createRandomFile('some text', $dir);
        $result2 = $this->ktapi->checkin_document($doc_id,  'testdoc1.txt', 'Testing API', $tempfilename, false, KT_TEST_USER, KT_TEST_PASS);
        $this->assertEqual($result2['status_code'], 0);
        $this->assertEqual($result2['results']['document_id'], $doc_id);

        // Create document shortcut
        $shortcut = $this->ktapi->create_document_shortcut(1, $doc_id, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $this->assertEqual($shortcut['status_code'], 0);
        $this->assertEqual($shortcut['results']['title'], 'New API test doc');
        $this->assertEqual($shortcut['results']['folder_id'], $folder_id);

        // Delete the document
        $result3 = $this->ktapi->delete_document($doc_id, 'Testing API', true, KT_TEST_USER, KT_TEST_PASS);
        $this->assertEqual($result3['status_code'], 0);
        // expunge the document
        $docObject = $this->ktapi->get_document_by_id($doc_id);
        $docObject->expunge();

        // Clean up - delete the folder
        $this->ktapi->delete_folder($folder_id, 'Testing API', KT_TEST_USER, KT_TEST_PASS);
        
        $detail2 = $this->ktapi->get_folder_detail($folder_id);
        $this->assertNotEqual($detail2['status_code'], 0);
    }
    
    /**
     * Tests finding of a folder or folder detail by name
     * 
     * Runs the following sub-tests:
     * 
     * . Root folder Folder by Name (root folder test)
     * . Folder Detail by Name in root folder (no duplicate names)
     * . Folder Detail by name in subfolder of root folder (no duplicate names)
     * . Folder by Name in subfolder of root folder (no duplicate names)
     * . Folder by Name in root folder (duplicate names)
     * . Folder Detail by name in subfolder of root folder (duplicate names)
     * . Folder by name in subfolder of root folder (duplicate names)
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
        $result1 = $this->ktapi->create_folder($parentId, $folderName, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $root_folder_id[] = $result1['results']['id'];
        $folders[$parentId][$result1['results']['id']] = $folderName;
        $this->assertEqual($result1['status_code'], 0);
        
    	// Create a second sub folder in the root folder
    	$parentId = 1;
    	$folderName = 'Test api sub-folder TWO';
        $result1 = $this->ktapi->create_folder($parentId, $folderName, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $root_folder_id[] = $result1['results']['id'];
        $folders[$parentId][$result1['results']['id']] = $folderName;
        $this->assertEqual($result1['status_code'], 0);

        // Create a sub folder in the first sub folder
        $parentId = $root_folder_id[0];
        $folderName = 'Test api sub-folder THREE';
        $result2 = $this->ktapi->create_folder($parentId, $folderName, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $sub_folder_id[0][] = $result2['results']['id'];
        $folders[$parentId][$result2['results']['id']] = $folderName;
        $this->assertEqual($result2['status_code'], 0);
        
        // Create a sub folder within the first sub folder which shares a name with one of the root sub folders
        $parentId = $sub_folder_id[0][0];
        $folderName = 'Test api sub-folder TWO';
        $result2 = $this->ktapi->create_folder($parentId, $folderName, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $sub_folder_id[0][] = $result2['results']['id'];
        $folders[$parentId][$result2['results']['id']] = $folderName;
        $this->assertEqual($result2['status_code'], 0);
        
        // Create a second sub folder in the first sub folder
        $parentId = $root_folder_id[0];
        $folderName = 'Test api sub-folder FOUR';
        $result2 = $this->ktapi->create_folder($parentId, $folderName, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $sub_folder_id[0][] = $result2['results']['id'];
        $folders[$parentId][$result2['results']['id']] = $folderName;
        $this->assertEqual($result2['status_code'], 0);
        
        // Create a sub folder within the second sub folder
        $parentId = $root_folder_id[1];
        $folderName = 'Test api sub-folder FIVE';
        $result2 = $this->ktapi->create_folder($parentId, $folderName, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $sub_folder_id[1][] = $result2['results']['id'];
        $folders[$parentId][$result2['results']['id']] = $folderName;
        $this->assertEqual($result2['status_code'], 0);
        
        // Create a sub folder within the second sub folder which shares a name with a sub folder in the first sub folder
        $parentId = $sub_folder_id[1][0];
        $folderName = 'Test api sub-folder THREE';
        $result2 = $this->ktapi->create_folder($parentId, $folderName, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $sub_folder_id[1][] = $result2['results']['id'];
        $folders[$parentId][$result2['results']['id']] = $folderName;
        $this->assertEqual($result2['status_code'], 0);
        
        // NOTE default parent is 1, so does not need to be declared when searching the root folder, but we use it elsewhere
        
        // Fetching of root folder - this used to be in the other test
        $parentId = 0;
        $folderName = 'Root Folder';
        // no parent required
        $folder = $this->ktapi->get_folder_by_name($folderName);
        $this->assertNotNull($folder);
        $this->assertIsA($folder, 'KTAPI_Folder');
        $this->assertNoErrors();
        // confirm folder id matches expected
        if (is_a($folder, 'KTAPI_Folder')) {
        	$this->assertEqual($folders[$parentId][$folder->get_folderid()], $folderName);
        }
        
        // Folder Detail by Name in root folder (no duplicate names)
        $parentId = 1;
        $folderName = 'Test api sub-folder ONE';
        // no parent required
    	$response = $this->ktapi->get_folder_detail_by_name($folderName);
        $this->assertNotNull($response['results']);
        $this->assertNoErrors();
        // confirm folder id matches expected
        if (!empty($response)) {
        	$this->assertEqual($folders[$parentId][$response['results']['id']], $folderName);
        }
        
        // Folder Detail by Name in sub folder of root folder (no duplicate names)
        $parentId = $root_folder_id[0];
        $folderName = 'Test api sub-folder FOUR';
        // no parent required
    	$response = $this->ktapi->get_folder_detail_by_name($folderName, $parentId);
        $this->assertNotNull($response['results']);
        $this->assertNoErrors();
        // confirm folder id matches expected
        if (!empty($response)) {
        	$this->assertEqual($folders[$parentId][$response['results']['id']], $folderName);
        }
        
        // Folder by Name in subfolder of root folder (no duplicate names)
        $parentId = $root_folder_id[0];
        $folderName = 'Test api sub-folder FOUR';
        $folder = $this->ktapi->get_folder_by_name($folderName, $parentId);
        $this->assertNotNull($folder);
        $this->assertIsA($folder, 'KTAPI_Folder');
        $this->assertNoErrors();
        // confirm folder id matches expected
        if (is_a($folder, 'KTAPI_Folder')) {
        	$this->assertEqual($folders[$parentId][$folder->get_folderid()], $folderName);
        }
        
        // Folder by Name in root folder (duplicate names)
        $parentId = 1;
        $folderName = 'Test api sub-folder TWO';
        // no parent required
        $folder = $this->ktapi->get_folder_by_name($folderName);
        $this->assertNotNull($folder);
        $this->assertIsA($folder, 'KTAPI_Folder');
        $this->assertNoErrors();
        // confirm folder id matches expected
        if (is_a($folder, 'KTAPI_Folder')) {
        	$this->assertEqual($folders[$parentId][$folder->get_folderid()], $folderName);
        }
        
        // Folder Detail by Name in sub folder of root folder (duplicate names)
        $parentId = $root_folder_id[0];
        $folderName = 'Test api sub-folder THREE';
        $response = $this->ktapi->get_folder_detail_by_name($folderName, $parentId);
        $this->assertNotNull($response['results']);
        $this->assertNoErrors();
        // confirm folder id matches expected
        if (!empty($response)) {
        	$this->assertEqual($folders[$parentId][$response['results']['id']], $folderName);
        }
        
        // Folder by Name in sub folder of sub folder (duplicate names)
        $parentId = $sub_folder_id[0][0];
        $folderName = 'Test api sub-folder TWO';
        $folder = $this->ktapi->get_folder_by_name($folderName, $parentId);
        $this->assertNotNull($folder);
        $this->assertIsA($folder, 'KTAPI_Folder');
        $this->assertNoErrors();
        // confirm folder id matches expected
        if (is_a($folder, 'KTAPI_Folder')) {
        	$this->assertEqual($folders[$parentId][$folder->get_folderid()], $folderName);
        }

		// Clean up - delete all of the folders
        foreach ($root_folder_id as $folder_id) {
        	$this->ktapi->delete_folder($folder_id, 'Testing API', KT_TEST_USER, KT_TEST_PASS);   
        }
        
        foreach ($sub_folder_id as $_folder_id_) {
        	foreach ($_folder_id_ as $folder_id) {	
        		$this->ktapi->delete_folder($folder_id, 'Testing API', KT_TEST_USER, KT_TEST_PASS);
        	}
        }
    }
    
    /**
    * This method tests the license whether it is a commercial or community edition of KnowledgeTree
    *
    */
    public function testIsCommercialEdition()
    {
        $isCommercial = $this->ktapi->isCommercialEdition();

        $this->assertNotNull($isCommercial);
        $this->assertIsA($isCommercial, 'boolean');
        $this->assertNoErrors();
    }

    /**
     * Helper function to create a document - appears unused so not updated to use new storage driver functions
     */
    function createDocument($title, $filename, $folder = null)
    {
        if(is_null($folder)){
            $folder = $this->root;
        }

        // Create a new document
        $randomFile = $this->createRandomFile();
        $this->assertTrue(is_file($randomFile));

        $document = $folder->add_document($title, $filename, 'Default', $randomFile, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $this->assertNotError($document);

        if(PEAR::isError($document)) return false;

        return $document;
    }

    /**
     * Helper function to delete docs
     */
    function deleteDocument($document)
    {
        $document->delete('Testing API');
        $document->expunge();
    }

    function createRandomFile($content = 'this is some text', $uploadDir = null) {
        if(is_null($uploadDir)){
           $uploadDir = dirname(__FILE__);
        }
        $temp = $this->storage->tempnam($uploadDir, 'myfile');
        $this->storage->write_file($temp, null, $content);
        return $temp;
    }
}
?>