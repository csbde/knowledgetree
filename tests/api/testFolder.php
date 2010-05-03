<?php
require_once (KT_DIR . '/tests/test.php');
require_once (KT_DIR . '/ktapi/ktapi.inc.php');
class APIFolderTestCase extends KTUnitTestCase {
    /**
     * @var KTAPI
     */
    var $ktapi;
    var $session;
    function setUp() {
        $this->ktapi = new KTAPI();
        $this->session = $this->ktapi->start_system_session();
    }
    function tearDown() {
        $this->session->logout();
    }
    function testCreateDuplicate() {
        $root = $this->ktapi->get_root_folder();
        $this->assertEntity($root, 'KTAPI_Folder');
        $folder = $root->add_folder('temp1');
        $this->assertEntity($folder, 'KTAPI_Folder');
        $folder2 = $root->add_folder('temp1');
        $this->assertError($folder2);
        $folder->delete('because');
        if (is_a($folder2, 'KTAPI_Folder')) {
            $folder2->delete('because');
        }
    }

    function testAddDocument() {
    	$tmpfname = tempnam("/tmp", "KTUNIT");
    	$fp = fopen($tmpfname, "w");
    	fwrite($fp, "Hello");
    	fclose($fp);

    	$folder = $this->ktapi->get_root_folder();
    	$res = $folder->add_document("Test Document", "test.txt", "Default", $tmpfname);
    	$this->assertEntity($res, 'KTAPI_Document');

    	$res = $res->delete("Test deletion");
    }

    function testDeleteFolder() {
    	$folder = $this->ktapi->get_root_folder();
    	$folder = $folder->add_folder('temp1');
        $this->assertEntity($folder, 'KTAPI_Folder');
        $folder->delete('because');

        $folder = $this->ktapi->get_folder_by_name('temp1');
        $this->assertError($folder);
    }

/*    function testRename() {
        $root = $this->ktapi->get_root_folder();
        $this->assertEntity($root, 'KTAPI_Folder');
        // add a sample folder
        $folder = $root->add_folder('newFolder');
        $this->assertEntity($folder, 'KTAPI_Folder');
        $folderid = $folder->get_folderid();
        // rename the folder
        $response = $folder->rename('renamedFolder');
        $this->assertEntity($response, 'PEAR_Error');
        // get the folder by id
        $folder = $this->ktapi->get_folder_by_id($folderid);
        $this->assertEntity($folder, 'KTAPI_Folder');
        $this->assertEqual($folder->get_folder_name(), 'renamedFolder');
        $folder->delete('cleanup');
    }*/

	function testGet() {
		$root = $this->ktapi->get_root_folder();
		$folder = $root->get_folder();
		$this->assertEntity($folder, 'Folder');

		$folder = $this->ktapi->get_folder_by_name("NONAMEFOLDER");
		$this->assertError($folder);

		$junk = pack("H*", "000102030405060708090A0B0C0D0E0F101112131415161718191A1B1C1D1E1F");
		$junk = $junk.$junk.$junk.$junk.$junk.$junk.$junk.$junk.$junk;
		$folder = $this->ktapi->get_folder_by_name($junk);
		$this->assertError($folder);

	}

	function testListing() {
		$root = $this->ktapi->get_root_folder();
		$listing = $root->get_listing();
		$this->assertExpectedResults(true, is_array($listing));
		foreach($listing as $key => $val) {

		}
	}

	 function testCreateFolders() {
        $root = $this->ktapi->get_root_folder();
        $this->assertEntity($root, 'KTAPI_Folder');
        $folder = $root->add_folder('temp1');
        $this->assertEntity($folder, 'KTAPI_Folder');
        $folder2 = $folder->add_folder('temp2');
        $this->assertEntity($folder, 'KTAPI_Folder');
        $folder3 = $root->add_folder('temp3');
        $this->assertEntity($folder, 'KTAPI_Folder');
        $folder4 = $folder3->add_folder('temp4');
        $this->assertEntity($folder, 'KTAPI_Folder');
        $folderids = array('temp1' => $folder->get_folderid(), 'temp2' => $folder2->get_folderid(), 'temp3' => $folder3->get_folderid(), 'temp4' => $folder4->get_folderid());
        unset($folder);
        unset($folder2);
        unset($folder3);
        unset($folder4);
        $paths = array('temp1' => '/temp1', 'temp2' => '/temp1/temp2', 'temp3' => '/temp3', 'temp4' => '/temp3/temp4',);
        // test reference by name
        foreach($paths as $key => $path) {
            $folder = $root->get_folder_by_name($path);
            $this->assertEntity($folder, 'KTAPI_Folder');
            if (!is_a($folder, 'KTAPI_Folder')) continue;
            $this->assertEqual($folder->get_folderid(), $folderids[$key]);
            $this->assertEqual('/'.$folder->get_full_path(), $path);
        }
        // lets clean up
        foreach($paths as $key => $path) {
            $folder = $root->get_folder_by_name($path);
            if (is_a($folder, 'KTAPI_Folder')) {
                $folder->delete('because ' . $path);
            }
            $folder = $root->get_folder_by_name($path);
            $this->assertEntity($folder, 'PEAR_Error');
        }
    }

