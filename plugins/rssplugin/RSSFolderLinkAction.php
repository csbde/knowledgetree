<?php
require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');
require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

require_once(KT_LIB_DIR . '/roles/Role.inc');
require_once(KT_DIR. '/plugins/rssplugin/KTrss.inc.php');

class RSSFolderLinkAction extends KTFolderAction {
    var $sName = 'ktcore.rss.plugin.folder.link';
    var $_sShowPermission = "ktcore.permissions.read";
    var $sDisplayName = 'RSS';
    
    function getDisplayName() {
        //get folder object
        $oFolder = $this->oFolder;
        
        // get folder id
        $iFId = $oFolder->getID();
        
        // return link...there MIGHT be a nicer way of doing this?
        return "RSS ".KTrss::getImageLink($iFId, 'folder');
    }
    
    function do_main() {
    	//get folder object
        $oFolder = $this->oFolder;
        
        // get folder id
        $iFId = $oFolder->getID();
    	
    	$oTemplating =& KTTemplating::getSingleton();
       	$oTemplate = $oTemplating->loadTemplate('RSSPlugin/rssfolderaction');
       	
       	$aTemplateData = array(
			'context' => $this,
			'link' => KTrss::getRssLink($iFId, 'folder'),
			'linkIcon' => KTrss::getImageLink($iFId, 'folder'),
		);
      
        return $oTemplate->render($aTemplateData);
    }
}
?>