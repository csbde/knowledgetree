<?php

/**
 * Contains the basic Acl functionality for KTAPI.
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
 *
 * @copyright 2008-2009, KnowledgeTree Inc.
 * @license GNU General Public License version 3
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version Version 0.9
 */

/**
 * KTAPI_Dynamic provides magic getter and setter functionality.
 *
 * To create a getter property XXX, implement functions called getXXX().
 * To create a setter property XXX, implement function called setXXX($property).
 *
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version 0.9
 */
abstract class KTAPI_Dynamic
{
	/**
	 * Generic getter method
	 *
     * @author KnowledgeTree Team
	 * @access protected
	 * @param string $property
	 * @return mixed
	 */
    protected
    function __get($property)
    {
        $method = 'get' . $property;
        if (method_exists($this, $method))
        {
            return call_user_func(array($this, $method));
        }
        throw new Exception('Unknown method ' . get_class($this) . '.' . $method);
    }

	/**
	 * Generic setter method
	 *
     * @author KnowledgeTree Team
	 * @access protected
	 * @param string $property
	 * @param mixed $value
	 */

    protected
    function __set($property, $value)
    {
        $method = 'set' . $property;
        if (method_exists($this, $method))
        {
            call_user_func(array($this, $method), $value);
        }
        throw new Exception('Unknown method ' . get_class($this) . '.' . $method);
    }
}

/**
 * The KTAPIMember class is a base class for KTAPI_User, KTAPI_Group and KTAPI_Role.
 *
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version 0.9
 */
abstract class KTAPI_Member extends KTAPI_Dynamic
{
    public abstract function getId();

    public abstract function getName();
}

/**
 * Encapsulates functionality around a user.
 *
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version 0.9
 */
class KTAPI_User extends KTAPI_Member
{
    /**
     * Reference to the original User object.
     *
     * @access private
     * @var User
     */
    private $user;

    /**
     * Constructor for KTAPI_User. This is private, and can only be constructed by the static getByXXX() functions.
     *
     * @access private
     * @param User $user
     */
    private
    function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Using the id, the user can be resolved.
     *
     * @author KnowledgeTree Team
     * @access public
     * @static
     * @param int $id
     * @return KTAPI_User Returns null if  there is no match.
     */
    public static
    function getById($id)
    {
        $user = User::get($id);

        if (PEAR::isError($user))
        {
            return $user;
        }

        return new KTAPI_User($user);
    }


    /**
     * Using the full name, the user can be resolved.
     *
     * @author KnowledgeTree Team
     * @access public
     * @static
     * @param string $name
     * @return KTAPI_User Returns null if  there is no match.
     */
    public static
    function getByName($name)
    {
        $sql = 'SELECT username FROM users where name=?';
        $username = DBUtil::getOneResultKey(array($sql, array($name)), 'username');

        if (PEAR::isError($username))
        {
            return $username;
        }

        return self::getByUsername($username);
    }

    /**
     * Using the username, the user is resolved.
     *
     * @author KnowledgeTree Team
     * @access public
     * @static
     * @param string $username
     * @return KTAPI_User  Returns null if  there is no match.
     */
    public static
    function getByUsername($username)
    {
        $user = User::getByUserName($username);

        if (PEAR::isError($user))
        {
            return $user;
        }

        return new KTAPI_User($user);
    }

    /**
     * Using the email, the user is resolved.
     *
     * @author KnowledgeTree Team
     * @access public
     * @static
     * @param string $email
     * @return KTAPI_User  Returns null if  there is no match.
     */
    public static
    function getByEmail($email)
    {
        $sql = 'SELECT username FROM users where email=?';
        $username = DBUtil::getOneResultKey(array($sql, $email), 'username');

        if (PEAR::isError($username))
        {
            return $username;
        }

        return self::getByUsername($username);
    }

    /**
     * Returns a list of users matching the filter criteria.
     *
     * @author KnowledgeTree Team
     * @access public
     * @static
     * @param string $filter
     * @param array $options
     * @return array of KTAPI_User
     */
    public static
    function getList($filter = null, $options = null)
    {
        $users = User::getList($filter, $options);

        if (PEAR::isError($users))
        {
            return $users;
        }

        $list = array();
        foreach($users as $user)
        {
            $list[] = new KTAPI_User($user);
        }

        return $list;
    }

    /**
     * Return id property. (readonly)
     *
     * @author KnowledgeTree Team
     * @access public
     * @return integer
     */
    public function getId() { return $this->user->getId(); }

    /**
     * Return username property. (readonly)
     *
     * @author KnowledgeTree Team
     * @access public
     * @return string
     */
    public function getUsername() { return $this->user->getUserName(); }

    /**
     * Return username property. (readonly)
     *
     * @author KnowledgeTree Team
     * @access public
     * @return string
     */
    public function getPassword() { return $this->user->getPassword(); }

    /**
     * Return display name property. (readonly)
     *
     * @author KnowledgeTree Team
     * @access public
     * @return string
     */
    public function getName() { return $this->user->getName(); }

    /**
     * Return email property. (readonly)
     *
     * @author KnowledgeTree Team
     * @access public
     * @return string
     */
    public function getEmail() { return $this->user->getEmail(); }

}

/**
 * Encapsulates functionality around a group.
 *
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version 0.9
 */
class KTAPI_Group extends KTAPI_Member
{
    /**
     * Reference to the original Group object.
     *
     * @access private
     * @var Group
     */
    private $group;

