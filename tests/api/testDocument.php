<?php
require_once (KT_DIR . '/tests/test.php');
require_once (KT_DIR . '/ktapi/ktapi.inc.php');

/**
 * Helper class for the KTAPI_Document unit tests
 *
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version 0.9
 */
class APIDocumentHelper {
    function createRandomFile($content = 'this is some text') {
        $temp = tempnam(dirname(__FILE__), 'myfile');
        $fp = fopen($temp, 'wt');
        fwrite($fp, $content);
        fclose($fp);
        return $temp;
    }
}

/**
 * Unit tests for the KTAPI_Document class
 *
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version 0.9
 */
class APIDocumentTestCase extends KTUnitTestCase {

    /**
     * @var KTAPI
     */
    var $ktapi;

    /**
     * @var KTAPI_Folder
     */
    var $root;

    /**
     * @var KTAPI_Session
     */
    var $session;

    /**
     * Create a ktapi session
     */
    function setUp() {
        $this->ktapi = new KTAPI();
        $this->session = $this->ktapi->start_system_session();
        $this->root = $this->ktapi->get_root_folder();
        $this->assertTrue($this->root instanceof KTAPI_Folder);
    }

    /**
     * End the ktapi session
     */
    function tearDown() {
        $this->session->logout();
    }

    /* *** KTAPI functions *** */

    function testGetRoleAllocation()
    {
        $randomFile = APIDocumentHelper::createRandomFile();
        $this->assertTrue(is_file($randomFile));

        $document = $this->root->add_document('testtitle', 'testname.txt', 'Default', $randomFile);
        @unlink($randomFile);
        $this->assertNotError($document);
        if(PEAR::isError($document)) return;

        $response = $this->ktapi->get_role_allocation_for_document($document->get_documentid());
        $this->assertEqual($response['status_code'], 0);

        $document->delete('Testing');
        $document->expunge();
    }
    
    // causing a failure due to:
    // Fatal error: Call to undefined function sendGroupEmails() in C:\ktdms\knowledgeTree\ktapi\KTAPIDocument.inc.php
    // This causes all following tests to fail as well
    /*
    function testEmailDocument()
    {
        $randomFile = APIDocumentHelper::createRandomFile();
        $this->assertTrue(is_file($randomFile));

        $document = $this->root->add_document('testtitle', 'testname.txt', 'Default', $randomFile);
        @unlink($randomFile);
        $this->assertNotError($document);
        if(PEAR::isError($document)) return;

        $members = array('users' => array(1));
        $response = $this->ktapi->email_document($document->get_documentid(), $members, 'Test Email', false);
        $this->assertEqual($response['status_code'], 0);

        $document->delete('Testing');
        $document->expunge();
    }
    */

    /* *** Class functions *** */

    /**
     * Tests the add and delete document functionality
     */
    function testAddDocument() {
        $randomFile = APIDocumentHelper::createRandomFile();
        $this->assertTrue(is_file($randomFile));

        $document = $this->root->add_document('testtitle.txt', 'testname.txt', 'Default', $randomFile);
        $this->assertNotError($document);

        if(PEAR::isError($document)) return;
        $this->assertTrue($document instanceof KTAPI_Document);
        @unlink($randomFile);
        $documentid = $document->get_documentid();

        // get document
        $document = $this->ktapi->get_document_by_id($documentid);
        $this->assertTrue($document instanceof KTAPI_Document);
        $this->assertEqual($document->get_title(), 'testtitle.txt');
        $this->assertFalse($document->is_deleted());
        $document->delete('Testing document add and delete');

        // check if document still exists
        $document = $this->ktapi->get_document_by_id($documentid);
        $this->assertTrue($document instanceof KTAPI_Document);
        $this->assertTrue($document->is_deleted());
        $document->expunge();

        // check if document still exists
        $document = $this->ktapi->get_document_by_id($documentid);
        $this->assertFalse($document instanceof KTAPI_Document);
    }

    /**
     * Tests the document download functionality
     */
    function testDownload() {
        $randomFile = APIDocumentHelper::createRandomFile();
        $this->assertTrue(is_file($randomFile));
        $document = $this->root->add_document('testtitle789', 'testname789.txt', 'Default', $randomFile);
        $this->assertNotError($document);

        if(PEAR::isError($document)) return;
        $this->assertTrue($document instanceof KTAPI_Document);
        @unlink($randomFile);

        $download_url = $document->get_download_url();
        $this->assertTrue(is_string($download_url));

        $doc_id = $document->get_documentid();
        $this->assertFalse(strpos($download_url, 'd='.$doc_id) === false);

        // Delete and expunge document
        $document->delete('Testing document download');
        $document->expunge();
    }

