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
 *
 * -------------------------------------------------------------------------
 *
 * Subscription notification type.
 *
 * To use this, instantiate a SubscriptionEvent, and call the
 * appropriate "event" method.
 *
 */

require_once(KT_LIB_DIR . "/database/dbutil.inc");
require_once(KT_LIB_DIR . "/subscriptions/Subscription.inc");
require_once(KT_LIB_DIR . "/users/User.inc");
require_once(KT_LIB_DIR . "/dashboard/Notification.inc.php");
require_once(KT_LIB_DIR . "/alert/delivery/EmailAlert.inc");

require_once(KT_LIB_DIR . "/templating/templating.inc.php");

class SubscriptionEvent {
    var $eventTypes = array(
        "AddFolder",
        "RemoveSubscribedFolder",
        "RemoveChildFolder",
        "AddDocument",
        "RemoveSubscribedDocument",
        "RemoveChildDocument",
        "ModifyDocument",
        "CheckInDocument",
        "CheckOutDocument",
        "MovedDocument",
        "MovedDocument2",
        "CopiedDocument",
        "CopiedDocument2",
        "ArchivedDocument",
        "RestoredArchivedDocument",
        "DownloadDocument",
    );

    var $subscriptionTypes = array(
        "Document" => 1,
        "Folder" => 2,
    );

	function &subTypes($sType) {
	    $subscriptionTypes = array(
            "Document" => 1,
            "Folder" => 2,
		);

		return KTUtil::arrayGet($subscriptionTypes, $sType, null);
	}

    var $alertedUsers = array();       // per-instance (e.g. per-event) list of users who were contacted.
    var $_parameters = array();        // internal storage for
    var $child = -1;                   // the child object-id (e.g. which initiated the event: document OR folder)
    var $parent = -1;                  // the folder-id of the parent

    // FIXME stubs.
    /* Each of these functions handles appropriate propogation (e.g. both
     * folder and document subscription) without calling secondary functions.
     * Every attempt is made to be as explicit as possible.
     */

     /*
      * Notification of bulk upload
      * Author  :   Jarrett Jordaan
      * Date    :   27/04/09
      *
      * @params :   KTDocumentUtil $oDocObjects
      *             KTFolderUtil $oParentFolder
      */
    function notifyBulkDocumentAction($oDocObjects, $eventType, $oParentFolder) {
        $content = new SubscriptionContent(); // needed for i18n
        // TODO Better way to check if this is a folder object
        if (is_object($oParentFolder)) {
            $parentId = $oParentFolder->getId();
            $aUsers = $this->_getSubscribers($parentId, $this->subscriptionTypes["Folder"]);
            $this->bulkNotification($aUsers, $eventType, $oDocObjects, $parentId);
        }
    }

     /*
      * Bulk upload email notification handler
      * Author  :    Jarrett Jordaan
      * Date    :   27/04/09
      *
      * @params :   User $aUsers
      *             string $eventType
      *             KTDocumentUtil $oDocObjects
      *             int $parentId
      */
    function bulkNotification($aUsers, $eventType, $oDocObjects, $parentId) {
        $content = new SubscriptionContent(); // needed for i18n
        $locationName = Folder::generateFullFolderPath($parentId);
        $userId = $_SESSION['userID'];
        $oUser = & User::get($userId);
        $oUserName = $oUser->getName();

        foreach ($aUsers as $oSubscriber) {
            $userNotifications = array();
            $aNotificationOptions = array();
            $userSubscriberId = $oSubscriber->getID();
            $emailAddress = $oSubscriber->getEmail();
            // Email type details
            $actionTypeEmail = $this->actionTypeEmail($eventType);
            // Better subject header with just the modified folder location
            $eSubject = "Subscription Notification: Bulk {$actionTypeEmail['message']}";
            // now the email content.
            $eContent .= "You are receiving this notification because you are subscribed to the \"$locationName\"<br/>";
            $eContent .= "Your colleague, {$oUser->getName()}, has performed a Bulk {$actionTypeEmail['type']} in the \"$locationName\" folder.<br/>";
            // REMOVE: debugger
            global $default;
            // Get first document/folders details into a notification
            $oNotification = false;
            foreach($oDocObjects as $oDocObject) {
                $targetName = $oDocObject->getName();
                $objectId = $oDocObject->getId();
                $aNotificationOptions['target_user'] = $userSubscriberId;
                $aNotificationOptions['actor_id'] = $userId;
                $aNotificationOptions['target_name'] = $targetName;
                $aNotificationOptions['location_name'] = $locationName;
                $aNotificationOptions['object_id'] = $objectId;
                $aNotificationOptions['event_type'] = $eventType;
                $oNotification =& KTSubscriptionNotification::generateSubscriptionNotification($aNotificationOptions);
                break;
            }
            $eContent .= $content->getEmailAlertContent($oNotification, $parentId);
            $oEmail = new EmailAlert($emailAddress, $eSubject, $eContent);
            $oEmail->send();
        }
    }

