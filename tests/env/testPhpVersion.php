<?php
require_once (KT_DIR . '/tests/test.php');
require_once (KT_DIR . '/setup//wizard/share/wizardBase.php');
require_once (KT_DIR . '/setup/wizard/installUtil.php');
require_once (KT_DIR . '/setup/wizard/step.php');
require_once (KT_DIR . '/setup/wizard/steps/dependencies.php');

/**
 * These are the unit tests for the KT environment
 *
 * This test can be run at any time to ensure the environment meets the requirements
 * to properly run knowledgetree e.g. http://localhost/tests/env.php
 *
 */
class EnvPhpVersionTestCase extends KTUnitTestCase {

    /**
     * @var oDepends
     */
    var $oDepends;

    /**
     * @var phpVersion
     */
    var $phpVersion;

    /**
     * Setup
     *
     */
    function setUp() {
        $this->oDepends = new dependencies();
    }

    /**
     * Cleanup
     *
     */
    function tearDown() {
      $this->oDepends = null;
    }

    /**
     * Testing PHP Version
     */
    function testPhpVersion()
    {
        $this->phpVersion = $this->oDepends->checkPhpVersion();
	$this->assertEqual($this->phpVersion['class'], 'tick');
    }

}
?>