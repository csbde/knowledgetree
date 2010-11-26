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
 */


/**
 * Uses the permissions cache to determine whether the current user has access to an object (folder / document)
 *
 */
class PermissionCache
{
    private static $permCache;
    private $memcache;
    private $table;
    private $permMap;

    private function __construct()
    {
        $this->table = 'permission_fast_cache';

        $this->permMap = array(
            'ktcore.permissions.read' => 1,
            'ktcore.permissions.write' => 2,
            'ktcore.permissions.addFolder' => 3,
            'ktcore.permissions.security' => 4,
            'ktcore.permissions.delete' => 5,
            'ktcore.permissions.workflow' => 6,
            'ktcore.permissions.folder_details' => 7,
            'ktcore.permissions.folder_rename' => 8
        );

        try {
            $this->memcache = new PermissionMemCache();
        }catch (Exception $e) {
            $this->memcache = false;
        }
    }

    public static function getSingleton()
    {
        if(empty(self::$permCache)){
            self::$permCache = new PermissionCache();
        }
        return self::$permCache;
    }

    public function checkPermission($lookupId, $permission = 'ktcore.permissions.read', $userId = null)
    {
        if(!is_numeric($lookupId)){
            return false;
        }

        $permId = (isset($this->permMap[$permission])) ? $this->permMap[$permission] : false;

        if(!is_numeric($permId)){
            return false;
        }

        $userId = is_numeric($userId) ? $userId : $_SESSION['userID'];

        $sql = "select p.id from permission_lookup_assignments p, permission_fast_cache c
                where p.permission_descriptor_id = c.descriptor_id AND permission_id = {$permId}
                AND user_id = {$userId} AND permission_lookup_id = {$lookupId}";

        $result = DBUtil::getOneResultKey($sql, 'id');

        if(is_numeric($result) && $result > 0){
            return true;
        }

        // Check system roles
        $check = $this->checkSystemRoles($permId, $lookupId, $userId);
        return $check;
    }

    public function updateCacheForUser($userId = null)
    {
        $userId = is_numeric($userId) ? $userId : $_SESSION['userID'];

        // Get the descriptor ids for the user
        $list = $this->getDescriptors($userId);

        // Remove current cache for the user
        $this->clearCacheForUser($userId);

        // Update cache table
        foreach ($list as $descriptor){
            $fields = array('user_id' => $userId, 'descriptor_id' => $descriptor);

            DBUtil::autoInsert($this->table, $fields);
        }
    }

    private function clearCacheForUser($userId)
    {
        $res = DBUtil::whereDelete($this->table, array('user_id' => $userId));
        return $res;
    }

    private function checkSystemRoles($permId, $lookupId, $userId)
    {
        $sql = "select role_id from permission_descriptor_roles d, permission_lookup_assignments pl
                where d.descriptor_id = pl.permission_descriptor_id
                AND permission_id = {$permId} AND permission_lookup_id = {$lookupId}";

        $result = DBUtil::getResultArrayKey($sql, 'role_id');

        if(in_array(-3, $result)){
            return true;
        }

        $oUser = User::get($userId);
        if(in_array(-4, $result) && !$oUser->isAnonymous() && $oUser->isLicensed()){
            return true;
        }
        return false;
    }

    private function getDescriptors($userId)
    {
        // for groups
        $sql = "select descriptor_id from permission_descriptor_groups d, users_groups_link g
                where d.group_id = g.group_id
                and user_id = {$userId}";

        $groupDesc = DBUtil::getResultArrayKey($sql, 'descriptor_id');

        // for users
        $sql = "select descriptor_id from permission_descriptor_users u
                where user_id = {$userId}";

        $userDesc = DBUtil::getResultArrayKey($sql, 'descriptor_id');

        // for roles
        // note: roles where the user is allocated to the role are incorporated in the users and groups
        // only the system roles are not included

        // merge all the descriptors
        $descriptors = array_merge($groupDesc, $userDesc);

        return $descriptors;
    }
}

/**
 * Stores and retrieves the users permissions from memcache
 */
class PermissionMemCache
{
    public function __construct()
    {
        $enabled = $this->initMemcache();

        if(!$enabled){
            throw new Exception('Memcache cannot be initialised');
        }
    }

    public function getItem()
    {}

    public function addItem()
    {}

    public function removeItem()
    {}

    private function initMemcache()
    {
        if(MemCacheUtil::$enabled){
            return true;
        }

        $oConfig = KTConfig::getSingleton();
        $enabled = $oConfig->setMemcache();
        return $enabled;
    }
}

?>