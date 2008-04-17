<?php
require_once (dirname(__FILE__) . '/../test.php');
require_once (KT_DIR . '/ktapi/ktapi.inc.php');
class APIDocumentHelper {
    function createRandomFile($content = 'this is some text') {
        $temp = tempnam(dirname(__FILE__), 'myfile');
        $fp = fopen($temp, 'wt');
        fwrite($fp, $content);
        fclose($fp);
        return $temp;
    }
}
class APIDocumentTestCase extends KTUnitTestCase {
    /**
     * @var KTAPI
     */
    var $ktapi;
    /**
     * @var KTAPI_Folder
     */
    var $root;
    var $session;
    function setUp() {
        $this->ktapi = new KTAPI();
        $this->session = $this->ktapi->start_system_session();
        $this->root = $this->ktapi->get_root_folder();
        $this->assertTrue(is_a($this->root, 'KTAPI_Folder'));
    }
    function tearDown() {
        $this->session->logout();
    }
    function testAddDocument() {
        $randomFile = APIDocumentHelper::createRandomFile();
        $this->assertTrue(is_file($randomFile));
        $document = $this->root->add_document('testtitle.txt', 'testname.txt', 'Default', $randomFile);
        $this->assertNotError($document);
        if(PEAR::isError($document)) return;
        $this->assertTrue(is_a($document, 'KTAPI_Document'));
        @unlink($randomFile);
        $documentid = $document->get_documentid();
        // get document
        $document = $this->ktapi->get_document_by_id($documentid);
        $this->assertTrue(is_a($document, 'KTAPI_Document'));
        $this->assertEqual($document->get_title(), 'testtitle.txt');
        $document->delete('because we can');
        // check if document still exists
        $document = $this->ktapi->get_document_by_id($documentid);
        $this->assertTrue(is_a($document, 'KTAPI_Document'));
        $this->assertTrue($document->is_deleted());
        $document->expunge();
        // check if document still exists
        $document = $this->ktapi->get_document_by_id($documentid);
        $this->assertFalse(is_a($document, 'KTAPI_Document'));
    }
    function testCheckinDocument() {
        $randomFile = APIDocumentHelper::createRandomFile();
        $this->assertTrue(is_file($randomFile));
        $document = $this->root->add_document('testtitle.txt', 'testname.txt', 'Default', $randomFile);
        $this->assertNotError($document);
        if(PEAR::isError($document)) return;
        $this->assertTrue(is_a($document, 'KTAPI_Document'));
        @unlink($randomFile);
        $documentid = $document->get_documentid();
        // document should be checked in
        $document = $this->ktapi->get_document_by_id($documentid);
        $this->assertFalse($document->is_checked_out());
        $document->checkout('because');
        // document should now be checked out
        $document = $this->ktapi->get_document_by_id($documentid);
        $this->assertTrue($document->is_checked_out());
        $document->undo_checkout('because we want to undo it');
        // document should be checked in
        $document = $this->ktapi->get_document_by_id($documentid);
        $this->assertFalse($document->is_checked_out());
        $document->checkout('because');
        // document should now be checked out
        $document = $this->ktapi->get_document_by_id($documentid);
        $this->assertTrue($document->is_checked_out());
        // create another random file
        $randomFile = APIDocumentHelper::createRandomFile('updating the previous content');
        $this->assertTrue(is_file($randomFile));
        $document->checkin('testname.txt', 'updating', $randomFile);
        @unlink($randomFile);
        // document should be checked in
        $document = $this->ktapi->get_document_by_id($documentid);
        $this->assertFalse($document->is_checked_out());
        $document->delete('because we can');
        $document->expunge();
    }
    function testAddingDuplicateTitle() {
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
        $document2 = $this->root->add_document('testtitle.txt', 'testname2.txt', 'Default', $randomFile);
        $this->assertFalse(is_a($document2, 'KTAPI_Document'));
        @unlink($randomFile);
        $document->delete('because we can');
        $document->expunge();
        if (is_a($document2, 'KTAPI_Document')) {
            $document2->delete('because we can');
            $document2->expunge();
        }
    }
    function testAddingDuplicateFile() {
        $randomFile = APIDocumentHelper::createRandomFile();
        $this->assertTrue(is_file($randomFile));
        $document = $this->root->add_document('testtitle.txt', 'testname.txt', 'Default', $randomFile);
        $this->assertNotError($document);
        if(PEAR::isError($document)) return;
        $this->assertTrue(is_a($document, 'KTAPI_Document'));
        $this->assertFalse(is_file($randomFile));
        $documentid = $document->get_documentid();
        $randomFile = APIDocumentHelper::createRandomFile();
        $this->assertTrue(is_file($randomFile));
        // filenames must be the same as above
        $document2 = $this->root->add_document('testtitle2.txt', 'testname.txt', 'Default', $randomFile);
        $this->assertFalse(is_a($document2, 'KTAPI_Document'));
        @unlink($randomFile);
        $document->delete('because we can');
        $document->expunge();
        if (is_a($document2, 'KTAPI_Document')) {
            $document2->delete('because we can');
            $document2->expunge();
        }
    }
}
?>
