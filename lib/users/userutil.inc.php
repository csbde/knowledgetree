<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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

require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/util/ktutil.inc');

class KTUserUtil {

    static private $objectTypeMap = array('F' => 'folder', 'D' => 'document');  // map object identifiers to full object names

    static public function createUser($username, $name, $password = null, $email_address = null, $email_notifications = false, $mobile_number = null, $max_sessions = 3, $source_id = null, $details = null, $details2 = null, $disabled_flag = 0)
    {
        global $default;

        $dupUser =& User::getByUserName($username);
        if (!PEAR::isError($dupUser)) {
            $default->log->warn('Couldn\'t create user, duplicate username.');
            return PEAR::raiseError(_kt("A user with that username already exists"));
        }

        $user =& User::createFromArray(array(
            "sUsername" => $username,
            "sName" => $name,
            "sPassword" => md5($password),
            "iQuotaMax" => 0,
            "iQuotaCurrent" => 0,
            "sEmail" => $email_address,
            "bEmailNotification" => $email_notifications,
            "sMobile" => $mobile_number,
            "bSmsNotification" => false,   // FIXME do we auto-act if the user has a mobile?
            "iMaxSessions" => $max_sessions,
            "authenticationsourceid" => $source_id,
            "authenticationdetails" => $details,
            "authenticationdetails2" => $details2,
            'disabled' => $disabled_flag,
        ));

        if (PEAR::isError($user) || ($user == false)) {
            $error = ($user === false) ? '' : $user->getMessage();
            $default->log->error('Couldn\'t create user: '. $error);
            return PEAR::raiseError(_kt("failed to create user."));
        }

        // run triggers on user creation
        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('user_create', 'postValidate');

        foreach ($aTriggers as $aTrigger) {
            $sTrigger = $aTrigger[0];
            $oTrigger = new $sTrigger;
            $aInfo = array(
                'user' => $user,
            );
            $oTrigger->setInfo($aInfo);
            $ret = $oTrigger->postValidate();
        }

        return $user;
    }

    static public function getUserField($userId, $fieldName = 'name')
    {
    	if (!is_array($userId)) { $userId = array($userId); }
    	$userId = array_unique($userId, SORT_NUMERIC);
    	if (!is_array($fieldName)) { $fieldName = array($fieldName); }

		//TODO: needs some work
    	$sql = "SELECT " . join(',', $fieldName) . " FROM users WHERE id IN (" . join(',', $userId) . ")";
    	$res = DBUtil::getResultArray($sql);
    	if (PEAR::isError($res) || empty($res)) {
    		return '';
    	} else {
    		return $res;
    	}
    }

