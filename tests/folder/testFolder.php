<?php

require_once(dirname(__FILE__) . '/../test.php');
require_once(KT_LIB_DIR . '/foldermanagement/folderutil.inc.php');
require_once(KT_LIB_DIR . '/import/fsimportstorage.inc.php');
require_once(KT_LIB_DIR . '/import/bulkimport.inc.php');

class FolderTestCase extends KTUnitTestCase {
    function setup() {
        $oRootFolder =& Folder::get(1);
        $this->oUser = User::get(1);
        $sName = 'FolderTest' . strftime('%Y%m%d%H%M%S');
        $this->oFolder =& KTFolderUtil::add($oRootFolder, $sName, $this->oUser);
    }

    function tearDown() {
        $aOptions = array('ignore_permissions' => true);
        KTFolderUtil::delete($this->oFolder, $this->oUser, 'test case', $aOptions);
    }

    function testAdd() {
	$oFolder =& KTFolderUtil::add($this->oFolder, 'testAdd', $this->oUser);

        $this->assertEntity($oFolder, 'Folder');
        $this->assertEqual($oFolder->getName(), 'testAdd');
        $this->assertEqual($oFolder->getParentID(), $this->oFolder->getId());
        $this->assertEqual($oFolder->getCreatorID(), $this->oUser->getId());
    }

    function testMove() {
	$oTestFolder = KTFolderUtil::add($this->oFolder, 'testMoveFolder', $this->oUser);
	$this->assertNotError($oTestFolder);
    if(PEAR::isError($oTestFolder)) return;
	$this->assertEntity($oTestFolder, 'Folder');

	$oSrcFolder = KTFolderUtil::add($this->oFolder, 'testMoveSrcFolder', $this->oUser);
	$this->assertNotError($oSrcFolder);
    if(PEAR::isError($oSrcFolder)) return;
	$this->assertEntity($oSrcFolder, 'Folder');

	$oFS =& new KTFSImportStorage(KT_DIR . "/tests/folder/move-dataset");
	$oBM =& new KTBulkImportManager($oSrcFolder, $oFS, $this->oUser);
	$this->assertNotError($oBM);
    if(PEAR::isError($oBM)) return;

	//$res = $oBM->import();
	//$this->assertNotError($res);
    //if(PEAR::isError($res)) return;

	$oDstFolder = KTFolderUtil::add($oTestFolder, 'testMoveDstFolder', $this->oUser);
	$this->assertNotError($oDstFolder);
    if(PEAR::isError($oDstFolder)) return;
	$this->assertEntity($oDstFolder, 'Folder');

	$res = KTFolderUtil::move($oSrcFolder, $oDstFolder, $this->oUser);
	$this->assertNotError($res);
    if(PEAR::isError($res)) return;
	$this->assertEqual($oSrcFolder->getParentID(), $oDstFolder->getID());
    }
}