    function actionTypeEmail($eventType) {
        switch($eventType){
            case 'AddFolder':
                return array("message"=>"Folders/Documents Added", "type"=>"Add");
            break;
            case 'RemoveSubscribedFolder':
                return array("message"=>"Removed Subscribed Folders/Documents", "type"=>"Remove");
            break;
            case 'RemoveChildFolder':
                return array("message"=>"Removed Folders/Documents", "type"=>"Remove");
            break;
            case 'AddDocument':
                return array("message"=>"Added Folders/Documents", "type"=>"Add");
            break;
            case 'RemoveSubscribedDocument':
                return array("message"=>"Removed Subscribed Folders/Documents", "type"=>"Remove");
            break;
            case 'RemoveChildDocument':
                return array("message"=>"Removed Folders/Documents", "type"=>"Remove");
            break;
            case 'ModifyDocument':
                return array("message"=>"Modified Folders/Documents", "type"=>"Modify");
            break;
            case 'CheckInDocument':
                return array("message"=>"Checked In Documents", "type"=>"Check In");
            break;
            case 'CheckOutDocument':
                return array("message"=>"Checked Out Documents", "type"=>"Check Out");
            break;
            case 'MovedDocument':
                return array("message"=>"Moved Documents/Folders", "type"=>"Move");
            break;
            case 'CopiedDocument':
                return array("message"=>"Copied Folders/Documents", "type"=>"Copy");
            break;
            case 'ArchivedDocument':
                return array("message"=>"Archived Folders/Documents", "type"=>"Archive");
            break;
            case 'RestoreArchivedDocument':
                return array("message"=>"Restored Archived Documents", "type"=>"Archive Restore");
            break;
            case 'DownloadDocument':
                return array("message"=>"Downloaded Folders/Documents", "type"=>"Download");
            break;
            default :
                return array("message"=>"Unknown Operations", "type"=>"Unknown");
            break;
        }
    }

    // alerts users who are subscribed to $iParentFolderId.
    function AddFolder($oAddedFolder, $oParentFolder) {
        $content = new SubscriptionContent(); // needed for i18n

	    // only useful for folder subscriptions.
        $aUsers = $this->_getSubscribers($oParentFolder->getId(), $this->subscriptionTypes["Folder"]);

        $parentId = $oParentFolder->getId();
        $this->sendNotification($aUsers, 'AddFolder', $oAddedFolder->getName(), $oAddedFolder->getId(), $parentId);
    }

    function AddDocument ($oAddedDocument, $oParentFolder) {
        $content = new SubscriptionContent(); // needed for i18n
	    // two parts to this:
        $aUsers = $this->_getSubscribers($oParentFolder->getId(), $this->subscriptionTypes["Folder"]);

        $parentId = $oParentFolder->getId();
        $this->sendNotification($aUsers, 'AddDocument', $oAddedDocument->getName(), $oAddedDocument->getId(), $parentId);
    }

    function RemoveFolder($oRemovedFolder, $oParentFolder) {
        $content = new SubscriptionContent(); // needed for i18n
	    // two cases to consider here:
		//    - notify people who are subscribed to the parent folder.
		//    - notify and unsubscribe people who are subscribed to the actual folder.

		// we need to start with the latter, so we don't "lose" any.
		$aUsers = $this->_getSubscribers($oRemovedFolder->getId(), $this->subscriptionTypes["Folder"]);
		foreach ($aUsers as $oSubscriber) {

		    // notification object first.
			$aNotificationOptions = array();
			$aNotificationOptions['target_user'] = $oSubscriber->getID();
			$aNotificationOptions['actor_id'] = KTUtil::arrayGet($_SESSION,"userID", null); // _won't_ be null.
			$aNotificationOptions['target_name'] = $oRemovedFolder->getName();
			$aNotificationOptions['location_name'] = Folder::generateFullFolderPath($oParentFolder->getId());
			$aNotificationOptions['object_id'] = $oParentFolder->getId();  // parent folder_id, since the removed one is removed.
			$aNotificationOptions['event_type'] = "RemoveSubscribedFolder";
			$oNotification =& KTSubscriptionNotification::generateSubscriptionNotification($aNotificationOptions);

			// now the email content.
			// FIXME this needs to be handled entirely within notifications from now on.
			if ($oSubscriber->getEmailNotification() && (strlen($oSubscriber->getEmail()) > 0)) {
			    $emailContent = $content->getEmailAlertContent($oNotification);
			    $emailSubject = $content->getEmailAlertSubject($oNotification);
			    $oEmail = new EmailAlert($oSubscriber->getEmail(), $emailSubject, $emailContent);
			    $oEmail->send();
			}

			// now grab each oSubscribers oSubscription, and delete.
			$oSubscription = Subscription::getByIds($oSubscriber->getId(), $oRemovedFolder->getId(), $this->subscriptionTypes["Folder"]);
			if (!(PEAR::isError($oSubscription) || ($oSubscription == false))) {
			    $oSubscription->delete();
			}
		}

		// now handle (for those who haven't been alerted) users watching the folder.
		$aUsers = $this->_getSubscribers($oParentFolder->getId(), $this->subscriptionTypes["Folder"]);

        $parentId = $oParentFolder->getId();
		$this->sendNotification($aUsers, 'RemoveChildFolder', $oRemovedFolder->getName(), $oParentFolder->getId(), $parentId);
	}

