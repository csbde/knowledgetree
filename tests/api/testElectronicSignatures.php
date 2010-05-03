<?php
require_once (KT_DIR . '/tests/test.php');
require_once (KT_DIR . '/ktapi/ktapi.inc.php');

// username and password for authentication
// must be set correctly for all of the tests to pass in all circumstances
define (KT_TEST_USER, 'admin');
define (KT_TEST_PASS, 'admin');

// NOTE These tests may fail if the system isn't clean - i.e. if there are folders and documents
// TODO Change the assert checks to look for the esignature specific messages?

// TODO The following signature enabled functions are NOT currently tested:
// add_folder_user_permissions($username, $folder_id, $namespace, $sig_username = '', $sig_password = '', $reason = '')
// add_folder_role_permissions($role, $folder_id, $namespace, $sig_username = '', $sig_password = '', $reason = '')
// add_folder_group_permissions($group, $folder_id, $namespace, $sig_username = '', $sig_password = '', $reason = '')
// add_group_to_role_on_folder($folder_id, $role_id, $group_id, $sig_username = '', $sig_password = '', $reason = '')
// remove_members_from_role_on_folder($folder_id, $role_id, $members, $sig_username = '', $sig_password = '', $reason = '')
// update_members_on_role_on_folder($folder_id, $role_id, $members, $update = 'add', $sig_username = '', $sig_password = '', $reason = '')
// create_folder_shortcut($target_folder_id, $source_folder_id, $sig_username = '', $sig_password = '', $reason = '')
// create_document_shortcut($target_folder_id, $source_document_id, $sig_username = '', $sig_password = '', $reason = '')
// checkin_document_with_metadata($document_id,  $filename, $reason, $tempfilename, $major_update, $metadata, $sysdata, $sig_username = '', $sig_password = '')
// checkin_small_document($document_id,  $filename, $reason, $base64, $major_update, $sig_username, $sig_password)
// checkin_small_document_with_metadata($document_id,  $filename, $reason, $base64, $major_update, $metadata, $sysdata, $sig_username = '', $sig_password = '')
// update_document_metadata($document_id,$metadata, $sysdata=null, $sig_username = '', $sig_password = '', $reason = '')
// add_document_with_metadata($folder_id,  $title, $filename, $documenttype, $tempfilename, $metadata, $sysdata, $sig_username, $sig_password, $reason)
// add_small_document($folder_id, $title, $filename, $documenttype, $base64, $sig_username, $sig_password, $reason);
// checkout_small_document($document_id, $reason, $download, $sig_username = '', $sig_password = '')
// undo_document_checkout($document_id, $reason, $sig_username = '', $sig_password = '')
// change_document_type($document_id, $documenttype, $sig_username = '', $sig_password = '', $reason = '')
// copy_document($document_id,$folder_id,$reason,$newtitle=null,$newfilename=null, $sig_username = '', $sig_password = '')
// move_document($document_id,$folder_id,$reason,$newtitle=null,$newfilename=null, $sig_username = '', $sig_password = '')
// rename_document_title($document_id,$newtitle, $sig_username = '', $sig_password = '', $reason = '')
// rename_document_filename($document_id,$newfilename, $sig_username = '', $sig_password = '', $reason = '')
// change_document_owner($document_id, $username, $reason, $sig_username = '', $sig_password = '')
// start_document_workflow($document_id,$workflow, $sig_username = '', $sig_password = '', $reason = '')
// delete_document_workflow($document_id, $sig_username = '', $sig_password = '', $reason = '')
// perform_document_workflow_transition($document_id,$transition,$reason, $sig_username = '', $sig_password = '')
// unlink_documents($parent_document_id, $child_document_id, $sig_username = '', $sig_password = '', $reason = '')
// link_documents($parent_document_id, $child_document_id, $type, $sig_username = '', $sig_password = '', $reason = '')
//
// TODO The following are tested via other functions, may want to test directly:
//
// The following are only tested for failure.  They were not tested in the ktapi test code
// I based these tests on and have given issues in the tests:
//
// copy_folder($source_id, $target_id, $reason, $sig_username = '', $sig_password = '')
// move_folder($source_id, $target_id, $reason, $sig_username = '', $sig_password = '')

/**
 * Unit tests specifically for testing the KTAPI functionality with API Electronic Signatures enabled
 * Tests are run for both failure and success, unlike the regular KTAPI tests which only look for a
 * success response on the functions requiring signatures
 *
 * IF API Electronic Signatures are NOT enabled, functions should not try to test
 * add these two lines to the beginning of any new test functions to ensure this:
 * 
 * // if not enabled, do not run remaining tests
 * if (!$this->esig_enabled) return null;
 */
class APIElectronicSignaturesTestCase extends KTUnitTestCase {