    /**
     * Takes a list of email addresses and sends invites to them to become KnowledgeTree users.
     * Users may be either licensed, with full access to the (non-admin) parts of the system,
     * or they may be unlicensed, with access only to content specifically shared with them.
     *
     * @param array $addressList The list of invitee email addresses
     * @param string $group The initial group to add the invitee's to
     * @param string $userType
     * @return array The lists of newly invited users, failed invitations and already existing users
     */
    static public function inviteUsersByEmail($addressList, $groupId = null, $userType = null, $shareContent = null)
    {
        global $default;

        if (empty($addressList)) {
            $response = array('invited' => 0, 'existing' => '', 'failed' => '', 'group' => '', 'type' => '', 'check' => 0);
            return $response;
        }

        $existingUsers = array();
        $invitedUsers = array();
        $failedUsers = array();
        $groupName = '';
    	$group = false;
		$message = '';
		$objectTypeName = null;
		$objectName = null;
    	$inSystemList = self::checkUniqueEmail($addressList);

    	// loop through any addresses that currently exist and unset them in the invitee list
    	$addressList = array_flip($addressList);
    	foreach ($inSystemList as $item) {
   	        unset($addressList[$item['email']]);
    	    $existingUsers[] = $item;
    	}
    	$addressList = array_flip($addressList);

    	// Get the group object if a group has been selected
    	// NOTE There is no need to prevent this for unlicensed users as there will be no group selected
    	if (is_numeric($groupId)) {
    	   $group = Group::get($groupId);

    	   if (PEAR::isError($group)) {
    	       $default->log->error("Invite users. Error on selected group ({$groupId}) - {$group->getMessage()}");
    	       $group = false;
    	   }
    	   else {
    	       $groupName = $group->getName();
    	   }
        }

    	// loop through remaining emails and add to the users table
    	// flag as "invited/shared" => disabled = 3/4
    	// 0 = live; 1 = disabled; 2 = deleted; 3 = invited; 4 = shared
    	$userTypeMap = array('live' => 0, 'disabled' => 1, 'deleted' => 2, 'invited' => 3, 'shared' => 4);
    	foreach ($addressList as $email) {
            if (empty($email)) {
                continue;
            }

            $user = self::createUser($email, '', null, $email, true, null, 3, null, null, null, $userTypeMap[$userType]);
            if (PEAR::isError($user)) {
               $default->log->error("Invite users. Error on creating invited user ({$email}) - {$user->getMessage()}");
               $failedUsers[] = $email;
               continue;
            }

            if ($group !== false) {
               $res = $group->addMember($user);
               if (PEAR::isError($res)) {
                   $default->log->error("Invite users. Error on adding user ({$email}) to group {$groupId} - {$res->getMessage()}");
                   continue;
               }
            }

            $message = isset($shareContent['message']) ? $shareContent['message'] : '';
            $invitedUser = array('id' => $user->getId(), 'email' => $email, 'message' => nl2br($message));

            // additional operations and fields for shared content
            if ($userType == 'shared') {
				self::addSharedContent($user->getId(), $shareContent['object_id'], $shareContent['object_type'], $shareContent['permission']);
            }

            $invitedUsers[] = $invitedUser;
    	}

    	// additional operations and fields for shared content
    	if ($userType == 'shared') {
    	    if (isset(self::$objectTypeMap[$shareContent['object_type']])) {
    	        $objectTypeName = self::$objectTypeMap[$shareContent['object_type']];
    	    }
    	    else {
    	        $objectTypeName = 'Unknown';
    	    }
    	    $objectName = self::getObjectName($shareContent['object_id'], $shareContent['object_type']);
    	}

    	// Send invitation
    	if (!empty($invitedUsers)) {
    	    self::sendInvitations($invitedUsers, $userType, $objectTypeName, $objectName);
    	}

    	$numInvited = count($invitedUsers);
    	$check = 0;
    	// Format the list of existing users
    	$existing = '';
    	if (!empty($existingUsers)) {
    	    foreach ($existingUsers as $item) {
    	        $existing .= '<li>';
    	        if (!empty($item['name'])) {
    	            $existing .= $item['name'] . ' - ';
    	        }
    	        $existing .= $item['email'] .'</li>';
    	    }
    	}

    	// Format the list of failed email addresses
    	$failed = '';
    	if (!empty($failedUsers)) {
    	    foreach ($failedUsers as $item) {
    	        $failed .= '<li>'.$item .'</li>';
    	    }
    	}

    	$response = array(	'invited' => $numInvited,
    						'existing' => $existing,
    						'failed' => $failed,
    						'group' => $groupName,
    						'type' => $userType,
    						'check' => $check,
//    						'hasPermissions' => '',
    						'permMessage' => '',
    						'noPerms' => ''
    					);

    	if (($userType == 'shared') && !empty($existingUsers)) {
    		// TODO : Removed today, will leave here as next week it might be put back!!!...sigh
    		// $response = self::checkPermissions($response, $existingUsers, $shareContent);
    		// add content and send notifications
    		foreach ($existingUsers as $existingUser) {
    		    // Create shared content
    		    self::addSharedContent($existingUser['id'], $shareContent['object_id'], $shareContent['object_type'], $shareContent['permission']);
    		    // TODO if user already exists, add a specific link to the newly shared content and set different link text
    		    // Send a sharing notification to existing users.
    		    self::sendNotifications($existingUsers, $shareContent['object_id'], $objectTypeName, $objectName, $shareContent['message']);
    		}

    		$response['invited'] = count($existingUsers) + (int)$numInvited;
    	}

    	if ($userType == 'shared') {
    	    /*// get list of users with whom content was shared - this can be found in a combination of $invitedUsers and $existingUsers
    	    $userList = array();
    	    $invited = array_merge($invitedUsers, $existingUsers);
    	    foreach ($invited as $user) {
    	       $userList[] = $user['email'];
    	    }
    	    $userList = implode(', ', $userList);*/

    	    // create the transaction record
    	    $s = ($response['invited'] == 1) ? '' : 's';
    	    if ($shareContent['object_type'] == 'D') {
    	        $document = Document::get($shareContent['object_id']);
                $documentTransaction = new DocumentTransaction($document, "Document shared with {$response['invited']} user$s", 'ktcore.transactions.share');
                $documentTransaction->create();
    	    }
    	    else if ($shareContent['object_type'] == 'F') {
    	        $transaction = KTFolderTransaction::createFromArray(array(
                    'folderid' => $shareContent['object_id'],
                    'comment' => "Folder shared with {$response['invited']} user$s",
                    'transactionNS' => 'ktcore.transactions.share',
                    'userid' => $_SESSION['userID'],
                    'ip' => Session::getClientIP(),
    	        	'parentid' => $shareContent['object_id'],	//TODO: need to get the parent ID here!
                ));
    	    }
    	}

    	return $response;
    }

