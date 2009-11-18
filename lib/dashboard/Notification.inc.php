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

require_once(KT_LIB_DIR . "/session/control.inc");
require_once(KT_LIB_DIR . "/ktentity.inc");
require_once(KT_LIB_DIR . "/database/datetime.inc");
require_once(KT_LIB_DIR . "/dashboard/NotificationRegistry.inc.php");

require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');

require_once(KT_LIB_DIR . '/workflow/workflowutil.inc.php');

require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');

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
    // sData1 and sData2 are 255-length character fields
    var $sData1;
    var $sData2;
    // sText1 is a 65535-length text field
    var $sText1;

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
    function getTextData1() { return $this->sText1; }
    function setTextData1($mValue) { $this->sText1 = $mValue; }

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
        "sText1" => "data_text_1",
    );

    function _table () {
        return KTUtil::getTableName('notifications');
    }

    function render() {
        $notificationRegistry =& KTNotificationRegistry::getSingleton();
        $handler = $notificationRegistry->getHandler($this->sType);

        if (is_null($handler)) { return null; }

        return $handler->handleNotification($this);
    }

    function &getHandler() {
        $notificationRegistry =& KTNotificationRegistry::getSingleton();
        $handler =& $notificationRegistry->getHandler($this->sType);
        return $handler;
    }

    // Static function
    function &get($iId) { return KTEntityUtil::get('KTNotification', $iId); }
    function &getList($sWhereClause = null, $aOptions = null ) {
        if(!is_array($aOptions)) $aOptions = array($aOptions);
            $aOptions['orderby'] = KTUtil::arrayGet($aOptions, 'orderby', 'creation_date DESC');
        return KTEntityUtil::getList2('KTNotification', $sWhereClause, $aOptions);
    }

    function &createFromArray($aOptions) { return KTEntityUtil::createFromArray('KTNotification', $aOptions); }

}

/** register the base handlers. */

// abstract base-class for notification handler.
class KTNotificationHandler extends KTStandardDispatcher {

    // FIXME rename this to renderNotification
	// called to _render_ the notification.
    function handleNotification($oKTNotification) {
		$oTemplating =& KTTemplating::getSingleton();
		$oTemplate = $oTemplating->loadTemplate("kt3/notifications/generic");

        $aTemplateData = array("context" => $oKTNotification, "oKTConfig" => $oKTConfig);
		return $oTemplate->render($aTemplateData);
    }

    function do_main() {
        $this->resolveNotification($this->notification);
    }

	// called to resolve the notification (typically from /notify.php?id=xxxxx
	function resolveNotification($oKTNotification) {
	    $_SESSION['KTErrorMessage'][] = _kt("This notification handler does not support publication.");
	    exit(redirect(generateControllerLink('dashboard')));
	}
}

// FIXME consider refactoring this into plugins/ktcore/ktstandard/KTSubscriptions.php

class KTSubscriptionNotification extends KTNotificationHandler {
    /* Subscription Notifications
	*
	*  Subscriptions are a large part of the notification volume.
	*  That said, notifications cater to a larger group, so there is some
	*  degree of mismatch between the two.
	*
	*  Mapping the needs of subscriptions onto the provisions of notifications
	*  works as:
	*
	*     $oKTN->label:      object name [e.g. Document Name]
	*     $oKTN->strData1:   event type [AddFolder, AddDocument, etc.]
	*     $oKTN->strData2:   _location_ name. (e.g. folder of the subscription.)
	*     $oKTN->intData1:   object id (e.g. document_id, folder_id)
	*     $oKTN->intData2:   actor id (e.g. user_id)
	*
	*/

	var $notificationType = 'ktcore/subscriptions';

	var $_eventObjectMap = array(
		"AddFolder" => 'folder',
        "RemoveSubscribedFolder" => '', // nothing. your subscription is now gone.
        "RemoveChildFolder" => 'folder',
        "AddDocument" => 'document',
        "RemoveSubscribedDocument" => '', // nothing. your subscription is now gone.
        "RemoveChildDocument" => 'folder',
        "ModifyDocument" => 'document',
        "CheckInDocument" => 'document',
        "CheckOutDocument" => 'document',
        "MovedDocument" => 'folder',
        "MovedDocument2" => 'document',
        "CopiedDocument" => 'folder',
        "CopiedDocument2" => 'document',
        "ArchivedDocument" => 'document', // can go through and request un-archival (?)
        "RestoredArchivedDocument" => 'document',
        "DiscussDocument" => 'document',
        );

