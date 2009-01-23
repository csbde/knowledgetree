<?php
require_once (KT_DIR . '/tests/test.php');
require_once (KT_DIR . '/ktapi/ktapi.inc.php');

/**
* These are the unit tests for the main KTAPI class
*
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
        $this->session = $this->ktapi->start_session('admin', 'admin');
        $this->root = $this->ktapi->get_root_folder();
        $this->assertTrue($this->root instanceof KTAPI_Folder);
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
        $document = $this->root->add_document('title_1.txt', 'name_1.txt', 'Default', $randomFile);
        @unlink($randomFile);

        $internalDocObject = $document->getObject();
        $user = $this->ktapi->can_user_access_object_requiring_permission($internalDocObject, $permission);

        $this->assertNotNull($user);
        $this->assertIsA($user, 'User');
        $this->assertNoErrors();

        // test case 2 - test for bad permissions string
        $permission = 'ktcore.permissions.badstring';

        // create the document object
        $randomFile = $this->createRandomFile();
        $document2 = $this->root->add_document('title_2.txt', 'name_2.txt', 'Default', $randomFile);
        @unlink($randomFile);

        $internalDocObject2 = $document2->getObject();
        $user = $this->ktapi->can_user_access_object_requiring_permission($internalDocObject2, $permission);

        $this->assertNotNull($user);
        $this->assertIsA($user, 'PEAR_Error');
        $this->assertNoErrors();

        // clean up
        $document->delete('Testing');
        $document2->delete('Testing');
        $document->expunge();
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
        @unlink($randomFile);

        $list = $this->ktapi->get_documents_by_oem_no('1');

        $this->assertTrue(empty($list));
        $this->assertNoErrors();

        // test case 2 - matching oem numbers
        // create the document object
        $randomFile = $this->createRandomFile();
        $document2 = $this->root->add_document('title_5.txt', 'name_5.txt', 'Default', $randomFile);
        @unlink($randomFile);

        $list = $this->ktapi->get_documents_by_oem_no('2');

        $this->assertFalse(empty($list));
        $this->assertNoErrors();


        // clean up
        $document->delete('Testing');
        $document2->delete('Testing');
        $document->expunge();
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

        $this->session = $this->ktapi->start_session('admin', 'admin');

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
    public function testGetFolderByName()
    {
        $folder = $this->ktapi->get_folder_by_name('Root Folder');

        $this->assertNotNull($folder);
        $this->assertIsA($folder, 'KTAPI_Folder');
        $this->assertNoErrors();
    }

    /**
    * This method tests the retrieval of a document by it's id
    *
    */
    public function testGetDocumentById()
    {
        // create the document object
        $randomFile = $this->createRandomFile();
        $document = $this->root->add_document('title_5.txt', 'name_5.txt', 'Default', $randomFile);
        @unlink($randomFile);

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
        $document = $this->root->add_document('test title 1', 'testfile1.txt', 'Default', $randomFile);
        @unlink($randomFile);

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

    function createRandomFile($content = 'this is some text') {
        $temp = tempnam(dirname(__FILE__), 'myfile');
        $fp = fopen($temp, 'wt');
        fwrite($fp, $content);
        fclose($fp);
        return $temp;
    }
}
?>