    /**
     * Check permissions on shared objects.
     *
     * @param array $response
     * @param array $existingUsers
     * @param array $shareContent
     * @return $response
     */
    static private function checkPermissions($response, $existingUsers, $shareContent)
    {
    	// Set warning to false
    	$noPermsUsers = array();
//    	$hasPermissions = true;
    	// Send invitation to existing users
	    foreach ($existingUsers as $existingUser)
	    {
	        // Check if system user
	        if ($existingUser['disabled'] != 4)
	        {
				// Get user
	        	$oUser = User::get($existingUser['id']);

	        	// Get permission
	        	if ($shareContent['permission'] == 1)
	        	{
	        		$sPermission = 'ktcore.permissions.write';
	        	}
	        	else
	        	{
	        		$sPermission = 'ktcore.permissions.read';
	        	}

	        	// Get folder or document
	        	if ($shareContent['object_type'] == 'F')
	        	{
	        		$oFolderDocument = Folder::get($shareContent['object_id']);
	        		$action = 'ktcore.actions.folder.permissions';
	        	}
	        	else
	        	{
	        		$oFolderDocument = Document::get($shareContent['object_id']);
	        		$action = 'ktcore.actions.document.permissions';
	        	}

	        	$objectTypeName = self::$objectTypeMap[$shareContent['object_type']];

	        	// Check if user has permission
	        	if (!KTPermissionUtil::userHasPermissionOnItem($oUser, $sPermission, $oFolderDocument))
	        	{
	        		if ($hasPermissions)
	        		{
	        			$hasPermissions = false;
	        			$response['hasPermissions'] = false;
		        		$objectTypeName = ucwords($objectTypeName);
		        		$params = array(	'kt_path_info' => $action,
		        							"f{$objectTypeName}Id" => $shareContent['object_id'],
		        							);
	    				$link = KTUtil::kt_url() . '/' . KTUtil::buildUrl("action.php", $params);
		        		$response['permMessage'] = "Please update permissions for $objectTypeName. <a href='$link' target='_blank'> Permissions </a>";
	        		}

	        		// Store existing system user
	        		$noPermsUsers[] = $existingUser;
	        	}
	        }
	    }

    	$noPerms = '';
    	if (!empty($noPermsUsers))
    	{
    	    foreach ($noPermsUsers as $item)
    	    {
    	        $noPerms .= '<li>';
    	        if (!empty($item['name']))
    	        {
    	            $noPerms .= $item['name'] . ' - ';
    	        }
    	        $noPerms .= $item['email'] .'</li>';
    	    }
    	    $response['noPerms'] = $noPerms;
    	}

    	return $response;
    }