    function KTSubscriptionNotification() {
        $this->_eventTypeNames = array(
            "AddFolder" => _kt('Folder added'),
            "RemoveSubscribedFolder" => _kt('Folder removed'), // nothing. your subscription is now gone.
            "RemoveChildFolder" => _kt('Folder removed'),
            "AddDocument" => _kt('Document added'),
            "RemoveSubscribedDocument" => _kt('Document removed'), // nothing. your subscription is now gone.
            "RemoveChildDocument" => _kt('Document removed'),
            "ModifyDocument" => _kt('Document modified'),
            "CheckInDocument" => _kt('Document checked in'),
            "CheckOutDocument" => _kt('Document checked out'),
            "MovedDocument" => _kt('Document moved'),
            "ArchivedDocument" => _kt('Document archived'), // can go through and request un-archival (?)
            "RestoredArchivedDocument" => _kt('Document restored'),
            "DiscussDocument" => _kt('Document Discussions updated'),
        );
        //parent::KTNotificationHandler();
    }
	// helper method to extract / set the various pieces of information
	function _getSubscriptionData($oKTNotification) {
		$info = array(
			'object_name' => $oKTNotification->getLabel(),
			'event_type' => $oKTNotification->getStrData1(),
			'location_name' => $oKTNotification->getStrData2(),
			'object_id' => $oKTNotification->getIntData1(),
			'actor_id' => $oKTNotification->getIntData2(),
			'has_actor' => false,
			'notify_id' => $oKTNotification->getId(),
		);

		$info['title'] = KTUtil::arrayGet($this->_eventTypeNames, $info['event_type'], 'Subscription alert:') .': ' . $info['object_name'];

		if ($info['actor_id'] !== null) {
			$oTempUser = User::get($info['actor_id']);
			if (PEAR::isError($oTempUser) || ($oTempUser == false)) {
				// no-act
				$info['actor'] = null;
			} else {
			    $info['actor'] = $oTempUser;
				$info['has_actor'] = true;
			}
		}

		if ($info['object_id'] !== null) {
		    $info['object'] = $this->_getEventObject($info['event_type'], $info['object_id']);
		}

		return $info;
	}

	// resolve the object type based on the alert type.
	function _getEventObject($sAlertType, $id) {
        $t = KTUtil::arrayGet($this->_eventObjectMap, $sAlertType ,'');

		if ($t == 'document') {
		    $o = Document::get($id);
			if (PEAR::isError($o) || ($o == false)) { return null;
			} else { return $o; }
		} else if ($t == 'folder') {
		    $o = Folder::get($id);
			if (PEAR::isError($o) || ($o == false)) { return null;
			} else { return $o; }
		} else {
			return null;
		}
	}

	function _getEventObjectType($sAlertType) {
		return KTUtil::arrayGet($this->_eventObjectMap, $sAlertType ,'');
	}

    function handleNotification($oKTNotification) {
		$oSubscriptionContent = new SubscriptionContent();
		return $oSubscriptionContent->getNotificationAlertContent($oKTNotification);
    }

	// helper to _create_ a notification, in a way that is slightly less opaque.
	function &generateSubscriptionNotification($aOptions) {
	    $creationInfo = array();
		/*
		"iId" => "id",
        "iUserId" => "user_id",
        "sLabel" => "label",
        "sType" => "type",
        "dCreationDate" => "creation_date",
        "iData1" => "data_int_1",
        "iData2" => "data_int_2",
        "sData1" => "data_str_1",
        "sData2" => "data_str_2",

		'object_name' => $oKTNotification->getLabel(),
		'event_type' => $oKTNotification->getStrData1(),
		'location_name' => $oKTNotification->getStrData2(),
		'object_id' => $oKTNotification->getIntData1(),
		'actor_id' => $oKTNotification->getIntData2(),
		'has_actor' => false,

		*/
		$creationInfo['sLabel'] = $aOptions['target_name'];
		$creationInfo['sData1']  = $aOptions['event_type'];
		$creationInfo['sData2']  = $aOptions['location_name'];
		$creationInfo['iData1']  = $aOptions['object_id'];
		$creationInfo['iData2']  = $aOptions['actor_id'];
		$creationInfo['iUserId']  = $aOptions['target_user'];
		$creationInfo['sType']  = 'ktcore/subscriptions';
		$creationInfo['dCreationDate'] = getCurrentDateTime(); // erk.

		global $default;

		//$default->log->debug('subscription notification:  from ' . print_r($aOptions, true));
		$default->log->debug('subscription notification:  using ' . print_r($creationInfo, true));

		$oNotification =& KTNotification::createFromArray($creationInfo);


		$default->log->debug('subscription notification:  created ' . print_r($oNotification, true));

		return $oNotification; // $res.
	}