	function testPermission() {
		$root = $this->ktapi->get_root_folder();
		$perm = $root->get_permissions();
		/* Not yet implemented */
	}

    function getSystemListing() {
        // TODO .. can do anything as admin...

    }

    function getAnonymousListing() {
        // TODO
        // probably won't be able to do unless the api caters for setting up anonymous...

    }

    function getUserListing() {
        // TODO

    }

    function testCopy() {
    	$root = $this->ktapi->get_root_folder();
    	$folder = $root->add_folder("Test folder2");
    	$new_folder = $root->add_folder("New test folder2");
    	$res = $folder->copy($new_folder, "Unit test copy2");

    	$folder->delete("Clean up test");
    	$new_folder->delete("Clean up test");

    	$this->assertNull($res, "Error returned");


    }

    function testMove() {
    	$root = $this->ktapi->get_root_folder();
    	$folder = $root->add_folder("Test folder2");
    	$new_folder = $root->add_folder("New test folder2");
    	$res = $folder->move($new_folder, "Unit test copy2");

    	$folder->delete("Clean up test");
    	$new_folder->delete("Clean up test");

    	$this->assertNull($res, "Error returned");
    }

    /**
     * Test role allocation and permission allocation
     */
    function testPermissions()
    {
        $root = $this->ktapi->get_root_folder();
        $folder = $root->add_folder('testXXXXX');
        $this->assertIsA($folder, 'KTAPI_Folder');
        if(PEAR::isError($folder)) return;

        $permAllocation = $folder->getPermissionAllocation();
        $this->assertNotNull($permAllocation);
        $this->assertEntity($permAllocation, KTAPI_PermissionAllocation);

        $roleAllocation = $folder->getRoleAllocation();
        $this->assertNotNull($roleAllocation);
        $this->assertEntity($roleAllocation, KTAPI_RoleAllocation);

        $folder->delete('testXXXXX');
    }

    function testTransactionHistory() {
    	$transactions = $this->ktapi->get_folder_transaction_history(1);
    	$this->assertIsA($transactions, 'array');
    }

    /**
    * Method to test the folder subscriptions for webservices
    *
    */
    public function testSubscriptions_KTAPI()
    {
        $this->ktapi->session_logout();
        $this->session = $this->ktapi->start_session('admin', 'admin');

        $root = $this->ktapi->get_root_folder();
        $folder = $root->add_folder('testXXXXX');
        $this->assertIsA($folder, 'KTAPI_Folder');
        $this->assertNotA($folder, 'PEAR_Error');
        $this->assertNoErrors();

        // case no subscription
        $response = $this->ktapi->is_folder_subscribed($folder->get_folderid());
        $this->assertIsA($response, 'array');
        $this->assertEqual($response['results']['subscribed'], 'FALSE');
        $this->assertNoErrors();

        //case add subscription
        $response = $this->ktapi->subscribe_to_folder($folder->get_folderid());
        $this->assertIsA($response, 'array');
        $this->assertEqual($response['results']['action_result'], 'TRUE');
        $this->assertNoErrors();

        //case add DUPLICATE subscription
        $response = $this->ktapi->subscribe_to_folder($folder->get_folderid());
        $this->assertIsA($response, 'array');
        $this->assertEqual($response['results']['action_result'], 'TRUE');
        $this->assertNoErrors();

        // case subscription exists
        $response = $this->ktapi->is_folder_subscribed($folder->get_folderid());
        $this->assertIsA($response, 'array');
        $this->assertEqual($response['results']['subscribed'], 'TRUE');
        $this->assertNoErrors();

        //case delete subscription
        $response = $this->ktapi->unsubscribe_from_folder($folder->get_folderid());
        $this->assertIsA($response, 'array');
        $this->assertEqual($response['results']['action_result'], 'TRUE');
        $this->assertNoErrors();

        //case delete NOT EXISTANT subscription
        $response = $this->ktapi->unsubscribe_from_folder($folder->get_folderid());
        $this->assertIsA($response, 'array');
        $this->assertEqual($response['results']['action_result'], 'TRUE');
        $this->assertNoErrors();

        $folder->delete('testXXXXX');
    }
}

?>