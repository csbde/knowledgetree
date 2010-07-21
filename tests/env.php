<?php

require_once('test.php');

class UnitTests extends TestSuite {
    function UnitTests() {

        $this->TestSuite('Unit tests (Environment)');

        // Test PHP Version

        $this->addFile('env/testPhpVersion.php');

        // Test PHP Extensions
        $this->addFile('env/testPhpExtensions.php');

        // Test PHP Configurations
        $this->addFile('env/testPhpConfigurations.php');

        // Test System Configurations
        $this->addFile('env/testSystemConfigurations.php');
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
