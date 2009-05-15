<?php
require_once (KT_DIR . '/tests/test.php');
require_once (KT_DIR . '/ktapi/ktapi.inc.php');

// username and password for authentication
// must be set correctly for all of the tests to pass in all circumstances
define (KT_TEST_USER, 'admin');
define (KT_TEST_PASS, 'admin');

/**
 * Unit tests for the KTAPI_BulkActions class
 *
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version 0.9
 *
 * NOTE All functions which require electronic signature checking need to send
 * the username and password and reason arguments, else the tests WILL fail IF
 * API Electronic Signatures are enabled.
 * Tests will PASS when API Signatures NOT enabled whether or not
 * username/password are sent.
 */
class APIBulkActionsTestCase extends KTUnitTestCase {

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
     * @var KTAPI_BulkActions
     */
    var $bulk;

    /**
     * Create a ktapi session
     */
    function setUp() {
        $this->ktapi = new KTAPI();
        $this->session = $this->ktapi->start_system_session();
        $this->root = $this->ktapi->get_root_folder();
        $this->bulk = new KTAPI_BulkActions($this->ktapi);
    }

    /**
     * End the ktapi session
     */
    function tearDown() {
        $this->session->logout();
    }

    /* *** Test KTAPI functions *** */

    /**
     * Testing the bulk actions - copy, move, delete
     */
    public function testApiBulkCopyMoveDelete()
    {
        // Create folder and documents
        $doc1 = $this->createDocument('Test Doc One', 'testdoc1.txt');
        $doc2 = $this->createDocument('Test Doc Two', 'testdoc2.txt');
        $folder1 = $this->root->add_folder("New test folder");
        $this->assertNotError($newFolder);
        if(PEAR::isError($newFolder)) return;

        $doc4 = $this->createDocument('Test Doc Four', 'testdoc4.txt', $folder1);

        $target_folder = $this->root->add_folder("New target folder");
        $this->assertNotError($target_folder);
        if(PEAR::isError($target_folder)) return;
        $target_folder_id = $target_folder->get_folderid();

        $aItems = array();
        $aItems['documents'][] = $doc1->get_documentid();
        $aItems['documents'][] = $doc2->get_documentid();
        $aItems['folders'][] = $folder1->get_folderid();

        // Call bulk action - copy
        $response = $this->ktapi->performBulkAction('copy', $aItems, 'Testing API', $target_folder_id, KT_TEST_USER, KT_TEST_PASS);

        $this->assertEqual($response['status_code'], 0);
        $this->assertTrue(empty($response['results']));

        // Test move action - delete and recreate target folder
        $target_folder->delete('Testing API');

        $target_folder = $this->root->add_folder("New target folder");
        $this->assertNotError($target_folder);
        if(PEAR::isError($target_folder)) return;
        $target_folder_id = $target_folder->get_folderid();

        $response = $this->ktapi->performBulkAction('move', $aItems, 'Testing API', $target_folder_id, KT_TEST_USER, KT_TEST_PASS);

        $this->assertEqual($response['status_code'], 0);
        $this->assertTrue(empty($response['results']));

        $response = $this->ktapi->performBulkAction('delete', $aItems, 'Testing API', null, KT_TEST_USER, KT_TEST_PASS);

        $this->assertEqual($response['status_code'], 0);
        $this->assertTrue(empty($response['results']));

        // Delete and expunge documents and folder
        $target_folder->delete('Testing API');
    }

    /**
     * Testing the bulk actions - checkout and cancel check out
     */
    public function testApiBulkCheckout()
    {
        // Create folder and documents
        $doc1 = $this->createDocument('Test Doc One', 'testdoc1.txt');
        $doc2 = $this->createDocument('Test Doc Two', 'testdoc2.txt');
        $folder1 = $this->root->add_folder("New test folder");
        $this->assertNotError($newFolder);
        if(PEAR::isError($newFolder)) return;

        $doc4 = $this->createDocument('Test Doc Four', 'testdoc4.txt', $folder1);

        $doc1_id = $doc1->get_documentid();
        $doc2_id = $doc2->get_documentid();

        $aItems = array();
        $aItems['documents'][] = $doc1_id;
        $aItems['documents'][] = $doc2_id;
        $aItems['folders'][] = $folder1->get_folderid();

        // Call bulk action - checkout
        $response = $this->ktapi->performBulkAction('checkout', $aItems, 'Testing API', null, KT_TEST_USER, KT_TEST_PASS);

        $this->assertEqual($response['status_code'], 0);
        $this->assertTrue(empty($response['results']));

        // update document object
        $doc1 = $this->ktapi->get_document_by_id($doc1_id);
        $this->assertTrue($doc1->is_checked_out());

        // cancel the checkout
        $response = $this->ktapi->performBulkAction('undo_checkout', $aItems, 'Testing API', null, KT_TEST_USER, KT_TEST_PASS);

        $this->assertEqual($response['status_code'], 0);
        $this->assertTrue(empty($response['results']));

        // delete items
        $response = $this->ktapi->performBulkAction('delete', $aItems, 'Testing API', null, KT_TEST_USER, KT_TEST_PASS);
        $this->assertEqual($response['status_code'], 0);
    }

