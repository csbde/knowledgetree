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

require_once(KT_LIB_DIR . '/actions/documentaction.inc.php');
require_once(KT_LIB_DIR . '/subscriptions/Subscription.inc');
require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');

// {{{ KTDocumentAssistAction
class KTDocumentAssistAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.assist';

    function getDisplayName() {
        return _kt('Request Assistance');
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt("assistance"));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/assistance');
        $fields = array();
        $fields[] = new KTStringWidget(_kt('Subject'), _kt('A one-line description introducing the assistance that you wish to receive'), 'subject', "", $this->oPage, true);
        $fields[] = new KTTextWidget(_kt('Details'), _kt('A full description of the assistance that you with to receive.  Provide all necessary information to assist in your request.'), 'details', "", $this->oPage, true);

        $oTemplate->setData(array(
            'context' => &$this,
            'fields' => $fields,
        ));
        return $oTemplate->render();
    }

    function do_assistance() {
        $sSubject = $this->oValidator->validateString($_REQUEST['subject']);
        $sDetails = $this->oValidator->validateString($_REQUEST['details']);
        $aUsers = array();
        $aGroups = array();
        $aRoles = array();

        foreach (Group::getAdministratorGroups() as $oGroup) {
            $aGroups[$oGroup->getId()] =& $oGroup;
        }

        foreach (Unit::getUnitsForFolder($this->oDocument->getFolderId()) as $oUnit) {
            foreach (Group::getUnitAdministratorGroupsByUnit($oUnit) as $oGroup) {
                $aGroups[$oGroup->getId()] =& $oGroup;
            }
        }

        $aRoles[-2] = Role::get(-2);
        $oDocument =& $this->oDocument;

        foreach ($aRoles as $oRole) {
            // Ignore anonymous or Everyone roles
            $iRoleId = KTUtil::getId($oRole);
            if (($iRoleId == -3) || ($iRoleId == -4)) {
                continue;
            }
            // first try on the document, then the folder above it.
            $oRoleAllocation = DocumentRoleAllocation::getAllocationsForDocumentAndRole($oDocument->getId(), $iRoleId);
            if (is_null($oRoleAllocation)) {
                // if we don't get a document role, try folder role.
                $oRoleAllocation = RoleAllocation::getAllocationsForFolderAndRole($oDocument->getFolderID(), $oRole->getId());
            }
            if (is_null($oRoleAllocation) || PEAR::isError($oRoleAllocation)) {
                continue;
            }
            $aRoleUsers = $oRoleAllocation->getUsers();
            $aRoleGroups = $oRoleAllocation->getGroups();

            foreach ($aRoleUsers as $id => $oU) {
                $aUsers[$id] = $oU;
            }
            foreach ($aRoleGroups as $id => $oGroup) {
                $aGroups[$id] = $oGroup;
            }
        }

        $aGroupMembershipSet = GroupUtil::buildGroupArray();
        $aAllIds = array_keys($aGroups);
        foreach ($aGroups as $id => $oGroup) {
            $aAllIds = kt_array_merge($aGroupMembershipSet[$id], $aAllIds);
        }

        foreach ($aAllIds as $id) {
            if (!array_key_exists($id, $aGroups)) {
                $aGroups[$id] = Group::get($id);
            }
        }

        // now, merge this (again) into the user-set.
        foreach ($aGroups as $oGroup) {
            $aNewUsers = $oGroup->getMembers();
            foreach ($aNewUsers as $oU) {
                            $id = $oU->getId();
                if (!array_key_exists($id, $aUsers)) {
                    $aUsers[$id] = $oU;
                }
            }
        }

        foreach ($aUsers as $oU) {
            if (!PEAR::isError($oU)) {
                KTAssistNotification::newNotificationForDocument($this->oDocument, $oU, $this->oUser, $sSubject, $sDetails);
            }
        }


        $this->commitTransaction();
        $params = 'fDocumentId=' . $oDocument->getId();
        $url = generateControllerLink('viewDocument', $params);
        exit(redirect($url));
    }
}
// }}}

