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
class APIAutoTestCase extends KTUnitTestCase {

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
    * This method sets up the KT session
    *
    */
    public function setUp() {
        $this->ktapi = new KTAPI();
        $this->session = $this->ktapi->start_session(KT_TEST_USER, KT_TEST_PASS);
        $this->root = $this->ktapi->get_root_folder();
        $this->assertTrue($this->root instanceof KTAPI_Folder);
    }

    /**
    * This method emds the KT session
    *
    */
    public function tearDown() {
        $this->session->logout();
    }
   
	function testJunkanonymous_login() { 
		$result = $this->ktapi->anonymous_login(null);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 1);
	}

	function tesRealanonymous_login() { 
		$result = $this->ktapi->anonymous_login($ip);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function testJunklogin() { 
		$result = $this->ktapi->login(null, null, null);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 1);
	}

	function tesReallogin() { 
		$result = $this->ktapi->login($username, $password, $ip);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function testJunkget_folder_detail_by_name() { 
		$result = $this->ktapi->get_folder_detail_by_name(null);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 1);
	}

	function tesRealget_folder_detail_by_name() { 
		$result = $this->ktapi->get_folder_detail_by_name($folder_name);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealcreate_document_shortcut() { 
		$result = $this->ktapi->create_document_shortcut($target_folder_id, $source_document_id);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealdelete_folder() {
        $result = $this->ktapi->delete_folder($folder_id, $reason, KT_TEST_USER, KT_TEST_PASS);
        $this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealrename_folder() { 
		$result = $this->ktapi->rename_folder($folder_id, $newname, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealcopy_folder() { 
		$result = $this->ktapi->copy_folder($source_id, $target_id, $reason, KT_TEST_USER, KT_TEST_PASS);
        $this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}


	function tesRealmove_folder() { 
		$result = $this->ktapi->move_folder($source_id, $target_id, $reason, KT_TEST_USER, KT_TEST_PASS);
        $this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function testJunkget_document_types() { 
		$result = $this->ktapi->get_document_types(null);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealget_document_types() { 
		$result = $this->ktapi->get_document_types($session_id);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function testJunkget_document_detail_by_filename() {
		$result = $this->ktapi->get_document_detail_by_filename(null, null, null);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 1);
	}

	function tesRealget_document_detail_by_filename() { 
		$result = $this->ktapi->get_document_detail_by_filename($folder_id, $filename, $detail);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function testJunkget_document_detail_by_title() { 
		$result = $this->ktapi->get_document_detail_by_title(null, null, null);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 1);
	}

	function tesRealget_document_detail_by_title() { 
		$result = $this->ktapi->get_document_detail_by_title($folder_id, $title, $detail);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function testJunkget_document_detail_by_name() { 
		$result = $this->ktapi->get_document_detail_by_name(null, null, null, null);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 1);
	}

	function tesRealget_document_detail_by_name() { 
		$result = $this->ktapi->get_document_detail_by_name($folder_id, $document_name, $what, $detail);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealget_document_shortcuts() {
		$result = $this->ktapi->get_document_shortcuts($document_id);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function testJunkadd_document() {
		$result = $this->ktapi->add_document(null, null, null, null, null, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 1);
	}

	function tesRealadd_document() { 
		$result = $this->ktapi->add_document($folder_id, $title, $filename, $documenttype, $tempfilename,
                                             KT_TEST_USER, KT_TEST_PASS, 'Testing API');
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealadd_small_document_with_metadata() { 
		$result = $this->ktapi->add_small_document_with_metadata($folder_id, $title, $filename, $documenttype, $base64, $metadata, $sysdata);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function testJunkadd_document_with_metadata() { 
		$result = $this->ktapi->add_document_with_metadata(null, null, null, null, null, null, null);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 1);
	}

	function tesRealadd_document_with_metadata() { 
		$result = $this->ktapi->add_document_with_metadata($folder_id, $title, $filename, $documenttype, $tempfilename, $metadata, $sysdata);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealadd_small_document() { 
		$result = $this->ktapi->add_small_document($folder_id, $title, $filename, $documenttype, $base64);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function testJunkcheckin_document() { 
		$result = $this->ktapi->checkin_document(null, null, null, null, null, KT_TEST_USER, KT_TEST_PASS);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 1);
	}

	function tesRealcheckin_document() { 
		$result = $this->ktapi->checkin_document($document_id, $filename, $reason, $tempfilename, $major_update, KT_TEST_USER, KT_TEST_PASS);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealcheckin_small_document_with_metadata() { 
		$result = $this->ktapi->checkin_small_document_with_metadata($document_id, $filename, $reason, $base64, $major_update, $metadata, $sysdata);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealcheckin_document_with_metadata() { 
		$result = $this->ktapi->checkin_document_with_metadata($document_id, $filename, $reason, $tempfilename, $major_update, $metadata, $sysdata);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealcheckin_small_document() { 
		$result = $this->ktapi->checkin_small_document($document_id, $filename, $reason, $base64, $major_update);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealcheckout_document() { 
		$result = $this->ktapi->checkout_document($document_id, $reason, $download);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealcheckout_small_document() { 
		$result = $this->ktapi->checkout_small_document($document_id, $reason, $download);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealundo_document_checkout() { 
		$result = $this->ktapi->undo_document_checkout($document_id, $reason, KT_TEST_USER, KT_TEST_PASS);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealdownload_document() { 
		$result = $this->ktapi->download_document($document_id, $version);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealdownload_small_document() { 
		$result = $this->ktapi->download_small_document($document_id, $version);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealdelete_document() { 
		$result = $this->ktapi->delete_document($document_id, $reason);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealchange_document_type() { 
		$result = $this->ktapi->change_document_type($document_id, $documenttype);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealmove_document() { 
		$result = $this->ktapi->move_document($document_id, $folder_id, $reason, $newtitle, $newfilename);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealrename_document_title() { 
		$result = $this->ktapi->rename_document_title($document_id, $newtitle);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealrename_document_filename() { 
		$result = $this->ktapi->rename_document_filename($document_id, $newfilename);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealchange_document_owner() { 
		$result = $this->ktapi->change_document_owner($document_id, $username, $reason);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealstart_document_workflow() { 
		$result = $this->ktapi->start_document_workflow($document_id, $workflow);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealdelete_document_workflow() { 
		$result = $this->ktapi->delete_document_workflow($document_id, 'Testing API', KT_TEST_USER, KT_TEST_PASS, true);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealperform_document_workflow_transition() { 
		$result = $this->ktapi->perform_document_workflow_transition($document_id, $transition, $reason);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealget_document_metadata() { 
		$result = $this->ktapi->get_document_metadata($document_id);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealupdate_document_metadata() { 
		$result = $this->ktapi->update_document_metadata($document_id, $metadata, $sysdata);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealget_document_workflow_state() { 
		$result = $this->ktapi->get_document_workflow_state($document_id);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealget_document_transaction_history() { 
		$result = $this->ktapi->get_document_transaction_history($document_id);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealget_document_version_history() { 
		$result = $this->ktapi->get_document_version_history($document_id);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealget_document_links() { 
		$result = $this->ktapi->get_document_links($document_id);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}


	function tesRealunlink_documents() { 
		$result = $this->ktapi->unlink_documents($parent_document_id, $child_document_id);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesReallink_documents() { 
		$result = $this->ktapi->link_documents($parent_document_id, $child_document_id, $type);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function tesRealget_client_policies() { 
		$result = $this->ktapi->get_client_policies($client);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

	function testJunksearch() { 
		$result = $this->ktapi->search(null, null);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 1);
	}

	function tesRealsearch() { 
		$result = $this->ktapi->search($query, $options);
		$this->assertIsA($result, 'array');
		$this->assertEqual($result['status_code'], 0);
	}

}
?>