    /**
    * @var object $ktapi The main ktapi object
    */
    var $ktapi;

    /**
    * @var object $session The KT session object
    */
    var $session;

    /**
     * @var object $root The KT folder object
     */
    var $root;

    /**
     * @var bool $esig_enabled True if electronic signatures for api are enabled | False if not enabled
     */
    var $esig_enabled;

    /**
    * This method sets up the KT session
    *
    */
    public function setUp() {
        $this->ktapi = new KTAPI();
        $this->session = $this->ktapi->start_session(KT_TEST_USER, KT_TEST_PASS);
        $this->root = $this->ktapi->get_root_folder();
        $this->assertTrue($this->root instanceof KTAPI_Folder);
        $this->esig_enabled = $this->ktapi->electronic_sig_enabled();

        // if not enabled, do not run remaining tests
        if (!$this->esig_enabled) return null;
        
        $this->assertTrue($this->esig_enabled);

        // force reset of lockout status just in case :)
        unset($_SESSION['esignature_attempts']);
        unset($_SESSION['esignature_lock']);
    }

    /**
    * This method emds the KT session
    *
    */
    public function tearDown() {
        $this->session->logout();
    }

    /**
     * Test lockout on multiple failed authentications
     */
    public function testLockout()
    {
        // if not enabled, do not run remaining tests
        if (!$this->esig_enabled) return null;

        // doesn't matter what we call here, just need 3 failed attempts
        // NOTE the number of failed attempts must be changed if there is
        //      a change in the electronic signature definition of the
        //      maximum number of attempts before lockout

        $result = $this->ktapi->create_folder(1, 'New test api folder');
        $this->assertEqual($result['status_code'], 1);
        $result = $this->ktapi->create_folder(1, 'New test api folder');
        $this->assertEqual($result['status_code'], 1);
        $result = $this->ktapi->create_folder(1, 'New test api folder');
        $this->assertEqual($result['status_code'], 1);

        // fourth attempt to check lockout message returned
        $result = $this->ktapi->create_folder(1, 'New test api folder');
        $this->assertEqual($result['status_code'], 1);   
        $eSignature = new ESignature('api');
        $this->assertTrue($result['message'] == $eSignature->getLockMsg());

        // force reset of the lockout so that remaining tests can run :)
        unset($_SESSION['esignature_attempts']);
        unset($_SESSION['esignature_lock']);
    }

