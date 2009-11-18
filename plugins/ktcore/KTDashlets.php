<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
 * California 94120-7775, or email info@knowledgetree.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original
 * copyright notice.
 * Contributor( s): ______________________________________
 *
 */

require_once(KT_LIB_DIR . '/dashboard/dashlet.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/dashboard/Notification.inc.php');
require_once(KT_LIB_DIR . '/security/Permission.inc');

require_once(KT_LIB_DIR . '/help/help.inc.php');
require_once(KT_LIB_DIR . '/help/helpreplacement.inc.php');

// ultra simple skeleton for the admin tutorial
class KTInfoDashlet extends KTBaseDashlet {
    var $aHelpInfo;
    var $canEdit = false;
    var $helpLocation = 'ktcore/welcome.html';
    var $help_id;

    function KTInfoDashlet() {
        global $default;
        $this->sTitle = sprintf(_kt('Welcome to %s'), APP_NAME);
        $versionName = substr($default->versionName, -17);

        if($versionName != 'Community Edition')
        {
        	$this->helpLocation = 'ktcore/welcomeCommercial.html';
        }

	    //This check is for non english language packs which might not have
	    //a commercial welcome page.
	    $oHelpCheck = KTHelp::getHelpInfo($this->helpLocation);
	    if(PEAR::isError($oHelpCheck))
        {
        	$this->helpLocation = 'ktcore/welcome.html';
        }
    }

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
            // We now check for substitute help files.  try to generate an error.\
            // This function can return a PEAR Error object, we will need to check it
            $aHelpInfo = KTHelp::getHelpInfo($this->helpLocation);
            if(PEAR::isError($aHelpInfo))
            {
            	return false;
            }
        }

        // NORMAL users never see edit-option.
        if (!$can_edit) {
            if (empty($aHelpInfo)) {
                return false;
            }
        }

        $this->aHelpInfo = $aHelpInfo;
        $this->canEdit = $can_edit;
        $this->sTitle = str_replace('#APP_NAME#', APP_NAME, $this->aHelpInfo['title']);

        return true;
    }

    function render() {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/dashlets/kt3release');

        $aTemplateData = array(
            'title' => str_replace('#APP_NAME#', APP_NAME, $this->aHelpInfo['title']),
            'body' => str_replace('#APP_NAME#', APP_NAME, $this->aHelpInfo['body']),
            'can_edit' => $this->canEdit,
            'target_name' => $this->helpLocation,
            'help_id' => $this->aHelpInfo['help_id'],
        );
        return $oTemplate->render($aTemplateData);
    }
}


class KTNotificationDashlet extends KTBaseDashlet {
    var $oUser;

    function KTNotificationDashlet() {
        $this->sTitle = _kt('Items that require your attention');
    }

    function is_active($oUser) {
        $this->oUser = $oUser;
        $notifications = KTNotification::getList(array('user_id = ?', $this->oUser->getId()));
        if (empty($notifications)) { return false; }
        return true;
    }

    function render() {

        $notifications = KTNotification::getList(array('user_id = ?', $this->oUser->getId()));
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
        $oTemplate = $oTemplating->loadTemplate('ktcore/dashlets/notifications');
        $aTemplateData = array(
            'notifications' => $notifications,
            'notification_count' => $num_notifications,
            'visible_count' => count($notifications),
            'link_all' => $link,
        );
        return $oTemplate->render($aTemplateData);
    }
}



// replace the old checked-out docs.
class KTCheckoutDashlet extends KTBaseDashlet {

    var $oUser;

    function KTCheckoutDashlet() {
        $this->sTitle = _kt('Your Checked-out Documents');
    }

    function is_active($oUser) {

    	$this->oUser = $oUser;
        $this->checked_out_documents = Document::getList(array('checked_out_user_id = ?', $this->oUser->getId()));

        global $default;
    	$oConfig =& KTConfig::getSingleton();
    	if($oConfig->get('dashboard/alwaysShowYCOD')) return true;

        return (!empty($this->checked_out_documents));
    }

    function getDocumentLink($oDocument) {
        return generateControllerLink('viewDocument', 'fDocumentId=' . $oDocument->getId());
    }

    function render() {


        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/dashlets/checkedout');
        $aTemplateData = array(
            'context' => $this,
            'documents' => $this->checked_out_documents,
        );
        return $oTemplate->render($aTemplateData);
    }
}


// replace the old checked-out docs.
class KTIndexerStatusDashlet extends KTBaseDashlet {

    var $aTriggerSet;
    var $noTransforms;

    function KTIndexerStatusDashlet() {
        $this->sTitle = _kt('Indexer Status');
    }

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
        $oTemplate = $oTemplating->loadTemplate('ktcore/dashlets/indexer_status');
        $aTemplateData = array(
            'context' => $this,
            'no_transforms' => $this->noTransforms,
            'transforms' => $this->aTriggerSet,
        );
        return $oTemplate->render($aTemplateData);
    }
}

// replace the old checked-out docs.
class KTMailServerDashlet extends KTBaseDashlet {
    var $sClass = 'ktError';

    function KTMailServerDashlet() {
        $this->sTitle = _kt('Mail Server Status');
    }

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
        $oTemplate = $oTemplating->loadTemplate('ktcore/dashlets/mailserver');
        $admin = Permission::userIsSystemAdministrator($_SESSION['userID']);
        $aTemplateData = array(
            'context' => $this,
            'admin' => $admin,
        );
        return $oTemplate->render($aTemplateData);
    }
}

?>