    static public function addSharedContent($user_id, $objectId, $objectTypeId, $permission)
    {
        // Add shared content entry.
        require_once(KT_LIB_DIR . '/render_helpers/sharedContent.inc');
        $oSharedContent = new SharedContent($user_id, $_SESSION['userID'], $objectId, self::$objectTypeMap[$objectTypeId], $permission);

        // Check for existing object and delete if it exists.
        if ($oSharedContent->exists())
        {
            $oSharedContent->delete();
        }

        $res = $oSharedContent->create();
        if (!$res)
        {
            global $default;
            $default->log->error("Failed sharing " . ($objectTypeId == 'F') ? 'folder' : 'file' . " $objectId with invited user id $user_id.");
        }
    }

    /**
     * Check how many licenses are available in the system.
     *
     * @param int $iInvited
     * @return int
     */
    static public function checkUserLicenses($iInvited, $iAvailable)
    {
        if ($iAvailable <= 0) {
            return 1;
        }

        $rem = $iAvailable - (int)$iInvited;
        if ($rem < 0) {
            return 2;
        }

        return 0;
    }

    /**
     * Create the unique url for the invite/share and send to the queue
     *
     * @param array $emailList Array of email addresses: format $list[] = array('id' => $id, 'email' => $email)
     * @return bool
     */
    static public function sendInvitations($emailList, $userType, $objectTypeName = null, $objectName = null)
    {
        $sender = self::getSender();
        $list = array();
        foreach ($emailList as $item) {
			if($userType != 'shared')
			{
				$link = self::createUserLink($item);
				$linktext = 'Click on this link to complete your profile and login';
			}
			else
			{
				$link = self::createSharedLink($item, $objectTypeName);
				$linktext = 'Click on this link to login and view the shared content';
			}
            $list[] = array(	'name' => '',
            					'email' => $item['email'],
            					'sender' => $sender,
            					'contenttype' => $objectTypeName,
            					'contentname' => $objectName,
            					'sender' => $sender,
            					'linktext' => $linktext,
            					'link' => $link,
            					'message' => $item['message'],
            				);
        }

        if (empty($list)) {
            return true;
        }

		if($userType != 'shared')
		{
			return self::sendUserInvite($list);
		}
		else
		{
			return self::sendSharedInvite($list);
		}
    }

    static public function createSharedLink($item, $objectTypeName)
    {
    	// The first shared object will always show in browse view landing.
    	// No need to redirect to it
        return self::createUserLink($item); // . '_' . $item['id'] . '_' . $objectTypeName;
    }

    static public function createUserLink($item)
    {
    	$url = KTUtil::kt_url() . '/users/key/';
        // new user id generation
        $user_id = $item['id'];
        $user = (int)$user_id * 354;
        $user = base_convert($user, 10, 25);
        $link = $url . '88' . $user;

        return $link;
    }

    /**
     * Dispatch shared user invite
     *
     * @param array $list - email parameters
     * @return boolean - true on success, false on failure
     */
    static public function sendSharedInvite($list)
    {
        if (ACCOUNT_ROUTING_ENABLED)
        {
            return self::dispatchQueueEvent($list, _kt('KnowledgeTree Invitation'), 'Send email invite', 'shared-user-content.html');
        }

        return true;
    }

    /**
     * Dispatch user invite
     *
     * @param array $list - email parameters
     * @return boolean - true on success, false on failure
     */
    static public function sendUserInvite($list)
    {
        if (ACCOUNT_ROUTING_ENABLED)
        {
            return self::dispatchQueueEvent($list, _kt('KnowledgeTree Invitation'), 'Send email invite', 'invite-user-content.html');
        }

        return true;
    }

