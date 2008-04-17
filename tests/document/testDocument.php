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

    function &_addFile($sFileName, $aOptions = null) {
	if(!is_array($aOptions)) $aOptions = array();
	$oDocument =& KTDocumentUtil::add($this->oFolder, $sFileName, $this->oUser, $aOptions);
	return $oDocument;
    }

/*    function testAddInOneGo() {
        $sLocalname = dirname(__FILE__) . '/dataset1/critique-of-pure-reason.txt';
        $sFilename = tempnam("/tmp", "kt_tests_document_add");
        copy($sLocalname, $sFilename);

        $oDocument =& $this->_addFile('testAddInOneGo.txt', array(
            'contents' => new KTFSFileLike($sFilename),
            'metadata' => array(),
        )); 

        $res = $this->assertEntity($oDocument, 'Document');
        if ($res === false) {
            return;
        }
        $res = $this->assertEqual($oDocument->getStatusId(), 1);
        if ($res === false) {
            return;
        }

        $oStorage =& KTStorageManagerUtil::getSingleton();
        $sTmpFile = $oStorage->temporaryFile($oDocument);
        $sLocalContents = file_get_contents($sLocalname);
        $sStoredContents = file_get_contents($sTmpFile);
        $oStorage->freeTemporaryFile($sTmpFile);

        $res = $this->assertEqual($sLocalContents, $sStoredContents);
        if ($res === false) {
            return;
        }

        $oDocument =& KTDocumentUtil::add($this->oFolder, "testaddinonego.txt", $this->oUser, array(
            'contents' => new KTFSFileLike($sFilename),
        ));
        $res = $this->assertEntity($oDocument, 'PEAR_Error');
        if ($res === false) {
            return;
        }
    }

    function testAddSeparately() {
        $oDocument =& $this->_addFile("testAddSeparately.txt");
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

    // Ok, not a test yet.
    function testCreateMetadataVersion() {
	$oDocument =& $this->_addFile("testCreateMetadataVersion.txt");
	$res = KTDocumentUtil::saveMetadata($oDocument, array());

	$this->assertEqual($res, true);
    }
*/
}