    /**
     * Constructor for KTAPI_Group. This is private, and can only be constructed by the static getByXXX() functions.
     *
     * @author KnowledgeTree Team
     * @access private
     * @param Group $group
     */
    private
    function __construct($group)
    {
        $this->group = $group;
    }

    /**
     * Using the id, the group can be resolved.
     *
     * @author KnowledgeTree Team
     * @access public
     * @static
     * @param int $id
     * @return KTAPI_Group Returns null if  there is no match.
     */
    public static
    function getById($id)
    {
        $group = Group::get($id);

        if (PEAR::isError($group))
        {
            return $group;
        }

        return new KTAPI_Group($group);
    }


    /**
     * Using the name, the group can be resolved.
     *
     * @author KnowledgeTree Team
     * @access public
     * @static
     * @param string $name
     * @return KTAPI_Group Returns null if  there is no match.
     */
    public static
    function getByName($name)
    {
        $group = Group::getByName($name);

        if (PEAR::isError($group))
        {
            return $group;
        }

        return new KTAPI_Group($group);
    }

    /**
     * Returns a list of groups matching the filter criteria.
     *
     * @author KnowledgeTree Team
     * @access public
     * @static
     * @param string $filter
     * @param array $options
     * @return array of KTAPI_Group
     */
    public static
    function getList($filter = null, $options = null)
    {
        $groups = Group::getList($filter, $options);

        if (PEAR::isError($groups))
        {
            return $groups;
        }

        $list = array();
        foreach($groups as $group)
        {
            $list[] = new KTAPI_Group($group);
        }

        return $list;
    }

    /**
     * Return username property. (readonly)
     *
     * @author KnowledgeTree Team
     * @access public
     * @return string
     */
    public function getId() { return $this->group->getId(); }

    /**
     * Return display name property. (readonly)
     *
     * @author KnowledgeTree Team
     * @access public
     * @return string
     */
    public function getName() { return $this->group->getName(); }

    /**
     * Indicates if the group members are system administrators. (readonly)
     *
     * @author KnowledgeTree Team
     * @access public
     * @return boolean
     */
    public function getIsSystemAdministrator() { return $this->group->getSysAdmin(); }

}

/**
 * Encapsulates functionality around a role.
 *
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version 0.9
 */
class KTAPI_Role extends KTAPI_Member
{
    /**
     * Reference to the original Role object.
     *
     * @access private
     * @var Role
     */
    private $role;

    /**
     * Constructor for KTAPI_Group. This is private, and can only be constructed by the static getByXXX() functions.
     *
     * @author KnowledgeTree Team
     * @access private
     * @param Role $role
     */
    private
    function __construct($role)
    {
        $this->role = $role;
    }

    /**
     * Using the id, the role can be resolved.
     *
     * @author KnowledgeTree Team
     * @access public
     * @static
     * @param int $id
     * @return KTAPI_Role Returns null if  there is no match.
     */
    public static
    function getById($id)
    {
        $role = Role::get($id);
        if (PEAR::isError($role))
        {
            return $role;
        }

        return new KTAPI_Role($role);
    }

    /**
     * Using the name, the role can be resolved.
     *
     * @author KnowledgeTree Team
     * @access public
     * @static
     * @param string $name
     * @return KTAPI_Role Returns null if  there is no match.
     */
    public static
    function getByName($name)
    {
        $sql = 'SELECT id FROM roles WHERE name=?';
        $id = DBUtil::getOneResultKey(array($sql, array($name)), 'id');
        if (PEAR::isError($id))
        {
            return $id;
        }

        $role = Role::get($id);

        return new KTAPI_Role($role);
    }

    /**
     * Returns a list of roles matching the filter criteria.
     *
     * @author KnowledgeTree Team
     * @access public
     * @static
     * @param string $filter
     * @param array $options
     * @return array of KTAPI_Role
     */
    public static
    function getList($filter = null, $options = array())
    {
        $roles = Role::getList($filter, $options);

        if (PEAR::isError($roles))
        {
            return $roles;
        }

        $list = array();
        foreach($roles as $role)
        {
            $list[] = new KTAPI_Role($role);
        }

        return $list;
    }

    /**
     * Return id property. (readonly)
     *
     * @author KnowledgeTree Team
     * @access public
     * @return string
     */
    public function getId() { return $this->role->getId(); }

    /**
     * Return display name property. (readonly)
     *
     * @author KnowledgeTree Team
     * @access public
     * @return string
     */
    public function getName() { return $this->role->getName(); }

}



/**
 * Encapsulation functionality around a permission.
 *
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version 0.9
 */
class KTAPI_Permission extends KTAPI_Dynamic
{
    /**
     * Reference to the original KTPermission object.
     *
     * @access private
     * @var KTPermission
     */
    private $permission;

    /**
     * Constructor for KTAPI_Permission. This is private, and can only be constructed by the static getByXXX() functions.
     *
     * @author KnowledgeTree Team
     * @access private
     * @param KTPermission $permission
     */
    private
    function __construct($permission)
    {
        $this->permission = $permission;
    }

    /**
     * Return a list of permissions.
     *
     * @author KnowledgeTree Team
     * @access public
     * @static
     * @param string $filter
     * @param array $options
     * @return array of KTAPI_Permission
     */
    public static
    function getList($filter = null, $options = null)
    {
        $permissions = KTPermission::getList($filter);
        if (PEAR::isError($permissions))
        {
            return $permissions;
        }

        $list = array();
        foreach($permissions as $permission)
        {
            $list[] = new KTAPI_Permission($permission);
        }

        return $list;
    }