    /**
     * Testing folder creation and deletion, add document, get folder contents, folder detail
     * Folder shortcuts and actions
     */
    public function testFolderApiFunctions()
    {
        // if not enabled, do not run remaining tests
        if (!$this->esig_enabled) return null;
        
        // Create a folder
        // test without authentication - should fail
        $result1 = $this->ktapi->create_folder(1, 'New test api folder');
        $this->assertEqual($result1['status_code'], 1);

        // test with authentication
        $result2 = $this->ktapi->create_folder(1, 'New test api folder', KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $folder_id = $result2['results']['id'];
        $this->assertEqual($result2['status_code'], 0);
        $this->assertTrue($result2['results']['parent_id'] == 1);

        // Create a sub folder
        // test without authentication - should fail
        $result3 = $this->ktapi->create_folder($folder_id, 'New test api sub-folder');
        $this->assertEqual($result3['status_code'], 1);

        // test with authentication
        $result4 = $this->ktapi->create_folder($folder_id, 'New test api sub-folder', KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $folder_id2 = $result4['results']['id'];
        $this->assertEqual($result4['status_code'], 0);

        // Add a document
        global $default;
        $dir = $default->uploadDirectory;
        $tempfilename = $this->createRandomFile('some text', $dir);

        // test without authentication - should fail
        $doc = $this->ktapi->add_document($folder_id,  'New API test doc', 'testdoc1.txt', 'Default', $tempfilename);
        $this->assertEqual($doc['status_code'], 1);

        // test with authentication
        $doc = $this->ktapi->add_document($folder_id,  'New API test doc', 'testdoc1.txt', 'Default', $tempfilename,
                                          KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $this->assertEqual($doc['status_code'], 0);
        $doc_id = $doc['results']['document_id'];
        $this->assertEqual($doc['results']['title'], 'New API test doc');

        // Rename the folder
        // test without authentication - should fail
        $renamed = $this->ktapi->rename_folder($folder_id, 'Renamed test folder');
        $this->assertEqual($renamed['status_code'], 1);

        // test with authentication
        $renamed = $this->ktapi->rename_folder($folder_id, 'Renamed test folder', KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $this->assertEqual($renamed['status_code'], 0);

        /**
         * Copy and move appear to fail in other parts of the code, so only going to test failure here
         * 
         * Must be VERY careful here with skipping the valid submissions, 3 failed auth attempts in a row locks out the user!
         */
        // Copy folder
        // test without authentication - should fail
        $copied = $this->ktapi->copy_folder($source_id, $target_id, $reason);
        $this->assertEqual($copied['status_code'], 1);

//        // test with authentication
//        $copied = $this->ktapi->copy_folder($source_id, $target_id, $reason, KT_TEST_USER, KT_TEST_PASS);
//        echo $copied['status_code']."sd<BR>";
//        $this->assertEqual($copied['status_code'], 0);

        // Move folder
        // test without authentication - should fail
        $moved = $this->ktapi->move_folder($source_id, $target_id, $reason);
        $this->assertEqual($moved['status_code'], 1);
        
        // force reset of the lockout so that remaining tests can run :)
        unset($_SESSION['esignature_attempts']);
        unset($_SESSION['esignature_lock']);

//        // test with authentication
//        $moved = $this->ktapi->move_folder($source_id, $target_id, $reason, KT_TEST_USER, KT_TEST_PASS);
//        $this->assertEqual($moved['status_code'], 0);

        // force reset of the lockout so that remaining tests can run :)
        unset($_SESSION['esignature_attempts']);
        unset($_SESSION['esignature_lock']);

        // Clean up - delete the folder
        // test without authentication - should fail
        $deleted = $this->ktapi->delete_folder($folder_id, 'Testing API');
        $this->assertEqual($deleted['status_code'], 1);

        // test with authentication
        $deleted = $this->ktapi->delete_folder($folder_id, 'Testing API', KT_TEST_USER, KT_TEST_PASS);
        $this->assertEqual($deleted['status_code'], 0);
    }

    /**
     * Testing document get, update, actions, delete, shortcuts and detail
     */
    public function testDocumentApiFunctions()
    {
        // if not enabled, do not run remaining tests
        if (!$this->esig_enabled) return null;
        
        // Create a folder
        // test without authentication - should fail
        $result1 = $this->ktapi->create_folder(1, 'New test api folder');
        $this->assertEqual($result1['status_code'], 1);
        
        // test with authentication
        $result2 = $this->ktapi->create_folder(1, 'New test api folder', KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $folder_id = $result2['results']['id'];
        $this->assertEqual($result2['status_code'], 0);
        
        // Create a sub folder
        // test without authentication - should fail
        $result3 = $this->ktapi->create_folder($folder_id, 'New test api sub-folder');
        $this->assertEqual($result3['status_code'], 1);
        
        // test with authentication
        $result4 = $this->ktapi->create_folder($folder_id, 'New test api sub-folder', KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $folder_id2 = $result4['results']['id'];
        $this->assertEqual($result4['status_code'], 0);

        // Add a document
        global $default;
        $dir = $default->uploadDirectory;
        $tempfilename = $this->createRandomFile('some text', $dir);
        
        // test without authentication - should fail
        $doc = $this->ktapi->add_document($folder_id,  'New API test doc', 'testdoc1.txt', 'Default', $tempfilename);
        $this->assertEqual($doc['status_code'], 1);
        
        // test with authentication
        $doc = $this->ktapi->add_document($folder_id,  'New API test doc', 'testdoc1.txt', 'Default', $tempfilename,
                                          KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $this->assertEqual($doc['status_code'], 0);
        $doc_id = $doc['results']['document_id'];
        
        // Checkout the document
        // test without authentication - should fail
        $result1 = $this->ktapi->checkout_document($doc_id, 'Testing API', true);
        $this->assertEqual($result1['status_code'], 1);
        
        // test with authentication
        $result2 = $this->ktapi->checkout_document($doc_id, 'Testing API', true, KT_TEST_USER, KT_TEST_PASS);
        $this->assertEqual($doc['status_code'], 0);
        $this->assertTrue(!empty($result2['results']));

        // Checkin the document
        $dir = $default->uploadDirectory;
        $tempfilename = $this->createRandomFile('some text', $dir);
        // test without authentication - should fail
        $result3 = $this->ktapi->checkin_document($doc_id,  'testdoc1.txt', 'Testing API', $tempfilename, false);
        $this->assertEqual($result3['status_code'], 1);
        
        // test with authentication
        $result4 = $this->ktapi->checkin_document($doc_id,  'testdoc1.txt', 'Testing API', $tempfilename, false, KT_TEST_USER, KT_TEST_PASS);
        $this->assertEqual($result4['status_code'], 0);
        $this->assertEqual($result4['results']['document_id'], $doc_id);
        
        // Delete the document
        // test without authentication - should fail
        $result5 = $this->ktapi->delete_document($doc_id, 'Testing API');
        $this->assertEqual($result5['status_code'], 1);
        
        // test with authentication
        $result6 = $this->ktapi->delete_document($doc_id, 'Testing API', true, KT_TEST_USER, KT_TEST_PASS);
        $this->assertEqual($result6['status_code'], 0);

        // Clean up - delete the folder
        // test without authentication - should fail
        $result7 = $this->ktapi->delete_folder($folder_id, 'Testing API');
        $this->assertEqual($result7['status_code'], 1);
        
        $result8 = $this->ktapi->delete_folder($folder_id, 'Testing API', KT_TEST_USER, KT_TEST_PASS);
        $this->assertEqual($result8['status_code'], 0);
    }

    /**
     * Test role allocation on folders
     */
    function testAllocatingMembersToRoles()
    {
        // if not enabled, do not run remaining tests
        if (!$this->esig_enabled) return null;

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
        // test without authentication - should fail
        $result = $this->ktapi->add_user_to_role_on_folder($folder_id, $role_id, $user_id);
        $this->assertEqual($result['status_code'], 1);
        // test with authentication
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
        // test without authentication - should fail
        $result = $this->ktapi->remove_user_from_role_on_folder($folder_id, $role_id, $user_id);
        $this->assertEqual($result['status_code'], 1);
        // test with authentication
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
        // if not enabled, do not run remaining tests
        if (!$this->esig_enabled) return null;

        $folder = $this->ktapi->get_folder_by_name('test123');
        if(!$folder instanceof KTAPI_Folder){
            $folder = $this->root->add_folder('test123');
        }
        $folder_id = $folder->get_folderid();

        $allocation = $this->ktapi->get_role_allocation_for_folder($folder_id);
        $this->assertEqual($allocation['status_code'], 0);

        // Override
        // test without authentication - should fail
        $result = $this->ktapi->override_role_allocation_on_folder($folder_id);
        $this->assertEqual($result['status_code'], 1);
        // test with authentication
        $result = $this->ktapi->override_role_allocation_on_folder($folder_id, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $this->assertEqual($result['status_code'], 0);

        $role_id = 2; // Publisher
        $user_id = 1; // Admin
        $group_id = 1; // System Administrators
        $members = array('users' => array($user_id), 'groups' => array($group_id));

        // test without authentication - should fail
        $result = $this->ktapi->add_members_to_role_on_folder($folder_id, $role_id, $members);
        $this->assertEqual($result['status_code'], 1);
        // test with authentication
        $result = $this->ktapi->add_members_to_role_on_folder($folder_id, $role_id, $members, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $this->assertEqual($result['status_code'], 0);

        $check = $this->ktapi->is_member_in_role_on_folder($folder_id, $role_id, $user_id, 'user');
        $this->assertEqual($check['status_code'], 0);
        $this->assertEqual($check['results'], 'YES');

        // Remove all
        // test without authentication - should fail
        $result = $this->ktapi->remove_all_role_allocation_from_folder($folder_id, $role_id);
        $this->assertEqual($result['status_code'], 1);
        // test with authentication
        $result = $this->ktapi->remove_all_role_allocation_from_folder($folder_id, $role_id, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $this->assertEqual($result['status_code'], 0);

        $check = $this->ktapi->is_member_in_role_on_folder($folder_id, $role_id, $group_id, 'group');
        $this->assertEqual($check['status_code'], 0);
        $this->assertEqual($check['results'], 'NO');

        // Inherit
        // test without authentication - should fail
        $result = $this->ktapi->inherit_role_allocation_on_folder($folder_id);
        $this->assertEqual($result['status_code'], 1);
        // test with authentication
        $result = $this->ktapi->inherit_role_allocation_on_folder($folder_id, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $this->assertEqual($result['status_code'], 0);

        // clean up
        $folder->delete('Testing API');
    }

    /**
     * Testing the bulk actions - copy, move, delete
     */
    public function testApiBulkCopyMoveDelete()
    {
        // if not enabled, do not run remaining tests
        if (!$this->esig_enabled) return null;

        // Create folder and documents
        $doc1 = $this->createDocument('Test Doc One', 'testdoc1.txt');
        $doc2 = $this->createDocument('Test Doc Two', 'testdoc2.txt');
        $folder1 = $this->root->add_folder("New test folder");
        $this->assertNotError($newFolder);
        if(PEAR::isError($newFolder)) return;

        $doc4 = $this->createDocument('Test Doc Four', 'testdoc4.txt', $folder1);

        $target_folder = $this->root->add_folder("New target folder");
        $this->assertNotError($target_folder);
        if(PEAR::isError($target_folder)) return;
        $target_folder_id = $target_folder->get_folderid();

        $aItems = array();
        $aItems['documents'][] = $doc1->get_documentid();
        $aItems['documents'][] = $doc2->get_documentid();
        $aItems['folders'][] = $folder1->get_folderid();

        // Call bulk action - copy
        // test without authentication - should fail
        $response = $this->ktapi->performBulkAction('copy', $aItems, 'Testing API', $target_folder_id);
        $this->assertEqual($response['status_code'], 1);
        // test with authentication
        $response = $this->ktapi->performBulkAction('copy', $aItems, 'Testing API', $target_folder_id, KT_TEST_USER, KT_TEST_PASS);
        $this->assertEqual($response['status_code'], 0);
        $this->assertTrue(empty($response['results']));

        // Test move action - delete and recreate target folder
        $target_folder->delete('Testing API');

        $target_folder = $this->root->add_folder("New target folder");
        $this->assertNotError($target_folder);
        if(PEAR::isError($target_folder)) return;
        $target_folder_id = $target_folder->get_folderid();

        // test without authentication - should fail
        $response = $this->ktapi->performBulkAction('move', $aItems, 'Testing API', $target_folder_id);
        $this->assertEqual($response['status_code'], 1);
        // test with authentication
        $response = $this->ktapi->performBulkAction('move', $aItems, 'Testing API', $target_folder_id, KT_TEST_USER, KT_TEST_PASS);
        $this->assertEqual($response['status_code'], 0);
        $this->assertTrue(empty($response['results']));

        // test without authentication - should fail
        $response = $this->ktapi->performBulkAction('delete', $aItems, 'Testing API');
        $this->assertEqual($response['status_code'], 1);
        // test with authentication
        $response = $this->ktapi->performBulkAction('delete', $aItems, 'Testing API', null, KT_TEST_USER, KT_TEST_PASS);
        $this->assertEqual($response['status_code'], 0);
        $this->assertTrue(empty($response['results']));

        // Delete and expunge documents and folder
        $target_folder->delete('Testing API');
    }

    /**
     * Testing the bulk actions - checkout and cancel check out
     */
    public function testApiBulkCheckout()
    {
        // if not enabled, do not run remaining tests
        if (!$this->esig_enabled) return null;

        // Create folder and documents
        $doc1 = $this->createDocument('Test Doc One', 'testdoc1.txt');
        $doc2 = $this->createDocument('Test Doc Two', 'testdoc2.txt');
        $folder1 = $this->root->add_folder("New test folder");
        $this->assertNotError($newFolder);
        if(PEAR::isError($newFolder)) return;

        $doc4 = $this->createDocument('Test Doc Four', 'testdoc4.txt', $folder1);

        $doc1_id = $doc1->get_documentid();
        $doc2_id = $doc2->get_documentid();

        $aItems = array();
        $aItems['documents'][] = $doc1_id;
        $aItems['documents'][] = $doc2_id;
        $aItems['folders'][] = $folder1->get_folderid();

        // Call bulk action - checkout
        // test without authentication - should fail
        $response = $this->ktapi->performBulkAction('checkout', $aItems, 'Testing API', null);
        $this->assertEqual($response['status_code'], 1);
        // test with authentication
        $response = $this->ktapi->performBulkAction('checkout', $aItems, 'Testing API', null, KT_TEST_USER, KT_TEST_PASS);
        $this->assertEqual($response['status_code'], 0);
        $this->assertTrue(empty($response['results']));

        // update document object
        $doc1 = $this->ktapi->get_document_by_id($doc1_id);
        $this->assertTrue($doc1->is_checked_out());

        // cancel the checkout
        // test without authentication - should fail
        $response = $this->ktapi->performBulkAction('undo_checkout', $aItems, 'Testing API', null);
        $this->assertEqual($response['status_code'], 1);
        // test with authentication
        $response = $this->ktapi->performBulkAction('undo_checkout', $aItems, 'Testing API', null, KT_TEST_USER, KT_TEST_PASS);
        $this->assertEqual($response['status_code'], 0);
        $this->assertTrue(empty($response['results']));

        // delete items
        // test without authentication - should fail
        $response = $this->ktapi->performBulkAction('delete', $aItems, 'Testing API');
        $this->assertEqual($response['status_code'], 1);
        // test with authentication
        $response = $this->ktapi->performBulkAction('delete', $aItems, 'Testing API', null, KT_TEST_USER, KT_TEST_PASS);
        $this->assertEqual($response['status_code'], 0);
    }

    /**
     * Testing the bulk actions - checkout and cancel check out
     */
    public function testApiBulkImmute()
    {
        // if not enabled, do not run remaining tests
        if (!$this->esig_enabled) return null;

        // Create folder and documents
        $doc1 = $this->createDocument('Test Doc One', 'testdoc1.txt');
        $doc2 = $this->createDocument('Test Doc Two', 'testdoc2.txt');
        $folder1 = $this->root->add_folder("New test folder");
        $this->assertNotError($newFolder);
        if(PEAR::isError($newFolder)) return;

        $doc4 = $this->createDocument('Test Doc Four', 'testdoc4.txt', $folder1);

        $doc1_id = $doc1->get_documentid();
        $doc2_id = $doc2->get_documentid();

        $aItems = array();
        $aItems['documents'][] = $doc1_id;
        $aItems['documents'][] = $doc2_id;
        $aItems['folders'][] = $folder1->get_folderid();

        // Call bulk action - checkout
        // test without authentication - should fail
        $response = $this->ktapi->performBulkAction('immute', $aItems, 'Testing API');
        $this->assertEqual($response['status_code'], 1);
        $doc1 = $this->ktapi->get_document_by_id($doc1_id);
        $this->assertFalse($doc1->isImmutable());
        // test with authentication
        $response = $this->ktapi->performBulkAction('immute', $aItems, 'Testing API', null, KT_TEST_USER, KT_TEST_PASS);
        $this->assertEqual($response['status_code'], 0);
        $this->assertTrue(empty($response['results']));

        // update document object
        $doc1 = $this->ktapi->get_document_by_id($doc1_id);
        $this->assertTrue($doc1->isImmutable());

        // remove immutability for deletion
        $doc1->unimmute();
        $doc2->unimmute();
        $doc4->unimmute();

        // delete items
        // test without authentication - should fail
        $response = $this->ktapi->performBulkAction('delete', $aItems, 'Testing API');
        $this->assertEqual($response['status_code'], 1);
        // test with authentication
        $response = $this->ktapi->performBulkAction('delete', $aItems, 'Testing API', null, KT_TEST_USER, KT_TEST_PASS);
        $this->assertEqual($response['status_code'], 0);
    }

//    /* *** Test Bulk actions class *** */
//
//    /**
//     * Test the bulk copy functionality
//     */
//    function testCopy()
//    {
//        // Create documents
//        $doc1 = $this->createDocument('Test Doc One', 'testdoc1.txt');
//        $doc2 = $this->createDocument('Test Doc Two', 'testdoc2.txt');
//        $doc3 = $this->createDocument('Test Doc Three', 'testdoc3.txt');
//        $folder1 = $this->root->add_folder("New copy folder");
//        $this->assertNotError($newFolder);
//        if(PEAR::isError($newFolder)) return;
//
//        $doc4 = $this->createDocument('Test Doc Four', 'testdoc4.txt', $folder1);
//
//        // Add a folder
//        $targetFolder = $this->root->add_folder("New target folder");
//        $this->assertNotError($newFolder);
//        if(PEAR::isError($newFolder)) return;
//
//        $aItems = array($doc1, $doc2, $doc3, $folder1);
//
//        // Copy documents and folder into target folder
//        $res = $this->bulk->copy($aItems, $targetFolder, 'Testing bulk copy');
//
//        $this->assertTrue(empty($res));
//
//        // Check the documents copied
//        $listDocs = $targetFolder->get_listing(1, 'D');
//        $this->assertTrue(count($listDocs) == 3);
//
//        // Check the folder copied
//        $listFolders = $targetFolder->get_listing(1, 'F');
//        $this->assertTrue(count($listFolders) == 1);
//
//        // Check the document contained in the folder copied
//        $newFolderId = $listFolders[0]['id'];
//        $newFolder = $this->ktapi->get_folder_by_id($newFolderId);
//        $listSubDocs = $newFolder->get_listing(1, 'D');
//        $this->assertTrue(count($listSubDocs) == 1);
//
//        // Delete and expunge documents and folder
//        $this->deleteDocument($doc1);
//        $this->deleteDocument($doc2);
//        $this->deleteDocument($doc3);
//        $this->deleteDocument($doc4);
//        $targetFolder->delete('Testing bulk copy');
//        $folder1->delete('Testing bulk copy');
//    }
//
//    /**
//     * Test the bulk move functionality
//     */
//    function testMove()
//    {
//        // Create documents
//        $doc1 = $this->createDocument('Test Doc One', 'testdoc1.txt');
//        $doc2 = $this->createDocument('Test Doc Two', 'testdoc2.txt');
//        $doc3 = $this->createDocument('Test Doc Three', 'testdoc3.txt');
//        $folder1 = $this->root->add_folder("New move folder");
//        $this->assertNotError($newFolder);
//        if(PEAR::isError($newFolder)) return;
//
//        $doc4 = $this->createDocument('Test Doc Four', 'testdoc4.txt', $folder1);
//
//        // Add a folder
//        $targetFolder = $this->root->add_folder("New target folder");
//        $this->assertNotError($newFolder);
//        if(PEAR::isError($newFolder)) return;
//
//        $aItems = array($doc1, $doc2, $doc3, $folder1);
//
//        // Copy documents and folder into target folder
//        $res = $this->bulk->move($aItems, $targetFolder, 'Testing bulk move');
//
//        $this->assertTrue(empty($res));
//
//        // Check document has been moved not copied
//        $detail = $doc1->get_detail();
//        $this->assertFalse($detail['folder_id'] == $this->root->get_folderid());
//        $this->assertTrue($detail['folder_id'] == $targetFolder->get_folderid());
//
//        // Check folder has been moved not copied
//        $this->assertFalse($folder1->get_parent_folder_id() == $this->root->get_folderid());
//        $this->assertTrue($folder1->get_parent_folder_id() == $targetFolder->get_folderid());
//
//        // Check the documents copied
//        $listDocs = $targetFolder->get_listing(1, 'D');
//        $this->assertTrue(count($listDocs) == 3);
//
//        // Check the folder copied
//        $listFolders = $targetFolder->get_listing(1, 'F');
//        $this->assertTrue(count($listFolders) == 1);
//
//        // Check the document contained in the folder copied
//        $newFolderId = $listFolders[0]['id'];
//        $newFolder = $this->ktapi->get_folder_by_id($newFolderId);
//        $listSubDocs = $newFolder->get_listing(1, 'D');
//        $this->assertTrue(count($listSubDocs) == 1);
//
//        // Delete and expunge documents and folder
//        $this->deleteDocument($doc1);
//        $this->deleteDocument($doc2);
//        $this->deleteDocument($doc3);
//        $this->deleteDocument($doc4);
//        $targetFolder->delete('Testing bulk copy');
//        $folder1->delete('Testing bulk copy');
//    }
//
//    /**
//     * Test the bulk checkout and cancel checkout functionality
//     */
//    function testCheckout()
//    {
//        // Create documents
//        $doc1 = $this->createDocument('Test Doc One', 'testdoc1.txt');
//        $doc2 = $this->createDocument('Test Doc Two', 'testdoc2.txt');
//        $doc3 = $this->createDocument('Test Doc Three', 'testdoc3.txt');
//        $folder1 = $this->root->add_folder("New test folder");
//        $this->assertNotError($newFolder);
//        if(PEAR::isError($newFolder)) return;
//
//        $doc4 = $this->createDocument('Test Doc Four', 'testdoc4.txt', $folder1);
//
//        $aItems = array($doc1, $doc2, $doc3, $folder1);
//
//        // Checkout documents and folder
//        $res = $this->bulk->checkout($aItems, 'Testing bulk checkout');
//
//        $this->assertTrue(empty($res));
//
//        $this->assertTrue($doc1->is_checked_out());
//        $this->assertTrue($doc2->is_checked_out());
//        $this->assertTrue($doc3->is_checked_out());
//
//        // refresh the doc4 document object to reflect changes
//        $doc4 = KTAPI_Document::get($this->ktapi, $doc4->get_documentid());
//        $this->assertTrue($doc4->is_checked_out());
//
//        $res = $this->bulk->undo_checkout($aItems, 'Testing bulk undo / cancel checkout');
//
//        $this->assertTrue(empty($res));
//
//        $this->assertFalse($doc1->is_checked_out());
//        $this->assertFalse($doc2->is_checked_out());
//        $this->assertFalse($doc3->is_checked_out());
//
//        // refresh the doc4 document object to reflect changes
//        $doc4 = KTAPI_Document::get($this->ktapi, $doc4->get_documentid());
//        $this->assertFalse($doc4->is_checked_out());
//
//        // Delete and expunge documents and folder
//        $this->deleteDocument($doc1);
//        $this->deleteDocument($doc2);
//        $this->deleteDocument($doc3);
//        $this->deleteDocument($doc4);
//        $folder1->delete('Testing bulk checkout');
//    }
//
//    /**
//     * Test the bulk immute functionality
//     */
//    function testImmute()
//    {
//        // Create documents
//        $doc1 = $this->createDocument('Test Doc One', 'testdoc1.txt');
//        $doc2 = $this->createDocument('Test Doc Two', 'testdoc2.txt');
//        $doc3 = $this->createDocument('Test Doc Three', 'testdoc3.txt');
//        $folder1 = $this->root->add_folder("New test folder");
//        $this->assertNotError($newFolder);
//        if(PEAR::isError($newFolder)) return;
//
//        $doc4 = $this->createDocument('Test Doc Four', 'testdoc4.txt', $folder1);
//
//        $aItems = array($doc1, $doc2, $doc3, $folder1);
//
//        // Immute documents
//        $res = $this->bulk->immute($aItems);
//
//        $this->assertTrue(empty($res));
//
//        $this->assertTrue($doc1->isImmutable());
//        $this->assertTrue($doc2->isImmutable());
//        $this->assertTrue($doc3->isImmutable());
//
//        // refresh the doc4 document object to reflect changes
//        $doc4 = KTAPI_Document::get($this->ktapi, $doc4->get_documentid());
//        $this->assertTrue($doc4->isImmutable());
//
//        // remove immutability for deletion
//        $doc1->unimmute();
//        $doc2->unimmute();
//        $doc3->unimmute();
//        $doc4->unimmute();
//
//        // Delete and expunge documents and folder
//        $this->deleteDocument($doc1);
//        $this->deleteDocument($doc2);
//        $this->deleteDocument($doc3);
//        $this->deleteDocument($doc4);
//        $folder1->delete('Testing bulk checkout');
//    }
//
//    /**
//     * Test the bulk delete functionality
//     */
//    function testDelete()
//    {
//        // Create documents
//        $doc1 = $this->createDocument('Test Doc One', 'testdoc1.txt');
//        $doc2 = $this->createDocument('Test Doc Two', 'testdoc2.txt');
//        $doc3 = $this->createDocument('Test Doc Three', 'testdoc3.txt');
//        $folder1 = $this->root->add_folder("New test folder");
//        $this->assertNotError($newFolder);
//        if(PEAR::isError($newFolder)) return;
//
//        $doc4 = $this->createDocument('Test Doc Four', 'testdoc4.txt', $folder1);
//
//        $aItems = array($doc1, $doc2, $doc3, $folder1);
//
//        // Delete documents and folder
//        $res = $this->bulk->delete($aItems, 'Testing bulk delete');
//
//        $this->assertTrue(empty($res));
//
//        // Check documents have been deleted
//        $this->assertTrue($doc1->is_deleted());
//        $this->assertTrue($doc2->is_deleted());
//        $this->assertTrue($doc3->is_deleted());
//
//        // refresh the doc4 document object to reflect changes
//        $doc4 = KTAPI_Document::get($this->ktapi, $doc4->get_documentid());
//        $this->assertTrue($doc4->is_deleted());
//
//        // Check folder has been deleted
//        $folder = $this->ktapi->get_folder_by_name('New test folder');
//        $this->assertError($folder);
//
//        // Expunge documents
//        $doc1->expunge();
//        $doc2->expunge();
//        $doc3->expunge();
//        $doc4->expunge();
//    }
//
//    /**
//     * Test the bulk archive functionality
//     */
//    function testArchive()
//    {
//        // Create documents
//        $doc1 = $this->createDocument('Test Doc One', 'testdoc1.txt');
//        $doc2 = $this->createDocument('Test Doc Two', 'testdoc2.txt');
//        $doc3 = $this->createDocument('Test Doc Three', 'testdoc3.txt');
//        $folder1 = $this->root->add_folder("New test folder");
//        $this->assertNotError($newFolder);
//        if(PEAR::isError($newFolder)) return;
//
//        $doc4 = $this->createDocument('Test Doc Four', 'testdoc4.txt', $folder1);
//
//        $aItems = array($doc1, $doc2, $doc3, $folder1);
//
//        // Archive documents and folder
//        $res = $this->bulk->archive($aItems, 'Testing bulk archive');
//
//        $this->assertTrue(empty($res));
//
//        $document1 = $doc1->getObject();
//        $this->assertTrue($document1->getStatusID() == 4);
//
//        // refresh the doc4 document object to reflect changes
//        $doc4 = KTAPI_Document::get($this->ktapi, $doc4->get_documentid());
//        $document4 = $doc4->getObject();
//        $this->assertTrue($document4->getStatusID() == 4);
//
//        // Restore for deletion
//        $doc1->restore();
//        $doc2->restore();
//        $doc3->restore();
//        $doc4->restore();
//
//        // Delete and expunge documents and folder
//        $this->deleteDocument($doc1);
//        $this->deleteDocument($doc2);
//        $this->deleteDocument($doc3);
//        $this->deleteDocument($doc4);
//        $folder1->delete('Testing bulk archive');
//    }
    
    /**
     * Helper function to create a document
     */
    function createDocument($title, $filename, $folder = null)
    {
        if(is_null($folder)){
            $folder = $this->root;
        }

        // Create a new document
        $randomFile = $this->createRandomFile();
        $this->assertTrue(is_file($randomFile));

        if ($this->esig_enabled)
        {
            $document = $folder->add_document($title, $filename, 'Default', $randomFile, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        }
        else
        {
            $document = $folder->add_document($title, $filename, 'Default', $randomFile);
        }
        $this->assertNotError($document);

        @unlink($randomFile);
        if(PEAR::isError($document)) return false;

        return $document;
    }

    /**
     * Helper function to delete docs
     */
    function deleteDocument($document)
    {
        $document->delete('Testing API');
        $document->expunge();
    }

    function createRandomFile($content = 'this is some text', $uploadDir = null) {
        if(is_null($uploadDir)){
           $uploadDir = dirname(__FILE__);
        }
        $temp = tempnam($uploadDir, 'myfile');
        $fp = fopen($temp, 'wt');
        fwrite($fp, $content);
        fclose($fp);
        return $temp;
    }
}
?>