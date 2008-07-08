<?php

require_once(dirname(__FILE__) . '/../test.php');
require_once(KT_LIB_DIR . '/foldermanagement/folderutil.inc.php');
require_once(KT_LIB_DIR . '/import/fsimportstorage.inc.php');
require_once(KT_LIB_DIR . '/import/bulkimport.inc.php');

class BrowseUtilTestCase extends KTUnitTestCase {
    function setup() {
        $oRootFolder =& Folder::get(1);
        $this->oUser = User::get(1);
        $sName = 'BrowseUtilTest' . strftime('%Y%m%d%H%M%S');
        $this->oFolder =& KTFolderUtil::add($oRootFolder, $sName, $this->oUser);
    }

    function tearDown() {
        $aOptions = array('ignore_permissions' => true);
        KTFolderUtil::delete($this->oFolder, $this->oUser, 'test case', $aOptions);
    }

    function testFolderOrDocument() {
	$oFolder =& KTFolderUtil::add($this->oFolder, 'testFolderOrDocument', $this->oUser);
	$this->assertNotError($oFolder);
    if(PEAR::isError($oFolder)) return;
	$oDocument =& KTDocumentUtil::add($oFolder, 'testFolderOrDocument.txt', $this->oUser, array());
	$this->assertNotError($oDocument);
    if(PEAR::isError($oDocument)) return;
	$sPath = "/Root Folder/" . $this->oFolder->getName() . "/testFolderOrDocument/";


	$aReturn =& KTBrowseUtil::folderOrDocument($sPath . 'testFolderOrDocument.txt');
	$this->assertEqual($aReturn[0], $oFolder->getID());
	$this->assertEqual($aReturn[1], $oDocument->getID());
	$this->assertEqual($aReturn[2], NULL);
	
	$bReturn =& KTBrowseUtil::folderOrDocument($sPath . 'testFolderOrDocument.txt/ktcore.delete');
	$this->assertEqual($bReturn, false);

	$aReturn =& KTBrowseUtil::folderOrDocument($sPath . 'testFolderOrDocument.txt/ktcore.delete', true);
	$this->assertEqual($aReturn[0], $oFolder->getID());
	$this->assertEqual($aReturn[1], $oDocument->getID());
	$this->assertEqual($aReturn[2], 'ktcore.delete');


	$aReturn =& KTBrowseUtil::folderOrDocument($sPath);
	$this->assertEqual($aReturn[0], $oFolder->getID());
	$this->assertEqual($aReturn[1], NULL);
	$this->assertEqual($aReturn[2], NULL);
	
	$bReturn =& KTBrowseUtil::folderOrDocument($sPath . 'ktcore.delete');
	$this->assertEqual($bReturn, false);

	$aReturn =& KTBrowseUtil::folderOrDocument($sPath . 'ktcore.delete', true);
	$this->assertEqual($aReturn[0], $oFolder->getID());
	$this->assertEqual($aReturn[1], NULL);
	$this->assertEqual($aReturn[2], 'ktcore.delete');

    }

    function _getId($sPath) {
	return (int)substr($sPath, strrpos($sPath, '=') + 1);
    }
	
    function testBreadcrumbsForFolder() {
	$oFolder =& KTFolderUtil::add($this->oFolder, 'testBreadcrumbsForFolder', $this->oUser);

	$aBreadcrumbs =& KTBrowseUtil::breadcrumbsForFolder($oFolder, array('final'=>true));

	$this->assertEqual($this->_getId($aBreadcrumbs[0]['url']), 1);
	$this->assertEqual($aBreadcrumbs[0]['name'], 'Folders');
	
	$this->assertEqual($this->_getId($aBreadcrumbs[1]['url']), $this->oFolder->getId());
	$this->assertEqual($aBreadcrumbs[1]['name'], $this->oFolder->getName());
	
	$this->assertNull($aBreadcrumbs[2]['url']);
	$this->assertEqual($aBreadcrumbs[2]['name'], $oFolder->getName());

	
	$aBreadcrumbs =& KTBrowseUtil::breadcrumbsForFolder($oFolder, array('final'=>false));

	$this->assertEqual($this->_getId($aBreadcrumbs[0]['url']), 1);
	$this->assertEqual($aBreadcrumbs[0]['name'], 'Folders');
	
	$this->assertEqual($this->_getId($aBreadcrumbs[1]['url']), $this->oFolder->getId());
	$this->assertEqual($aBreadcrumbs[1]['name'], $this->oFolder->getName());
	
	$this->assertEqual($this->_getId($aBreadcrumbs[2]['url']), $oFolder->getId());
	$this->assertEqual($aBreadcrumbs[2]['name'], $oFolder->getName());

    }
	
    function testBreadcrumbsForDocument() {
	$oFolder =& KTFolderUtil::add($this->oFolder, 'testBreadcrumbsForDocument', $this->oUser);
	$oDocument =& KTDocumentUtil::add($oFolder, 'testBreadcrumbsForDocument.txt', $this->oUser, array());

	$aBreadcrumbs =& KTBrowseUtil::breadcrumbsForDocument($oDocument, array('final'=>true));
	
	$this->assertEqual($this->_getId($aBreadcrumbs[0]['url']), 1);
	$this->assertEqual($aBreadcrumbs[0]['name'], 'Folders');
	
	$this->assertEqual($this->_getId($aBreadcrumbs[1]['url']), $this->oFolder->getId());
	$this->assertEqual($aBreadcrumbs[1]['name'], $this->oFolder->getName());
	
	$this->assertEqual($this->_getId($aBreadcrumbs[2]['url']), $oFolder->getId());
	$this->assertEqual($aBreadcrumbs[2]['name'], $oFolder->getName());

	$this->assertNull($aBreadcrumbs[3]['url']);
	$this->assertEqual($aBreadcrumbs[3]['name'], $oDocument->getName());


	$aBreadcrumbs =& KTBrowseUtil::breadcrumbsForDocument($oDocument, array('final' => false));
	
	$this->assertEqual($this->_getId($aBreadcrumbs[0]['url']), 1);
	$this->assertEqual($aBreadcrumbs[0]['name'], 'Folders');
	
	$this->assertEqual($this->_getId($aBreadcrumbs[1]['url']), $this->oFolder->getId());
	$this->assertEqual($aBreadcrumbs[1]['name'], $this->oFolder->getName());
	
	$this->assertEqual($this->_getId($aBreadcrumbs[2]['url']), $oFolder->getId());
	$this->assertEqual($aBreadcrumbs[2]['name'], $oFolder->getName());

	$this->assertEqual($this->_getId($aBreadcrumbs[3]['url']), $oDocument->getId());
	$this->assertEqual($aBreadcrumbs[3]['name'], $oDocument->getName());
    }
	
    // lots of set up. tbd.
    function testInAdminMode() {
    }

}