    /**
     * Returns a KTAPI_Permission based on id.
     *
     * @author KnowledgeTree Team
     * @access public
     * @static
     * @param int $id
     * @return KTAPI_Permission Returns null if the namespace could not be resolved.
     */
    public static
    function getById($id)
    {
        $permission = KTPermission::get($id);

        if (PEAR::isError($permission))
        {
            return $permission;
        }

        return new KTAPI_Permission($permission);
    }

    /**
     * Returns a KTAPI_Permission based on namespace.
     *
     * @author KnowledgeTree Team
     * @access public
     * @static
     * @param string $namespace
     * @return KTAPI_Permission Returns null if the namespace could not be resolved.
     */
    public static
    function getByNamespace($namespace)
    {
        $permission = KTPermission::getByName($namespace);

        if (PEAR::isError($permission))
        {
            return $permission;
        }

        return new KTAPI_Permission($permission);
    }

    /**
     * Returns the permission id.
     *
     * @author KnowledgeTree Team
     * @access public
     * @return int
     */
    public
    function getId() { return $this->permission->getId(); }

    /**
     * Returns the permission name.
     *
     * @author KnowledgeTree Team
     * @access public
     * @return string
     */
    public
    function getName() { return $this->permission->getHumanName(); }

    /**
     * Returns the permission namespace.
     *
     * @author KnowledgeTree Team
     * @access public
     * @return string
     */
    public
    function getNamespace() { return $this->permission->getName(); }
}

/**
 * Allocation Base class
 *
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version 0.9
 */
abstract class KTAPI_AllocationBase extends KTAPI_Dynamic
{
    /**
     * Reference to the original KTAPI_FolderItem object.
     *
     * @access protected
     * @var KTAPI_FolderItem
     */
    protected $folderItem;

    /**
     * A map of the perission allocation
     *
     * @access protected
     * @var array
     */
    protected $map;

    /**
     * A copy of the map that can be restored when required.
     *
     * @access protected
     * @var array
     */
    protected $mapCopy;

    /**
     * Indicates if changes have been made.
     *
     * @access protected
     * @var boolean
     */
    protected $changed;


    /**
     * Instance of the KTAPI object
     *
     * @access protected
     * @var KTAPI
     */
    protected $ktapi;

    /**
     * Constructor for KTAPI_Permission. This is protected, and can only be constructed by the static getAllocation() function.
     *
     * @author KnowledgeTree Team
     * @access protected
     * @param KTAPI_FolderItem $folderItem
     */
    protected
    function __construct(KTAPI $ktapi, KTAPI_FolderItem $folderItem)
    {
        $this->ktapi = $ktapi;
        $this->changed = false;
        $this->folderItem = $folderItem;
        $this->_resolveAllocations();
    }

    /**
     * Helper method to identify the member type for the map.
     *
     * @author KnowledgeTree Team
     * @access protected
     * @param KTAPI_Member $member
     * @return string
     */
    protected
    function _getMemberType(KTAPI_Member $member)
    {
        $type = get_class($member);
        switch($type)
        {
            case 'KTAPI_User': $type = 'user'; break;
            case 'KTAPI_Group': $type = 'group'; break;
            case 'KTAPI_Role': $type = 'role'; break;
            default:
                throw new Exception('Unknown type: ' . $type);
        }
        return $type;
    }

    /**
     * Log the transaction for the current user.
     *
     * @author KnowledgeTree Team
     * @access protected
     * @param string $comment
     * @param string $namespace
     * @return object
     */
    protected
    function _logTransaction($comment, $namespace)
    {
        $type = get_class($this->folderItem);

        $object = $this->folderItem->getObject();
        $objectId = $object->getId();

        switch ($type)
        {
            case 'KTAPI_Folder':
                KTFolderTransaction::createFromArray(array(
                    'folderid' => $objectId,
                    'comment' => $comment,
                    'transactionNS' => $namespace,
                    'userid' => $_SESSION['userID'],
                    'ip' => Session::getClientIP(),
                ));

                break;
            case 'KTAPI_Document':
                DocumentTransaction::createFromArray(array(
                    'folderid' => $objectId,
                    'comment' => $comment,
                    'transactionNS' => $namespace,
                    'userid' => $_SESSION['userID'],
                    'ip' => Session::getClientIP(),
                ));
                break;
            default:
                throw new Exception('Unexpected type: ' . $type);
        }

        return $object;
    }

    /**
     * Restore working copy, voiding the add(), remove() changes.
     *
     * @author KnowledgeTree Team
     * @access public
     */
    public
    function restore()
    {
        $this->map = $this->mapCopy;
        $this->changed = false;
    }

    protected abstract function _resolveAllocations();

    public abstract function inheritAllocation();

    public abstract function overrideAllocation();

    public abstract function save();

}


/**
 * Manages functionality arround permission allocation on a specific folder item.
 *
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version 0.9
 */
final class KTAPI_PermissionAllocation extends KTAPI_AllocationBase
{
    /**
     * Returns the permission allocation for a specified folder item.
     *
     * @author KnowledgeTree Team
     * @access public
     * @static
     * @param KTAPI
     * @param KTAPI_FolderItem
     * @return KTAPI_PermissionAllocation
     */
    public static
    function getAllocation(KTAPI $ktapi, KTAPI_FolderItem $folderItem)
    {
        $permissionAllocation = new KTAPI_PermissionAllocation($ktapi, $folderItem);

        return $permissionAllocation;
    }

