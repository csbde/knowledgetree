<?php

require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

class BrowseableFolderDashlet extends KTBaseDashlet {
	var $oUser;
	
	function is_active($oUser) {
		$this->oUser = $oUser;
		
		return true;
	}
	
    function render() {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('browseabledashlet/dashlet');

        $aFolders = KTBrowseUtil::getBrowseableFolders($this->oUser);
        if (PEAR::isError($aFolders)) { 
            // just hide it.
            $aFolders = array();
        }
        
        $aTemplateData = array(
            'folders' => $aFolders,
        );
        return $oTemplate->render($aTemplateData);
    }
}

?>