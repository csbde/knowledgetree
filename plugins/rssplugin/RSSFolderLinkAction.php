<?php
require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');
require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

require_once(KT_LIB_DIR . '/roles/Role.inc');

class RSSFolderLinkAction extends KTFolderAction {
    var $sName = 'ktcore.rss.plugin.folder.link';
    var $_sShowPermission = "ktcore.permissions.read";
    var $sDisplayName = "RSS";
    
    function getDisplayName() {
        // built server path
        $hostPath = "http://".$_SERVER['HTTP_HOST']."/".$GLOBALS['KTRootUrl']."/";
        
        //get folder object
        $oFolder = $this->oFolder;
        
        // get folder id
        $iFId = $oFolder->getID();
        
        // return link...there MIGHT be a nicer way of doing this?
        return "<a href='".KTBrowseUtil::buildBaseUrl('rss')."?folderId=".$iFId."'><img src='".$hostPath."resources/graphics/rss.gif' alt='RSS' border=0/></a>";
    }
}
?>