    function RemoveDocument($oRemovedDocument, $oParentFolder) {
        $content = new SubscriptionContent(); // needed for i18n
	    // two cases to consider here:
		//    - notify people who are subscribed to the parent folder.
		//    - notify and unsubscribe people who are subscribed to the actual folder.

		// we need to start with the latter, so we don't "lose" any.
		$aUsers = $this->_getSubscribers($oRemovedDocument->getId(), $this->subscriptionTypes["Document"]);
		foreach ($aUsers as $oSubscriber) {

		    // notification object first.
			$aNotificationOptions = array();
			$aNotificationOptions['target_user'] = $oSubscriber->getID();
		    $aNotificationOptions['actor_id'] = KTUtil::arrayGet($_SESSION,"userID", null); // _won't_ be null.
		    $aNotificationOptions['target_name'] = $oRemovedDocument->getName();
		    $aNotificationOptions['location_name'] = Folder::generateFullFolderPath($oParentFolder->getId());
		    $aNotificationOptions['object_id'] = $oParentFolder->getId();  // parent folder_id, since the removed one is removed.
		    $aNotificationOptions['event_type'] = "RemoveSubscribedDocument";
			$oNotification =& KTSubscriptionNotification::generateSubscriptionNotification($aNotificationOptions);

			// now the email content.
			// FIXME this needs to be handled entirely within notifications from now on.
			if ($oSubscriber->getEmailNotification() && (strlen($oSubscriber->getEmail()) > 0)) {
			    $emailContent = $content->getEmailAlertContent($oNotification);
				$emailSubject = $content->getEmailAlertSubject($oNotification);
				$oEmail = new EmailAlert($oSubscriber->getEmail(), $emailSubject, $emailContent);
				$oEmail->send();
			}

			// now grab each oSubscribers oSubscription, and delete.
			$oSubscription = Subscription::getByIds($oSubscriber->getId(), $oRemovedDocument->getId(), $this->subscriptionTypes["Document"]);
			if (!(PEAR::isError($oSubscription) || ($oSubscription == false))) {
			    $oSubscription->delete();
			}
		}

		// now handle (for those who haven't been alerted) users watching the folder.
		$aUsers = $this->_getSubscribers($oParentFolder->getId(), $this->subscriptionTypes["Folder"]);

        $parentId = $oParentFolder->getId();
		$this->sendNotification($aUsers, 'RemoveChildDocument', $oRemovedDocument->getName(), $oParentFolder->getId(), $parentId);
	}
    function ModifyDocument($oModifiedDocument, $oParentFolder)  {
        $content = new SubscriptionContent(); // needed for i18n
	    // OK:  two actions:  document registrants, folder registrants.
        $aDocUsers = $this->_getSubscribers($oModifiedDocument->getId(), $this->subscriptionTypes["Document"]);
        $aFolderUsers = $this->_getSubscribers($oParentFolder->getId(), $this->subscriptionTypes["Folder"]);
        $aUsers = array_merge($aDocUsers, $aFolderUsers);

        $parentId = $oParentFolder->getId();
        $this->sendNotification($aUsers, 'ModifyDocument', $oModifiedDocument->getName(), $oModifiedDocument->getId(), $parentId);
    }