    /**
     * Tests the get metadata, update metadata and get packed metadata functionality
     */
    function testGetMetadata() {
        // Create a new document
        $randomFile = APIDocumentHelper::createRandomFile();
        $this->assertTrue(is_file($randomFile));

        $document = $this->root->add_document('testtitle123', 'testname123.txt', 'Default', $randomFile);
        $this->assertNotError($document);

        @unlink($randomFile);
        if(PEAR::isError($document)) return;

        // Get the document metadata
        $metadata = $document->get_metadata();
        $this->assertTrue(count($metadata) > 0);

        $this->assertTrue($metadata[0]['fieldset'] == 'Tag Cloud');
        $this->assertTrue($metadata[0]['fields'][0]['description'] == 'Tag Words');

        // Update the metadata - add a tag
        $metadata[0]['fields'][0]['value'] = 'test';

        $res = $document->update_metadata($metadata);
        $this->assertFalse($res instanceof PEAR_Error);

        $new_metadata = $document->get_packed_metadata();

        $this->assertTrue($new_metadata[0][0]->getDescription() == 'Tag Words');
        $this->assertTrue($new_metadata[0][1] == 'test');

        // Delete and expunge document
        $document->delete('Testing document get metadata');
        $document->expunge();
    }

    /**
     * Tests the copy functionality. Includes the get_title() functionality.
     */
    function testCopy() {
        // Create a new document
        $randomFile = APIDocumentHelper::createRandomFile();
        $this->assertTrue(is_file($randomFile));

        $document = $this->root->add_document('testtitle123', 'testname123.txt', 'Default', $randomFile);
        $this->assertNotError($document);

        @unlink($randomFile);
        if(PEAR::isError($document)) return;

        // Add a folder to copy into
        $newFolder = $this->root->add_folder("New folder");
        $this->assertNotError($newFolder);

        if(PEAR::isError($newFolder)) return;

        // Copy document into the new folder
        $copyDoc = $document->copy($newFolder, 'Testing document copy');
        $this->assertTrue($copyDoc instanceof KTAPI_Document);
        $this->assertTrue($copyDoc->get_title() == 'testtitle123');

        // Delete and expunge documents
        $document->delete('Testing document copy');
        $document->expunge();
        $copyDoc->delete('Testing document copy');
        $copyDoc->expunge();

        // Delete test folder
        $newFolder->delete('Testing document copy');
    }

    /**
     * Tests the move functionality. Includes the get_detail() functionality.
     */
    function testMove() {
        // Create a new document
        $randomFile = APIDocumentHelper::createRandomFile();
        $this->assertTrue(is_file($randomFile));

        $document = $this->root->add_document('testtitle246', 'testname246.txt', 'Default', $randomFile);
        $this->assertNotError($document);

        @unlink($randomFile);
        if(PEAR::isError($document)) return;

        // Add a folder to copy into
        $newFolder = $this->root->add_folder("New folder");
        $this->assertNotError($newFolder);

        if(PEAR::isError($newFolder)) return;

        // Copy document into the new folder
        $document->move($newFolder, 'Testing document move');
        $detail = $document->get_detail();

        $this->assertFalse($detail['folder_id'] == $this->root->get_folderid());
        $this->assertTrue($detail['folder_id'] == $newFolder->get_folderid());

        // Delete and expunge documents
        $document->delete('Testing document move');
        $document->expunge();

        // Delete test folder
        $newFolder->delete('Testing document move');
    }

    /**
     * Tests the checkout, checkin, cancel checkout and is_checked_out document functionality
     */
    function testCheckinCheckout() {
        $randomFile = APIDocumentHelper::createRandomFile();
        $this->assertTrue(is_file($randomFile));
        $document = $this->root->add_document('testtitle369', 'testname369.txt', 'Default', $randomFile);
        $this->assertNotError($document);

        if(PEAR::isError($document)) return;
        $this->assertTrue($document instanceof KTAPI_Document);
        @unlink($randomFile);
        $documentid = $document->get_documentid();

        // document should be checked in
        $document = $this->ktapi->get_document_by_id($documentid);
        $this->assertFalse($document->is_checked_out());
        $document->checkout('Testing document checkout');

        // document should now be checked out
        $document = $this->ktapi->get_document_by_id($documentid);
        $this->assertTrue($document->is_checked_out());
        $document->undo_checkout('Testing document cancel checkout');

        // document should be checked in
        $document = $this->ktapi->get_document_by_id($documentid);
        $this->assertFalse($document->is_checked_out());
        $document->checkout('Testing document checkout');

        // document should now be checked out
        $document = $this->ktapi->get_document_by_id($documentid);
        $this->assertTrue($document->is_checked_out());

        // create another random file
        $randomFile = APIDocumentHelper::createRandomFile('updating the previous content');
        $this->assertTrue(is_file($randomFile));
        $document->checkin('testname369.txt', 'Updating test checkin document', $randomFile);
        @unlink($randomFile);

        // document should be checked in
        $document = $this->ktapi->get_document_by_id($documentid);
        $this->assertFalse($document->is_checked_out());
        $document->delete('Testing document checkin');
        $document->expunge();
    }