    /**
     * Force the current folder item to inherit permission from the parent.
     *
     * @author KnowledgeTree Team
     * @access public
     */
    public
    function inheritAllocation()
    {
        $object = $this->_logTransaction(_kt('Inherit permissions from parent'), 'ktcore.transactions.permissions_change');

        KTPermissionUtil::inheritPermissionObject($object);

        $this->_resolvePermissions();
    }

    /**
     * Creates a copy of the current permissions.
     *
     * @author KnowledgeTree Team
     * @access public
     */
    public
    function overrideAllocation()
    {
        $object = $this->_logTransaction(_kt('Override permissions from parent'), 'ktcore.transactions.permissions_change');

        KTPermissionUtil::copyPermissionObject($object);
    }

    /**
     * Gives permission to the specified member.
     *
     * @author KnowledgeTree Team
     * @access public
     * @param KTAPI_Member $member A KTAPI_Role, KTAPI_Group or KTAPI_User.
     * @param KTAPI_Permission $permission
     */
    public
    function add(KTAPI_Member $member, KTAPI_Permission $permission)
    {
        $this->_set($member, $permission, true);
    }

    /**
     * Removes permission from the specified member.
     * NOTE: This only removes permission if it was already granted.
     *
     * @author KnowledgeTree Team
     * @access public
     * @param KTAPI_Member $member A KTAPI_Role, KTAPI_Group or KTAPI_User.
     * @param KTAPI_Permission $permission
     */
    public
    function remove(KTAPI_Member $member, KTAPI_Permission $permission)
    {
        $this->_set($member, $permission, false);
    }

    /**
     * Helper method to update the permission map for the current folder item.
     *
     * @author KnowledgeTree Team
     * @access private
     * @param KTAPI_Member $member
     * @param KTAPI_Permission $permission
     * @param boolean $on
     */
    private
    function _set(KTAPI_Member $member, KTAPI_Permission $permission, $on)
    {
        // @TODO
        //if (!$this->map['editable'])
        //{
        //    throw new Exception('Cannot edit allocation.');
        //}
        $type = $this->_getMemberType($member);

        $this->changed = true;
        $this->map[$type . 's']['map'][$member->Id][$permission->Id] = $on;
    }

    /**
     * Returns a list of members.
     *
     * @author KnowledgeTree Team
     * @access private
     * @param string $type
     * @return array
     */
    private
    function _getMemberList($type)
    {
        return $this->map[$type]['active'];
    }

    /**
     * Return list of users for which there are allocations.
     *
     * @author KnowledgeTree Team
     * @access public
     * @return array
     */
    public
    function getUsers()
    {
        return $this->_getMemberList('users');
    }

    /**
     * Return list of groups for which there are allocations.
     *
     * @author KnowledgeTree Team
     * @access public
     * @return array
     */
    public
    function getGroups()
    {
        return $this->_getMemberList('groups');
    }

    /**
     * Return list of members for which there are allocations.
     *
     * @author KnowledgeTree Team
     * @access public
     * @return array
     */
    public
    function getRoles()
    {
        return $this->_getMemberList('roles');
    }

    /**
     * Returns the map of permissions for the specific member.
     *
     * @author KnowledgeTree Team
     * @access public
     * @param KTAPI_Member $member
     * @return array
     */
    public
    function getMemberPermissions(KTAPI_Member $member)
    {
        $type = $this->_getMemberType($member);

        return $this->map[$type . 's']['map'][$member->Id];
    }

    /**
     * Returns true if the permission is set for the specific member.
     *
     * @author KnowledgeTree Team
     * @access public
     * @param KTAPI_Member $member
     * @param KTAPI_Permission $permission
     * @return boolean
     */
    public
    function isMemberPermissionSet(KTAPI_Member $member, KTAPI_Permission $permission)
    {
        $type = $this->_getMemberType($member);

        $map = & $this->map[$type . 's']['map'];

        $memberId = $member->Id;
        if (!array_key_exists($memberId, $map))
        {
            return false;
        }

        $permissionId = $permission->Id;
        if (!array_key_exists($permissionId, $map[$memberId]))
        {
            return false;
        }

        return $map[$memberId][$permissionId];
    }

    /**
     * Returns the properties defined in the system.
     *
     * @author KnowledgeTree Team
     * @access public
     * @return array
     *
     */
    public
    function getPermissions()
    {
        return $this->map['permissions'];
    }

