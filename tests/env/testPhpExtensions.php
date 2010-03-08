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
class EnvPhpExtensionsTestCase extends KTUnitTestCase {

    /**
     * @var oDepends
     */
    var $oDepends;

    /**
     * @var arrConfigActual
     */
    var $arrRequiredExtensions;

    /**
     * Setup the php configs to initialize the system checks.
     *
     */
    function setUp() {
      $this->oDepends = new dependencies();
      $this->arrRequiredExtensions = $this->oDepends->getRequiredExtensions();
    }

    /**
     * Cleaning up
     */
    function tearDown() {
      $this->oDepends = null;
    }

    /**
     * Testing extensions
     */
    function testExtensions() {
      foreach ($this->arrRequiredExtensions as $ext) {
            $ext['available'] = 'no';
            if($this->oDepends->checkExtension($ext['extension'])){
                $ext['available'] = 'yes';
            } else {
                if($ext['required'] == 'no') {
                    $errorMsg = '['.$ext['extension'].'] Missing optional extension: '.$ext['name'];
                } else {
                    $errorMsg = '['.$ext['extension'].'] Missing required extension: '.$ext['name'];
                }
            }
        $this->assertEqual($ext['available'], 'yes', $errorMsg);
      }
    }


    public function getExtension($key) {
      foreach ($this->arrRequiredExtensions as $ext) {
        if ($ext['extension'] == $key) {
          return $ext;
        }
      }
      return false;
    }

    /**
     * Testing IconV
     */
    function testIconV() {
      $key = 'iconv';
      $ext = $this->getExtension($key);
      $errorMsg = '['.$ext['extension'].'] Missing optional extension: '.$ext['name'];
      $this->assertEqual($this->oDepends->checkExtension($ext['extension']), true, $errorMsg);
    }

    /**
     * Testing MySQL
     */
    function testMySQL() {
      $key = 'mysql';
      $ext = $this->getExtension($key);
      $errorMsg = '['.$ext['extension'].'] Missing required extension: '.$ext['name'];
      $this->assertEqual($this->oDepends->checkExtension($ext['extension']), true, $errorMsg);
    }

    /**
     * Testing cURL
     */
    function testcURL() {
      $key = 'curl';
      $ext = $this->getExtension($key);
      $errorMsg = '['.$ext['extension'].'] Missing required extension: '.$ext['name'];
      $this->assertEqual($this->oDepends->checkExtension($ext['extension']), true, $errorMsg);
    }

    /**
     * Testing XMLRPC
     */
    function testXMLRPC() {
      $key = 'xmlrpc';
      $ext = $this->getExtension($key);
      $errorMsg = '['.$ext['extension'].'] Missing required extension: '.$ext['name'];
      $this->assertEqual($this->oDepends->checkExtension($ext['extension']), true, $errorMsg);
    }


    /**
     * Testing Multi Byte Strings
     */
    function testMultiByteStrings() {
      $key = 'mbstring';
      $ext = $this->getExtension($key);
      $errorMsg = '['.$ext['extension'].'] Missing optional extension: '.$ext['name'];
      $this->assertEqual($this->oDepends->checkExtension($ext['extension']), true, $errorMsg);
    }

    /**
     * Testing LDAP
     */
    function testLDAP() {
      $key = 'ldap';
      $ext = $this->getExtension($key);
      $errorMsg = '['.$ext['extension'].'] Missing optional extension: '.$ext['name'];
      $this->assertEqual($this->oDepends->checkExtension($ext['extension']), true, $errorMsg);
    }

    /**
     * Testing JSON
     */
    function testJSON() {
      $key = 'json';
      $ext = $this->getExtension($key);
      $errorMsg = '['.$ext['extension'].'] Missing required extension: '.$ext['name'];
      $this->assertEqual($this->oDepends->checkExtension($ext['extension']), true, $errorMsg);
    }

    /**
     * Testing Open SSL
     */
    function testOpenSSL() {
      $key = 'openssl';
      $ext = $this->getExtension($key);
      $errorMsg = '['.$ext['extension'].'] Missing optional extension: '.$ext['name'];
      $this->assertEqual($this->oDepends->checkExtension($ext['extension']), true, $errorMsg);
    }

}
?>