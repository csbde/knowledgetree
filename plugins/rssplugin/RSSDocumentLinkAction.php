<?php
require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');
require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

require_once(KT_LIB_DIR . '/roles/Role.inc');

class RSSDocumentLinkAction extends KTDocumentAction {
    var $sName = 'ktcore.rss.plugin.document.link';
    var $_sShowPermission = "ktcore.permissions.read";
    var $sDisplayName = "RSS";
    
    function getDisplayName() {
        // built server path
        $hostPath = "http://".$_SERVER['HTTP_HOST']."/".$GLOBALS['KTRootUrl']."/";
        
        //get document object
        $oDocument = $this->oDocument;
        
        // get document id
        $iFId = $oDocument->getID();
        
        // return link...there MIGHT be a nicer way of doing this?
        return "<a href='".KTBrowseUtil::buildBaseUrl('rss')."?docId=".$iFId."' target='_blank'><img src='".$hostPath."resources/graphics/rss.gif' alt='RSS' border=0/></a>";
    }
}
?>