    function DiscussDocument($oModifiedDocument, $oParentFolder)  {
        $content = new SubscriptionContent(); // needed for i18n
	    // OK:  two actions:  document registrants, folder registrants.
        $aDocUsers = $this->_getSubscribers($oModifiedDocument->getId(), $this->subscriptionTypes["Document"]);
        $aFolderUsers = $this->_getSubscribers($oParentFolder->getId(), $this->subscriptionTypes["Folder"]);
        $aUsers = array_merge($aDocUsers, $aFolderUsers);

        $parentId = $oParentFolder->getId();
        $this->sendNotification($aUsers, 'DiscussDocument', $oModifiedDocument->getName(), $oModifiedDocument->getId(), $parentId);
    }

    function CheckInDocument($oModifiedDocument, $oParentFolder)  {
        $content = new SubscriptionContent(); // needed for i18n
	    // OK:  two actions:  document registrants, folder registrants.
        $aDocUsers = $this->_getSubscribers($oModifiedDocument->getId(), $this->subscriptionTypes["Document"]);
        $aFolderUsers = $this->_getSubscribers($oParentFolder->getId(), $this->subscriptionTypes["Folder"]);
        $aUsers = array_merge($aDocUsers, $aFolderUsers);

        $parentId = $oParentFolder->getId();
        $this->sendNotification($aUsers, 'CheckInDocument', $oModifiedDocument->getName(), $oModifiedDocument->getId(), $parentId);
    }

    function CheckOutDocument($oModifiedDocument, $oParentFolder)  {
        $content = new SubscriptionContent(); // needed for i18n
	    // OK:  two actions:  document registrants, folder registrants.
        $aDocUsers = $this->_getSubscribers($oModifiedDocument->getId(), $this->subscriptionTypes["Document"]);
        $aFolderUsers = $this->_getSubscribers($oParentFolder->getId(), $this->subscriptionTypes["Folder"]);
        $aUsers = array_merge($aDocUsers, $aFolderUsers);

        $parentId = $oParentFolder->getId();
        $this->sendNotification($aUsers, 'CheckOutDocument', $oModifiedDocument->getName(), $oModifiedDocument->getId(), $parentId);
    }

    function MoveDocument($oMovedDocument, $oToFolder, $oFromFolder, $moveOrCopy = "MovedDocument")  {
        $parentId = $oToFolder->getId();

        // Document registrants
        $aDocUsers = $this->_getSubscribers($oMovedDocument->getId(), $this->subscriptionTypes["Document"]);
        $this->sendNotification($aDocUsers, $moveOrCopy.'2', $oMovedDocument->getName(), $oMovedDocument->getId(), $parentId);

	    // Folder registrants
        $aFromUsers = $this->_getSubscribers($oFromFolder->getId(), $this->subscriptionTypes["Folder"]);
        $aFolderUsers = $this->_getSubscribers($oToFolder->getId(), $this->subscriptionTypes["Folder"]);
        $aUsers = array_merge($aFromUsers, $aFolderUsers);
        $this->sendNotification($aUsers, $moveOrCopy, $oMovedDocument->getName(), $oToFolder->getId(), $parentId);
    }

    function ArchivedDocument($oModifiedDocument, $oParentFolder)  {
        $content = new SubscriptionContent(); // needed for i18n
	    // OK:  two actions:  document registrants, folder registrants.
        $aDocUsers = $this->_getSubscribers($oModifiedDocument->getId(), $this->subscriptionTypes["Document"]);
        $aFolderUsers = $this->_getSubscribers($oParentFolder->getId(), $this->subscriptionTypes["Folder"]);
        $aUsers = array_merge($aDocUsers, $aFolderUsers);

        $parentId = $oParentFolder->getId();
        $this->sendNotification($aUsers, 'ArchivedDocument', $oModifiedDocument->getName(), $oModifiedDocument->getId(), $parentId);
    }

    function RestoreDocument($oModifiedDocument, $oParentFolder)  {
        $content = new SubscriptionContent(); // needed for i18n
	    // OK:  two actions:  document registrants, folder registrants.
        $aDocUsers = $this->_getSubscribers($oModifiedDocument->getId(), $this->subscriptionTypes["Document"]);
        $aFolderUsers = $this->_getSubscribers($oParentFolder->getId(), $this->subscriptionTypes["Folder"]);
        $aUsers = array_merge($aDocUsers, $aFolderUsers);

        $parentId = $oParentFolder->getId();
        $this->sendNotification($aUsers, 'RestoreArchivedDocument', $oModifiedDocument->getName(), $oModifiedDocument->getId(), $parentId);
    }

