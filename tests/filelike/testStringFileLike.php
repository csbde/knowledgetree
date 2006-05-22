<?php

require_once(dirname(__FILE__) . "/../test.php");
require_once(KT_LIB_DIR . '/filelike/stringfilelike.inc.php');

class StringFileLikeTestCase extends KTUnitTestCase {
    function testRead() {
        $sContents = "asdf";
        $f = new KTStringFileLike($sContents);
        $sBack = $f->read(64);
    }

    function testEof() {
        $sContents = "asdf";
        $f = new KTStringFileLike($sContents);
        $sBack = $f->read(64);
        $this->assertEqual($f->eof(), true);
    }

    function testEof2() {
        $sContents = "asdf";
        $f = new KTStringFileLike($sContents);
        $sBack = $f->read(3);
        $this->assertEqual($f->eof(), false);
    }

    function testEof3() {
        $sContents = "asdf";
        $f = new KTStringFileLike($sContents);
        $sBack = $f->read(4);
        $this->assertEqual($f->eof(), true);
    }

    function testShortRead() {
        $sContents = "asdf";
        $f = new KTStringFileLike($sContents);
        $sBack = $f->read(3);
        $this->assertEqual($sBack, 'asd');
    }

    function testGetContents() {
        $sContents = "asdf";
        $f = new KTStringFileLike($sContents);
        $sBack = $f->get_contents();
        $this->assertEqual($sBack, $sContents);
    }

    function testCheckPos() {
        $f = new KTStringFileLike($sContents);
        $sBack = $f->get_contents();
        $this->assertEqual($sBack, $sContents);
    }
}

?>
