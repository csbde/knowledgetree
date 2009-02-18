<?php
require_once (KT_DIR . '/tests/test.php');
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
    * Method to test the user webservice  fucntions
    *
    */
    public function testUsers_KTAPI()
    {
        $response = $this->ktapi->get_user_list();
        $this->assertIsA($response, 'array');
        $this->assertEqual($response['status_code'], 0);
        $this->assertNoErrors();

        $response = $this->ktapi->get_user_by_id(1);
        $this->assertIsA($response, 'array');
        $this->assertEqual($response['status_code'], 0);
        $this->assertEqual($response['results']['name'], 'Administrator');
        $this->assertNoErrors();

        $response = $this->ktapi->get_user_by_username('admin');
        $this->assertIsA($response, 'array');
        $this->assertEqual($response['status_code'], 0);
        $this->assertEqual($response['results']['name'], 'Administrator');
        $this->assertNoErrors();

        $response = $this->ktapi->get_user_by_name('Administrator');
        $this->assertIsA($response, 'array');
        $this->assertEqual($response['status_code'], 0);
        $this->assertEqual($response['results']['name'], 'Administrator');
        $this->assertNoErrors();
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
        $folder = $this->ktapi->get_folder_by_name('test123');
        if(!$folder instanceof KTAPI_Folder){
            $folder = $root->add_folder('test123');
        }
        $allocation = KTAPI_RoleAllocation::getAllocation($this->ktapi, $folder);

        $membership = $allocation->getMembership();
        $this->assertTrue(empty($membership));

        $role2 = KTAPI_Role::getByName('Reviewer');
        $role = KTAPI_Role::getByName('Publisher');
        $user = KTAPI_User::getByUsername('admin');
        $user2 = KTAPI_User::getByUsername('anonymous');
        $group = KTAPI_Group::getByName('System Administrators');

        $this->assertFalse($allocation->doesRoleHaveMember($role, $user));
        $this->assertFalse($allocation->doesRoleHaveMember($role2, $user));

        // Add Admin user to Reviewer role
        $allocation->add($role2, $user);
        // Add Admin user to Publisher role
        $allocation->add($role, $user);
        // Add Sys admin group to Publisher role
        $allocation->add($role, $group);
        // Add Anonymous to Publisher role - duplicate to test
        $allocation->add($role, $user2);
        $allocation->add($role, $user2);
        $allocation->save();

        // Test membership function
        $membership = $allocation->getMembership();
        $this->assertFalse(empty($membership));
        $this->assertIsA($membership, 'array', 'getMembership should return an array');

        $this->assertTrue($membership['Reviewer']['user'][1] == 'Administrator');
        $this->assertTrue($membership['Publisher']['group'][1] == 'System Administrators');

        $membership = $allocation->getMembership('Rev');
        $this->assertFalse(empty($membership));

        $this->assertTrue($membership['Reviewer']['user'][1] == 'Administrator');
        $this->assertFalse(isset($membership['Publisher']));

        $this->assertTrue($allocation->doesRoleHaveMember($role, $user));
        $this->assertTrue($allocation->doesRoleHaveMember($role, $group));

        // Test role removal
        $allocation->remove($role, $user);
        $this->assertFalse($allocation->doesRoleHaveMember($role, $user));

        $allocation->remove($role, $user2);
        $this->assertFalse($allocation->doesRoleHaveMember($role, $user2));

        $allocation->save();

        // now, just overwrite the allocation variable, and check that the assertions still hold.

        $allocation = KTAPI_RoleAllocation::getAllocation($this->ktapi, $root);
        $this->assertFalse($allocation->doesRoleHaveMember($role, $user));
        $this->assertFalse($allocation->doesRoleHaveMember($role, $user2));

        $folder->delete('Testing role allocation');
    }

    /**
     * Test KTAPI_PermissionAllocation getAllocation(), add(), remove(), save()
     *
     */
    function testPermissionAllocation()
    {
        $root = $this->ktapi->get_root_folder();
        $folder = $this->ktapi->get_folder_by_name('test123');
        if(!$folder instanceof KTAPI_Folder){
            $folder = $root->add_folder('test123');
        }

        $allocation = KTAPI_PermissionAllocation::getAllocation($this->ktapi, $folder);

        $group = KTAPI_Group::getByName('System Administrators');
        $user = KTAPI_User::getByUsername('anonymous');
        $role = KTAPI_Role::getByName('Publisher');
        $read = KTAPI_Permission::getByNamespace('ktcore.permissions.read');
        $write = KTAPI_Permission::getByNamespace('ktcore.permissions.write');
        $addFolder = KTAPI_Permission::getByNamespace('ktcore.permissions.addFolder');
        $security = KTAPI_Permission::getByNamespace('ktcore.permissions.security');

        $allocation->add($user, $read);
        $allocation->add($user, $write);
        $allocation->add($user, $addFolder);
        $allocation->add($user, $security);
        $allocation->add($role, $read);
        $allocation->add($role, $write);
        $allocation->remove($group, $write);

        $allocation->save();

        // refresh object and check permission allocations
        $folder2 = $this->ktapi->get_folder_by_name('test123');

        $allocation = KTAPI_PermissionAllocation::getAllocation($this->ktapi, $folder2);
        $this->assertTrue($allocation->isMemberPermissionSet($user, $read));
        $this->assertTrue($allocation->isMemberPermissionSet($user, $write));
        $this->assertTrue($allocation->isMemberPermissionSet($role, $write));
        $this->assertFalse($allocation->isMemberPermissionSet($group, $write));

        $folder->delete('Testing permission allocation');
    }
}
?>