    function DownloadDocument($oDocument, $oParentFolder)  {
        $content = new SubscriptionContent(); // needed for i18n
	    // OK:  two actions:  document registrants, folder registrants.
        $aDocUsers = $this->_getSubscribers($oDocument->getId(), $this->subscriptionTypes["Document"]);
        $aFolderUsers = $this->_getSubscribers($oParentFolder->getId(), $this->subscriptionTypes["Folder"]);
        $aUsers = array_merge($aDocUsers, $aFolderUsers);

        $parentId = $oParentFolder->getId();
        $this->sendNotification($aUsers, 'DownloadDocument', $oDocument->getName(), $oDocument->getId(), $parentId);
    }

    function sendNotification($aUsers, $eventType, $targetName, $objectId, $parentId) {
        $content = new SubscriptionContent(); // needed for i18n

	    //$aUsers = $this->_getSubscribers($oDocument->getId(), $this->subscriptionTypes["Document"]);

	    $locationName = Folder::generateFullFolderPath($parentId);
	    $userId = $_SESSION['userID'];
		foreach ($aUsers as $oSubscriber) {

		    $emailAddress = $oSubscriber->getEmail();
		    if ($oSubscriber->getEmailNotification() && !empty($emailAddress)) {

    		    // notification object first.
    			$aNotificationOptions = array();
    			$aNotificationOptions['target_user'] = $oSubscriber->getID();
    		    $aNotificationOptions['actor_id'] = $userId;
    		    $aNotificationOptions['target_name'] = $targetName;
    		    $aNotificationOptions['location_name'] = $locationName;
    		    $aNotificationOptions['object_id'] = $objectId;
    		    $aNotificationOptions['event_type'] = $eventType;
    			$oNotification =& KTSubscriptionNotification::generateSubscriptionNotification($aNotificationOptions);

    			// now the email content.
			    $emailContent = $content->getEmailAlertContent($oNotification);
				$emailSubject = $content->getEmailAlertSubject($oNotification);
				$oEmail = new EmailAlert($emailAddress, $emailSubject, $emailContent);
				$oEmail->send();
			}
		}
    }

    // small helper function to assist in identifying the numeric id.
    function _getKeyForType($sEventType) {
        foreach ($this->eventTypes as $key => $val) {
            if ($val == $sSubType) { return $key; }
        }
        return -1;
    }

    // helper function to get & adjust the $alertedUsers
    // note that this has side-effects:  $this->alertedUsers is a merged version
    // after this has been called.
    function _pruneAlertedUsers($aUserIds) {
        $returnArray = array_diff($aUserIds, $this->alertedUsers);
        $this->alertedUsers = kt_array_merge($returnArray, $this->alertedUsers); // now contains all users who will have been alerted.
        return $returnArray;
    }

    // gets subscribers to object, with appropriate type (e.g. folder or document).
    // need the second part because docs and folders have separate ids.
	// based on the old SubscriptionEngine::retrieveSubscribers.
    function _getSubscribers($iObjectId, $iSubType) {
	    global $default;        // for the logging.
		if (KTLOG_CACHE) $default->log->debug("_getSubscribers(id=$iObjectId, type=$iSubType); table=" .Subscription::getTableName($iSubType). "; id=" .Subscription::getIdFieldName($iSubType));

		$aUsers = array();
		$aNewUsers = array();
		$aSubUsers = array();
        $table = Subscription::getTableName($iSubType);
        $field = Subscription::getIdFieldName($iSubType);

		// If we're dealing with a folder then get those user who are subscribed to one of the parent folders and want notifications on sub folders
		if($iSubType == $this->subscriptionTypes["Folder"] && $iObjectId != 1){
		    // Get parent folder ids
		    $query= "SELECT parent_folder_ids FROM folders WHERE id = {$iObjectId}";
		    $aParentIds = DBUtil::getResultArrayKey($query, 'parent_folder_ids');
		    $parentIds = $aParentIds[0];

		    // Get those users who have checked the subfolders option on the above folders
		    $query = "SELECT user_id FROM {$table} WHERE {$field} IN ({$parentIds}) AND with_subfolders = 1";
		    $aSubUsers = DBUtil::getResultArrayKey($query, 'user_id');
		}

        $sQuery = "SELECT user_id FROM {$table} WHERE {$field} = ?";
        $aParams = array($iObjectId);

		$aNewUsers = DBUtil::getResultArrayKey(array($sQuery, $aParams), 'user_id');

		// Add any users from parent folders
		$aNewUsers = array_merge($aNewUsers, $aSubUsers);
		$aNewUsers = array_unique($aNewUsers);

		// Remove alerted users
		$aNewUsers = $this->_pruneAlertedUsers($aNewUsers);


		$iCurrentUserId = $_SESSION['userID'];
		// notionally less efficient than the old code.  if its a big issue, can easily
		// be refactored.
		foreach ($aNewUsers as $iUserId) {
		    // the user doesn't need to be notified for his/her own modifications
		    if($iUserId == $iCurrentUserId){
		        continue;
		    }

			$oUser = & User::get($iUserId);

			// do a quick prune here, for performance/maintenance reasons.
			if (PEAR::isError($oUser) || ($oUser == false)) {
			    $sQuery = "DELETE FROM " . Subscription::getTableName($iSubType) . " WHERE user_id = ?";
				$aParams = array($iUserId);
			    DBUtil::runQuery(array($sQuery, $sParams));
				$default->log->error("SubscriptionEvent::fireSubscription error removing subscription for user id=$iUserId");
			} else {
			    $aUsers[] = $oUser;
			}
		}

		if (KTLOG_CACHE) $default->log->debug('retrieveSubscribers found count=' . count($aUsers));
        return $aUsers;
    }
}

