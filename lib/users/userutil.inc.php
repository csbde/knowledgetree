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

class KTUserUtil
{
    static function createUser($username, $name, $password = null, $email_address = null, $email_notifications = false, $mobile_number = null, $max_sessions = 3, $source_id = null, $details = null, $details2 = null)
    {
        $dupUser =& User::getByUserName($username);
        if(!PEAR::isError($dupUser)) {
            return PEAR::raiseError(_kt("A user with that username already exists"));
        }

        $oUser =& User::createFromArray(array(
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
        ));

        if (PEAR::isError($oUser) || ($oUser == false)) {
            return PEAR::raiseError(_kt("failed to create user."));
        }

        // run triggers on user creation
        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('user_create', 'postValidate');

        foreach ($aTriggers as $aTrigger) {
            $sTrigger = $aTrigger[0];
            $oTrigger = new $sTrigger;
            $aInfo = array(
                'user' => $oUser,
            );
            $oTrigger->setInfo($aInfo);
            $ret = $oTrigger->postValidate();
        }

        return $oUser;
    }
    
    public static function getUserField($userId,$fieldName='name'){
    	if(!is_array($userId)){	$userId=array($userId);	}
    	$userId=array_unique($userId,SORT_NUMERIC);
    	if(!is_array($fieldName)){$fieldName=array($fieldName);	}
    	
		//TODO: needs some work
    	$sql="SELECT ".join(',',$fieldName)." FROM users WHERE id IN (".join(',',$userId).")";
    	$res=DBUtil::getResultArray($sql);
//    	print_r($res); die;
    	if(PEAR::isError($res) || empty($res)){
    		return '';
    	}else{
    		return $res;
    	}
    }
}


?>