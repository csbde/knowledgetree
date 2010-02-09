<?php
require_once (KT_DIR . '/tests/test.php');
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
class EnvPhpConfigTestCase extends KTUnitTestCase {

    /**
     * @var oDepends
     */
    var $oDepends;

    /**
     * @var arrConfigActual
     */
    var $arrConfigActual;

    /**
     * @var arrConfigRecommended
     */
    var $arrConfigRecommended;

    /**
     * @var safeMode
     */
    var $safeMode;

    /**
     * @var fileUploads
     */
    var $fileUploads;

    /**
     * @var magicQuotesGPC
     */
    var $magicQuotesGPC;

    /**
     * @var magicQuotesRuntime
     */
    var $magicQuotesRuntime;

    /**
     * @var registerGlobals
     */
    var $registerGlobals;

    /**
     * @var outputBuffering
     */
    var $outputBuffering;

    /**
     * @var sessionAutoStart
     */
    var $sessionAutoStart;

    /**
     * @var automaticPrependFile
     */
    var $automaticPrependFile;

    /**
     * @var automaticAppendFile
     */
    var $automaticAppendFile;

    /**
     * @var openBaseDirectory
     */
    var $openBaseDirectory;

    /**
     * @var defaultMimetype
     */
    var $defaultMimetype;

    /**
     * @var maximumPostSize
     */
    var $maximumPostSize;

    /**
     * @var maximumUploadSize
     */
    var $maximumUploadSize;

    /**
     * @var memoryLimit
     */
    var $memoryLimit;


    /**
     * Setup the php configs to initialize the system checks.
     *
     */
    function setUp() {
      $this->oDepends = new dependencies();
      $this->arrConfigRecommended = $this->oDepends->getConfigurations();
      $this->arrConfigLimits = $this->oDepends->getLimits();
      $this->arrConfigActual = $this->oDepends->checkPhpConfiguration();
    }

    /**
     * Cleaning up
     */
    function tearDown() {
      $this->oDepends = null;
    }

    function getRecommendedConfig($key) {
      foreach ($this->arrConfigRecommended as $conf) {
        if ($conf['configuration'] == $key) {
          return $conf;
        }
      }

      foreach ($this->arrConfigLimits as $conf) {
        if ($conf['configuration'] == $key) {
          return $conf;
        }
      }

      return false;
    }


    function getActualConfig($key) {
      foreach ($this->arrConfigActual as $conf) {
        if ($conf['configuration'] == $key) {
          return $conf;
        }
      }
      return false;
    }

    /**
     * Testing safeMode
     */
    function testSafeMode() {
      $key = 'safe_mode';
      $actualConfig = $this->getActualConfig($key);
      $requiredConfig = $this->getRecommendedConfig($key);
      $this->assertEqual($actualConfig['setting'], $requiredConfig['recommended']);
    }

    /**
     * Testing fileUploads
     */
    function testFileUploads() {
      $key = 'file_uploads';
      $actualConfig = $this->getActualConfig($key);
      $requiredConfig = $this->getRecommendedConfig($key);
      $this->assertEqual($actualConfig['setting'], $requiredConfig['recommended']);
    }

    /**
     * Testing magicQuotesGPC
     */
    function testMagicQuotesGPC() {
      $key = 'magic_quotes_gpc';
      $actualConfig = $this->getActualConfig($key);
      $requiredConfig = $this->getRecommendedConfig($key);
      $this->assertEqual($actualConfig['setting'], $requiredConfig['recommended']);
    }

    /**
     * Testing magicQuotesRuntime
     */
    function testMagicQuotesRuntime() {
      $key = 'magic_quotes_runtime';
      $actualConfig = $this->getActualConfig($key);
      $requiredConfig = $this->getRecommendedConfig($key);
      $this->assertEqual($actualConfig['setting'], $requiredConfig['recommended']);
    }

    /**
     * Testing registerGlobals
     */
    function testRegisterGlobals() {
      $key = 'register_globals';
      $actualConfig = $this->getActualConfig($key);
      $requiredConfig = $this->getRecommendedConfig($key);
      $this->assertEqual($actualConfig['setting'], $requiredConfig['recommended']);
    }

    /**
     * Testing outputBuffering
     */
    function testOutputBuffering() {
      $key = 'output_buffering';
      $actualConfig = $this->getActualConfig($key);
      $requiredConfig = $this->getRecommendedConfig($key);
      $this->assertEqual($actualConfig['setting'], $requiredConfig['recommended']);
    }

    /**
     * Testing sessionAutoStart
     */
    function testSessionAutoStart() {
      $key = 'session.auto_start';
      $actualConfig = $this->getActualConfig($key);
      $requiredConfig = $this->getRecommendedConfig($key);
      $this->assertEqual($actualConfig['setting'], $requiredConfig['recommended']);
    }

    /**
     * Testing automaticPrependFile
     */
    function testAutomaticPrependFile() {
      $key = 'auto_prepend_file';
      $actualConfig = $this->getActualConfig($key);
      $requiredConfig = $this->getRecommendedConfig($key);
      $this->assertEqual($actualConfig['setting'], $requiredConfig['recommended']);
    }