// interesting:  how do we want to generate email & notification content?
// first suggestion:
//    - generate this content here.
//    - alternatively, generate the content inside the notification environment (for part 2).

/* very simple class to handle and hold the various and sundry event types content for emails. */
class SubscriptionContent {
    // have to be instantiated, or the i18n can't work.
	function SubscriptionContent() {
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
            "MovedDocument2" => _kt('Document moved'),
            "CopiedDocument" => _kt('Document copied'),
            "CopiedDocument2" => _kt('Document copied'),
            "ArchivedDocument" => _kt('Document archived'), // can go through and request un-archival (?)
            "DownloadDocument" => _kt('Document downloaded'),
            "RestoredArchivedDocument" => _kt('Document restored'),
            "DiscussDocument" => _kt('Document Discussions updated'),
        );
	}

	/**
	* This function generates the email that will be sent for subscription notifications
	*
 	* @author KnowledgeTree Team
	* @access public
	* @param object $oKTNotification: The notification object
	* @return string $str: The html string that will be sent via email
	*/
	function getEmailAlertContent($oKTNotification, $bulk_action = 0) {
        if($bulk_action == 0) $bulk_action = false;
        // set up logo and title
        $rootUrl = KTUtil::kt_url();

        $info = $this->_getSubscriptionData($oKTNotification);

        // set up email text
        $addFolderText = _kt('The folder "').$info['object_name']._kt('" was added');
        $removeSubscribedFolderText = _kt('The folder "').$info['object_name']._kt('" to which you were subscribed, has been removed');
        $removeChildFolderText = _kt('The folder "').$info['object_name']._kt('" has been removed');
        $addDocumentText = _kt('The document "').$info['object_name']._kt('" was added');
        $removeSubscribedDocumentText = _kt('The document "').$info['object_name']._kt('" to which you were subscribed, has been removed');
        $removeChildDocumentText = _kt('The document "').$info['object_name']._kt('" has been removed');
        $modifyDocumentText = _kt('The document "').$info['object_name']._kt('" has been changed');
        $checkInDocumentText = _kt('The document "').$info['object_name']._kt('" has been checked in');
        $checkOutDocumentText = _kt('The document "').$info['object_name']._kt('" has been checked out');
        $moveDocumentText = _kt('The document "').$info['object_name']._kt('" has been moved');
        $copiedDocumentText = _kt('The document "').$info['object_name']._kt('" has been copied');
        $archivedDocumentText = _kt('The document "').$info['object_name']._kt('"');
        $restoreArchivedDocumentText = _kt('The document "').$info['object_name']._kt('" has been restored by an administrator');
        $downloadDocumentText = _kt('The document "').$info['object_name']._kt('"');
        $documentAlertText = _kt('An alert on the document "').$info['object_name']._kt('" has been added or modified');

        if($info['location_name'] !== NULL && !$bulk_action){
            $addFolderText .= _kt(' to "').$info['location_name']._kt('"');
            $removeChildFolderText .= _kt(' from the folder "').$info['location_name']._kt('"');
            $addDocumentText .= _kt(' to "').$info['location_name']._kt('"');
            $removeChildDocumentText .= _kt(' from the folder "').$info['location_name']._kt('" to which you are subscribed');
            $modifyDocumentText .= _kt(' in the folder "').$info['location_name']._kt('"');
            $checkInDocumentText .= _kt(', in the folder "').$info['location_name']._kt('"');
            $checkOutDocumentText .= _kt(', from the folder "').$info['location_name']._kt('"');
            $moveDocumentText .= _kt(' to the folder "').$info['location_name']._kt('"');
            $copiedDocumentText .= _kt(' to the folder "').$info['location_name']._kt('"');
            $archivedDocumentText .= _kt(' in the folder "').$info['location_name']._kt('" has been archived');
            $downloadDocumentText .= _kt(' in the folder "').$info['location_name']._kt('" has been downloaded');
            $documentAlertText .= _kt(' in the folder "').$info['location_name']._kt('"');
        }
        if($bulk_action && $info['event_type']!="RemoveSubscribedFolder") {
            $browse = "$rootUrl/browse.php?fFolderId=$bulk_action";
            $subFolder = '<a href="'.$browse.'">'._kt('View Subscription Folder ').'</a>';
        }
        // set up links
        switch($info['event_type']){
            case 'AddFolder':
                $text = $addFolderText;
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'];
                if(!$bulk_action) $links = '<a href="'.$url.'">'._kt('View New Folder').'</a>';
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'].'&notify_action=clear';
                $links .= '&#160;|&#160;<a href="'.$url.'">'._kt('Clear Alert').'</a>';
                break;
            case 'RemoveSubscribedFolder':
                $text = $removeSubscribedFolderText;
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'].'&notify_action=clear';
                $links = '<a href="'.$url.'">'._kt('Clear Alert').'</a>';
                break;
            case 'RemoveChildFolder':
                $text = $removeChildFolderText;
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'];
                if(!$bulk_action) $links = '<a href="'.$url.'">'._kt('View Folder').'</a>';
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'].'&notify_action=clear';
                $links .= '&#160;|&#160;<a href="'.$url.'">'._kt('Clear Alert').'</a>';
                break;
            case 'AddDocument':
                $text = $addDocumentText;
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'];
                if(!$bulk_action) $links = '<a href="'.$url.'">'._kt('View Document').'</a>';
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'].'&notify_action=clear';
                $links .= '&#160;|&#160;<a href="'.$url.'">'._kt('Clear Alert').'</a>';
                break;
            case 'RemoveSubscribedDocument':
                $text = $removeSubscribedDocumentText;
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'].'&notify_action=clear';
                $links = '<a href="'.$url.'">'._kt('Clear Alert').'</a>';
                break;
            case 'RemoveChildDocument':
                $text = $removeChildDocumentText;
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'].'&notify_action=clear';
                $links = '<a href="'.$url.'">'._kt('Clear Alert').'</a>';
                break;
            case 'ModifyDocument':
                $text = $modifyDocumentText;
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'];
                if(!$bulk_action) $links = '<a href="'.$url.'">'._kt('View Document').'</a>';
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'].'&notify_action=clear';
                $links .= '&#160;|&#160;<a href="'.$url.'">'._kt('Clear Alert').'</a>';
                break;
            case 'CheckInDocument':
                $text = $checkInDocumentText;
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'];
                $links = '<a href="'.$url.'">'._kt('View Document').'</a>';
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'].'&notify_action=clear';
                $links .= '&#160;|&#160;<a href="'.$url.'">'._kt('Clear Alert').'</a>';
                break;
            case 'CheckOutDocument':
                $text = $checkOutDocumentText;
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'];
                if(!$bulk_action) $links = '<a href="'.$url.'">'._kt('View Document').'</a>';
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'].'&notify_action=clear';
                $links .= '&#160;|&#160;<a href="'.$url.'">'._kt('Clear Alert').'</a>';
                break;
            case 'MovedDocument':
            case 'MovedDocument2':
                $text = $modifyDocumentText;
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'];
                if(!$bulk_action) $links = '<a href="'.$url.'">'._kt('View New Location').'</a>';
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'].'&notify_action=clear';
                $links .= '&#160;|&#160;<a href="'.$url.'">'._kt('Clear Alert').'</a>';
                break;
            case 'CopiedDocument':
            case 'CopiedDocument2':
                $text = $copiedDocumentText;
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'];
                if(!$bulk_action) $links = '<a href="'.$url.'">'._kt('View Document').'</a>';
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'].'&notify_action=clear';
                $links .= '&#160;|&#160;<a href="'.$url.'">'._kt('Clear Alert').'</a>';
                break;
            case 'ArchivedDocument':
                $text = $archivedDocumentText;
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'].'&notify_action=clear';
                $links = '<a href="'.$url.'">'._kt('Clear Alert').'</a>';
                break;
            case 'RestoreArchivedDocument':
                $text = $restoreArchivedDocumentText;
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'];
                if(!$bulk_action) $links = '<a href="'.$url.'">'._kt('View Document').'</a>';
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'].'&notify_action=clear';
                $links .= '&#160;|&#160;<a href="'.$url.'">'._kt('Clear Alert').'</a>';
                break;
            case 'DownloadDocument':
                $text = $downloadDocumentText;
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'];
                if(!$bulk_action) $links = '<a href="'.$url.'">'._kt('View Document').'</a>';
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'].'&notify_action=clear';
                $links .= '&#160;|&#160;<a href="'.$url.'">'._kt('Clear Alert').'</a>';
                break;
            case 'ModifyDocumentAlert':
                $text = $documentAlertText;
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'];
                $links = '<a href="'.$url.'">'._kt('View Document').'</a>';
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'].'&notify_action=clear';
                $links .= '&#160;|&#160;<a href="'.$url.'">'._kt('Clear Alert').'</a>';
                $url = $rootUrl.'/notify.php?id='.$info['notify_id'].'&notify_action=viewall';
                $links .= '&#160;|&#160;<a href="'.$url.'">'._kt('View all alerts on this document').'</a>';
        }

        if($info['actor_name'] !== NULL && $info['event_type'] != 'RestoredArchivedDocument'){
            $text .= _kt(', by ').$info['actor_name'];
        }

        // we can re-use the normal template.
		// however, we need to wrap it - no need for a second template here.
		//$str = '<html><body>' . $this->getNotificationAlertContent($oKTNotification) . '</body></html>';
        if(!$bulk_action) {
            $str = '<br />
                      &#160;&#160;&#160;&#160;<b>'._kt('Subscription notification').': '.$this->_eventTypeNames[$info['event_type']].'</b>
                      <br />
                      <br />
                      &#160;&#160;&#160;&#160;'.$text.'
                      <br />
                      <br />
                      &#160;&#160;&#160;&#160;'.$links.'
                      <br />
                      <br />';
        } else {
            $str = '<br />
                    <br />
                    '.$subFolder.'
                    &#160;'.$links.'
                    <br />
                    <br />';
        }
		return $str;
	}

	function getEmailAlertSubject($oKTNotification) {
	    $info = $this->_getSubscriptionData($oKTNotification);
	    return $info["title"];
	}

	function getNotificationAlertContent($oKTNotification) {
	    $info = $this->_getSubscriptionData($oKTNotification);
	    $oTemplating =& KTTemplating::getSingleton();

	    $oTemplate = $oTemplating->loadTemplate("kt3/notifications/subscriptions." . $info['event_type']);
	    // if, for some reason, this doesn't actually work, use the "generic" title.
	    if (PEAR::isError($oTemplate)) {
            $oTemplate = $oTemplating->loadTemplate("kt3/notifications/subscriptions.generic");
	    }
	    // FIXME we need to specify the i18n by user.

	    $isBroken = false;
	    if (PEAR::isError($info['object']) || ($info['object'] === false) || is_null($info['object'])) {
		$isBroken = true;
	    }

	    $aTemplateData = array("context" => $oKTNotification,
				   "info" => $info,
				   "is_broken" => $isBroken,
				   );
	    return $oTemplate->render($aTemplateData);
	}
	// no separate subject function, its rolled into get...Content()

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
        "DownloadDocument" => 'document',
        "RestoredArchivedDocument" => 'document',
        "DiscussDocument" => 'document');



	function _getSubscriptionData($oKTNotification) {
        $appName = APP_NAME;

        $info = array(
			'object_name' => $oKTNotification->getLabel(),
			'event_type' => $oKTNotification->getStrData1(),
			'location_name' => $oKTNotification->getStrData2(),
			'object_id' => $oKTNotification->getIntData1(),
			'actor_id' => $oKTNotification->getIntData2(),
			'has_actor' => false,
			'notify_id' => $oKTNotification->getId(),
		);

//		$info['title'] = KTUtil::arrayGet($this->_eventTypeNames, $info['event_type'], 'Subscription alert:') .': ' . $info['object_name'];
        $info['title'] = $appName.': '._kt('Subscription notification for').' "'.$info['object_name'].'" - '.$this->_eventTypeNames[$info['event_type']];


		if ($info['actor_id'] !== null) {
			$oTempUser = User::get($info['actor_id']);
			if (PEAR::isError($oTempUser) || ($oTempUser == false)) {
			    // no-act
			    $info['actor'] = null;
			} else {
			    $info['actor'] = $oTempUser;
			    $info['has_actor'] = true;

			    $sName = $oTempUser->getName();
			    $iUnitId = $oTempUser->getUnitId();

			    if($iUnitId !== false) {
				$oUnit = Unit::get($iUnitId);
				if(!PEAR::isError($oUnit)) {
				    $sName .= sprintf(" (%s)", $oUnit->getName());
				}
			    }

			    $info['actor_name'] = $sName;
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
}

?>
