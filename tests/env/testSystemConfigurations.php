<?php
require_once (KT_DIR . '/tests/test.php');
require_once (KT_DIR . '/setup/wizard/installUtil.php');
require_once (KT_DIR . '/setup/wizard/step.php');
require_once (KT_DIR . '/setup/wizard/steps/configuration.php');

/**
 * These are the unit tests for the KT environment
 *
 * This test can be run at any time to ensure the environment meets the requirements
 * to properly run knowledgetree e.g. http://localhost/tests/env.php
 *
 */
class EnvPhpSystemTestCase extends KTUnitTestCase {

    /**
     * @var oConfig
     */
    var $oConfig;

    /**
     * @var memoryLimit
     */
    var $arrServerInfo;


    /**
     * Setup the php configs to initialize the system checks.
     *
     */
    function setUp() {
      $this->oConfig = new configuration();
      $this->arrServerInfo = $this->oConfig->getServerInfo();
      $this->arrPathInfo = $this->oConfig->getPathInfo($this->arrServerInfo['file_system_root']['value'], false);
    }

    /**
     * Cleaning up
     */
    function tearDown() {
      $this->oConfig = null;
    }

    /**
     * Testing configFile
     */
    function testConfigFile() {
		$oStorage = KTStorageManagerUtil::getSingleton();
      $key = 'configFile';
      $fileValid = ($oStorage->file_exists($this->arrPathInfo[$key]['path']));
      $this->assertTrue($fileValid, 'Config file : [' . $this->arrPathInfo[$key]['path'] . '] could not be found.');
    }

    /**
     * Testing documentRoot
     */
    function testDocumentRoot() {
      $key = 'documentRoot';
      $this->assertEqual($this->arrPathInfo[$key]['class'], 'tick', $this->arrPathInfo[$key]['msg']);
    }

    /**
     * Testing logDirectory
     */
    function testLogDirectory() {
      $key = 'logDirectory';
      $this->assertEqual($this->arrPathInfo[$key]['class'], 'tick', $this->arrPathInfo[$key]['msg']);
    }

    /**
     * Testing tmpDirectory
     */
    function testTmpDirectory() {
      $key = 'tmpDirectory';
      $this->assertEqual($this->arrPathInfo[$key]['class'], 'tick', $this->arrPathInfo[$key]['msg']);
    }

    /**
     * Testing cacheDirectory
     */
    function testCacheDirectory() {
      $key = 'cacheDirectory';
      $this->assertEqual($this->arrPathInfo[$key]['class'], 'tick', $this->arrPathInfo[$key]['msg']);
    }

    /**
     * Testing uploadDirectory
     */
    function testUploadDirectory() {
      $key = 'uploadDirectory';
      $this->assertEqual($this->arrPathInfo[$key]['class'], 'tick', $this->arrPathInfo[$key]['msg']);
    }

    /**
     * Testing varDirectory
     */
    function testvarDirectory() {
      $key = 'varDirectory';
      $this->assertEqual($this->arrPathInfo[$key]['class'], 'tick', $this->arrPathInfo[$key]['msg']);
    }

}
?>