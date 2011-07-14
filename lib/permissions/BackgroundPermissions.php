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

require_once(KT_LIB_DIR . '/memcache/ktmemcache.php');

class BackgroundPermissions {

    private $folderId;
    private $userId;
    private $accountName;
    private $actionNameSpace = 'ktcore.actions.folder.permissions';
    private $memcacheKey;
    private $duration;
    private $inTransaction = false;
    
    public function __construct($folderId, $accountName)
    {
        $this->folderId = $folderId;
        $this->accountName = $accountName;
        $this->memcacheKey = $this->actionNameSpace . '|' . $this->folderId . '|' . $this->accountName;
    }
    
    public function updatePermissions()
    {
        $info = $this->getInfoFromMemcache();
        
        $startTime = $this->getTime();
        $this->setTransaction('start');
        
        $this->userId = $info['userId'];
        $permissionObjectId = $info['permissionObjectId'];
        $selectedPermissions = $info['selectedPermissions'];
        
        $folder = Folder::get($this->folderId);
        $success = KTPermissionUtil::updatePermissionObject($folder, $permissionObjectId, $selectedPermissions, $this->userId);
        
        if ($success === false) {
            $this->setTransaction('rollback');
            $this->finishUpdate($success, $startTime);
            return ;
        }
        
        $success = KTPermissionUtil::updatePermissionLookupForObject($permissionObjectId, $this->folderId);
        if ($success === false) {
            $this->setTransaction('rollback');
            $this->finishUpdate($success, $startTime);
            return;
        }
        $this->setTransaction('end');
        $this->finishUpdate(true, $startTime);
    }
    
    private function getInfoFromMemcache()
    {
        $memcache = KTMemcache::getKTMemcache();
        $info = $memcache->get($this->memcacheKey);
        return $info;
    }
    
    private function clearMemcacheInfo()
    {
        $memcache = KTMemcache::getKTMemcache();
        $memcache->delete($this->memcacheKey);
    }
    
    public function checkIfBackgrounded()
    {
        $memcache = KTMemcache::getKTMemcache();
        $info = $memcache->get($this->memcacheKey);
        
        if ($info === false) {
            return false;
        }
        
        if (!empty($info)) {
            return true;
        }
        
        return false;
    }
    
    public function backgroundPermissionsUpdate($permissionObjectId, $selectedPermissions, $userId)
    {
        $this->setAsBackgrounded($permissionObjectId, $selectedPermissions, $userId);
    
        global $default;	
    	$phpPath = $default->php;
    	$script =  KT_DIR . '/plugins/ktcore/folder/updatePermissionsTask.php';
    	$arguments = "{$this->folderId} {$this->accountName}";
    	$command = "{$phpPath} {$script} {$arguments} > /dev/null &";
    	
    	KTUtil::pexec($command);
    }
    
    private function setAsBackgrounded($permissionObjectId, $selectedPermissions, $userId)
    {
        $info = array();
        $info['permissionObjectId'] = $permissionObjectId;
        $info['folderId'] = $this->folderId;
        $info['selectedPermissions'] = $selectedPermissions;
        $info['userId'] = $userId;
        
        $expiry = 60*60*5;  // 5 hour expiry on the permissions task - too high? too low?
        
        $memcache = KTMemcache::getKTMemcache();
        $memcache->set($this->memcacheKey, $info, $expiry);
    }
    
    private function finishUpdate($success, $startTime)
    {
        $endTime = $this->getTime();
        $this->duration = round($endTime - $startTime, 2);
        
        $this->clearMemcacheInfo();
        $this->sendNotification($success);
    }
    
    private function setTransaction($status = 'start')
    {
        switch($status) {
            case 'end':
                DBUtil::commit();
                $this->inTransaction = false;
            break;
            
            case 'rollback':
                DBUtil::rollback();
                $this->inTransaction = false;
            break;
            
            case 'start':
            default:
                DBUtil::startTransaction();
                $this->inTransaction = true;
        }
    }
        
    private function getTime()
    {
        $microtime_simple = explode(' ', microtime());
        $time = (float)$microtime_simple[1] + (float)$microtime_simple[0];
        
        return $time;
    }
    
    private function sendNotification($success = true)
    {
        global $default;
        
        $user = User::get($this->userId);
        
        if (PEAR::isError($user)) {
            $default->log->error('Permissions: Error getting user - ' . $user->getMessage());
            return;
        }
        
        $name = $user->getName();
        $emailAddress = $user->getEmail();
        
        $folder = Folder::get($this->folderId);
        $folderName = $folder->getName();
        
        $folderLink = KTUtil::ktLink('action.php', $this->actionNameSpace, 'fFolderId=' . $this->folderId);
        
        $message = _kt("Dear {$name},") . '<br/><br/>';
        
        $link = "<a href='$folderLink'>{$folderName}</a>";
        
        if ($success) {
            $subject = _kt('Permissions Update Completed Successfully.');
            $message .= _kt("Your request has completed successfully and the permissions have been updated on the folder {$link}. ");
            
            $default->log->info("Permissions: Update completed in {$this->duration} seconds");
        } 
        else {
            $subject = _kt('Permissions Update Failed.');
            $message .= _kt("Your request to update the permissions on the folder {$link} has failed. ");
            
            $default->log->error("Permissions: Update failed");
        }
        
        $email = new Email();
        $email->send($emailAddress, $subject, $message);
    }
    
    private function taskKilled($error = '')
    {
        global $default;
        $default->log->error("Permissions: Backgrounded update stopped - {$error}");
        
        $this->finishUpdate($success, time());
        
        if ($this->inTransaction) {
            $this->setTransaction('rollback');
        }
    }
    
    public function handleShutdown()
    {
        $error = error_get_last();
        if ($error['type'] === E_ERROR || $error['type'] === E_CORE_ERROR) {
            $this->taskKilled($error['message']);
        }
    }
    
    public function handleInterrupt($signal)
    {
        $error = 'Process interrupted';
        $this->taskKilled($error);
    }
}

?>