    /**
     * Testing automaticAppendFile
     */
    function testAutomaticAppendFile() {
      $key = 'auto_append_file';
      $actualConfig = $this->getActualConfig($key);
      $requiredConfig = $this->getRecommendedConfig($key);
      $this->assertEqual($actualConfig['setting'], $requiredConfig['recommended']);
    }

    /**
     * Testing openBaseDirectory
     */
    function testOpenBaseDirectory() {
      $key = 'open_basedir';
      $actualConfig = $this->getActualConfig($key);
      $requiredConfig = $this->getRecommendedConfig($key);
      $this->assertEqual($actualConfig['setting'], $requiredConfig['recommended']);
    }

    /**
     * Testing defaultMimetype
     */
    function testDefaultMimetype() {
      $key = 'default_mimetype';
      $actualConfig = $this->getActualConfig($key);
      $requiredConfig = $this->getRecommendedConfig($key);
      $this->assertEqual($actualConfig['setting'], $requiredConfig['recommended']);
    }

    function to_byte($size) {
      $res = preg_match_all('/[a-z]/isU', $size, $matches, PREG_PATTERN_ORDER);
      $indicator = $matches[0][0];
      if ($indicator == '') return false;

      switch ($indicator) {
        case 'B':
          return $size;
        case 'KB':
          return $size * (1024);
        case 'K':
          return $size * (1024);
        case 'M':
          return $size * (1024 * 1024);
        case 'MB':
          return $size * (1024 * 1024);
        case 'G':
          return $size * (1024 * 1024 * 1024);
        case 'GB':
          return $size * (1024 * 1024 * 1024);
        case 'T':
          return $size * (1024 * 1024 * 1024 * 1024);
        case 'TB':
          return $size * (1024 * 1024 * 1024 * 1024);
        case 'P':
          return $size * (1024 * 1024 * 1024 * 1024 * 1024);
        case 'PB':
          return $size * (1024 * 1024 * 1024 * 1024 * 1024);
        case 'E':
          return $size * (1024 * 1024 * 1024 * 1024 * 1024 * 1024);
        case 'EB':
          return $size * (1024 * 1024 * 1024 * 1024 * 1024 * 1024);
        case 'Z':
          return $size * (1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024);
        case 'ZB':
          return $size * (1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024);
        case 'Y':
          return $size * (1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024);
        case 'YB':
          return $size * (1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024);
        default:
          return $size;

      }

      //return $size ? round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i] : '0 Bytes';
    }

    /**
     * Testing maximumPostSize
     */
    function testMaximumPostSize() {
      $key = 'post_max_size';
      $actualConfig = $this->getActualConfig($key);
      $requiredConfig = $this->getRecommendedConfig($key);

      $byteSetting = $this->to_byte($actualConfig['setting']);
      $byteRecommended = $this->to_byte($requiredConfig['recommended']);
      //Testing that the byte conversion passed
      $this->assertNotEqual($byteSetting, false, 'Could Not Convert Actual Setting to Bytes [' . $actualConfig['setting'] . ']');
      $result = ($byteSetting >= $byteRecommended);
      $this->assertTrue($result, 'current php.ini conf value for '.$key.' ['.$actualConfig['setting'].'] is too small. The recommended size is ' . $requiredConfig['recommended']);
    }

    /**
     * Testing maximumUploadSize
     */
    function testMaximumUploadSize() {
      $key = 'upload_max_filesize';
      $actualConfig = $this->getActualConfig($key);
      $requiredConfig = $this->getRecommendedConfig($key);

      $byteSetting = $this->to_byte($actualConfig['setting']);
      $byteRecommended = $this->to_byte($requiredConfig['recommended']);
      //Testing that the byte conversion passed
      $this->assertNotEqual($byteSetting, false, 'Could Not Convert Actual Setting to Bytes [' . $actualConfig['setting'] . ']');
      $result = ($byteSetting >= $byteRecommended);
      $this->assertTrue($result, 'current php.ini conf value for '.$key.' ['.$actualConfig['setting'].'] is too small. The recommended size is ' . $requiredConfig['recommended']);
    }

    /**
     * Testing memoryLimit
     */
    function testMemoryLimit() {
      $key = 'memory_limit';
      $actualConfig = $this->getActualConfig($key);
      $requiredConfig = $this->getRecommendedConfig($key);

      $byteSetting = $this->to_byte($actualConfig['setting']);
      $byteRecommended = $this->to_byte($requiredConfig['recommended']);
      //Testing that the byte conversion passed
      $this->assertNotEqual($byteSetting, false, 'Could Not Convert Actual Setting to Bytes [' . $actualConfig['setting'] . ']');
      $result = ($byteSetting >= $byteRecommended);
      $this->assertTrue($result, 'current php.ini conf value for '.$key.' ['.$actualConfig['setting'].'] is too small. The recommended size is ' . $requiredConfig['recommended']);
    }

}
?>