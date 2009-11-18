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
        $isBroken = (PEAR::isError($oDoc) || ($oDoc->getStatusID() != LIVE && $oDoc->getStatusID() != ARCHIVED));

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
        $this->aBreadcrumbs = array(array('action' => 'dashboard', 'name' => _kt('Dashboard')));
        $this->oPage->setBreadcrumbDetails(_kt('Help request'));

        $oTemplating =& KTTemplating::getSingleton();
        $oKTNotification =& $this->oNotification;
        $oDoc = Document::get($oKTNotification->getIntData1());
        $isBroken = (PEAR::isError($oDoc) || ($oDoc->getStatusID() != LIVE));
        $isArchived = ($oDoc->getStatusID() == ARCHIVED)? true : false;

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
            'is_archived' => $isArchived,

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

    function notify_restore() {
        $iDocId = $this->oNotification->getIntData1();
        $res = $this->restore($iDocId);
        if(PEAR::isError($res) || !$res){
            $msg = _kt('Document could not be restored');
            if($res){
                $msg .= ': '.$res->getMessage();
            }
            $this->addErrorMessage($msg);
        }else{
            $this->addInfoMessage(_kt('The document has been successfully restored.'));
        }

        $notify_id = $_REQUEST['id'];
        $url = KTUtil::ktLink("notify.php", '', "id=$notify_id");
        exit(redirect($url));
    }

    function restore($iDocId) {
        // Get the document object
        $oDoc = Document::get($iDocId);

        if (PEAR::isError($oDoc) || ($oDoc === false)) {
            return $oDoc;
        }

        $this->startTransaction();
        $iRestoreFolder = $oDoc->getRestoreFolderId();
        $oFolder = Folder::get($iRestoreFolder);

        // move to root if parent no longer exists.
        if (PEAR::isError($oFolder)) {
            $oDoc->setFolderId(1);
            $oFolder = Folder::get(1);
        } else {
            $oDoc->setFolderId($iRestoreFolder);
        }

        $oStorage = KTStorageManagerUtil::getSingleton();

        if ($oStorage->restore($oDoc)) {
            $oDoc = Document::get($iDocId); // storage path has changed for most recent object...
            $oDoc->setStatusId(LIVE);
            $oDoc->setPermissionObjectId($oFolder->getPermissionObjectId());
            $res = $oDoc->update();

            if (PEAR::isError($res) || ($res == false)) {
                return $res;
            }

            $res = KTPermissionUtil::updatePermissionLookup($oDoc);

            if (PEAR::isError($res)) {
                return $res;
            }

            // create a doc-transaction.
            $oTransaction = new DocumentTransaction($oDoc, sprintf(_kt("Restored from deleted state by %s"), $this->oUser->getName()), 'ktcore.transactions.update');
            $oTransaction->create();
        }
        $this->commitTransaction();
        return true;
    }
}

?>
