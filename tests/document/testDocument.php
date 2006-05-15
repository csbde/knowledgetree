<?php

require_once(dirname(__FILE__) . '/../test.php');
require_once(KT_LIB_DIR . '/foldermanagement/folderutil.inc.php');

class DocumentTestCase extends KTUnitTestCase {
    function setup() {
        $oRootFolder =& Folder::get(1);
        $this->oUser = User::get(1);
        $sName = 'DocumentTest' . strftime('%Y%m%d%H%M%S');
        $this->oFolder =& KTFolderUtil::add($oRootFolder, $sName, $this->oUser);
    }

    function tearDown() {
        $aOptions = array('ignore_permissions' => true);
        KTFolderUtil::delete($this->oFolder, $this->oUser, 'test case', $aOptions);
    }

    function testAddInOneGo() {
        $sLocalname = dirname(__FILE__) . '/dataset1/critique-of-pure-reason.txt';
        $sFilename = tempnam("/tmp", "kt_tests_document_add");
        copy($sLocalname, $sFilename);

        $oDocument =& KTDocumentUtil::add($this->oFolder, "testaddinonego.txt", $this->oUser, array(
            'contents' => new KTFSFileLike($sFilename),
            'metadata' => array(),
        ));
        $this->assertEntity($oDocument, 'Document');
        $this->assertEqual($oDocument->getStatusId(), 1);
        $oStorage =& KTStorageManagerUtil::getSingleton();
        $sTmpFile = $oStorage->temporaryFile($oDocument);
        $sLocalContents = file_get_contents($sLocalname);
        $sStoredContents = file_get_contents($sTmpFile);
        $oStorage->freeTemporaryFile($sTmpFile);
        $this->assertEqual($sLocalContents, $sStoredContents);
        $oDocument =& KTDocumentUtil::add($this->oFolder, "testaddinonego.txt", $this->oUser, array(
            'contents' => new KTFSFileLike($sFilename),
        ));
        $this->assertEntity($oDocument, 'PEAR_Error');
    }

    function testAddSeparately() {
        $oDocument =& KTDocumentUtil::add($this->oFolder, "testAddSeparately.txt", $this->oUser, array());
        $this->assertEntity($oDocument, 'Document');
        $this->assertEqual($oDocument->getStatusId(), 5);

        $sLocalname = dirname(__FILE__) . '/dataset1/critique-of-pure-reason.txt';
        $sFilename = tempnam("/tmp", "kt_tests_document_add");
        copy($sLocalname, $sFilename);

        $res = KTDocumentUtil::storeContents($oDocument, new KTFSFileLike($sFilename));
        $this->assertEqual($oDocument->getStatusId(), 5);
        $res = KTDocumentUtil::saveMetadata($oDocument, array());
        $this->assertEqual($oDocument->getStatusId(), 1);
    }
}