    /**
     * Testing the bulk actions - checkout and cancel check out
     */
    public function testApiBulkImmute()
    {
        // Create folder and documents
        $doc1 = $this->createDocument('Test Doc One', 'testdoc1.txt');
        $doc2 = $this->createDocument('Test Doc Two', 'testdoc2.txt');
        $folder1 = $this->root->add_folder("New test folder");
        $this->assertNotError($newFolder);
        if(PEAR::isError($newFolder)) return;

        $doc4 = $this->createDocument('Test Doc Four', 'testdoc4.txt', $folder1);

        $doc1_id = $doc1->get_documentid();
        $doc2_id = $doc2->get_documentid();

        $aItems = array();
        $aItems['documents'][] = $doc1_id;
        $aItems['documents'][] = $doc2_id;
        $aItems['folders'][] = $folder1->get_folderid();

        // Call bulk action - checkout
        $response = $this->ktapi->performBulkAction('immute', $aItems, 'Testing API', null, KT_TEST_USER, KT_TEST_PASS);

        $this->assertEqual($response['status_code'], 0);
        $this->assertTrue(empty($response['results']));

        // update document object
        $doc1 = $this->ktapi->get_document_by_id($doc1_id);
        $this->assertTrue($doc1->isImmutable());

        // remove immutability for deletion
        $doc1->unimmute();
        $doc2->unimmute();
        $doc4->unimmute();

        // delete items
        $response = $this->ktapi->performBulkAction('delete', $aItems, 'Testing API', null, KT_TEST_USER, KT_TEST_PASS);
        $this->assertEqual($response['status_code'], 0);
    }

    /* *** Test Bulk actions class *** */

    /**
     * Test the bulk copy functionality
     */
    function testCopy()
    {
        // Create documents
        $doc1 = $this->createDocument('Test Doc One', 'testdoc1.txt');
        $doc2 = $this->createDocument('Test Doc Two', 'testdoc2.txt');
        $doc3 = $this->createDocument('Test Doc Three', 'testdoc3.txt');
        $folder1 = $this->root->add_folder("New copy folder");
        $this->assertNotError($newFolder);
        if(PEAR::isError($newFolder)) return;

        $doc4 = $this->createDocument('Test Doc Four', 'testdoc4.txt', $folder1);

        // Add a folder
        $targetFolder = $this->root->add_folder("New target folder");
        $this->assertNotError($newFolder);
        if(PEAR::isError($newFolder)) return;

        $aItems = array($doc1, $doc2, $doc3, $folder1);

        // Copy documents and folder into target folder
        $res = $this->bulk->copy($aItems, $targetFolder, 'Testing bulk copy');

        $this->assertTrue(empty($res));

        // Check the documents copied
        $listDocs = $targetFolder->get_listing(1, 'D');
        $this->assertTrue(count($listDocs) == 3);

        // Check the folder copied
        $listFolders = $targetFolder->get_listing(1, 'F');
        $this->assertTrue(count($listFolders) == 1);

        // Check the document contained in the folder copied
        $newFolderId = $listFolders[0]['id'];
        $newFolder = $this->ktapi->get_folder_by_id($newFolderId);
        $listSubDocs = $newFolder->get_listing(1, 'D');
        $this->assertTrue(count($listSubDocs) == 1);

        // Delete and expunge documents and folder
        $this->deleteDocument($doc1);
        $this->deleteDocument($doc2);
        $this->deleteDocument($doc3);
        $this->deleteDocument($doc4);
        $targetFolder->delete('Testing bulk copy');
        $folder1->delete('Testing bulk copy');
    }

