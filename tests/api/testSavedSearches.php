<?php
require_once (KT_DIR . '/tests/test.php');
require_once (KT_DIR . '/ktapi/ktapi.inc.php');

/**
* These are the unit tests for the main KTAPI class
*
*/
class savedSearchTestCase extends KTUnitTestCase {

    /**
    * @var object $ktapi The main ktapi object
    */
    var $ktapi;

    /**
    * @var object $savedSearch The saved searches object object
    */
    var $savedSearch;

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
        $this->savedSearch = new savedSearches($this->ktapi);
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
    * This method tests the creation of the saved search
    *
    */
    public function testCreate()
    {
        //case 1: user logged in
        $searchID = $this->savedSearch->create('test_search', '(GeneralText contains "title")');

        $this->assertNotA($searchID, 'PEAR_Error');
        $this->assertNotNull($searchID);
        $this->assertNoErrors();

        $this->savedSearch->delete($searchID);

        //case 2: user NOT logged in
        $this->ktapi->session_logout();
        $searchID = $this->savedSearch->create('test_search', '(GeneralText contains "title")');

        $this->assertIsA($searchID, 'PEAR_Error');
        $this->assertNoErrors();
    }

    /**
    * This method tests the retrieval for the saved search by it's id
    *
    */
    public function testGetSavedSearch()
    {
        // case 1: search exists
        $searchID = $this->savedSearch->create('test_search', '(GeneralText contains "title")');
        $list = $this->savedSearch->getList();

        foreach($list as $item){
            if($item['id'] == $searchID){
                $search = $item['id'];
                break;
            }
        }
        $savedSearch = $this->savedSearch->getSavedSearch($search);

        $this->assertNotNull($savedSearch);
        $this->assertNoErrors();
        $this->savedSearch->delete($searchID);

        // case 2: search does NOT exists
        $list = $this->savedSearch->getList();
        $inList = FALSE;
        foreach($list as $item){
            if($item['id'] == $searchID){
                $inList = TRUE;
                break;
            }
        }

        $this->assertNotA($list, 'PEAR_Error');
        $this->assertFalse($inList);
        $this->assertNoErrors();

    }

    /**
    * This method tests the list of the saved search
    *
    */
    public function testList()
    {
        // case 1: Saved searches exist
        $array = array();
        $searchID = $this->savedSearch->create('test_search', '(GeneralText contains "title")');
        $list = $this->savedSearch->getList();

        $this->assertNotA($list, 'PEAR_Error');
        $this->assertNotEqual($list, $array);
        $this->assertNoErrors();

        $this->savedSearch->delete($searchID);

        // case 2: saved search does NOT exist
        $list = $this->savedSearch->getList();

        $inList = FALSE;
        foreach($list as $item){
            if($item['id'] == $searchID){
                $inList = TRUE;
                break;
            }
        }
        $this->assertNotA($list, 'PEAR_Error');
        $this->assertFalse($inList);
        $this->assertNoErrors();
}

    /**
    * This method tests the deleting of the saved search
    *
    */
    public function testDelete()
    {
        $searchID = $this->savedSearch->create('test_search', '(GeneralText contains "title")');
        $this->savedSearch->delete($searchID);
        $result = $this->savedSearch->getSavedSearch($searchID);

        $array = array();
        $this->assertEqual($result, $array);
        $this->assertNotA($result, 'PEAR_Error');
        $this->assertNoErrors();
    }

    /**
    * This method tests the processing of the saved search
    *
    */
    public function testRunSavedSearch()
    {
        // create the document object
        $randomFile = $this->createRandomFile();
        $document = $this->root->add_document('title.txt', 'name_1.txt', 'Default', $randomFile);
        @unlink($randomFile);

        $searchID = $this->savedSearch->create('test_search', '(GeneralText contains "title")');

        $result = $this->savedSearch->runSavedSearch($searchID);

        $this->assertNotNull($result);
        $this->assertNotA($result, 'PEAR_Error');
        $this->assertNoErrors();

        $document->delete('Testing');
        $document->expunge();

        $this->savedSearch->delete($searchID);
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