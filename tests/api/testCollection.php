<?php
require_once (dirname(__FILE__) . '/../test.php');
require_once (KT_DIR . '/ktapi/ktapi.inc.php');
class APICollectionTestCase extends KTUnitTestCase {

	function testCollection() {
        $ktapi = new KTAPI();
        $session = $ktapi->start_session('admin', 'admin');
        $this->assertNotError($session);
        
        $columns = $ktapi->get_columns_for_view();
        
        $this->assertIsA($columns, 'array');
    }

}
?>
