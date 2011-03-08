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

require_once(KT_DIR . '/lib/memcache/MemCacheUtil.helper.php');
/**
 * Uses the permissions cache to determine whether the current user has access to an object (folder / document)
 *
 */
class PermissionCache
{
    /**
     * The PermissionCache object
     * @access private
     * @var PermissionCache
     */
    private static $permCache;

    /**
     * The Permission Memcache object
     * @access private
     * @var PermissionMemCache
     */
    private $memcache = false;

    /**
     * The name of the cache table
     * @access private
     * @var string
     */
    private $table;

    /**
     * The mapping of the permission namespace to id
     * @access private
     * @var array
     */
    private $permMap;

    private $storeByUser;

    /**
     * Constructor to set up the permissions mapping and the memcache class
     *
     * @access private
     */
    private function __construct()
    {
        $this->storeByUser = TRUE;
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
        } catch (Exception $e) {
            $this->memcache = false;
        }
    }

    /**
     * Singleton pattern - get the existing instance of the class or create a new one
     *
     * @access public
     * @return PermissionCache
     */
    public static function getSingleton()
    {
        if (empty(self::$permCache)) {
            self::$permCache = new PermissionCache();
        }

        return self::$permCache;
    }

    /**
     * Check whether a user has a given permission on an object.
     *
     * @access public
     * @param int $lookupId The permission lookup id for the object (folder | document)
     * @param string $permission The namespace of the permission to check, eg 'ktcore.permissions.read'
     * @param int $userId The id of the user
     * @return boolean True if the user has permission | False if not allowed
     */
    public function checkPermission($lookupId, $permission, $userId = null)
    {
        if (!is_numeric($lookupId)) {
            return false;
        }

        $permId = (isset($this->permMap[$permission])) ? $this->permMap[$permission] : false;

        if (!is_numeric($permId)) {
            return false;
        }

        $userId = is_numeric($userId) ? $userId : $_SESSION['userID'];

        // Validate the users permissions
        // If the userId passed differs from the current user, then validate the cached permissions
        if ($this->memcache !== false) {
            $check = $this->memcache->validateMemcachePermissions();
            if (!$check || $userId != $_SESSION['userID']) {
                $this->validateCachedPermissions($userId);
                unset($_SESSION['Permissions_Cache']);
            }
        }

        if ($this->storeByUser) {
            return $this->checkCachedPermission2($lookupId, $permId, $userId);
        }

        return $this->checkCachedPermission($lookupId, $permId, $userId);
    }

    public function invalidateCache()
    {
        if ($this->memcache !== false) {
            $this->memcache->invalidateMemcachePermissions();
        }

        unset($_SESSION['Permissions_Cache']);
        unset($_SESSION['Permissions_Namespace']);

        return true;
    }

    /**
     * Create / renew the permissions cache for a given user
     *
     * @access public
     * @param int $userId The id of the user
     */
    public function updateCacheForUser($userId = null, $list = null)
    {
        $userId = is_numeric($userId) ? $userId : $_SESSION['userID'];

        if (!is_array($list) || empty($list)) {
            // Get the descriptor ids for the user
            $list = $this->getDescriptors($userId);
        }

        // Get the current set of descriptors
        $cached = $this->getCachedDescriptors($userId);

        // Find the new descriptors
        $new = array_diff($list, $cached);

        // Find the descriptors where the user has been removed
        $removed = array_diff($cached, $list);

        // Insert all the new descriptors
        if (!empty($new)) {
            $fields = array();
            foreach ($new as $descriptor) {
                $fields[] = array('user_id' => $userId, 'descriptor_id' => $descriptor);
            }

            $columns = array('user_id', 'descriptor_id');
            DBUtil::multiInsert($this->table, $columns, $fields);
        }

        // Delete all the removed descriptors
        if (!empty($removed)) {
            foreach ($removed as $descriptor) {
                $fields = array('user_id' => $userId, 'descriptor_id' => $descriptor);

                DBUtil::whereDelete($this->table, $fields);
            }
        }
    }

    /**
     * Get the cached descriptors for the given user
     *
     * @param int $userId The id of the given user
     * @return array
     */
    private function getCachedDescriptors($userId)
    {
        $sql = "SELECT descriptor_id FROM {$this->table}
                WHERE user_id = {$userId}";
        $result = DBUtil::getResultArrayKey($sql, 'descriptor_id');

        if (!is_array($result)) {
            return array();
        }

        return $result;
    }

    /**
     * Clear the permissions cache for a given user - deletes all entries
     *
     * @access private
     * @param int $userId The id of the user
     * @return mixed True on success | PEAR_ERROR on failure
     */
    private function clearCacheForUser($userId)
    {
        $res = DBUtil::whereDelete($this->table, array('user_id' => $userId));
        return $res;
    }

    /**
     * Check whether a given permission lookup id includes any system roles
     * -3: Everyone; -4: Licensed Users
     *
     * @access private
     * @param int $permId The id of the permission to check, eg 1: read permission
     * @param int $lookupId The permission lookup id for the object (folder | document)
     * @param int $userId The id of the user
     * @return boolean True if the system roles give permission | False if not allowed
     */
    private function checkSystemRoles($permId, $lookupId, $userId)
    {
        $sql = "select role_id from permission_descriptor_roles d, permission_lookup_assignments pl
                where d.descriptor_id = pl.permission_descriptor_id
                AND permission_id = {$permId} AND permission_lookup_id = {$lookupId}";

        $result = DBUtil::getResultArrayKey($sql, 'role_id');

        if (in_array(-3, $result)) {
            return true;
        }

        $oUser = User::get($userId);
        if (in_array(-4, $result) && !$oUser->isAnonymous() && $oUser->isLicensed()) {
            return true;
        }

        return false;
    }

    /**
     * Fetch all the permission descriptors containing the user or groups of the user
     *
     * @access private
     * @param int $userId The id of the user
     * @return array The list of descriptor id's
     */
    private function getDescriptors($userId)
    {
        // for groups
        $groupDesc = array();
        $groups = $this->resolveUserGroups($userId);

        if (!empty($groups)) {
            $groupList = implode(', ', $groups);

            $sql = "select descriptor_id from permission_descriptor_groups d
                    where d.group_id in ({$groupList})";

            $groupDesc = DBUtil::getResultArrayKey($sql, 'descriptor_id');
        }

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

    /**
     * Get all groups of which the user is a member and their parent groups
     *
     * @access private
     * @param int $userId The id of the user
     * @return array The list of group id's
     */
    private function resolveUserGroups($userId)
    {
        // Get groups for user
        $sql = "SELECT group_id FROM users_groups_link
                WHERE user_id = {$userId}";
        $userGroups = DBUtil::getResultArrayKey($sql, 'group_id');

        if (empty($userGroups)) {
            return array();
        }

        // Get all sub groups
        $subGroups = GroupUtil::_listSubGroups();

        // Get group heirarchy
        $checkGroups = array();
        foreach ($subGroups as $parent_id => $groups) {
            // find any sub groups that the user is a member of
            $intersect = array_intersect($groups, $userGroups);

            // if there are groups then add them along with the parent group to the list to check against
            if (!empty($intersect)) {
                $checkGroups = array_merge($checkGroups, $intersect);
                $checkGroups[] = $parent_id;
            }
        }

        $checkGroups = array_merge($userGroups, $checkGroups);
        $checkGroups = array_unique($checkGroups);

        return $checkGroups;
    }

    /**
     * Check whether a user has a given permission on an object.
     * The check will access memcache to check, if nothing exists then the database will be checked.
     *
     * @access public
     * @param int $lookupId The permission lookup id for the object (folder | document)
     * @param int $permId The id of the permission to check, eg 1 = read
     * @param int $userId The id of the user
     * @return boolean True if the user has permission | False if not allowed
     */
    private function checkCachedPermission($lookupId, $permId, $userId)
    {
        // Check the permissions in memcache
        if ($this->memcache !== false) {
            $check = $this->memcache->checkPermission($userId, $lookupId, $permId);

            if (is_bool($check)) {
                return $check;
            }

            // if the memcache permission check returns anything other than a boolean value then
            // we use the database to check and reset the memcache key further down
            $check = false;
        }

        $sql = "select p.id from permission_lookup_assignments p, permission_fast_cache c
                where p.permission_descriptor_id = c.descriptor_id AND permission_id = {$permId}
                AND user_id = {$userId} AND permission_lookup_id = {$lookupId}";

        $result = DBUtil::getOneResultKey($sql, 'id');

        if (is_numeric($result) && $result > 0) {
            $check = true;
        } else {
            // Check system roles
            $check = $this->checkSystemRoles($permId, $lookupId, $userId);
        }

        // Set the permission check in memcache
        if ($this->memcache !== false) {
            $this->memcache->setPermission($userId, $lookupId, $permId, $check);
        }

        return $check;
    }

    /**
     * Check whether a user has a given permission on an object.
     * The check will access memcache to check, if nothing exists then the database will be checked.
     *
     * @access public
     * @param int $lookupId The permission lookup id for the object (folder | document)
     * @param int $permId The id of the permission to check, eg 1 = read
     * @param int $userId The id of the user
     * @return boolean True if the user has permission | False if not allowed
     */
    private function checkCachedPermission2($lookupId, $permId, $userId)
    {
        // Get the users permissions from session
        $permissions = isset($_SESSION['Permissions_Cache'][$userId]) ? $_SESSION['Permissions_Cache'][$userId] : false;

        if ($permissions !== false) {
            if (isset($permissions[$lookupId][$permId]) && $permissions[$lookupId][$permId]) {
                return true;
            }
            return $this->checkSystemRoles($permId, $lookupId, $userId);
        }

        // If the permissions are not set in session, get them from memcache
        if ($this->memcache !== false) {
            $permissions = $this->memcache->getUserPermissions($userId);

            if ($permissions !== false) {
                $_SESSION['Permissions_Cache'][$userId] = $permissions;

                if (isset($permissions[$lookupId][$permId]) && $permissions[$lookupId][$permId]) {
                    return true;
                }
                return $this->checkSystemRoles($permId, $lookupId, $userId);
            }
        }

        // The users permissions haven't been cached - now we validate them and cache them
        $this->validateCachedPermissions($userId);

        $sql = "SELECT p.permission_id, p.permission_lookup_id FROM permission_lookup_assignments p, permission_fast_cache c
                WHERE p.permission_descriptor_id = c.descriptor_id AND user_id = {$userId}";

        $result = DBUtil::getResultArray($sql);

        if (PEAR::isError($result) || empty($result)) {
            $_SESSION['Permissions_Cache'][$userId] = array();
            return $this->checkSystemRoles($permId, $lookupId, $userId);
        }

        // Create the correct array structure for easy lookup
        $permissions = array();

        foreach ($result as $key => $item) {
            $lookup = $item['permission_lookup_id'];
            $perm = $item['permission_id'];
            $permissions[$lookup][$perm] = true;
        }

        $_SESSION['Permissions_Cache'][$userId] = $permissions;

        // Set the permissions in memcache
        if ($this->memcache !== false) {
            $this->memcache->setUserPermissions($userId, $permissions);
        }

        if (isset($permissions[$lookupId][$permId]) && $permissions[$lookupId][$permId]) {
            return true;
        }

        return $this->checkSystemRoles($permId, $lookupId, $userId);
    }

    /**
     * Check whether the cached permissions match the system permissions and update them if required.
     *
     * @param int $userId
     * @param bool $update Default true. If set to true the function will update the permissions | If false it will return true/false dependent on the validation
     * @return bool True if validate | False if in need of an update
     */
    private function validateCachedPermissions($userId, $update = true)
    {
        // Get cached descriptors
        $sql = "select descriptor_id from permission_fast_cache
                where user_id = {$userId};";
        $cached = DBUtil::getResultArrayKey($sql, 'descriptor_id');

        // If cache is empty - return false -> needs update
        if (empty($cached) || PEAR::isError($cached)) {
            if ($update) {
                $this->updateCacheForUser($userId);
            }
            return false;
        }

        // Get descriptors for user and compare against cache
        $descriptors = $this->getDescriptors($userId);

        // Check for new descriptors
        $diff = array_diff($descriptors, $cached);

        if (empty($diff)) {
            // Check for removed descriptors
            $diff2 = array_diff($cached, $descriptors);

            if (empty($diff2)) {
                return true;
            }
        }

        if ($update) {
            $this->updateCacheForUser($userId, $descriptors);
        }

        return false;
    }

}


