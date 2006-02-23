<?php

require_once(KT_LIB_DIR . '/dashboard/dashlet.inc.php');
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dashboard/Notification.inc.php");
require_once(KT_LIB_DIR . "/security/Permission.inc");

// ultra simple skeleton for the admin tutorial
class KTBeta1InfoDashlet extends KTBaseDashlet {
	function is_active($oUser) {
		return true;
	}
	
    function render() {
		$oTemplating =& KTTemplating::getSingleton();
		$oTemplate = $oTemplating->loadTemplate("ktcore/dashlets/beta1info");
		$aTemplateData = array(
		);
		return $oTemplate->render($aTemplateData);
    }
}


class KTNotificationDashlet extends KTBaseDashlet {

	var $oUser;
	
	function is_active($oUser) {
		$this->oUser = $oUser;
		
		return true;
	}
	
    function render() {
	    
        $notifications = KTNotification::getList(array("user_id = ?", $this->oUser->getId()));
        $num_notifications = count($notifications);
		
		$_MAX_NOTIFICATIONS = 5;
		
		// FIXME in lieu of pagination, we slice.
		if ($num_notifications > $_MAX_NOTIFICATIONS) {
		    $notifications = array_slice($notifications, 0, $_MAX_NOTIFICATIONS);
		}
        
		$oTemplating =& KTTemplating::getSingleton();
		$oTemplate = $oTemplating->loadTemplate("ktcore/dashlets/notifications");
		$aTemplateData = array(
		    "notifications" => $notifications,
			"notification_count" => $num_notifications,
			"visible_count" => count($notifications),
		);
		return $oTemplate->render($aTemplateData);
    }
}



// replace the old checked-out docs.
class KTCheckoutDashlet extends KTBaseDashlet {

	var $oUser;
	
	function is_active($oUser) {
		$this->oUser = $oUser;
		return true;
	}
	
	function getDocumentLink($oDocument) {
		return generateControllerLink('viewDocument', 'fDocumentId=' . $oDocument->getId());
	}
	
    function render() {
	    
        $checked_out_documents = Document::getList(array("checked_out_user_id = ?", $this->oUser->getId()));
        
		$oTemplating =& KTTemplating::getSingleton();
		$oTemplate = $oTemplating->loadTemplate("ktcore/dashlets/checkedout");
		$aTemplateData = array(
		    "context" => $this,
		    "documents" => $checked_out_documents,
		);
		return $oTemplate->render($aTemplateData);
    }
}



?>