	/**
	 * View the notification, and clear if requested
	 *
	 * @param unknown_type $oKTNotification
	 */
	function resolveNotification($oKTNotification) {
	    $notify_action = KTUtil::arrayGet($_REQUEST, 'notify_action', null);
		if ($notify_action == 'clear') {
            $_SESSION['KTInfoMessage'][] = _kt('Cleared notification.');
			$oKTNotification->delete();
			exit(redirect(generateControllerLink('dashboard')));
		}

		// otherwise, we want to redirect the to object represented by the item.
		//  - viewDocument and viewFolder are the appropriate items.
		//  - object_id
		$info = $this->_getSubscriptionData($oKTNotification);

		$object_type = $this->_getEventObjectType($info['event_type']);

		if ($object_type == '') {
		    $_SESSION['KTErrorMessage'][] = 'This notification has no "target".  Please report as a bug that this subscription should only have a clear action.' . $object_type;
		    exit(redirect(generateControllerLink('dashboard')));
		}

		if ($object_type == 'document') {
		    if ($info['object_id'] !== null) { // fails and generates an error with no doc-id.
				$params = 'fDocumentId=' . $info['object_id'];
				$url = generateControllerLink('viewDocument', $params);
				//$oKTNotification->delete(); // clear the alert.
				exit(redirect($url));
			}
		} else if ($object_type == 'folder') {
		    if ($info['object_id'] !== null) { // fails and generates an error with no doc-id.
				$params = 'fFolderId=' . $info['object_id'];
				$url = generateControllerLink('browse', $params);
				//$oKTNotification->delete(); // clear the alert.
				exit(redirect($url));
			}
		}
		$_SESSION['KTErrorMessage'][] = sprintf('This notification has no "target".  Please inform the %s developers that there is a target bug with type: ' . $info['event_type'], APP_NAME);
		exit(redirect(generateControllerLink('dashboard')));
	}

}

class KTWorkflowNotification extends KTNotificationHandler {

    function & clearNotificationsForDocument($oDocument) {
		$aNotifications = KTNotification::getList('data_int_1 = ' . $oDocument->getId());
		foreach ($aNotifications as $oNotification) {
			$oNotification->delete();
		}

	}

	function & newNotificationForDocument($oDocument, $oUser, $oState, $oActor, $sComments) {
		$aInfo = array();
		$aInfo['sData1'] = $oState->getName();
		$aInfo['sData2'] = $sComments;
		$aInfo['iData1'] = $oDocument->getId();
		$aInfo['iData2'] = $oActor->getId();
		$aInfo['sType'] = 'ktcore/workflow';
		$aInfo['dCreationDate'] = getCurrentDateTime();
		$aInfo['iUserId'] = $oUser->getId();
		$aInfo['sLabel'] = $oDocument->getName();

		$oNotification = KTNotification::createFromArray($aInfo);

		$handler = new KTWorkflowNotification();

		if ($oUser->getEmailNotification() && (strlen($oUser->getEmail()) > 0)) {
			$emailContent = $handler->handleNotification($oNotification);
			$emailSubject = sprintf(_kt('Workflow Notification: %s'), $oDocument->getName());
			$oEmail = new EmailAlert($oUser->getEmail(), $emailSubject, $emailContent);
			$oEmail->send();
		}

		return $oNotification;
	}

	function handleNotification($oKTNotification) {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oTemplating->loadTemplate('ktcore/workflow/workflow_notification');

		$oDoc = Document::get($oKTNotification->getIntData1());
		$isBroken = (PEAR::isError($oDoc) || ($oDoc->getStatusID() != LIVE));

        $oTemplate->setData(array(
            'context' => $this,
			'document_id' => $oKTNotification->getIntData1(),
			'state_name' => $oKTNotification->getStrData1(),
			'actor' => User::get($oKTNotification->getIntData2()),
			'document_name' => $oKTNotification->getLabel(),
			'notify_id' => $oKTNotification->getId(),
			'document' => $oDoc,
			'is_broken' => $isBroken,
        ));
        return $oTemplate->render();
	}

	function resolveNotification($oKTNotification) {
	    $notify_action = KTUtil::arrayGet($_REQUEST, 'notify_action', null);
		if ($notify_action == 'clear') {
		    $_SESSION['KTInfoMessage'][] = _kt('Workflow Notification cleared.');
			$oKTNotification->delete();
			exit(redirect(generateControllerLink('dashboard')));
		}

		$params = 'fDocumentId=' . $oKTNotification->getIntData1();
		$url = generateControllerLink('viewDocument', $params);
		//$oKTNotification->delete(); // clear the alert.
		exit(redirect($url));
	}
}

?>
