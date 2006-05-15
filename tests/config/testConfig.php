<?php

require_once(dirname(__FILE__) . '/../test.php');
require_once(KT_LIB_DIR . '/config/config.inc.php');

class ConfigTestCase extends KTUnitTestCase {
    function testGetFlat() {
        $KTConfig = new KTConfig;
        $KTConfig->loadFile(dirname(__FILE__) . "/foo.ini");
        $aExpectedRet = "asdf";
        $aRet = $KTConfig->get("asdf");
        $this->assertEqual($aExpectedRet, $aRet);
    }

    function testGetNS() {
        $KTConfig = new KTConfig;
        $KTConfig->loadFile(dirname(__FILE__) . "/foo.ini");
        $aExpectedRet = "asdf";
        $aRet = $KTConfig->get("asdf/asdf");
        $this->assertEqual($aExpectedRet, $aRet);
    }

    function testExpandSimple() {
        $KTConfig = new KTConfig;
        $KTConfig->loadFile(dirname(__FILE__) . "/expand.ini");
        $aExpectedRet = "bb";
        $aRet = $KTConfig->get("expand/b");
        $this->assertEqual($aExpectedRet, $aRet);
    }

    function testExpandEmail() {
        $KTConfig = new KTConfig;
        $KTConfig->loadFile(dirname(__FILE__) . "/expand.ini");
        $aExpectedRet = "kt@mail.example.org";
        $aRet = $KTConfig->get("mail/emailFrom");
        $this->assertEqual($aExpectedRet, $aRet);
    }

    function testExpandMulti() {
        $KTConfig = new KTConfig;
        $KTConfig->loadFile(dirname(__FILE__) . "/expand.ini");
        $aExpectedRet = "zxcvasdfzxcvrewqzxcvasdf";
        $aRet = $KTConfig->get("multi/c");
        $this->assertEqual($aExpectedRet, $aRet);
    }

    function testMulti1() {
        $KTConfig = new KTConfig;
        $KTConfig->loadFile(dirname(__FILE__) . "/foo.ini");
        $KTConfig->loadFile(dirname(__FILE__) . "/bar.ini");
        $aExpectedRet = "foo";
        $aRet = $KTConfig->get("bar");
        $this->assertEqual($aExpectedRet, $aRet);
    }

    function testMulti2() {
        $KTConfig = new KTConfig;
        $KTConfig->loadFile(dirname(__FILE__) . "/foo.ini");
        $KTConfig->loadFile(dirname(__FILE__) . "/bar.ini");
        $aExpectedRet = "foo";
        $aRet = $KTConfig->get("bar/bar");
        $this->assertEqual($aExpectedRet, $aRet);
    }

    function testMulti3() {
        $KTConfig = new KTConfig;
        $KTConfig->loadFile(dirname(__FILE__) . "/foo.ini");
        $KTConfig->loadFile(dirname(__FILE__) . "/bar.ini");
        $aExpectedRet = "foo";
        $aRet = $KTConfig->get("bar/asdf");
        $this->assertEqual($aExpectedRet, $aRet);
    }

    function testMulti4() {
        $KTConfig = new KTConfig;
        $KTConfig->loadFile(dirname(__FILE__) . "/foo.ini");
        $KTConfig->loadFile(dirname(__FILE__) . "/bar.ini");
        $aExpectedRet = "asdf";
        $aRet = $KTConfig->get("asdf/asdf");
        $this->assertEqual($aExpectedRet, $aRet);
    }
}

?>
