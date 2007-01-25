<?php
require_once(KT_LIB_DIR . '/actions/documentaction.inc.php');
require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

require_once(KT_LIB_DIR . '/roles/Role.inc');
require_once(KT_DIR. '/plugins/rssplugin/KTrss.inc.php');

class RSSDocumentLinkAction extends KTDocumentAction {
    var $sName = 'ktcore.rss.plugin.document.link';
    var $_sShowPermission = "ktcore.permissions.read";
    var $sDisplayName = "RSS";
    
    function getDisplayName() {
        //get document object
        $oDocument = $this->oDocument;
        
        // get document id
        $iFId = $oDocument->getID();
        
        // return link...there MIGHT be a nicer way of doing this?
        return "RSS ".KTrss::getImageLink($iFId, 'document');
    }
    
    function do_main() {
    	//get document object
        $oDocument = $this->oDocument;
        
        // get document id
        $iDId = $oDocument->getID();
    	
    	$oTemplating =& KTTemplating::getSingleton();
       	$oTemplate = $oTemplating->loadTemplate('RSSPlugin/rssdocumentaction');
       	
       	$aTemplateData = array(
			'context' => $this,
			'link' => KTrss::getRssLink($iDId, 'document'),
			'linkIcon' => KTrss::getImageLink($iDId, 'document'),
		);
      
        return $oTemplate->render($aTemplateData);
    }
}
?>