    /**
     * Returns an associative array with permissions mapped onto users, groups and roles.
     *
     * @author KnowledgeTree Team
     * @access public
     * @access protected
     */
    protected
    function _resolveAllocations()
    {
        $object = $this->folderItem->getObject();
        $objectId = $object->getPermissionObjectID();

        $oPO = KTPermissionObject::get($objectId);

        $permissions = KTPermission::getList();
        $cleanPermissions = array();

        $map = array(
            'roles' => array('active'=>array(), 'map'=>array()),
            'users' => array('active'=>array(), 'map'=>array()),
            'groups' => array('active'=>array(), 'map'=>array()),
            'permissions' => array()
        );

        foreach($permissions as $permission)
        {
            $permissionId = $permission->getId();
            $cleanPermissions[$permissionId] = false;
            $map['permissions'][$permissionId] = $permission->getHumanName();
        }

        // The next 3 sections of code are slightly repetitive.

        // Get all group permission assignments
        $sql = "SELECT
                    pa.permission_id, g.name, g.id
                FROM
                    permission_assignments pa
                    INNER JOIN permissions p ON p.id = pa.permission_id
                    INNER JOIN permission_descriptor_groups pdg ON pa.permission_descriptor_id = pdg.descriptor_id
                    INNER JOIN groups_lookup g ON pdg.group_id = g.id
                WHERE
                    pa.permission_object_id = ?
                ORDER BY g.name
        ";
        $groupPermissions = DBUtil::getResultArray(array($sql, array($objectId)));
        foreach($groupPermissions as $group)
        {
            $groupId = $group['id'];
            if (!array_key_exists($groupId, $map['groups']['active']))
            {
                $map['groups']['map'][$groupId] = $cleanPermissions;
            }
            $map['groups']['active'][$groupId] = $group['name'];
            $map['groups']['map'][$groupId][$group['permission_id']] = true;
        }

        // Get all role permission assignments

        $sql = "SELECT
                    pa.permission_id, r.name, r.id
                FROM
                    permission_assignments pa
                    INNER JOIN permissions p ON p.id = pa.permission_id
                    INNER JOIN permission_descriptor_roles pdr ON pa.permission_descriptor_id = pdr.descriptor_id
                    INNER JOIN roles r ON pdr.role_id = r.id
                WHERE
                    pa.permission_object_id = ?
                ORDER BY r.name
        ";
        $rolePermissions = DBUtil::getResultArray(array($sql, array($objectId)));
        foreach($rolePermissions as $role)
        {
            $roleId = $role['id'];
            if (!array_key_exists($roleId, $map['roles']['active']))
            {
                $map['roles']['map'][$roleId] = $cleanPermissions;
            }
            $map['roles']['active'][$roleId] = $role['name'];
            $map['roles']['map'][$roleId][$role['permission_id']] = true;
        }

        // Get all user permission assignments

        $sql = "SELECT
                    pa.permission_id, u.name, u.id
                FROM
                    permission_assignments pa
                    INNER JOIN permissions p ON p.id = pa.permission_id
                    INNER JOIN permission_descriptor_users pdu ON pa.permission_descriptor_id = pdu.descriptor_id
                    INNER JOIN users u ON pdu.user_id = u.id
                WHERE
                    pa.permission_object_id = ?
                ORDER BY u.name
        ";

        $userPermissions = DBUtil::getResultArray(array($sql, $objectId));
        foreach($userPermissions as $user)
        {
            $userId = $user['id'];
            if (!array_key_exists($userId, $map['users']['active']))
            {
                $map['users']['map'][$userId] = $cleanPermissions;
            }
            $map['users']['active'][$userId] = $user['name'];
            $map['users']['map'][$userId][$user['permission_id']] = true;
        }

        // resolve editable, inherited, inheritable

        $user = $this->ktapi->get_session()->get_user();

        $editable = KTPermissionUtil::userHasPermissionOnItem($user, 'ktcore.permissions.security', $object) || KTBrowseUtil::inAdminMode($user, $this->folderItem);

        $inherited = KTPermissionUtil::findRootObjectForPermissionObject($oPO);

        $inheritedId = $inherited->getId();
        $objectId = $object->getId();

        $map['inherited'] = ($inheritedId !== $objectId) && ($objectId != 1);

        // only allow inheritance of permissions from parent if not inherited, -and- folder is editable
        $map['inheritable'] = $editable && !$map['inherited'] && ($objectId != 1);

        // only allow edit if the folder is editable and not inherited
        $map['editable'] = $editable && !$map['inherited'];

        $this->map = $map;
        $this->mapCopy = $map;
        $this->changed = false;
    }

    /**
     * Saves changes made by add() and remove().
     *
     * @author KnowledgeTree Team
     * @access public
     */
    public
    function save()
    {
        if (!$this->changed)
        {
            // we don't have to do anything if nothing has changed.
            return;
        }

        // if the current setup is inherited, then we must create a new copy to store the new associations.
        if ($this->getIsInherited())
        {
            $this->overrideAllocation();
        }

        $permissions = KTPermission::getList();

        $folderItemObject = $this->_logTransaction(_kt('Updated permissions'), 'ktcore.transactions.permissions_change');

        $permissionObject = KTPermissionObject::get($folderItemObject->getPermissionObjectId());

        // transform the map into the structure expected

        foreach ($permissions as $permission)
        {
            $permissionId = $permission->getId();

            // not the association is singular here
            $allowed = array('group'=>array(),'role'=>array(),'user'=>array());

            // fill the group allocations
            foreach($this->map['groups']['map'] as $groupId => $allocations )
            {
                if ($allocations[$permissionId])
                {
                    $allowed['group'][] = $groupId;
                }
            }

            // fill the user allocations
            foreach($this->map['users']['map'] as $userId => $allocations )
            {
                if ($allocations[$permissionId])
                {
                    $allowed['user'][] = $userId;
                }
            }

            // fill the role allocations
            foreach($this->map['roles']['map'] as $roleId => $allocations )
            {
                if ($allocations[$permissionId])
                {
                    $allowed['role'][] = $roleId;
                }
            }

            KTPermissionUtil::setPermissionForId($permission, $permissionObject, $allowed);
        }

        KTPermissionUtil::updatePermissionLookupForPO($permissionObject);

        // set the copy to be that of the modified version.

        $this->mapCopy = $this->map;
        $this->changed = false;
    }

