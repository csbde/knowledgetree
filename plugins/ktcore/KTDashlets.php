<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

require_once(KT_LIB_DIR . '/dashboard/dashlet.inc.php');
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dashboard/Notification.inc.php");
require_once(KT_LIB_DIR . "/security/Permission.inc");

require_once(KT_LIB_DIR . '/help/help.inc.php');
require_once(KT_LIB_DIR . '/help/helpreplacement.inc.php');

// ultra simple skeleton for the admin tutorial
class KTInfoDashlet extends KTBaseDashlet {
    var $aHelpInfo;
    var $canEdit = false;
    var $helpLocation = 'ktcore/welcome.html';
    var $help_id;

    function is_active($oUser) {
        // FIXME help is a little too mixed.
        $aHelpInfo = array();
        $can_edit = Permission::userIsSystemAdministrator($_SESSION['userID']);
               
        $help_path = KTHelp::_getLocationInfo($this->helpLocation, null, true);
        
        //var_dump($help_path);
        
        if (PEAR::isError($help_path) || empty($help_path['external'])) {
            if ($can_edit) {
                // give it a go.
                $aHelpInfo = KTHelp::getHelpInfo($this->helpLocation);
                if (PEAR::isError($aHelpInfo)) {
                    // otherwise, fail out.
                    $aHelpInfo = array(
                        'title' => _kt('Welcome message'),
                        'body' => _kt('Since you are a system administrator, you can see this message. Please click "edit" below here to create some welcome content, since there is no welcome content available in your language.'),
                    );
                }
            } else {
                return false;
            }
        } else {
            // We now check for substitute help files.  try to generate an error.
            $aHelpInfo = KTHelp::getHelpInfo($this->helpLocation);
        }
        
        // NORMAL users never see edit-option.
        if (!$can_edit) {
            if (empty($aHelpInfo)) {
                return false;
            }
        } 
        
        $this->aHelpInfo = $aHelpInfo;
        $this->canEdit = $can_edit;
        
        return true;
    }
    
    function render() {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/dashlets/kt3release");
        
        
        
        $aTemplateData = array(
            'title' => $this->aHelpInfo['title'],
            'body' => $this->aHelpInfo['body'],
            'can_edit' => $this->canEdit,
            'target_name' => $this->helpLocation,
            'help_id' => $this->aHelpInfo['help_id'],
        );
        return $oTemplate->render($aTemplateData);
    }
}


class KTNotificationDashlet extends KTBaseDashlet {

    var $oUser;
    
    function is_active($oUser) {
        $this->oUser = $oUser;
        $notifications = KTNotification::getList(array("user_id = ?", $this->oUser->getId()));
        if (empty($notifications)) { return false; }
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
        
        $oPluginRegistry =& KTPluginRegistry::getSingleton();
        $oPlugin =& $oPluginRegistry->getPlugin('ktcore.plugin');
        $link = $oPlugin->getPagePath('notifications');
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/dashlets/notifications");
        $aTemplateData = array(
            "notifications" => $notifications,
            "notification_count" => $num_notifications,
            "visible_count" => count($notifications),
            "link_all" => $link,
        );
        return $oTemplate->render($aTemplateData);
    }
}



// replace the old checked-out docs.
class KTCheckoutDashlet extends KTBaseDashlet {

    var $oUser;
    
    function is_active($oUser) {
        $this->oUser = $oUser;
        $this->checked_out_documents = Document::getList(array("checked_out_user_id = ?", $this->oUser->getId()));
        
        return (!empty($this->checked_out_documents));
        return true;
    }
    
    function getDocumentLink($oDocument) {
        return generateControllerLink('viewDocument', 'fDocumentId=' . $oDocument->getId());
    }
    
    function render() {
        
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/dashlets/checkedout");
        $aTemplateData = array(
            "context" => $this,
            "documents" => $this->checked_out_documents,
        );
        return $oTemplate->render($aTemplateData);
    }
}


// replace the old checked-out docs.
class KTIndexerStatusDashlet extends KTBaseDashlet {

    var $aTriggerSet;
    var $noTransforms;

    function is_active($oUser) {
        if (!Permission::userIsSystemAdministrator($oUser)) {
            return false;
        }
        
        require_once(KT_LIB_DIR . '/triggers/triggerregistry.inc.php');
        
        $noTransforms = false;
        
        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('content', 'transform');
        $aTriggerSet = array();
        if (empty($aTriggers)) {
            $noTransforms = true;
        } else {
            foreach ($aTriggers as $aTrigger) {
                $sTrigger = $aTrigger[0];
                if ($aTrigger[1]) {
                    require_once($aTrigger[1]);
                }
                $oTrigger = new $sTrigger;
                
                $sDiagnostic = $oTrigger->getDiagnostic();
                // empty is OK.
                if (is_null($sDiagnostic) || ($sDiagnostic == false)) {
                    continue;
                }
                
                $aTypes = (array) $oTrigger->mimetypes;
                $aTypesStr = array();
                foreach ($aTypes as $sTypeName => $v) {
                    //if ($sTypeName != 'application/octet-stream') { // never use application/octet-stream
                        $aTypesStr[KTMime::getFriendlyNameForString($sTypeName)] = 1;
                    //}
                }
                
                $aTriggerSet[] = array('types' => $aTypesStr, 'diagnostic' => $sDiagnostic);
            }
        }     
        $this->aTriggerSet = $aTriggerSet;
        $this->noTransforms = $noTransforms;   

        return ($noTransforms || !empty($aTriggerSet)); // no diags and have some transforms.
    }
    
    function render() {	
        
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/dashlets/indexer_status");
        $aTemplateData = array(
            "context" => $this,
            "no_transforms" => $this->noTransforms,
            'transforms' => $this->aTriggerSet,
        );
        return $oTemplate->render($aTemplateData);
    }
}

// replace the old checked-out docs.
class KTMailServerDashlet extends KTBaseDashlet {

    function is_active($oUser) {
        $oConfig =& KTConfig::getSingleton();
        $sEmailServer = $oConfig->get('email/emailServer');
        if ($sEmailServer == 'none') {
            return true;
        }
        if (empty($sEmailServer)) {
            return true;
        }
        return false;
    }
    
    function render() {	
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/dashlets/mailserver");
        $admin = Permission::userIsSystemAdministrator($_SESSION['userID']);
        $aTemplateData = array(
            "context" => $this,
            'admin' => $admin,
        );
        return $oTemplate->render($aTemplateData);
    }
}

?>