/**
 * Stores and retrieves the users permissions from memcache.
 * Memcache does not support tags or namespaces. A workaround is to create a namespace and prefix it
 * to all the keys stored in memcache.
 * To clear everything associated with the namespace, a new namespace can be created. The old items will expire after
 * a set period of time.
 *
 * For example, when updating the permissions of a given account, to clear the old permissions,
 * each one would require updating individually, with thousands of these, this will be a slow procedure. Instead we use a
 * namespace for each one. Clearing them only requires creating a new namespace leaving the old ones to expire.
 */
class PermissionMemCache
{
    /**
     * The namespace to be used with all permissions in memcache
     * @access private
     * @var string
     */
    private $namespace;

    /**
     * The key for accessing the namespace in memcache
     * @access private
     * @var string
     */
    private $namespaceKey;

    /**
     * The expiration period for all permissions in memcache
     * @access private
     * @var int
     */
    private $expiration;

    /**
     * Initialise the connection to memcache and setup the namespace key.
     *
     * @access public
     */
    public function __construct()
    {
        $enabled = $this->initMemcache();

        if (!$enabled) {
            throw new Exception('Memcache cannot be initialised');
        }

        $this->expiration = 60*60*24*14; // 2 weeks - permissions don't get updated too often

        // Create the key for the namespace using the account name
        $namespaceKey = 'permissions_key';
        if (ACCOUNT_ROUTING_ENABLED) {
            $namespaceKey = ACCOUNT_NAME . '_' . $namespaceKey;
        }
        $this->namespaceKey = $namespaceKey;
        $this->namespace = $this->getNamespace();
    }