    /**
     * Indicates if any changes have been made.
     *
     * @author KnowledgeTree Team
     * @access public
     * @return boolean
     */
    public
    function getHasChanged() { return $this->changed; }

    /**
     * Indicates if the current folder item is allowed to inherit permissions from the parent.
     *
     * @author KnowledgeTree Team
     * @access public
     * @return boolean
     */
    public
    function getIsInheritable() { return $this->map['inheritable']; }

    /**
     * Indicates it the current folder item currently inherits the permissions from the parent.
     *
     * @author KnowledgeTree Team
     * @access public
     * @return boolean
     */
    public
    function getIsInherited() { return $this->map['inherited']; }

    /**
     * Indicates if the permissions are editable but the current user.
     *
     * @author KnowledgeTree Team
     * @access public
     * @return boolean
     */
    public
    function getIsEditable() { return $this->map['editable']; }

}

/**
 * Manages functionality arround role allocation on a specific folder item.
 *
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version 0.9
 */
final class KTAPI_RoleAllocation extends KTAPI_AllocationBase
{
	/**
	 * Resolve the folder item allocations
	 *
     * @author KnowledgeTree Team
     * @access public
	 * @access protected
	 */
    protected
    function _resolveAllocations()
    {
        $object = $this->folderItem->getObject();
        $objectId = $object->getId();

        $map = array(
            'user'=>array(),
            'group'=>array(),
            'role'=>array('role'=>array(), 'userAllocation'=>array(),'groupAllocation'=>array()),
        );

        // Get allocation of users to to role
        $sql = 'SELECT
                    ra.role_id, r.name as rolename, pdu.user_id, u.name as username
                FROM
                    role_allocations ra
                    INNER JOIN roles r ON ra.role_id = r.id
                    INNER JOIN permission_descriptor_users pdu ON ra.permission_descriptor_id = pdu.descriptor_id
                    INNER JOIN users u ON u.id = pdu.user_id
                WHERE
                    ra.folder_id = ?';
        $allocations = DBUtil::getResultArray(array($sql, array($objectId)));

        foreach($allocations as $allocation)
        {
            $userId = $allocation['user_id'];
            $roleId = $allocation['role_id'];
            $map['user'][$userId] = $allocation['username'];
            $map['role']['role'][$roleId] = $allocation['rolename'];
            $map['role']['userAllocation'][$roleId][$userId] = $userId;
        }

        // Get allocation of users to to role
        $sql = 'SELECT
                    ra.role_id, r.name as rolename, pdg.group_id, g.name as groupname
                FROM
                    role_allocations ra
                    INNER JOIN roles r ON ra.role_id = r.id
                    INNER JOIN permission_descriptor_groups pdg ON ra.permission_descriptor_id = pdg.descriptor_id
                    INNER JOIN groups_lookup g ON g.id = pdg.group_id
                WHERE
                    ra.folder_id = ?';
        $allocations = DBUtil::getResultArray(array($sql, $objectId));

        foreach($allocations as $allocation)
        {
            $groupId = $allocation['group_id'];
            $roleId = $allocation['role_id'];
            $map['group'][$groupId] = $allocation['groupname'];
            $map['role']['role'][$roleId] = $allocation['rolename'];
            $map['role']['groupAllocation'][$roleId][$groupId] = $groupId;
        }

        // create the map
        $this->map = $map;
        $this->mapCopy = $map;
        $this->changed = false;
    }


    /**
     * Returns a reference to the role alloction on a folder item.
     *
     * @author KnowledgeTree Team
     * @access public
     * @static
     * @param KTAPI $ktapi
     * @param KTAPI_FolderItem $folderItem
     * @return KTAPI_RoleAllocation
     */
    public static
    function getAllocation(KTAPI $ktapi, KTAPI_FolderItem $folderItem)
    {
        $allocation = new KTAPI_RoleAllocation($ktapi, $folderItem);
        return $allocation;
    }

    /**
     * Return an array mapping the membership.
     *
     * @author KnowledgeTree Team
     * @access public
     * @static
     * @param string $filter
     * @param array $options
     * @return array of KTAPIMember
     */
    public
    function getMembership($filter = null, $options = array())
    {
        $membership = array();
        $map = $this->map;

        if(empty($map['role']['role'])) return array();

        foreach ($map['role']['role'] as $roleId => $role){

            // If the filter doesn't match, skip
            if(!is_null($filter) && strpos($role, $filter) === false || strpos($role, $filter) != 0){
                continue;
            }

            $users = array();
            $groups = array();

            // Get users in role
            if(isset($map['role']['userAllocation'][$roleId])){
                foreach ($map['role']['userAllocation'][$roleId] as $userId){
                    $users[$userId] = $map['user'][$userId];
                }
            }

            // Get groups in role
            if(isset($map['role']['groupAllocation'][$roleId])){
                foreach($map['role']['groupAllocation'][$roleId] as $groupId){
                    $groups[$groupId] = $map['group'][$groupId];
                }
            }

            // Assign to membership array
            $membership[$role]['user'] = $users;
            $membership[$role]['group'] = $groups;
        }

        return $membership; // array of (role=>array(user=>array(), group=>array()))
    }

