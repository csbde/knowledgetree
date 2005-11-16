<?php

require_once(KT_LIB_DIR . "/ktentity.inc");
require_once(KT_LIB_DIR . "/dashboard/NotificationRegistry.inc.php");


/**
 * class Notification
 *
 * Represents a basic message, about an item, to a user.  This ends up on the dashboard.
 */
class KTNotification extends KTEntity {
    /** primary key value */
    var $iId = -1;
    var $iUserId;
    
    // sType and sLabel provide the title of the dashboard alert.
    var $sLabel;             // a simple label - e.g. the document's title, or so forth.
    var $sType;              // namespaced item type. (e.g. ktcore/subscriptions, word/officeupload)
                             // this is used to create the appropriate renderobj.

    var $dCreationTime = null; // the date/time of this items creation.

    // iData1 and iData2 and integers, which can be used for whatever.
    // sData1 and sData2 are similar.
    // (i.e. you get very stupid subclassing semantics with up to 4 variables this way.
    var $iData1;
    var $iData2;
    var $sData1;
    var $sData2;    
    
    var $_bUsePearError = true;
    
    function getId() { return $this->iId; }
    
    function getLabel() { return $this->sLabel; }    
    function setLabel($sLabel) { $this->sLabel = $sLabel; }
    function getType() { return $this->sType; }    
    function setType($sType) { $this->sType = $sType; }
    
    function getIntData1() { return $this->iData1; }    
    function setIntData1($iData1) { $this->iData1 = $iData1; }
    function getIntData2() { return $this->iData2; }    
    function setIntData2($iData2) { $this->iData2 = $iData2; }
    function getStrData1() { return $this->sData1; }    
    function setStrData1($sData1) { $this->sData1 = $sData1; }
    function getStrData2() { return $this->sData2; }    
    function setStrData2($sData2) { $this->sData2 = $sData2; }    

    var $_aFieldToSelect = array(
        "iId" => "id",
        "iUserId" => "user_id",
        "sLabel" => "label",        
        "sType" => "type",
        "dCreationDate" => "creation_date",
        "iData1" => "data_int_1",
        "iData2" => "data_int_2",
        "sData1" => "data_str_1",
        "sData2" => "data_str_2",
        );
    
    function _table () {
        return KTUtil::getTableName('notifications');
    }
	
	function render() {
		$notificationRegistry =& KTNotificationRegistry::getSingleton();
		$handler = $notificationRegistry->getHandler($this->sType);
		return $handler->handleNotification($this);
	}

    // Static function
    function &get($iId) { return KTEntityUtil::get('KTNotification', $iId); }
    function &getList($sWhereClause = null) { return KTEntityUtil::getList2('KTNotification', $sWhereClause);	}	
    function &createFromArray($aOptions) { return KTEntityUtil::createFromArray('KTNotification', $aOptions); }

}

/** register the base handlers. */


$notificationRegistry =& KTNotificationRegistry::getSingleton();

// abstract base-class for notification handler.
class KTNotificationHandler {
    function handleNotification($oKTNotification) {
		$oTemplating = new KTTemplating;
		$oTemplate = $oTemplating->loadTemplate("kt3/notifications/generic");
		$aTemplateData = array(
              "context" => $oKTNotification,
		);
		return $oTemplate->render($aTemplateData);
    }
}

class KTSubscriptionNotification extends KTNotificationHandler {
    function handleNotification($oKTNotification) {
		$oTemplating = new KTTemplating;
		$oTemplate = $oTemplating->loadTemplate("kt3/notifications/subscriptions");
		$aTemplateData = array(
              "context" => $oKTNotification,
		);
		return $oTemplate->render($aTemplateData);
    }
}

$notificationRegistry->registerNotificationHandler("ktcore/subscriptions","KTSubscriptionNotification");

?>