    /**
     * Check whether the memcache permissions have been updated.
     * Store the permissions namespace in session to ensure that a permissions update will be propogated.
     *
     * @return bool True if valid | False if invalid
     */
    public function validateMemcachePermissions()
    {
        $session = (isset($_SESSION['Permissions_Namespace']) && !empty($_SESSION['Permissions_Namespace'])) ? $_SESSION['Permissions_Namespace'] : '';

        if (empty($session)) {
            $_SESSION['Permissions_Namespace'] = $this->getNamespace();
            return false;
        }

        $namespace = $this->getNamespace();
        if ($session != $namespace) {
            $_SESSION['Permissions_Namespace'] = $namespace;
            return false;
        }

        return true;
    }

    /**
     * Invalidate the permissions stored in memcache by updating the namespace.
     * Note: the namespace is unique per account so this will invalidate permissions for the current account
     *
     * @access public
     */
    public function invalidateMemcachePermissions()
    {
        $this->setNamespace();
        unset($_SESSION['Permissions_Namespace']);
    }

    /**
     * Create an unique key for the permission based on the user, lookup and permission id and store in memcache
     * The value is stored in memcache as a 1 / 0. A returned value of false from memcache means the key does not exist.
     *
     * @access public
     * @param int $userId The id of the user
     * @param int $lookupId The permission lookup id for the object (folder | document)
     * @param int $permId The id of the permission to check, eg 1: read permission
     * @param boolean $value True if the user has permission | False if not allowed
     */
    public function setPermission($userId, $lookupId, $permId, $value = '')
    {
        $key = $this->namespace . '|' . $userId . '|' . $lookupId . '|' . $permId;
        $value = ($value === true) ? 1 : 0;
        $this->setItem($key, $value);
    }