    /**
     * Link a member to a role on the current folder item.
     *
     * @author KnowledgeTree Team
     * @access public
     * @param KTAPI_Role $role Must be a KTAPIRole, or an array of roles.
     * @param KTAPI_Member $member A KTAPI_Group, KTAPI_User, array.
     */
    public
    function add(KTAPI_Role $role, KTAPI_Member $member)
    {
        $map = & $this->map;
        $type = $this->_getMemberType($member);

        $memberId = $member->Id;
        $map[$type][$memberId] = $member->Name;

        $roleId = $role->Id;

        $map['role']['role'][$roleId] = $role->Name;

        $allocation = $type . 'Allocation';
        if (!array_key_exists($roleId, $map['role'][$allocation]))
        {
            $map['role'][$allocation][$roleId] = array();
        }
        if (array_key_exists($memberId, $map['role'][$allocation][$roleId]))
        {
            // if the key exists, we don't have to do anything.
            return;
        }
        $map['role'][$allocation][$roleId][$memberId] = $memberId;

        $this->changed = true;
    }

    /**
     * Remove a member from a role on the current folder item.
     *
     * @author KnowledgeTree Team
     * @access public
     * @param KTAPI_Role $role Must be a KTAPIRole, or an array of roles.
     * @param KTAPI_Member $member A KTAPI_Group or KTAPI_User.
     */
    public
    function remove(KTAPI_Role $role, KTAPI_Member $member)
    {
        $map = & $this->map;

        $roleId = $role->Id;
        $memberId = $member->Id;

        $type = $this->_getMemberType($member);
        $allocation = $type . 'Allocation';

        $array = & $map['role'][$allocation][$roleId];

        if (array_key_exists($memberId, $array))
        {
            unset($array[$memberId]);
        }
        $this->changed = true;
    }

    /**
     * Check's if a role has a specific member
     *
     * @author KnowledgeTree Team
     * @access public
     * @param KTAPI_Role $role
     * @param KTAPI_Member $member
     * @return boolean
     */
    public
    function doesRoleHaveMember(KTAPI_Role $role, KTAPI_Member $member)
    {
        $map = & $this->map;

        $roleId = $role->Id;
        $memberId = $member->Id;

        $type = $this->_getMemberType($member);
        $allocation = $type . 'Allocation';

        if (!array_key_exists($roleId, $map['role'][$allocation]))
        {
            return false;
        }

        $array = & $map['role'][$allocation][$roleId];

        return (array_key_exists($memberId, $array));
    }


    /**
     * Removes all members associated with roles on the current folder item.
     *
     * @author KnowledgeTree Team
     * @access public
     * @param KTAPI_Role $role Must be a KTAPI_Role, or an array of roles.
     * @param KTAPI_Member $member A KTAPI_Group or KTAPI_User.
     */
    public
    function removeAll($role = null)
    {
        $map = & $this->map;

        if (is_null($role))
        {
            $map['role']['userAllocation'] = array();
            $map['role']['groupAllocation'] = array();
        }
        else
        {
            $roleId = $role->Id;

            $map['role']['userAllocation'][$roleId] = array();
            $map['role']['groupAllocation'][$roleId] = array();
        }

        $this->changed = true;
    }

    /**
     * Override the role allocation for each role
     *
     * @author KnowledgeTree Team
     * @access public
     */
    public
    function overrideAllocation()
    {
        foreach($this->map['role']['role'] as $roleId=>$roleName)
        {
            $this->overrideRoleAllocation(KTAPI_Role::getById($roleId));
        }
    }

	/**
	 * Overrides a role allocation
	 *
     * @author KnowledgeTree Team
     * @access public
	 * @param KTAPI_Role $role
	 */
    public
    function overrideRoleAllocation(KTAPI_Role $role)
    {
        $roleId = $role->Id;

        $object = $this->folderItem->getObject();
        $objectId = $object->getId();
        $parentId = $object->getParentID();

        // FIXME do we need to check that this role _isn't_ allocated?
        $roleAllocation = new RoleAllocation();
        $roleAllocation->setFolderId($objectId);
        $roleAllocation->setRoleId($roleId);

        // create a new permission descriptor.
        // FIXME we really want to duplicate the original (if it exists)

        $allowed = array(); // no-op, for now.
        $roleAllocation->setAllowed($allowed);
        $res = $roleAllocation->create();

		$this->_logTransaction(_kt('Override parent allocation'), 'ktcore.transactions.role_allocations_change');


        // inherit parent permissions
        $parentAllocation = RoleAllocation::getAllocationsForFolderAndRole($parentId, $roleId);
        if (!is_null($parentAllocation) && !PEAR::isError($parentAllocation))
        {
        	$descriptor = $parentAllocation->getPermissionDescriptor();

        	$allowed = $descriptor->getAllowed();

        	$allowed = array(
        	   'user' => $allowed['user'],
        	   'group' => $allowed['group'],
        	);

        	$roleAllocation->setAllowed($allowed);
        	$res = $roleAllocation->update();

        }

        // regenerate permissions

		$this->_regeneratePermissionsForRole($roleId);
		return $roleAllocation;
    }

    /**
     * Force all roles to inherit role associations.
     *
     * @author KnowledgeTree Team
     * @access public
     */
    public
    function inheritAllocation()
    {
        if (!$this->canInheritRoleAllocation())
        {
            return;
        }

        $this->_logTransaction(_kt('Use parent allocation'), 'ktcore.transactions.role_allocations_change');

        foreach($this->map['role']['role'] as $roleId=>$roleName)
        {
            $this->inheritRoleAllocation(KTAPI_Role::getById($roleId), false);
        }
    }

