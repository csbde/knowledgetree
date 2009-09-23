<?php

require_once('test.php');

class UnitTests extends TestSuite {
    function UnitTests() {

        $this->TestSuite('Unit tests');

        // CMIS API
        $this->addFile('ktcmis/testCmisApi.php');

        // KTAPI
        // Some of these tests will fail if Electronic Signatures are enabled for the API.
        // To fix, check the failing functions and add 'admin', 'admin' as username and password,
        // and where necessary send 'Testing API' as a reason
        $this->addFile('api/testApi.php');
        $this->addFile('api/testAuto.php');
        $this->addFile('api/testSavedSearches.php');
        $this->addFile('api/testAcl.php');
        $this->addFile('api/testAuthentication.php');

        // the next two appear to not use the ktapi functions which require signatures,
        // e.g. the move function is called directly on the document ($document->move())
        // instead of using the $ktapi->move_document() function
        //
        // Additionally testDocument fails on 3 tests whether API Signatures are on or off:
        // Method: testGetMetadata
        $this->addFile('api/testDocument.php');
        $this->addFile('api/testFolder.php');

        $this->addFile('api/testBulkActions.php');
        $this->addFile('api/testCollection.php');

        // While the original tests for esignatures have been set up to work
        // if Electronic Signatures are NOT enabled for the API, new tests may not
        // include the check which allows the tests to be bypassed when esignatures
        // are not on, so if you have failures, check there first :)
        $this->addFile('api/testElectronicSignatures.php');

//        $this->addFile('SQLFile/test_sqlfile.php');
//        $this->addFile('cache/testCache.php');
//        $this->addFile('config/testConfig.php');
//        $this->addFile('document/testDocument.php');
//        $this->addFile('document/testDocumentUtil.php');
//        $this->addFile('folder/testFolder.php');
//        $this->addFile('browseutil/testBrowseUtil.php');
//        $this->addFile('filelike/testStringFileLike.php');

        // Search (2) and indexing
//        $this->addFile('documentProcessor/testExtracters.php');
//        $this->addFile('documentProcessor/testGuidInserter.php');
//        $this->addFile('search2/testSearch.php');
    }
}

$test = &new UnitTests();
if (SimpleReporter::inCli()) {
    exit ($test->run(new KTTextReporter()) ? 0 : 1);
}

// pass parameter ?show=all to display all passes
$param = (isset($_REQUEST['show']) && $_REQUEST['show'] == 'all') ? true : false;
$test->run(new KTHtmlReporter($param));

?>