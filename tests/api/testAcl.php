<?php
require_once (KTAPI_TEST_DIR . '/test.php');
require_once (KT_DIR . '/ktapi/ktapi.inc.php');

class APIAclTestCase extends KTUnitTestCase {

    /**
     * @var KTAPI
     */
    var $ktapi;
    var $session;

    /**
     * Setup the session
     *
     */
    function setUp() {
        $this->ktapi = new KTAPI();
        $this->session = $this->ktapi->start_system_session();
    }

    /**
     * Logout of the session.
     *
     */
    function tearDown() {
        $this->session->logout();
    }

    /**
     *
     * Test KTAPI_User getList(), getById(), getByName, getByUsername()
     *
     * @TODO KTAPI_User::getByEmail()
     */
    function testUsers()
    {
        // getList()
        $list = KTAPI_User::getList();
        $this->assertTrue(count($list) > 0);

        // getById()
        $user = KTAPI_User::getById(1);
        $this->assertTrue($user->Username == 'admin');
        $this->assertTrue($user->Name == 'Administrator');

        // getByName()
        $user = KTAPI_User::getByName('Anonymous');
        $this->assertTrue($user->Id == -2);

        // getByUsername()
        $user = KTAPI_User::getByUsername('admin');
        $this->assertTrue($user->Id == 1);

    }

    /**
     * Test KTAPI_Group getList(), getById(), getByName
     *
     */
    function testGroups()
    {
        // getList()
        $list = KTAPI_Group::getList();
        $this->assertTrue(count($list) > 0);

        // getById()
        $group = KTAPI_Group::getById(1);
        $this->assertTrue($group->Name == 'System Administrators');

        // getByName()
        $group = KTAPI_Group::getByName('System Administrators');
        $this->assertTrue($group->Id == 1);
        $this->assertTrue($group->IsSystemAdministrator);

    }

    /**
     * Test KTAPI_Role getList(), getById(), getByName
     *
     */
    function testRoles()
    {
        // getList()
        $list = KTAPI_Role::getList();
        $this->assertTrue(count($list) > 0);

        // getById()
        $role = KTAPI_Role::getById(-2);
        $this->assertTrue($role->Name == 'Owner');

        // getByName()
        $role = KTAPI_Role::getByName('Publisher');
        $this->assertTrue($role->Id == 2);
    }

    /**
     * Test KTAPI_Permission getList(), getById(), getByNamespace()
     *
     */
    function testPermission()
    {
        // getList()
        $list = KTAPI_Permission::getList();
        $this->assertTrue(count($list) > 0);

        // getById()
        $permission = KTAPI_Permission::getById(1);
        $this->assertTrue($permission->Namespace == 'ktcore.permissions.read');
        $this->assertTrue($permission->Name == 'Read');

        // getByNamespace()
        $permission = KTAPI_Permission::getByNamespace('ktcore.permissions.write');
        $this->assertTrue($permission->Name == 'Write');
    }

    /**
     * Test KTAPI_RoleAllocation getAllocation(), add(), remove(), save()
     *
     * @TODO finish
     *
     */
    function testRoleAllocation()
    {
        $root = $this->ktapi->get_root_folder();

        $allocation = KTAPI_RoleAllocation::getAllocation($this->ktapi, $root);

        $role = KTAPI_Role::getByName('Publisher');
        $user = KTAPI_User::getByUsername('admin');
        $user2 = KTAPI_User::getByUsername('anonymous');
        $group = KTAPI_Group::getByName('System Administrators');

        $allocation->add($role, $user);
        $allocation->add($role, $user2);
        $allocation->add($role, $user2); // yup. this is a dup. just to test.
        $allocation->add($role, $group);

        $this->assertTrue($allocation->doesRoleHasMember($role, $user));

        $allocation->remove($role, $user);

        $this->assertFalse($allocation->doesRoleHasMember($role, $user));

        $allocation->remove($role, $user2);

        $this->assertFalse($allocation->doesRoleHasMember($role, $user2));

        $allocation->save();
    }

    /**
     * Test KTAPI_PermissionAllocation getAllocation(), add(), remove(), save()
     *
     */
    function testPermissionAllocation()
    {
        $root = $this->ktapi->get_root_folder();

        $allocation = KTAPI_PermissionAllocation::getAllocation($this->ktapi, $root);

        $group = KTAPI_Group::getByName('System Administrators');
        $user = KTAPI_User::getByUsername('anonymous');
        $read = KTAPI_Permission::getByNamespace('ktcore.permissions.read');
        $write = KTAPI_Permission::getByNamespace('ktcore.permissions.write');

        $allocation->add($user, $read);
        $allocation->add($user, $write);
        $allocation->delete($group, $write);

        $allocation->save();


        $root2 = $this->ktapi->get_root_folder();

        $allocation = KTAPI_PermissionAllocation::getAllocation($this->ktapi, $root2);
        $this->assertTrue($allocation->isMemberPermissionSet($user, $read));
        $this->assertTrue($allocation->isMemberPermissionSet($user, $write));

        $this->assertFalse($allocation->isMemberPermissionSet($group, $write));

    }

}
?>