    /**
     * Can the folder inherit allocations
     *
     * @author KnowledgeTree Team
     * @access public
     * @return unknown
     */
    public
    function canInheritRoleAllocation()
    {
        $object = $this->folderItem->getObject();
        $objectId = $object->getId();

        return ($objectId != 1);
    }

    /**
     * Inherit the role associations from the parent.
     *
     * @author KnowledgeTree Team
     * @access public
     * @param KTAPI_Role $role
     * @param boolean $log
     */
    public
    function inheritRoleAllocation(KTAPI_Role $role, $log = true)
    {
        if (!$this->canInheritRoleAllocation())
        {
            return;
        }

        $object = $this->folderItem->getObject();
        $objectId = $object->getId();

        $roleId = $role->Id;
        if ($log)
        {
            $this->_logTransaction(_kt('Use parent allocation'), 'ktcore.transactions.role_allocations_change');
        }

        $roleAllocation = RoleAllocation::getAllocationsForFolderAndRole($objectId, $roleId);

        $res = $roleAllocation->delete();
        if (PEAR::isError($res))
        {
            return $res;
        }

        if ($res == false)
        {
            return PEAR::raiseError(_kt('Could not inherit allocation from parent.'));
        }

        $this->_regeneratePermissionsForRole($roleId);
    }

    /**
     * Regenerate permissions for a role.
     *
     * Adapted from KTRoleAllocationPlugin::regeneratePermissionsForRole()
     *
     * @author KnowledgeTree Team
     * @access public
     * @access private
     * @param int $iRoleId
     */
    private
	function _regeneratePermissionsForRole($iRoleId)
	{
	    $object = $this->folderItem->getObject();

	    $iStartFolderId = $object->getId();
		/*
		 * 1. find all folders & documents "below" this one which use the role
		 *    definition _active_ (not necessarily present) at this point.
		 * 2. tell permissionutil to regen their permissions.
		 *
		 * The find algorithm is:
		 *
		 *  folder_queue <- (iStartFolderId)
		 *  while folder_queue is not empty:
		 *     active_folder =
		 *     for each folder in the active_folder:
		 *         find folders in _this_ folder without a role-allocation on the iRoleId
		 *            add them to the folder_queue
		 *         update the folder's permissions.
		 *         find documents in this folder:
		 *            update their permissions.
		 */

		$sRoleAllocTable = KTUtil::getTableName('role_allocations');
		$sFolderTable = KTUtil::getTableName('folders');
		$sQuery = sprintf('SELECT f.id as id FROM %s AS f LEFT JOIN %s AS ra ON (f.id = ra.folder_id) WHERE ra.id IS NULL AND f.parent_id = ?', $sFolderTable, $sRoleAllocTable);


		$folder_queue = array($iStartFolderId);
		while (!empty($folder_queue)) {
			$active_folder = array_pop($folder_queue);

			$aParams = array($active_folder);

			$aNewFolders = DBUtil::getResultArrayKey(array($sQuery, $aParams), 'id');
			if (PEAR::isError($aNewFolders)) {
				$this->errorRedirectToMain(_kt('Failure to generate folderlisting.'));
			}
			$folder_queue = kt_array_merge ($folder_queue, (array) $aNewFolders); // push.


			// update the folder.
			$oFolder =& Folder::get($active_folder);
			if (PEAR::isError($oFolder) || ($oFolder == false)) {
			    $this->errorRedirectToMain(_kt('Unable to locate folder: ') . $active_folder);
			}

			KTPermissionUtil::updatePermissionLookup($oFolder);
			$aDocList =& Document::getList(array('folder_id = ?', $active_folder));
			if (PEAR::isError($aDocList) || ($aDocList === false)) {
			    $this->errorRedirectToMain(sprintf(_kt('Unable to get documents in folder %s: %s'), $active_folder, $aDocList->getMessage()));
			}

			foreach ($aDocList as $oDoc) {
			    if (!PEAR::isError($oDoc)) {
			        KTPermissionUtil::updatePermissionLookup($oDoc);
				}
			}
		}
	}


	/**
	 * Save's the role allocation
	 *
     * @author KnowledgeTree Team
     * @access public
	 */
    public
    function save()
    {
        if (!$this->changed)
        {
            // we don't have to do anything if nothing has changed.
            return;
        }

        $map = & $this->map;
        $folderId = $this->folderItem->getObject()->getId();

        foreach($map['role']['role'] as $roleId => $roleName)
        {
             $roleAllocation = RoleAllocation::getAllocationsForFolderAndRole($folderId, $roleId);

             $allowed = array();

             $userIds = array();
             $groupIds = array();
             if (array_key_exists($roleId, $map['role']['userAllocation']))
             {
                foreach($map['role']['userAllocation'][$roleId] as $userId)
                {
                    $userIds[] = $userId;
                }
             }
             if (array_key_exists($roleId, $map['role']['groupAllocation']))
             {
                foreach($map['role']['groupAllocation'][$roleId] as $groupId)
                {
                    $groupIds[] = $groupId;
                }
             }

             $allowed['user'] = $userIds;
             $allowed['group'] = $groupIds;

             if (is_null($roleAllocation))
             {
                 $roleAllocation = $this->overrideRoleAllocation(KTAPI_Role::getById($roleId));
             }

             $roleAllocation->setAllowed($allowed);
		     $roleAllocation->update();
        }
    }
}

?>