    /**
     * Check whether a user has a given permission on an object.
     * The check will access memcache to check, if nothing exists then the database will be checked.
     *
     * @access public
     * @param int $userId The id of the user
     * @param int $lookupId The permission lookup id for the object (folder | document)
     * @param int $permId The id of the permission to check, eg 1: read permission
     * @return boolean True if the user has permission | False if not allowed
     */
    public function checkPermission($userId, $lookupId, $permId)
    {
        $key = $this->namespace . '|' . $userId . '|' . $lookupId . '|' . $permId;
        $value = $this->getItem($key);

        if ($value === false) {
            return 'Error';
        }

        $value = ($value === 1) ? true : false;
        return $value;
    }

    /**
     * Get the users permissions
     * The check will access memcache to check, if nothing exists then the database will be checked.
     *
     * @access public
     * @param int $userId The id of the user
     * @return array The list of allowed permissions
     */
    public function getUserPermissions($userId)
    {
        $key = $this->namespace . '|' . $userId;
        $value = $this->getItem($key);
        return $value;
    }

    /**
     * Set the users permissions
     *
     * @access public
     * @param int $userId The id of the user
     * @param array $permissions The list of allowed permissions
     */
    public function setUserPermissions($userId, $permissions)
    {
        $key = $this->namespace . '|' . $userId;
        $this->setItem($key, $permissions);
    }