    /**
     * Test the bulk move functionality
     */
    function testMove()
    {
        // Create documents
        $doc1 = $this->createDocument('Test Doc One', 'testdoc1.txt');
        $doc2 = $this->createDocument('Test Doc Two', 'testdoc2.txt');
        $doc3 = $this->createDocument('Test Doc Three', 'testdoc3.txt');
        $folder1 = $this->root->add_folder("New move folder");
        $this->assertNotError($newFolder);
        if(PEAR::isError($newFolder)) return;

        $doc4 = $this->createDocument('Test Doc Four', 'testdoc4.txt', $folder1);

        // Add a folder
        $targetFolder = $this->root->add_folder("New target folder");
        $this->assertNotError($newFolder);
        if(PEAR::isError($newFolder)) return;

        $aItems = array($doc1, $doc2, $doc3, $folder1);

        // Copy documents and folder into target folder
        $res = $this->bulk->move($aItems, $targetFolder, 'Testing bulk move');

        $this->assertTrue(empty($res));

        // Check document has been moved not copied
        $detail = $doc1->get_detail();
        $this->assertFalse($detail['folder_id'] == $this->root->get_folderid());
        $this->assertTrue($detail['folder_id'] == $targetFolder->get_folderid());

        // Check folder has been moved not copied
        $this->assertFalse($folder1->get_parent_folder_id() == $this->root->get_folderid());
        $this->assertTrue($folder1->get_parent_folder_id() == $targetFolder->get_folderid());

        // Check the documents copied
        $listDocs = $targetFolder->get_listing(1, 'D');
        $this->assertTrue(count($listDocs) == 3);

        // Check the folder copied
        $listFolders = $targetFolder->get_listing(1, 'F');
        $this->assertTrue(count($listFolders) == 1);

        // Check the document contained in the folder copied
        $newFolderId = $listFolders[0]['id'];
        $newFolder = $this->ktapi->get_folder_by_id($newFolderId);
        $listSubDocs = $newFolder->get_listing(1, 'D');
        $this->assertTrue(count($listSubDocs) == 1);

        // Delete and expunge documents and folder
        $this->deleteDocument($doc1);
        $this->deleteDocument($doc2);
        $this->deleteDocument($doc3);
        $this->deleteDocument($doc4);
        $targetFolder->delete('Testing bulk copy');
        $folder1->delete('Testing bulk copy');
    }

    /**
     * Test the bulk checkout and cancel checkout functionality
     */
    function testCheckout()
    {
        // Create documents
        $doc1 = $this->createDocument('Test Doc One', 'testdoc1.txt');
        $doc2 = $this->createDocument('Test Doc Two', 'testdoc2.txt');
        $doc3 = $this->createDocument('Test Doc Three', 'testdoc3.txt');
        $folder1 = $this->root->add_folder("New test folder");
        $this->assertNotError($newFolder);
        if(PEAR::isError($newFolder)) return;

        $doc4 = $this->createDocument('Test Doc Four', 'testdoc4.txt', $folder1);

        $aItems = array($doc1, $doc2, $doc3, $folder1);

        // Checkout documents and folder
        $res = $this->bulk->checkout($aItems, 'Testing bulk checkout');

        $this->assertTrue(empty($res));

        $this->assertTrue($doc1->is_checked_out());
        $this->assertTrue($doc2->is_checked_out());
        $this->assertTrue($doc3->is_checked_out());

        // refresh the doc4 document object to reflect changes
        $doc4 = KTAPI_Document::get($this->ktapi, $doc4->get_documentid());
        $this->assertTrue($doc4->is_checked_out());

        $res = $this->bulk->undo_checkout($aItems, 'Testing bulk undo / cancel checkout');

        $this->assertTrue(empty($res));

        $this->assertFalse($doc1->is_checked_out());
        $this->assertFalse($doc2->is_checked_out());
        $this->assertFalse($doc3->is_checked_out());

        // refresh the doc4 document object to reflect changes
        $doc4 = KTAPI_Document::get($this->ktapi, $doc4->get_documentid());
        $this->assertFalse($doc4->is_checked_out());

        // Delete and expunge documents and folder
        $this->deleteDocument($doc1);
        $this->deleteDocument($doc2);
        $this->deleteDocument($doc3);
        $this->deleteDocument($doc4);
        $folder1->delete('Testing bulk checkout');
    }

    /**
     * Test the bulk immute functionality
     */
    function testImmute()
    {
        // Create documents
        $doc1 = $this->createDocument('Test Doc One', 'testdoc1.txt');
        $doc2 = $this->createDocument('Test Doc Two', 'testdoc2.txt');
        $doc3 = $this->createDocument('Test Doc Three', 'testdoc3.txt');
        $folder1 = $this->root->add_folder("New test folder");
        $this->assertNotError($newFolder);
        if(PEAR::isError($newFolder)) return;

        $doc4 = $this->createDocument('Test Doc Four', 'testdoc4.txt', $folder1);

        $aItems = array($doc1, $doc2, $doc3, $folder1);

        // Immute documents
        $res = $this->bulk->immute($aItems);

        $this->assertTrue(empty($res));

        $this->assertTrue($doc1->isImmutable());
        $this->assertTrue($doc2->isImmutable());
        $this->assertTrue($doc3->isImmutable());

        // refresh the doc4 document object to reflect changes
        $doc4 = KTAPI_Document::get($this->ktapi, $doc4->get_documentid());
        $this->assertTrue($doc4->isImmutable());

        // remove immutability for deletion
        $doc1->unimmute();
        $doc2->unimmute();
        $doc3->unimmute();
        $doc4->unimmute();

        // Delete and expunge documents and folder
        $this->deleteDocument($doc1);
        $this->deleteDocument($doc2);
        $this->deleteDocument($doc3);
        $this->deleteDocument($doc4);
        $folder1->delete('Testing bulk checkout');
    }