class KTAssistNotification extends KTNotificationHandler {
    function & clearNotificationsForDocument($oDocument) {
        $aNotifications = KTNotification::getList('data_int_1 = ' . $oDocument->getId());
        foreach ($aNotifications as $oNotification) {
            $oNotification->delete();
        }
    }

    function &newNotificationForDocument($oDocument, $oUser, $oActor, $sSubject, $sDetails) {
        $aInfo = array();
        $aInfo['sData1'] = $sSubject;
        $aInfo['sText1'] = $sDetails;
        $aInfo['iData1'] = $oDocument->getId();
        $aInfo['iData2'] = $oActor->getId();
        $aInfo['sType'] = 'ktcore/assist';
        $aInfo['dCreationDate'] = getCurrentDateTime();
        $aInfo['iUserId'] = $oUser->getId();
        $aInfo['sLabel'] = $oDocument->getName();

        $oNotification = KTNotification::createFromArray($aInfo);

        $handler = new KTAssistNotification();

        if ($oUser->getEmailNotification() && (strlen($oUser->getEmail()) > 0)) {
            $emailContent = $handler->handleNotification($oNotification);
            $emailSubject = sprintf(_kt('Assistance request: %s'), $oDocument->getName());
            $oEmail = new EmailAlert($oUser->getEmail(), $emailSubject, $emailContent);
            $oEmail->send();
        }

        return $oNotification;
    }

    function handleNotification($oKTNotification) {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oTemplating->loadTemplate('ktcore/assist/assist_notification');

        $oDoc = Document::get($oKTNotification->getIntData1());
        $isBroken = (PEAR::isError($oDoc) || ($oDoc->getStatusID() != LIVE));

        $oTemplate->setData(array(
            'context' => $this,
            'document_id' => $oKTNotification->getIntData1(),
            'subject' => $oKTNotification->getStrData1(),
            'actor' => User::get($oKTNotification->getIntData2()),
            'document_name' => $oKTNotification->getLabel(),
            'notify_id' => $oKTNotification->getId(),
            'details' => $oKTNotification->getTextData1(),
            'document' => $oDoc,
            'is_broken' => $isBroken,
        ));
        return $oTemplate->render();
    }

    function resolveNotification($oKTNotification) {
        $notify_action = KTUtil::arrayGet($_REQUEST, 'notify_action', null);
        $this->oNotification =& $oKTNotification;
        $this->redispatch('notify_action', 'notify');
        exit(0);
    }

    function notify_main() {
        $oTemplating =& KTTemplating::getSingleton();
        $oKTNotification =& $this->oNotification;
        $oDoc = Document::get($oKTNotification->getIntData1());
        $isBroken = (PEAR::isError($oDoc) || ($oDoc->getStatusID() != LIVE));

        $oTemplate =& $oTemplating->loadTemplate('ktcore/assist/assist_notification_details');
        $oTemplate->setData(array(
            'context' => $this,
            'document_id' => $oKTNotification->getIntData1(),
            'subject' => $oKTNotification->getStrData1(),
            'actor' => User::get($oKTNotification->getIntData2()),
            'document_name' => $oKTNotification->getLabel(),
            'notify_id' => $oKTNotification->getId(),
            'details' => $oKTNotification->getTextData1(),
            'document' => $oDoc,
            'is_broken' => $isBroken,
            
        ));
        return $oTemplate->render();
    }

    function notify_clear() {
        $_SESSION['KTInfoMessage'][] = _kt('Assistance Request cleared.');
        $this->oNotification->delete();
        exit(redirect(generateControllerLink('dashboard')));
    }

    function notify_view() {
        $params = 'fDocumentId=' . $this->oNotification->getIntData1();
        $url = generateControllerLink('viewDocument', $params);
        // $this->oNotification->delete(); // clear the alert.
        exit(redirect($url));
    }
}

?>
