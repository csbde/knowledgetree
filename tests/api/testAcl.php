<?php
require_once (KT_DIR . '/tests/test.php');
require_once (KT_DIR . '/ktapi/ktapi.inc.php');

// username and password for authentication
// must be set correctly for all of the tests to pass in all circumstances
define (KT_TEST_USER, 'admin');
define (KT_TEST_PASS, 'admin');

/**
 * These are the unit tests for the main KTAPI class
 *
 * NOTE All functions which require electronic signature checking need to send
 * the username and password and reason arguments, else the tests WILL fail IF
 * API Electronic Signatures are enabled.
 * Tests will PASS when API Signatures NOT enabled whether or not
 * username/password are sent.
 */
class APIAclTestCase extends KTUnitTestCase {

    /**
     * @var KTAPI
     */
    var $ktapi;
    var $session;
    var $root;

    /**
     * Setup the session
     *
     */
    function setUp() {
        $this->ktapi = new KTAPI();
        $this->session = $this->ktapi->start_system_session();
        $this->root = $this->ktapi->get_root_folder();
    }

    /**
     * Logout of the session.
     *
     */
    function tearDown() {
        $this->session->logout();
    }


    /* *** Testing KTAPI ACL functions *** */

    /**
     * Testing get list of roles
     */
    function testGetRoles()
    {
        $list = $this->ktapi->get_roles();

        $this->assertEqual($list['status_code'], 0);
        $this->assertTrue(!empty($list['results']));

        // filter roles - should return the "Everyone" role
        $list = $this->ktapi->get_roles('Ever');

        $this->assertEqual($list['status_code'], 0);
        $this->assertTrue(!empty($list['results']));
        $this->assertEqual(count($list['results']), 1);
        $this->assertEqual($list['results'][0]['name'], 'Everyone');
    }

    /**
     * Testing get role by id and name
     */
    function testGetRole()
    {
        // get by id -2 - should return system role Owner
        $role = $this->ktapi->get_role_by_id(-2);

        $this->assertEqual($role['status_code'], 0);
        $this->assertTrue(!empty($role['results']));
        $this->assertEqual($role['results']['name'], 'Owner');

        // get by name Authenticated
        $role = $this->ktapi->get_role_by_name('Authenticated Users');

        $this->assertEqual($role['status_code'], 0);
        $this->assertTrue(!empty($role['results']));
        $this->assertEqual($role['results']['name'], 'Authenticated Users');
        $this->assertEqual($role['results']['id'], -4);
    }

    /**
     * Test role allocation on folders
     */
    function testAllocatingMembersToRoles()
    {
        $folder = $this->ktapi->get_folder_by_name('test123');
        if(!$folder instanceof KTAPI_Folder){
            $folder = $this->root->add_folder('test123');
        }
        $folder_id = $folder->get_folderid();

        $allocation = $this->ktapi->get_role_allocation_for_folder($folder_id);
        $this->assertEqual($allocation['status_code'], 0);
        $this->assertTrue(empty($allocation['results']));

        // add a user to a role
        $role_id = 2; // Publisher
        $user_id = 1; // Admin
        $result = $this->ktapi->add_user_to_role_on_folder($folder_id, $role_id, $user_id, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $this->assertEqual($result['status_code'], 0);

        $allocation = $this->ktapi->get_role_allocation_for_folder($folder_id);
        $this->assertEqual($allocation['status_code'], 0);
        $this->assertTrue(isset($allocation['results']['Publisher']));
        $this->assertEqual($allocation['results']['Publisher']['user'][1], 'Administrator');

        // test check on members in the role
        $check = $this->ktapi->is_member_in_role_on_folder($folder_id, $role_id, $user_id, 'user');
        $this->assertEqual($check['status_code'], 0);
        $this->assertEqual($check['results'], 'YES');

        // remove user from a role
        $result = $this->ktapi->remove_user_from_role_on_folder($folder_id, $role_id, $user_id, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $this->assertEqual($result['status_code'], 0);

        $allocation = $this->ktapi->get_role_allocation_for_folder($folder_id);
        $this->assertEqual($allocation['status_code'], 0);
        $this->assertFalse(isset($allocation['results']['Publisher']));

        // clean up
        $folder->delete('Testing API');
    }

    /**
     * Test inherit and override role allocation and remove all allocations
     */
    function testRoleAllocationInheritance()
    {
        $folder = $this->ktapi->get_folder_by_name('test123');
        if(!$folder instanceof KTAPI_Folder){
            $folder = $this->root->add_folder('test123');
        }
        $folder_id = $folder->get_folderid();

        $allocation = $this->ktapi->get_role_allocation_for_folder($folder_id);
        $this->assertEqual($allocation['status_code'], 0);

        // Override
        $result = $this->ktapi->override_role_allocation_on_folder($folder_id, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $this->assertEqual($result['status_code'], 0);

        $role_id = 2; // Publisher
        $user_id = 1; // Admin
        $group_id = 1; // System Administrators
        $members = array('users' => array($user_id), 'groups' => array($group_id));

        $result = $this->ktapi->add_members_to_role_on_folder($folder_id, $role_id, $members, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $this->assertEqual($result['status_code'], 0);

        $check = $this->ktapi->is_member_in_role_on_folder($folder_id, $role_id, $user_id, 'user');
        $this->assertEqual($check['status_code'], 0);
        $this->assertEqual($check['results'], 'YES');

        // Remove all
        $result = $this->ktapi->remove_all_role_allocation_from_folder($folder_id, $role_id, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $this->assertEqual($result['status_code'], 0);

        $check = $this->ktapi->is_member_in_role_on_folder($folder_id, $role_id, $group_id, 'group');
        $this->assertEqual($check['status_code'], 0);
        $this->assertEqual($check['results'], 'NO');

        // Inherit
        $result = $this->ktapi->inherit_role_allocation_on_folder($folder_id, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $this->assertEqual($result['status_code'], 0);

        // clean up
        $folder->delete('Testing API');
    }

    /* *** Testing ACL classes *** */

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
        $this->assertTrue($user->Username == KT_TEST_USER);
        $this->assertTrue($user->Name == 'Administrator');

        // getByName()
        $user = KTAPI_User::getByName('Anonymous');
        $this->assertTrue($user->Id == -2);

        // getByUsername()
        $user = KTAPI_User::getByUsername(KT_TEST_USER);
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

        $response = $this->ktapi->get_user_by_username(KT_TEST_USER);
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
        $user = KTAPI_User::getByUsername(KT_TEST_USER);
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