    /**
     * Test the bulk delete functionality
     */
    function testDelete()
    {
        // Create documents
        $doc1 = $this->createDocument('Test Doc One', 'testdoc1.txt');
        $doc2 = $this->createDocument('Test Doc Two', 'testdoc2.txt');
        $doc3 = $this->createDocument('Test Doc Three', 'testdoc3.txt');
        $folder1 = $this->root->add_folder("New test folder");
        $this->assertNotError($newFolder);
        if(PEAR::isError($newFolder)) return;

        $doc4 = $this->createDocument('Test Doc Four', 'testdoc4.txt', $folder1);

        $aItems = array($doc1, $doc2, $doc3, $folder1);

        // Delete documents and folder
        $res = $this->bulk->delete($aItems, 'Testing bulk delete');

        $this->assertTrue(empty($res));

        // Check documents have been deleted
        $this->assertTrue($doc1->is_deleted());
        $this->assertTrue($doc2->is_deleted());
        $this->assertTrue($doc3->is_deleted());

        // refresh the doc4 document object to reflect changes
        $doc4 = KTAPI_Document::get($this->ktapi, $doc4->get_documentid());
        $this->assertTrue($doc4->is_deleted());

        // Check folder has been deleted
        $folder = $this->ktapi->get_folder_by_name('New test folder');
        $this->assertError($folder);

        // Expunge documents
        $doc1->expunge();
        $doc2->expunge();
        $doc3->expunge();
        $doc4->expunge();
    }

    /**
     * Test the bulk archive functionality
     */
    function testArchive()
    {
        // Create documents
        $doc1 = $this->createDocument('Test Doc One', 'testdoc1.txt');
        $doc2 = $this->createDocument('Test Doc Two', 'testdoc2.txt');
        $doc3 = $this->createDocument('Test Doc Three', 'testdoc3.txt');
        $folder1 = $this->root->add_folder("New test folder");
        $this->assertNotError($newFolder);
        if(PEAR::isError($newFolder)) return;

        $doc4 = $this->createDocument('Test Doc Four', 'testdoc4.txt', $folder1);

        $aItems = array($doc1, $doc2, $doc3, $folder1);

        // Archive documents and folder
        $res = $this->bulk->archive($aItems, 'Testing bulk archive');

        $this->assertTrue(empty($res));

        $document1 = $doc1->getObject();
        $this->assertTrue($document1->getStatusID() == 4);

        // refresh the doc4 document object to reflect changes
        $doc4 = KTAPI_Document::get($this->ktapi, $doc4->get_documentid());
        $document4 = $doc4->getObject();
        $this->assertTrue($document4->getStatusID() == 4);

        // Restore for deletion
        $doc1->restore();
        $doc2->restore();
        $doc3->restore();
        $doc4->restore();

        // Delete and expunge documents and folder
        $this->deleteDocument($doc1);
        $this->deleteDocument($doc2);
        $this->deleteDocument($doc3);
        $this->deleteDocument($doc4);
        $folder1->delete('Testing bulk archive');
    }

    /**
     * Helper function to delete docs
     */
    function deleteDocument($document)
    {
        $document->delete('Testing bulk actions');
        $document->expunge();
    }

    /**
     * Helper function to create a document
     */
    function createDocument($title, $filename, $folder = null)
    {
        if(is_null($folder)){
            $folder = $this->root;
        }

        // Create a new document
        $randomFile = $this->createRandomFile();
        $this->assertTrue(is_file($randomFile));

        $document = $folder->add_document($title, $filename, 'Default', $randomFile);
        $this->assertNotError($document);

        @unlink($randomFile);
        if(PEAR::isError($document)) return false;

        return $document;
    }

    /**
     * Helper function to create a file
     *
     * @param unknown_type $content
     * @return unknown
     */
    function createRandomFile($content = 'this is some text') {
        $temp = tempnam(dirname(__FILE__), 'myfile');
        file_put_contents($temp, $content);
        return $temp;
    }
}
?>
