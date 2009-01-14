<?php
require_once (KT_DIR . '/ktapi/ktapi.inc.php');


class Search2TestCase extends KTUnitTestCase {

    /**
     * @var KTAPI
     */
    var $ktapi;
    var $session;
    var $root;
    var $indexer;
    var $doc;
    var $docId;

    /**
     * Setup the session, add and index the document
     *
     */
    function setUp() {
        $this->ktapi = new KTAPI();
        $this->session = $this->ktapi->start_system_session();
        $this->root = $this->ktapi->get_root_folder();
        $doc = array();
        $docId = array();

        // Add documents to DB and lucene index
        $filename = 'Test987.txt';
        $title = 'Test987';
        $this->addNewDocument($title, $filename, 0);

        $filename = 'Test654.txt';
        $title = 'Test654';
        $content = 'Searchable text: abcde ghijk 4567';
        $this->addNewDocument($title, $filename, 1, $content);

        $filename = 'Test321.txt';
        $title = 'Test321';
        $content = 'Searchable text: abcde mnopqr 891011';
        $this->addNewDocument($title, $filename, 2, $content);
    }

    /**
     * Delete the document from the DB and the lucene index, logout of the session.
     *
     */
    function tearDown() {
        // Remove documents from DB and lucene index
        foreach($this->doc as $key => $doc){
            $this->indexer->deleteDocument($this->docId[$key]);
            $doc->delete('Deleting test document');
            $doc->expunge();
        }

        $this->session->logout();
    }

    /**
     * Test the database search
     *
     */
    function testDBSearch() {
		$results = processSearchExpression('(GeneralText contains "Test987")');

		$this->assertTrue(count($results) > 0);
		$this->assertTrue($results[0]['filename'] == 'Test987.txt');
    }

    /**
     * Test the content search
     *
     */
    function testLuceneSearch() {
        $results = processSearchExpression('(GeneralText contains "abcde")');

		$this->assertTrue(count($results) >= 3);

		$results2 = processSearchExpression('(GeneralText contains "mnopqr")');

		$this->assertTrue(count($results) > 0);

		$filename = '';
		foreach($results2 as $res){
		    if($res['document_id'] == $this->docId[2]){
                $filename = $res['filename'];
		    }
		}
		$this->assertTrue($filename == 'Test321.txt');
    }

    /**
     * Test a combination database and content search
     *
     */
    function testComboSearch() {
        $results = processSearchExpression('(GeneralText contains "abcde") AND (Title contains "Test987")');

		$this->assertTrue(count($results) == 1);
		$this->assertTrue($results[0]['filename'] == 'Test987.txt');
    }

    /* ** Utility functions ** */

    function addNewDocument($title, $filename, $i, $content = null)
    {
        $file = $this->createFile($filename, $content);
        $document = $this->root->add_document($title, $filename, 'Default', $file);

        $this->indexer = Indexer::get();
        $this->docId[$i] = $document->get_documentid();
        $version = '0.1';
        $file = $this->createFile($filename, $content); // ktapi add_document deletes the temp file so recreate it for indexing.
        $status = $this->indexer->indexDocument($this->docId[$i], $file, $title, $version);

        @unlink($file);

        if(PEAR::isError($document)) return;

        $this->doc[$i] = $document;
    }

    /**
     * Create a temporary document
     *
     * @param string $filename
     * @param string $content
     * @return string
     */
    function createFile($filename = 'myfile.txt', $content = null) {
        if(empty($content)){
            $content = 'Searchable text: abcde xyz 12345';
        }

        $temp = tempnam(dirname(__FILE__), $filename);
        $fp = fopen($temp, 'wt');
        fwrite($fp, $content);
        fclose($fp);
        return $temp;
    }
}
?>