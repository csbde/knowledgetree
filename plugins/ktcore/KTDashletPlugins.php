<?php

require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/dashboard/dashlet.inc.php');
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dashboard/Notification.inc.php");
require_once(KT_LIB_DIR . "/security/Permission.inc");


$oRegistry =& KTPluginRegistry::getSingleton();
$oPlugin =& $oRegistry->getPlugin('ktcore.plugin');

/* 
* The registration hooks. 
* 
* Since this is too small to _actually_ need a full plugin object, we go:
*
*/

// ultra simple skeleton for the admin tutorial
class KTBeta1InfoDashlet extends KTBaseDashlet {
	function is_active($oUser) {
		return true;
	}
	
    function render() {
		$oTemplating = new KTTemplating;
		$oTemplate = $oTemplating->loadTemplate("ktcore/dashlets/beta1info");
		$aTemplateData = array(
		);
		return $oTemplate->render($aTemplateData);
    }
}

$oPlugin->registerDashlet('KTBeta1InfoDashlet', 'ktcore.dashlet.beta1info', __FILE__);

class KTNotificationDashlet extends KTBaseDashlet {

	var $oUser;
	
	function is_active($oUser) {
		$this->oUser = $oUser;
		
		return true;
	}
	
    function render() {
	    
        $notifications = KTNotification::getList(array("user_id = ?", $this->oUser->getId()));
        
		$oTemplating = new KTTemplating;
		$oTemplate = $oTemplating->loadTemplate("ktcore/dashlets/notifications");
		$aTemplateData = array(
		    "notifications" => $notifications,
		);
		return $oTemplate->render($aTemplateData);
    }
}


$oPlugin->registerDashlet('KTNotificationDashlet', 'ktcore.dashlet.notifications', __FILE__);

// replace the old checked-out docs.
class KTCheckoutDashlet extends KTBaseDashlet {

	var $oUser;
	
	function is_active($oUser) {
		$this->oUser = $oUser;
		return true;
	}
	
    function render() {
	    
        $checked_out_documents = Document::getList(array("checked_out_user_id = ?", $this->oUser->getId()));
        
		$oTemplating = new KTTemplating;
		$oTemplate = $oTemplating->loadTemplate("ktcore/dashlets/checkedout");
		$aTemplateData = array(
		    "documents" => $checked_out_documents,
		);
		return $oTemplate->render($aTemplateData);
    }
}

$oPlugin->registerDashlet('KTCheckoutDashlet', 'ktcore.dashlet.checkout', __FILE__);


?>