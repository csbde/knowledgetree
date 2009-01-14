<?php

require_once('test.php');

class UnitTests extends TestSuite {
    function UnitTests() {

        $this->TestSuite('Unit tests');
        $this->addFile('api/testAuthentication.php');
        $this->addFile('api/testDocument.php');
        $this->addFile('api/testFolder.php');
        $this->addFile('SQLFile/test_sqlfile.php');
        $this->addFile('cache/testCache.php');
        $this->addFile('config/testConfig.php');
        $this->addFile('document/testDocument.php');
        $this->addFile('document/testDocumentUtil.php');
//        $this->addFile('folder/testFolder.php');
//        $this->addFile('browseutil/testBrowseUtil.php');
//        $this->addFile('filelike/testStringFileLike.php');

        $this->addFile('documentProcessor/testExtracters.php');
//        $this->addFile('documentProcessor/testIndexer.php');
        $this->addFile('documentProcessor/testGuidInserter.php');
        $this->addFile('search2/testSearch.php');
    }
}

$test = &new UnitTests();
if (SimpleReporter::inCli()) {
    exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());

?>