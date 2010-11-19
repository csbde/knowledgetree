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

class KTUserUtil {

    public static function createUser($username, $name, $password = null, $email_address = null, $email_notifications = false, $mobile_number = null, $max_sessions = 3, $source_id = null, $details = null, $details2 = null, $disabled_flag = 0)
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

    public static function getUserField($userId, $fieldName = 'name')
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
     * @param boolean $type licensed | unlicensed
     * @return array The lists of newly invited users, failed invitations and already existing users
     */
    public static function inviteUsersByEmail($addressList, $group = null, $type = null, $shareContent = null)
    {
        if (empty($addressList)) {
            $response = array('invited' => 0, 'existing' => '', 'failed' => '', 'group' => '', 'type' => '', 'check' => 0);
            return $response;
        }

        global $default;

        $existingUsers = array();
        $invitedUsers = array();
        $failedUsers = array();
        $groupName = '';

    	$inSystemList = self::checkUniqueEmail($addressList);
    	/*if ($type == 'invited') {
    	   $availableLicenses = (int)BaobabKeyUtil::availableUserLicenses();
    	}*/

    	// loop through any addresses that currently exist and unset them in the invitee list
    	$addressList = array_flip($addressList);
    	foreach ($inSystemList as $item) {
//    	    if ($type != 'shared') {
    	        unset($addressList[$item['email']]);
//    	    }
    	    $existingUsers[] = $item;
    	}
    	$addressList = array_flip($addressList);

    	// Get the group object if a group has been selected
    	// NOTE There is no need to prevent this for unlicensed users as there will be no group selected
    	$group = false;
    	if (is_numeric($group)) {
    	   $group = Group::get($group);

    	   if (PEAR::isError($group)) {
    	       $default->log->error("Invite users. Error on selected group ({$group}) - {$group->getMessage()}");
    	       $group = false;
    	   } else {
    	       $groupName = $group->getName();
    	   }
        }

    	// loop through remaining emails and add to the users table
    	// flag as "invited/shared" => disabled = 3/4
    	// 0 = live; 1 = disabled; 2 = deleted; 3 = invited; 4 = shared
    	$types = array('live' => 0, 'disabled' => 1, 'deleted' => 2, 'invited' => 3, 'shared' => 4);
    	foreach ($addressList as $email) {
            if (empty($email)) {
                continue;
            }
            $user = self::createUser($email, '', null, $email, true, null, 3, null, null, null, $types[$type]);

            if (PEAR::isError($user)) {
               $default->log->error("Invite users. Error on creating invited user ({$email}) - {$user->getMessage()}");
               $failedUsers[] = $email;
               continue;
            }
			
            if ($group !== false) {
               $res = $group->addMember($user);
               if (PEAR::isError($res)) {
                   $default->log->error("Invite users. Error on adding user ({$email}) to group {$group} - {$res->getMessage()}");
                   continue;
               }
            }
            
			if($type == 'shared') {
				self::addSharedContent($user->getId(), $shareContent['object_id'], $shareContent['object_type'], $shareContent['permission']);
			}
            
            $invitedUsers[] = array('id' => $user->getId(), 'email' => $email);
    	}

    	// Send invitation
    	if (!empty($invitedUsers)) {
    	    self::sendInvitations($invitedUsers);
    	}

    	$numInvited = count($invitedUsers);

    	$check = 0;
    	/*if ($type == 'invited') {
    	   $check = self::checkUserLicenses($numInvited, $availableLicenses);
    	}*/

    	// Format the list of existing users
    	$existing = '';
    	if (!empty($existingUsers)){
    	    foreach ($existingUsers as $item){
    	        $existing .= '<li>';
    	        if (!empty($item['name'])) {
    	            $existing .= $item['name'] . ' - ';
    	        }

    	        $existing .= $item['email'] .'</li>';
    	    }
    	}

    	// Format the list of failed email addresses
    	$failed = '';
    	if (!empty($failedUsers)){
    	    foreach ($failedUsers as $item){
    	        $failed .= '<li>'.$item .'</li>';
    	    }
    	}

    	$response = array('invited' => $numInvited, 'existing' => $existing, 'failed' => $failed, 'group' => $groupName, 'type' => $type, 'check' => $check);
    	
    	// Send invitation
    	if (($type == 'shared') && !empty($existingUsers)) {    	    
    	    foreach ($existingUsers as $existingUser) {
    	        self::addSharedContent($existingUser['id'], $shareContent['object_id'], $shareContent['object_type'], $shareContent['permission']);
    	    }
    	    self::sendInvitations($existingUsers);
    	}

    	return $response;
    }

    public static function addSharedContent($user_id, $object_id, $object_type, $permission)
    {
    	global $default;
		// Add shared content entry.
		require_once(KT_LIB_DIR . '/render_helpers/sharedContent.inc');
		$object_type = ($object_type == 'F') ? 'folder' : 'document';
		$oSharedContent = new SharedContent($user_id, $_SESSION['userID'], $object_id, $object_type, $permission);
		// Check for existsing object and delete if it exists.
		if($oSharedContent->exists())
		{
			$oSharedContent->delete();
		}
		$res = $oSharedContent->create();
		if (!$res)
		{
			$default->log->error("Failed sharing " . ($object_type == 'F') ? "folder" : " file " . " $object_id with invited user id $user_id.");
		}

    }
    /**
     * Check how many licenses are available in the system.
     *
     * @param int $iInvited
     * @return int
     */
    public static function checkUserLicenses($iInvited, $iAvailable)
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
     * Create the unique url for the invite and send to the queue
     *
     * @param array $emailList Array of email addresses: format $list[] = array('id' => $id, 'email' => $email)
     * @return bool
     */
    public static function sendInvitations($emailList)
    {
        // Use the current user as the "inviter" / sender of the emails
        // goes into the user array for use in the email
        $oSender = User::get($_SESSION['userID']);

        if (PEAR::isError($oSender) || empty($oSender)) {
            $sender = 'KnowledgeTree';
        } else {
            $sender = $oSender->getName();
        }

        $url = KTUtil::kt_url() . '/users/key/';

        $list = array();
        foreach ($emailList as $item) {
            //$key = 'skfjiwefjaldi';
            $user_id = $item['id'];
            //$user = KTUtil::encode($user, $key);
            $user = (int)$user_id * 354;
            $user = base_convert($user, 10, 25);
            $link = $url . '88' . $user;

            $list[] = array('name' => '', 'email' => $item['email'], 'sender' => $sender, 'link' => $link);
        }

        if (empty($list)) {
            return true;
        }

        global $default;

        $default->log->debug('Invited keys '. json_encode($list));
        $emailFrom = $default->emailFrom;

        if (ACCOUNT_ROUTING_ENABLED) {
            // dispatch queue event
            require_once(KT_LIVE_DIR . '/sqsqueue/dispatchers/queueDispatcher.php');

            $params = array();
            $params['subject'] = _kt('KnowledgeTree Invitation');
            $params['content_html'] = 'email/notifications/invite-user-content.html';
            $params['sender'] = array($emailFrom => 'KnowledgeTree');
            $params['recipients'] = $list;

            $oQueueDispatcher = new queueDispatcher();
        	$oQueueDispatcher->addProcess('mailer', $params);
        	$res = $oQueueDispatcher->sendToQueue();

        	return $res;
        }
        return true;
    }

    /**
     * Check whether an email address is being used within the system
     *
     * @param array $addresses
     * @return array
     */
    public static function checkUniqueEmail($addresses)
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

}


?>