    /**
     * Get the namespace to be used when storing permissions
     *
     * @access private
     * @return string The namespace
     */
    private function getNamespace()
    {
        $namespace = $this->getItem($this->namespaceKey);

        // If the key doesn't exist or has expired then set a new one.
        if (empty($namespace)) {
            $this->setNamespace();
            $namespace = $this->namespace;
        }

        return $namespace;
    }

    /**
     * Set a new unique namespace.
     * The namespace is a combination of the key and the current timestamp to make it unique.
     *
     * @access private
     */
    private function setNamespace()
    {
        $namespace = $this->namespaceKey . '_' . time();
        $namespace = base64_encode($namespace);
        $expiration = 60*60*24*28; // 4 weeks - can be a bit longer than the individual permissions
        $this->setItem($this->namespaceKey, $namespace);
        $this->namespace = $namespace;
    }

    /**
     * Get an item from memcache based on the supplied key.
     *
     * @access private
     * @param string $key The key of the item in memcache
     * @return mixed
     */
    private function getItem($key)
    {
        $value = MemCacheUtil::get($key);
        return $value;
    }

    /**
     * Set an item in memcache using a reference key and an expiration date.
     * The expiration date will default to the one created in the constructor if nothing is supplied.
     *
     * @access private
     * @param string $key The key of the item in memcache
     * @param mixed $value The value to be stored
     * @param int $expiration The length of time to keep the item beforing expiring / invalidating it
     * @return boolean
     */
    private function setItem($key, $value, $expiration = null)
    {
        $expiration = (is_numeric($expiration)) ? $expiration : $this->expiration;
        $res = MemCacheUtil::set($key, $value, $expiration);
        return $res;
    }

    /**
     * Initialise the connection to the memcache servers
     *
     * @access private
     * @return boolean True if connected | False if not
     */
    private function initMemcache()
    {
        if (MemCacheUtil::$enabled) { return true; }

        $oConfig = KTConfig::getSingleton();
        $enabled = $oConfig->setMemcache();
        return $enabled;
    }
}

?>