    /**
     * Create the unique url for the invite and send to the queue
     *
     * @param array $emailList Array of email addresses: format $list[] = array('id' => $id, 'email' => $email)
     * @return bool
     */
    static public function sendNotifications($emailList, $objectId, $objectTypeName, $objectName, $message = '')
    {
    	global $default;

        $sender = self::getSender();
        $list = array();

		if (is_null($objectTypeName) || ($objectTypeName == 'document'))
		{
			$objectName = self::getObjectName($objectId, 'D');
		}
		else if ($objectTypeName == 'folder')
		{
			$objectName = self::getObjectName($objectId, 'F');
		}

        foreach ($emailList as $item)
        {
            $list[] = array(	'name' => '',
            					'email' => $item['email'],
            					'sender' => $sender,
            					'contenttype' => $objectTypeName,
            					'contentname' => $objectName,
            					'linktext' => 'Click on this link to view the shared content',
            					'link' => self::createContentLink($objectTypeName, $objectId),
            					'title' => $objectName,
            					'message' => nl2br($message),
            					'type' => $objectType,
            				);
        }

        if (empty($list)) {
            return true;
        }

        $default->log->debug('Invited keys '. json_encode($list));

        if (ACCOUNT_ROUTING_ENABLED) {
            return self::dispatchQueueEvent($list, _kt("$sender wants to share a document using KnowledgeTree"), 'Send notification', 'shared-user-content.html');
        }

        return true;
    }

    /**
     * Create a content url for a document or a folder
     *
     * @param string $objectTypeName - folder or document
     * @param int $objectId - the id of folder or document
     * @return string $link - a link to content
     */
    static public function createContentLink($objectTypeName, $objectId)
    {
    	$server = KTUtil::kt_url();
    	if (is_null($objectTypeName) || ($objectTypeName == 'document'))
    	{
	        $document_link = KTUtil::buildUrl('/view.php', array('fDocumentId' => $objectId));
	        $link = $server . $document_link;
    	}
    	else if ($objectTypeName == 'folder')
    	{
        	$folder_link = KTUtil::buildUrl('/browse.php', array('fFolderId' => $objectId));
        	$link = $server . $folder_link;
    	}

    	return $link;
    }

    /**
     * Dispatch a queue event
     *
     * @param string $emailFrom
     * @param array $list
     * @return boolean
     */
    static private function dispatchQueueEvent($list, $subject, $action, $content, $emailFrom = null)
    {
        if (empty($emailFrom)) {
            global $default;
            $emailFrom = $default->emailFrom;
        }

        // dispatch queue event
        require_once(KT_LIVE_DIR . '/sqsqueue/dispatchers/queueDispatcher.php');

        $params = array();
        $params['subject'] = $subject;
        $params['content_html'] = "email/notifications/$content";
        $params['sender'] = array($emailFrom => 'KnowledgeTree');
        $params['recipients'] = $list;
        $params['action'] = $action;

        $oQueueDispatcher = new queueDispatcher();
        $oQueueDispatcher->addProcess('mailer', $params);
        $res = $oQueueDispatcher->sendToQueue();

        return $res;
    }

    /**
     * Retrieve the current logged in users name
     *
     * @return unknown
     */
    private static function getSender()
    {
        // default if user not found
        $sender = 'KnowledgeTree';

        // Use the current user as the "inviter" / sender of the emails
        // goes into the user array for use in the email
        $oSender = User::get($_SESSION['userID']);

        if (!PEAR::isError($oSender) || empty($oSender)) {
            $sender = $oSender->getName();
        }

        return $sender;
    }

    /**
     * Check whether an email address is being used within the system
     *
     * @param array $addresses
     * @return array
     */
    static public function checkUniqueEmail($addresses)
    {
        if (empty($addresses)) {
            return false;
        }

        if (is_string($addresses)) {
            $addresses = array($addresses);
        }

        if (!is_array($addresses)) {
            return false;
        }

        $list = implode("', '", $addresses);

        // Filter out deleted users (2) and shared users (4)
        $sql = "SELECT u.id, u.name, u.email, u.disabled FROM users u
                WHERE email IN ('{$list}') AND disabled != 2";

        $result = DBUtil::getResultArray($sql);

        return $result;
    }

    /**
     * Retrieve the name of a document or folder
     *
     * @param int $id - the id of the document or folder
     * @param int $objectTypeId - an 'F' for folder and a 'D' for document
     * @return string $name
     */
    static private function getObjectName($id, $objectTypeId)
    {
        $name = null;

        // Get folder or document
        $contentObject = ($objectTypeId == 'F') ? Folder::get($id) : Document::get($id);
        if (!PEAR::isError($contentObject)) {
            $name = $contentObject->getName();
        }

        return $name;
    }

}

?>