<?php
require_once (dirname(__FILE__) . '/../test.php');
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
    function copy() {
        // TODO
        
    }
    function move() {
        // TODO
        
    }
}
?>
