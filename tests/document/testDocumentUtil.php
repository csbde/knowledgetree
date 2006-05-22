<?php

require_once(dirname(__FILE__) . '/../test.php');
require_once(KT_LIB_DIR . '/foldermanagement/folderutil.inc.php');

class DocumentUtilTestCase extends KTUnitTestCase {

    function setup() {
        $oRootFolder =& Folder::get(1);
        $this->oUser = User::get(1);
        $sName = 'DocumentUtilTest' . strftime('%Y%m%d%H%M%S');
        $this->oFolder =& KTFolderUtil::add($oRootFolder, $sName, $this->oUser);
    }

    function tearDown() {
        $aOptions = array('ignore_permissions' => true);
        KTFolderUtil::delete($this->oFolder, $this->oUser, 'test case', $aOptions);
    }
}
