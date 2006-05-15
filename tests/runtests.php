<?php

require_once('test.php');

class UnitTests extends GroupTest {
    function UnitTests() {
        $this->GroupTest('Unit tests');
        $this->addTestFile('SQLFile/test_sqlfile.php');
        $this->addTestFile('cache/testCache.php');
        $this->addTestFile('config/testConfig.php');
        $this->addTestFile('document/testDocument.php');
    }

    function addTestFile($file) {
        if (!KTUtil::isAbsolutePath($file)) {
            $file = sprintf('%s/%s', dirname(__FILE__), $file);
        }
        return parent::addTestFile($file);
    }
}

$test = &new UnitTests();
if (SimpleReporter::inCli()) {
    exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());