    /**
     * Test role allocation and permission allocation
     */
    function testPermissions()
    {
        $randomFile = APIDocumentHelper::createRandomFile();
        $this->assertTrue(is_file($randomFile));

        $document = $this->root->add_document('testtitle', 'testname', 'Default', $randomFile);
        @unlink($randomFile);
        $this->assertNotError($document);
        if(PEAR::isError($document)) return;

        $permAllocation = $document->getPermissionAllocation();
        $this->assertNotNull($permAllocation);
        $this->assertEntity($permAllocation, KTAPI_PermissionAllocation);

        $roleAllocation = $document->getRoleAllocation();
        $this->assertNotNull($roleAllocation);
        $this->assertEntity($roleAllocation, KTAPI_RoleAllocation);

        $document->delete('Testing');
        $document->expunge();
    }

    /**
     * Tests the adding of a duplicate document title
     *
    function testAddingDuplicateTitle()
    {
        $randomFile = APIDocumentHelper::createRandomFile();
        $this->assertTrue(is_file($randomFile));
        $document = $this->root->add_document('testtitle.txt', 'testname.txt', 'Default', $randomFile);
        $this->assertNotError($document);

        if(PEAR::isError($document)) return;
        $this->assertEntity($document, 'KTAPI_Document');
        $this->assertFalse(is_file($randomFile));
        $documentid = $document->get_documentid();

        // file would have been cleaned up because of the add_document
        $randomFile = APIDocumentHelper::createRandomFile();
        $this->assertTrue(is_file($randomFile));

        // filenames must be the same as above
        $document2 = $this->root->add_document('testtitle.txt', 'testname.txt', 'Default', $randomFile);
        $this->assertFalse($document2 instanceof KTAPI_Document);
        @unlink($randomFile);
        $document->delete('because we can');
        $document->expunge();
        if ($document2 instanceof KTAPI_Document) {
            $document2->delete('because we can');
            $document2->expunge();
        }
    }

    /**
     * Tests the adding of duplicate document file names
     *
    function testAddingDuplicateFile()
    {
        $randomFile = APIDocumentHelper::createRandomFile();
        $this->assertTrue(is_file($randomFile));
        $document = $this->root->add_document('testtitle.txt', 'testname.txt', 'Default', $randomFile);
        $this->assertNotError($document);

        if(PEAR::isError($document)) return;
        $this->assertTrue($document instanceof KTAPI_Document);
        $this->assertFalse(is_file($randomFile));
        $documentid = $document->get_documentid();
        $randomFile = APIDocumentHelper::createRandomFile();
        $this->assertTrue(is_file($randomFile));

        // filenames must be the same as above
        $document2 = $this->root->add_document('testtitle2.txt', 'testname.txt', 'Default', $randomFile);
        $this->assertFalse($document2 instanceof KTAPI_Document);

        @unlink($randomFile);
        $document->delete('because we can');
        $document->expunge();
        if ($document2 instanceof KTAPI_Document) {
            $document2->delete('because we can');
            $document2->expunge();
        }
    }
    */

    /**
    * Method to test the document subscriptions for webservices
    *
    */
    public function testSubscriptions_KTAPI()
    {
        $this->ktapi->session_logout();
        $this->session = $this->ktapi->start_session('admin', 'admin');

        $randomFile = APIDocumentHelper::createRandomFile();
        $this->assertTrue(is_file($randomFile));

        $document = $this->root->add_document('testtitle.txt', 'testname.txt', 'Default', $randomFile);
        $this->assertIsA($document, 'KTAPI_Document');
        $this->assertNoErrors();

        @unlink($randomFile);
        $documentid = $document->get_documentid();

        // case no subscription
        $response = $this->ktapi->is_document_subscribed($documentid);
        $this->assertIsA($response, 'array');
        $this->assertEqual($response['results']['subscribed'], 'FALSE');
        $this->assertNoErrors();

        //case add subscription
        $response = $this->ktapi->subscribe_to_document($documentid);
        $this->assertIsA($response, 'array');
        $this->assertEqual($response['results']['action_result'], 'TRUE');
        $this->assertNoErrors();

        //case add DUPLICATE subscription
        $response = $this->ktapi->subscribe_to_document($documentid);
        $this->assertIsA($response, 'array');
        $this->assertEqual($response['results']['action_result'], 'TRUE');
        $this->assertNoErrors();

        // case subscription exists
        $response = $this->ktapi->is_document_subscribed($documentid);
        $this->assertIsA($response, 'array');
        $this->assertEqual($response['results']['subscribed'], 'TRUE');
        $this->assertNoErrors();

        //case delete subscription
        $response = $this->ktapi->unsubscribe_from_document($documentid);
        $this->assertIsA($response, 'array');
        $this->assertEqual($response['results']['action_result'], 'TRUE');
        $this->assertNoErrors();

        //case delete NOT EXISTANT subscription
        $response = $this->ktapi->unsubscribe_from_document($documentid);
        $this->assertIsA($response, 'array');
        $this->assertEqual($response['results']['action_result'], 'TRUE');
        $this->assertNoErrors();

        $document->delete('Test');
        $document->expunge();
    }
}

?>