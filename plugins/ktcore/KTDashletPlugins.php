<?php

require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/dashboard/dashlet.inc.php');
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dashboard/Notification.inc.php");

/* 
* The registration hooks. 
* 
* Since this is too small to _actually_ need a full plugin object, we go:
*
*/

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

$oRegistry =& KTPluginRegistry::getSingleton();
$oPlugin =& $oRegistry->getPlugin('ktcore.plugin');

$oPlugin->registerDashlet('KTNotificationDashlet', 'ktcore.notifications.dashlet', __FILE__);


?>