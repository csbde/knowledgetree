<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Limited
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
        "CopiedDocument",
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

    // alerts users who are subscribed to $iParentFolderId.
    function AddFolder($oAddedFolder, $oParentFolder) {
        $content = new SubscriptionContent(); // needed for i18n

	    // only useful for folder subscriptions.
        $aUsers = $this->_getSubscribers($oParentFolder->getId(), $this->subscriptionTypes["Folder"]);
    		foreach ($aUsers as $oSubscriber) {

    		    // notification object first.
    		    $aNotificationOptions = array();
    		    $aNotificationOptions['target_user'] = $oSubscriber->getID();
    		    $aNotificationOptions['actor_id'] = KTUtil::arrayGet($_SESSION,"userID", null); // _won't_ be null.
    		    $aNotificationOptions['target_name'] = $oAddedFolder->getName();
    		    $aNotificationOptions['location_name'] = Folder::generateFullFolderPath($oParentFolder->getId());
    		    $aNotificationOptions['object_id'] = $oAddedFolder->getId();  // parent folder_id, in this case.
    		    $aNotificationOptions['event_type'] = "AddFolder";
    			$oNotification =& KTSubscriptionNotification::generateSubscriptionNotification($aNotificationOptions);

    			// now the email content.
    			// FIXME this needs to be handled entirely within notifications from now on.
    			if ($oSubscriber->getEmailNotification() && (strlen($oSubscriber->getEmail()) > 0)) {
    			    $emailContent = $content->getEmailAlertContent($oNotification);
    				$emailSubject = $content->getEmailAlertSubject($oNotification);
    				$oEmail = new EmailAlert($oSubscriber->getEmail(), $emailSubject, $emailContent);
    				$oEmail->send();
    			}
    		}
    }
    function AddDocument ($oAddedDocument, $oParentFolder) {
        $content = new SubscriptionContent(); // needed for i18n
	    // two parts to this:
        $aUsers = $this->_getSubscribers($oParentFolder->getId(), $this->subscriptionTypes["Folder"]);
    		foreach ($aUsers as $oSubscriber) {

    		    // notification object first.
    			$aNotificationOptions = array();
    			$aNotificationOptions['target_user'] = $oSubscriber->getID();
    		    $aNotificationOptions['actor_id'] = KTUtil::arrayGet($_SESSION,"userID", null); // _won't_ be null - is this valid?
    		    $aNotificationOptions['target_name'] = $oAddedDocument->getName();
    		    $aNotificationOptions['location_name'] = Folder::generateFullFolderPath($oParentFolder->getId());
    		    $aNotificationOptions['object_id'] = $oAddedDocument->getId();  // parent folder_id, in this case.
    		    $aNotificationOptions['event_type'] = "AddDocument";

    			$oNotification =& KTSubscriptionNotification::generateSubscriptionNotification($aNotificationOptions);

    			// now the email content.
    			// FIXME this needs to be handled entirely within notifications from now on.
    			if ($oSubscriber->getEmailNotification() && (strlen($oSubscriber->getEmail()) > 0)) {
    			    $emailContent = $content->getEmailAlertContent($oNotification);
    				$emailSubject = $content->getEmailAlertSubject($oNotification);
    				$oEmail = new EmailAlert($oSubscriber->getEmail(), $emailSubject, $emailContent);
    				$oEmail->send();
    			}
    		}
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
		foreach ($aUsers as $oSubscriber) {

		    // notification object first.
			$aNotificationOptions = array();
			$aNotificationOptions['target_user'] = $oSubscriber->getID();
		    $aNotificationOptions['actor_id'] = KTUtil::arrayGet($_SESSION,"userID", null); // _won't_ be null.
		    $aNotificationOptions['target_name'] = $oRemovedFolder->getName();
		    $aNotificationOptions['location_name'] = Folder::generateFullFolderPath($oParentFolder->getId());
		    $aNotificationOptions['object_id'] = $oParentFolder->getId();  // parent folder_id, since the removed one is removed.
		    $aNotificationOptions['event_type'] = "RemoveChildFolder";
			$oNotification =& KTSubscriptionNotification::generateSubscriptionNotification($aNotificationOptions);

			// now the email content.
			// FIXME this needs to be handled entirely within notifications from now on.
			if ($oSubscriber->getEmailNotification() && (strlen($oSubscriber->getEmail()) > 0)) {
			    $emailContent = $content->getEmailAlertContent($oNotification);
				$emailSubject = $content->getEmailAlertSubject($oNotification);
				$oEmail = new EmailAlert($oSubscriber->getEmail(), $emailSubject, $emailContent);
				$oEmail->send();
			}
		}

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
		foreach ($aUsers as $oSubscriber) {

		    // notification object first.
			$aNotificationOptions = array();
			$aNotificationOptions['target_user'] = $oSubscriber->getID();
		    $aNotificationOptions['actor_id'] = KTUtil::arrayGet($_SESSION,"userID", null); // _won't_ be null.
		    $aNotificationOptions['target_name'] = $oRemovedDocument->getName();
		    $aNotificationOptions['location_name'] = Folder::generateFullFolderPath($oParentFolder->getId());
		    $aNotificationOptions['object_id'] = $oParentFolder->getId();  // parent folder_id, since the removed one is removed.
		    $aNotificationOptions['event_type'] = "RemoveChildDocument";
			$oNotification =& KTSubscriptionNotification::generateSubscriptionNotification($aNotificationOptions);

			// now the email content.
			// FIXME this needs to be handled entirely within notifications from now on.
			if ($oSubscriber->getEmailNotification() && (strlen($oSubscriber->getEmail()) > 0)) {
			    $emailContent = $content->getEmailAlertContent($oNotification);
				$emailSubject = $content->getEmailAlertSubject($oNotification);
				$oEmail = new EmailAlert($oSubscriber->getEmail(), $emailSubject, $emailContent);
				$oEmail->send();
			}
		}

	}
    function ModifyDocument($oModifiedDocument, $oParentFolder)  {
        $content = new SubscriptionContent(); // needed for i18n
	    // OK:  two actions:  document registrants, folder registrants.
        $aUsers = $this->_getSubscribers($oModifiedDocument->getId(), $this->subscriptionTypes["Document"]);
		foreach ($aUsers as $oSubscriber) {

		    // notification object first.
			$aNotificationOptions = array();
			$aNotificationOptions['target_user'] = $oSubscriber->getID();
		    $aNotificationOptions['actor_id'] = KTUtil::arrayGet($_SESSION,"userID", null); // _won't_ be null.
		    $aNotificationOptions['target_name'] = $oModifiedDocument->getName();
		    $aNotificationOptions['location_name'] = Folder::generateFullFolderPath($oParentFolder->getId());
		    $aNotificationOptions['object_id'] = $oModifiedDocument->getId();  // parent folder_id, in this case.
		    $aNotificationOptions['event_type'] = "ModifyDocument";
			$oNotification =& KTSubscriptionNotification::generateSubscriptionNotification($aNotificationOptions);

			// now the email content.
			// FIXME this needs to be handled entirely within notifications from now on.
			if ($oSubscriber->getEmailNotification() && (strlen($oSubscriber->getEmail()) > 0)) {
			    $emailContent = $content->getEmailAlertContent($oNotification);
				$emailSubject = $content->getEmailAlertSubject($oNotification);
				$oEmail = new EmailAlert($oSubscriber->getEmail(), $emailSubject, $emailContent);
				$oEmail->send();
			}
		}


        $aUsers = $this->_getSubscribers($oParentFolder->getId(), $this->subscriptionTypes["Folder"]);
		foreach ($aUsers as $oSubscriber) {

		    // notification object first.
			$aNotificationOptions = array();
			$aNotificationOptions['target_user'] = $oSubscriber->getID();
		    $aNotificationOptions['actor_id'] = KTUtil::arrayGet($_SESSION,"userID", null); // _won't_ be null.
		    $aNotificationOptions['target_name'] = $oModifiedDocument->getName();
		    $aNotificationOptions['location_name'] = Folder::generateFullFolderPath($oParentFolder->getId());
		    $aNotificationOptions['object_id'] = $oModifiedDocument->getId();  // parent folder_id, in this case.
		    $aNotificationOptions['event_type'] = "ModifyDocument";
			$oNotification =& KTSubscriptionNotification::generateSubscriptionNotification($aNotificationOptions);

			// now the email content.
			// FIXME this needs to be handled entirely within notifications from now on.
			if ($oSubscriber->getEmailNotification() && (strlen($oSubscriber->getEmail()) > 0)) {
			    $emailContent = $content->getEmailAlertContent($oNotification);
				$emailSubject = $content->getEmailAlertSubject($oNotification);
				$oEmail = new EmailAlert($oSubscriber->getEmail(), $emailSubject, $emailContent);
				$oEmail->send();
			}
		}
    }

    function DiscussDocument($oModifiedDocument, $oParentFolder)  {
        $content = new SubscriptionContent(); // needed for i18n
	    // OK:  two actions:  document registrants, folder registrants.
        $aUsers = $this->_getSubscribers($oModifiedDocument->getId(), $this->subscriptionTypes["Document"]);
		foreach ($aUsers as $oSubscriber) {

		    // notification object first.
			$aNotificationOptions = array();
			$aNotificationOptions['target_user'] = $oSubscriber->getID();
		    $aNotificationOptions['actor_id'] = KTUtil::arrayGet($_SESSION,"userID", null); // _won't_ be null.
		    $aNotificationOptions['target_name'] = $oModifiedDocument->getName();
		    $aNotificationOptions['location_name'] = Folder::generateFullFolderPath($oParentFolder->getId());
		    $aNotificationOptions['object_id'] = $oModifiedDocument->getId();  // parent folder_id, in this case.
		    $aNotificationOptions['event_type'] = "DiscussDocument";
			$oNotification =& KTSubscriptionNotification::generateSubscriptionNotification($aNotificationOptions);

			// now the email content.
			// FIXME this needs to be handled entirely within notifications from now on.
			if ($oSubscriber->getEmailNotification() && (strlen($oSubscriber->getEmail()) > 0)) {
			    $emailContent = $content->getEmailAlertContent($oNotification);
				$emailSubject = $content->getEmailAlertSubject($oNotification);
				$oEmail = new EmailAlert($oSubscriber->getEmail(), $emailSubject, $emailContent);
				$oEmail->send();
			}
		}

        $aUsers = $this->_getSubscribers($oParentFolder->getId(), $this->subscriptionTypes["Folder"]);
		foreach ($aUsers as $oSubscriber) {

		    // notification object first.
			$aNotificationOptions = array();
			$aNotificationOptions['target_user'] = $oSubscriber->getID();
		    $aNotificationOptions['actor_id'] = KTUtil::arrayGet($_SESSION,"userID", null); // _won't_ be null.
		    $aNotificationOptions['target_name'] = $oModifiedDocument->getName();
		    $aNotificationOptions['location_name'] = Folder::generateFullFolderPath($oParentFolder->getId());
		    $aNotificationOptions['object_id'] = $oModifiedDocument->getId();  // parent folder_id, in this case.
		    $aNotificationOptions['event_type'] = "DiscussDocument";
			$oNotification =& KTSubscriptionNotification::generateSubscriptionNotification($aNotificationOptions);

			// now the email content.
			// FIXME this needs to be handled entirely within notifications from now on.
			if ($oSubscriber->getEmailNotification() && (strlen($oSubscriber->getEmail()) > 0)) {
			    $emailContent = $content->getEmailAlertContent($oNotification);
				$emailSubject = $content->getEmailAlertSubject($oNotification);
				$oEmail = new EmailAlert($oSubscriber->getEmail(), $emailSubject, $emailContent);
				$oEmail->send();
			}
		}
    }

    function CheckInDocument($oModifiedDocument, $oParentFolder)  {
        $content = new SubscriptionContent(); // needed for i18n
	    // OK:  two actions:  document registrants, folder registrants.
        $aUsers = $this->_getSubscribers($oModifiedDocument->getId(), $this->subscriptionTypes["Document"]);
		foreach ($aUsers as $oSubscriber) {

		    // notification object first.
			$aNotificationOptions = array();
			$aNotificationOptions['target_user'] = $oSubscriber->getID();
		    $aNotificationOptions['actor_id'] = KTUtil::arrayGet($_SESSION,"userID", null); // _won't_ be null.
		    $aNotificationOptions['target_name'] = $oModifiedDocument->getName();
		    $aNotificationOptions['location_name'] = Folder::generateFullFolderPath($oParentFolder->getId());
		    $aNotificationOptions['object_id'] = $oModifiedDocument->getId();  // parent folder_id, in this case.
		    $aNotificationOptions['event_type'] = "CheckInDocument";
			$oNotification =& KTSubscriptionNotification::generateSubscriptionNotification($aNotificationOptions);

			// now the email content.
			// FIXME this needs to be handled entirely within notifications from now on.
			if ($oSubscriber->getEmailNotification() && (strlen($oSubscriber->getEmail()) > 0)) {
			    $emailContent = $content->getEmailAlertContent($oNotification);
				$emailSubject = $content->getEmailAlertSubject($oNotification);
				$oEmail = new EmailAlert($oSubscriber->getEmail(), $emailSubject, $emailContent);
				$oEmail->send();
			}
		}


        $aUsers = $this->_getSubscribers($oParentFolder->getId(), $this->subscriptionTypes["Folder"]);

		foreach ($aUsers as $oSubscriber) {

		    // notification object first.
			$aNotificationOptions = array();
			$aNotificationOptions['target_user'] = $oSubscriber->getID();
		    $aNotificationOptions['actor_id'] = KTUtil::arrayGet($_SESSION,"userID", null); // _won't_ be null.
		    $aNotificationOptions['target_name'] = $oModifiedDocument->getName();
		    $aNotificationOptions['location_name'] = Folder::generateFullFolderPath($oParentFolder->getId());
		    $aNotificationOptions['object_id'] = $oModifiedDocument->getId();  // parent folder_id, in this case.
		    $aNotificationOptions['event_type'] = "CheckInDocument";
			$oNotification =& KTSubscriptionNotification::generateSubscriptionNotification($aNotificationOptions);

			// now the email content.
			// FIXME this needs to be handled entirely within notifications from now on.
			if ($oSubscriber->getEmailNotification() && (strlen($oSubscriber->getEmail()) > 0)) {
			    $emailContent = $content->getEmailAlertContent($oNotification);
				$emailSubject = $content->getEmailAlertSubject($oNotification);
				$oEmail = new EmailAlert($oSubscriber->getEmail(), $emailSubject, $emailContent);
				$oEmail->send();

			}
		}
    }
    function CheckOutDocument($oModifiedDocument, $oParentFolder)  {
        $content = new SubscriptionContent(); // needed for i18n
	    // OK:  two actions:  document registrants, folder registrants.
        $aUsers = $this->_getSubscribers($oModifiedDocument->getId(), $this->subscriptionTypes["Document"]);

					foreach ($aUsers as $oSubscriber) {

					    // notification object first.
						$aNotificationOptions = array();
						$aNotificationOptions['target_user'] = $oSubscriber->getID();
					    $aNotificationOptions['actor_id'] = KTUtil::arrayGet($_SESSION,"userID", null); // _won't_ be null.
					    $aNotificationOptions['target_name'] = $oModifiedDocument->getName();
					    $aNotificationOptions['location_name'] = Folder::generateFullFolderPath($oParentFolder->getId());
					    $aNotificationOptions['object_id'] = $oModifiedDocument->getId();  // parent folder_id, in this case.
					    $aNotificationOptions['event_type'] = "CheckOutDocument";
						$oNotification =& KTSubscriptionNotification::generateSubscriptionNotification($aNotificationOptions);

						// now the email content.
						// FIXME this needs to be handled entirely within notifications from now on.
						if ($oSubscriber->getEmailNotification() && (strlen($oSubscriber->getEmail()) > 0)) {
						    $emailContent = $content->getEmailAlertContent($oNotification);
							$emailSubject = $content->getEmailAlertSubject($oNotification);
							$oEmail = new EmailAlert($oSubscriber->getEmail(), $emailSubject, $emailContent);
							$oEmail->send();
						}
					}


        $aUsers = $this->_getSubscribers($oParentFolder->getId(), $this->subscriptionTypes["Folder"]);
				foreach ($aUsers as $oSubscriber) {

				    // notification object first.
					$aNotificationOptions = array();
					$aNotificationOptions['target_user'] = $oSubscriber->getID();
				    $aNotificationOptions['actor_id'] = KTUtil::arrayGet($_SESSION,"userID", null); // _won't_ be null.
				    $aNotificationOptions['target_name'] = $oModifiedDocument->getName();
				    $aNotificationOptions['location_name'] = Folder::generateFullFolderPath($oParentFolder->getId());
				    $aNotificationOptions['object_id'] = $oModifiedDocument->getId();  // parent folder_id, in this case.
				    $aNotificationOptions['event_type'] = "CheckOutDocument";
					$oNotification =& KTSubscriptionNotification::generateSubscriptionNotification($aNotificationOptions);

					// now the email content.
					// FIXME this needs to be handled entirely within notifications from now on.
					if ($oSubscriber->getEmailNotification() && (strlen($oSubscriber->getEmail()) > 0)) {
					    $emailContent = $content->getEmailAlertContent($oNotification);
						$emailSubject = $content->getEmailAlertSubject($oNotification);
						$oEmail = new EmailAlert($oSubscriber->getEmail(), $emailSubject, $emailContent);
						$oEmail->send();
					}
				}
    }

    function MoveDocument($oMovedDocument, $oToFolder, $oFromFolder, $moveOrCopy = "MovedDocument")  {
        $content = new SubscriptionContent(); // needed for i18n
	    // OK:  two actions:  document registrants, folder registrants.
        $aUsers = $this->_getSubscribers($oMovedDocument->getId(), $this->subscriptionTypes["Document"]);

      	foreach ($aUsers as $oSubscriber) {
      	    // notification object first.
      	    $aNotificationOptions = array();
      	    $aNotificationOptions['target_user'] = $oSubscriber->getID();
      	    $aNotificationOptions['actor_id'] = KTUtil::arrayGet($_SESSION,"userID", null); // _won't_ be null.
      	    $aNotificationOptions['target_name'] = $oMovedDocument->getName();
      	    $aNotificationOptions['location_name'] = Folder::generateFullFolderPath($oToFolder->getId());
      	    $aNotificationOptions['object_id'] = $oToFolder->getId();  // parent folder_id, in this case.
      	    $aNotificationOptions['event_type'] = $moveOrCopy;
      	    $oNotification =& KTSubscriptionNotification::generateSubscriptionNotification($aNotificationOptions);

      	    // now the email content.
      	    // FIXME this needs to be handled entirely within notifications from now on.
      	    if ($oSubscriber->getEmailNotification() && (strlen($oSubscriber->getEmail()) > 0)) {
      		$emailContent = $content->getEmailAlertContent($oNotification);
      		$emailSubject = $content->getEmailAlertSubject($oNotification);
      		$oEmail = new EmailAlert($oSubscriber->getEmail(), $emailSubject, $emailContent);
      		$oEmail->send();
      	    }
      	}


        $aUsers = $this->_getSubscribers($oFromFolder->getId(), $this->subscriptionTypes["Folder"]);

      	foreach ($aUsers as $oSubscriber) {

      	    // notification object first.
      	    $aNotificationOptions = array();
      	    $aNotificationOptions['target_user'] = $oSubscriber->getID();
      	    $aNotificationOptions['actor_id'] = KTUtil::arrayGet($_SESSION,"userID", null); // _won't_ be null.
      	    $aNotificationOptions['target_name'] = $oMovedDocument->getName();
      	    $aNotificationOptions['location_name'] = Folder::generateFullFolderPath($oToFolder->getId());
      	    $aNotificationOptions['object_id'] = $oToFolder->getId();  // parent folder_id, in this case.
      	    $aNotificationOptions['event_type'] = $moveOrCopy;
      	    $oNotification =& KTSubscriptionNotification::generateSubscriptionNotification($aNotificationOptions);

      	    // now the email content.
      	    // FIXME this needs to be handled entirely within notifications from now on.
      	    if ($oSubscriber->getEmailNotification() && (strlen($oSubscriber->getEmail()) > 0)) {
      		$emailContent = $content->getEmailAlertContent($oNotification);
      		$emailSubject = $content->getEmailAlertSubject($oNotification);
      		$oEmail = new EmailAlert($oSubscriber->getEmail(), $emailSubject, $emailContent);
      		$oEmail->send();
      	    }
      	}

        $aUsers = $this->_getSubscribers($oToFolder->getId(), $this->subscriptionTypes["Folder"]);
      	foreach ($aUsers as $oSubscriber) {

      	    // notification object first.
      	    $aNotificationOptions = array();
      	    $aNotificationOptions['target_user'] = $oSubscriber->getID();
      	    $aNotificationOptions['actor_id'] = KTUtil::arrayGet($_SESSION,"userID", null); // _won't_ be null.
      	    $aNotificationOptions['target_name'] = $oMovedDocument->getName();
      	    $aNotificationOptions['location_name'] = Folder::generateFullFolderPath($oToFolder->getId());
      	    $aNotificationOptions['object_id'] = $oToFolder->getId();  // parent folder_id, in this case.
      	    $aNotificationOptions['event_type'] = $moveOrCopy;
      	    $oNotification =& KTSubscriptionNotification::generateSubscriptionNotification($aNotificationOptions);

      	    // now the email content.
      	    // FIXME this needs to be handled entirely within notifications from now on.
      	    if ($oSubscriber->getEmailNotification() && (strlen($oSubscriber->getEmail()) > 0)) {
      		$emailContent = $content->getEmailAlertContent($oNotification);
      		$emailSubject = $content->getEmailAlertSubject($oNotification);
      		$oEmail = new EmailAlert($oSubscriber->getEmail(), $emailSubject, $emailContent);
      		$oEmail->send();
      	    }
      	}
    }

    function ArchivedDocument($oModifiedDocument, $oParentFolder)  {
        $content = new SubscriptionContent(); // needed for i18n
	    // OK:  two actions:  document registrants, folder registrants.
        $aUsers = $this->_getSubscribers($oModifiedDocument->getId(), $this->subscriptionTypes["Document"]);
		foreach ($aUsers as $oSubscriber) {

		    // notification object first.
			$aNotificationOptions = array();
			$aNotificationOptions['target_user'] = $oSubscriber->getID();
		    $aNotificationOptions['actor_id'] = KTUtil::arrayGet($_SESSION,"userID", null); // _won't_ be null.
		    $aNotificationOptions['target_name'] = $oModifiedDocument->getName();
		    $aNotificationOptions['location_name'] = Folder::generateFullFolderPath($oParentFolder->getId());
		    $aNotificationOptions['object_id'] = $oModifiedDocument->getId();  // parent folder_id, in this case.
		    $aNotificationOptions['event_type'] = "ArchivedDocument";
			$oNotification =& KTSubscriptionNotification::generateSubscriptionNotification($aNotificationOptions);

			// now the email content.
			// FIXME this needs to be handled entirely within notifications from now on.
			if ($oSubscriber->getEmailNotification() && (strlen($oSubscriber->getEmail()) > 0)) {
			    $emailContent = $content->getEmailAlertContent($oNotification);
				$emailSubject = $content->getEmailAlertSubject($oNotification);
				$oEmail = new EmailAlert($oSubscriber->getEmail(), $emailSubject, $emailContent);
				$oEmail->send();
			}
		}


        $aUsers = $this->_getSubscribers($oParentFolder->getId(), $this->subscriptionTypes["Folder"]);
		foreach ($aUsers as $oSubscriber) {

		    // notification object first.
			$aNotificationOptions = array();
			$aNotificationOptions['target_user'] = $oSubscriber->getID();
		    $aNotificationOptions['actor_id'] = KTUtil::arrayGet($_SESSION,"userID", null); // _won't_ be null.
		    $aNotificationOptions['target_name'] = $oModifiedDocument->getName();
		    $aNotificationOptions['location_name'] = Folder::generateFullFolderPath($oParentFolder->getId());
		    $aNotificationOptions['object_id'] = $oModifiedDocument->getId();  // parent folder_id, in this case.
		    $aNotificationOptions['event_type'] = "ArchivedDocument";
			$oNotification =& KTSubscriptionNotification::generateSubscriptionNotification($aNotificationOptions);

			// now the email content.
			// FIXME this needs to be handled entirely within notifications from now on.
			if ($oSubscriber->getEmailNotification() && (strlen($oSubscriber->getEmail()) > 0)) {
			    $emailContent = $content->getEmailAlertContent($oNotification);
				$emailSubject = $content->getEmailAlertSubject($oNotification);
				$oEmail = new EmailAlert($oSubscriber->getEmail(), $emailSubject, $emailContent);
				$oEmail->send();
			}
		}
    }

    function RestoreDocument($oModifiedDocument, $oParentFolder)  {
        $content = new SubscriptionContent(); // needed for i18n
	    // OK:  two actions:  document registrants, folder registrants.
        $aUsers = $this->_getSubscribers($oModifiedDocument->getId(), $this->subscriptionTypes["Document"]);
		foreach ($aUsers as $oSubscriber) {

		    // notification object first.
			$aNotificationOptions = array();
			$aNotificationOptions['target_user'] = $oSubscriber->getID();
		    $aNotificationOptions['actor_id'] = KTUtil::arrayGet($_SESSION,"userID", null); // _won't_ be null.
		    $aNotificationOptions['target_name'] = $oModifiedDocument->getName();
		    $aNotificationOptions['location_name'] = Folder::generateFullFolderPath($oParentFolder->getId());
		    $aNotificationOptions['object_id'] = $oModifiedDocument->getId();  // parent folder_id, in this case.
		    $aNotificationOptions['event_type'] = "RestoreArchivedDocument";
			$oNotification =& KTSubscriptionNotification::generateSubscriptionNotification($aNotificationOptions);

			// now the email content.
			// FIXME this needs to be handled entirely within notifications from now on.
			if ($oSubscriber->getEmailNotification() && (strlen($oSubscriber->getEmail()) > 0)) {
			    $emailContent = $content->getEmailAlertContent($oNotification);
				$emailSubject = $content->getEmailAlertSubject($oNotification);
				$oEmail = new EmailAlert($oSubscriber->getEmail(), $emailSubject, $emailContent);
				$oEmail->send();
			}
		}


        $aUsers = $this->_getSubscribers($oParentFolder->getId(), $this->subscriptionTypes["Folder"]);
		foreach ($aUsers as $oSubscriber) {

		    // notification object first.
			$aNotificationOptions = array();
			$aNotificationOptions['target_user'] = $oSubscriber->getID();
		    $aNotificationOptions['actor_id'] = KTUtil::arrayGet($_SESSION,"userID", null); // _won't_ be null.
		    $aNotificationOptions['target_name'] = $oModifiedDocument->getName();
		    $aNotificationOptions['location_name'] = Folder::generateFullFolderPath($oParentFolder->getId());
		    $aNotificationOptions['object_id'] = $oModifiedDocument->getId();  // parent folder_id, in this case.
		    $aNotificationOptions['event_type'] = "RestoreArchivedDocument";
			$oNotification =& KTSubscriptionNotification::generateSubscriptionNotification($aNotificationOptions);

			// now the email content.
			// FIXME this needs to be handled entirely within notifications from now on.
			if ($oSubscriber->getEmailNotification() && (strlen($oSubscriber->getEmail()) > 0)) {
			    $emailContent = $content->getEmailAlertContent($oNotification);
				$emailSubject = $content->getEmailAlertSubject($oNotification);
				$oEmail = new EmailAlert($oSubscriber->getEmail(), $emailSubject, $emailContent);
				$oEmail->send();
			}
		}
    }

    function DownloadDocument($oDocument, $oParentFolder)  {
        $content = new SubscriptionContent(); // needed for i18n
	    // OK:  two actions:  document registrants, folder registrants.
        $aUsers = $this->_getSubscribers($oDocument->getId(), $this->subscriptionTypes["Document"]);
		foreach ($aUsers as $oSubscriber) {

		    // notification object first.
			$aNotificationOptions = array();
			$aNotificationOptions['target_user'] = $oSubscriber->getID();
		    $aNotificationOptions['actor_id'] = KTUtil::arrayGet($_SESSION,"userID", null); // _won't_ be null.
		    $aNotificationOptions['target_name'] = $oDocument->getName();
		    $aNotificationOptions['location_name'] = Folder::generateFullFolderPath($oParentFolder->getId());
		    $aNotificationOptions['object_id'] = $oDocument->getId();  // parent folder_id, in this case.
		    $aNotificationOptions['event_type'] = "DownloadDocument";
			$oNotification =& KTSubscriptionNotification::generateSubscriptionNotification($aNotificationOptions);

			// now the email content.
			// FIXME this needs to be handled entirely within notifications from now on.
			if ($oSubscriber->getEmailNotification() && (strlen($oSubscriber->getEmail()) > 0)) {
			    $emailContent = $content->getEmailAlertContent($oNotification);
				$emailSubject = $content->getEmailAlertSubject($oNotification);
				$oEmail = new EmailAlert($oSubscriber->getEmail(), $emailSubject, $emailContent);
				$oEmail->send();
			}
		}


        $aUsers = $this->_getSubscribers($oParentFolder->getId(), $this->subscriptionTypes["Folder"]);
		foreach ($aUsers as $oSubscriber) {

		    // notification object first.
			$aNotificationOptions = array();
			$aNotificationOptions['target_user'] = $oSubscriber->getID();
		    $aNotificationOptions['actor_id'] = KTUtil::arrayGet($_SESSION,"userID", null); // _won't_ be null.
		    $aNotificationOptions['target_name'] = $oDocument->getName();
		    $aNotificationOptions['location_name'] = Folder::generateFullFolderPath($oParentFolder->getId());
		    $aNotificationOptions['object_id'] = $oDocument->getId();  // parent folder_id, in this case.
		    $aNotificationOptions['event_type'] = "DownloadDocument";
			$oNotification =& KTSubscriptionNotification::generateSubscriptionNotification($aNotificationOptions);

			// now the email content.
			// FIXME this needs to be handled entirely within notifications from now on.
			if ($oSubscriber->getEmailNotification() && (strlen($oSubscriber->getEmail()) > 0)) {
			    $emailContent = $content->getEmailAlertContent($oNotification);
				$emailSubject = $content->getEmailAlertSubject($oNotification);
				$oEmail = new EmailAlert($oSubscriber->getEmail(), $emailSubject, $emailContent);
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
        $sQuery = "SELECT user_id FROM " . Subscription::getTableName($iSubType) .  " WHERE " . Subscription::getIdFieldName($iSubType) .  " = ?";
        $aParams = array($iObjectId);

		$aNewUsers = DBUtil::getResultArrayKey(array($sQuery, $aParams), "user_id");

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
            "CopiedDocument" => _kt('Document copied'),
            "ArchivedDocument" => _kt('Document archived'), // can go through and request un-archival (?)
            "DownloadDocument" => _kt('Document downloaded'),
            "RestoredArchivedDocument" => _kt('Document restored'),
            "DiscussDocument" => _kt('Document Discussions updated'),
        );
	}

	function getEmailAlertContent($oKTNotification) {
	    // we can re-use the normal template.
		// however, we need to wrap it - no need for a second template here.
		$str = '<html><body>' . $this->getNotificationAlertContent($oKTNotification) . '</body></html>';
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
        "MovedDocument" => 'document',
        "CopiedDocument" => 'document',
        "ArchivedDocument" => 'document', // can go through and request un-archival (?)
        "DownloadDocument" => 'document',
        "RestoredArchivedDocument" => 'document',
        "DiscussDocument" => 'document');



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
